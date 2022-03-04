<?php defined( 'ABSPATH' ) || exit;

/**
 * @since   2.3.2
 */
class AFL_WC_UTM_LICENSE_MANAGER {

	protected static $_instance = null;

	const OPTION_KEY_PREFIX = 'afl_wc_utm_';

	const STATUS_ACTIVE = 'active';
	const STATUS_INACTIVE = 'inactive';
	const STATUS_ERROR = 'error';
	const STATUS_ACTIVATED = 'activated';
	const STATUS_DEACTIVATED = 'deactivated';

	const ERROR_EXCEPTION = 'error_exception';
	const ERROR_LICENSE_INVALID = 'error_license_invalid';
	const ERROR_LICENSE_EMPTY = 'error_license_empty';
	const ERROR_ACTIVATION = 'error_activation';
	const ERROR_DEACTIVATION = 'error_deactivation';
	const ERROR_API_SERVER = 'error_api_server';

	const TRANSIENT_EXPIRY = 600;
	const RECHECK_MAX = 30;

	private static $config;

	public function __construct(){

	}

	public static function instance() {
		if(is_null( self::$_instance )) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function init($config){

		self::$config = AFL_WC_UTM_UTIL::merge_default($config, array(
			'software_type' => '',
			'software_slug' => '',
			'software_version' => '',
			'software_basename' => '',
			'api_base_url' => ''
		));

	}

	public static function register_hooks(){

		add_action('admin_init', array(__CLASS__, 'action_schedule_check_license_status'));
		add_action('afl_wc_utm_event_check_license_status', array(__CLASS__, 'check_license_status'));

		add_filter('plugins_api', array(__CLASS__, 'get_plugin_information'), 20, 3);
		add_filter('pre_set_site_transient_update_plugins', array(__CLASS__, 'check_software_updates'), 10, 1);

	}

	public static function action_schedule_check_license_status(){

		if ( !wp_next_scheduled( 'afl_wc_utm_event_check_license_status' ) ) :
			wp_schedule_event( time(), 'daily', 'afl_wc_utm_event_check_license_status' );
		endif;

	}

	public static function delete_schedule_check_license_status(){

		wp_clear_scheduled_hook( 'afl_wc_utm_event_check_license_status' );

	}

	public static function get_domain_instance(){

			$domain = '';

			$home_url = parse_url(get_option('home', ''));

			if (isset($home_url['host'])) :

				$domain = $home_url['host'];

				if (isset($home_url['path'])) :
					$domain .= $home_url['path'];
				endif;

			endif;

			return $domain;

	}

	public static function get_license_data(){
		$license = get_option(self::OPTION_KEY_PREFIX . 'license', array());

		$license = AFL_WC_UTM_UTIL::merge_default($license, array(
			'key' => '',
			'activation_status' => '',
			'license_status' => '',
			'recheck' => ''
		));

		return $license;
	}

	public static function save_license_data($data = array()){

		$data = AFL_WC_UTM_UTIL::merge_default($data, array(
			'key' => '',
			'activation_status' => '',
			'license_status' => '',
			'recheck' => ''
		));

		return update_option(self::OPTION_KEY_PREFIX . 'license', $data);
	}

	public static function delete_license_data(){
		return delete_option(self::OPTION_KEY_PREFIX . 'license');
	}

	public static function is_license_activated(){

		$license = self::get_license_data();

		if ($license['activation_status'] === self::STATUS_ACTIVATED) :
			return true;
		endif;

		return false;
	}

	public static function is_license_active(){

		$license = self::get_license_data();

		if ($license['license_status'] === self::STATUS_ACTIVE) :
			return true;
		endif;

		return false;
	}

	public static function delete_all_transients(){

		delete_transient(self::OPTION_KEY_PREFIX . 'plugin_update_http_response');
		delete_transient(self::OPTION_KEY_PREFIX . 'plugin_information_http_response');
		delete_transient(self::OPTION_KEY_PREFIX . 'license_status_http_response');

	}

	public static function activate_license($license_key = ''){

		try {

			//delete
			self::delete_all_transients();

			if (empty($license_key)) :
				return new WP_Error(self::ERROR_LICENSE_EMPTY, 'License key is empty');
			endif;

			$license_key = sanitize_text_field($license_key);

			$http_body = array(
				'license_key' => $license_key,
				'license_domain' => self::get_domain_instance(),
				'license_product_slug' => self::$config['software_slug'],
				'license_product_version' => self::$config['software_version']
			);

			$http_response = wp_remote_post(self::$config['api_base_url'] . 'activate-license', array(
				'body' => $http_body
			));

			//check http response
			if (is_wp_error($http_response) || empty($http_response['response']['code'])):
					return new WP_Error(self::ERROR_EXCEPTION, 'Unable to contact license server.');
			endif;

			$response_body = wp_remote_retrieve_body($http_response);
			$response_json = json_decode($response_body, true);

			if (!isset($response_json['code'])) :
				return new WP_Error(self::ERROR_EXCEPTION, 'License server is unable to process.');
			endif;

			if ($http_response['response']['code'] === 200 && $response_json['code'] == 'ok') :

				//save license
				$license_data = array(
					'key' => $license_key,
					'activation_status' => self::STATUS_ACTIVATED,
					'license_status' => self::STATUS_ACTIVE,
					'recheck' => 0
				);

				self::save_license_data($license_data);

				return $license_data;

			else:

				//error
				return new WP_Error(self::ERROR_LICENSE_INVALID, 'License is invalid / expired / reached the maximum number of domains.');

			endif;

		} catch (\Exception $e) {

			return new WP_Error(self::ERROR_EXCEPTION, 'Unable to activate license because of code exception.');

		}

	}

	public static function deactivate_license(){

		try {

			//delete
			self::delete_all_transients();

			$license_data = self::get_license_data();

			if (empty($license_data['key'])) :
				return new WP_Error(self::ERROR_LICENSE_EMPTY, 'License key is empty.');
			endif;

			//delete license
			self::delete_license_data();

			$http_body= array(
				'license_key' => $license_data['key'],
				'license_domain' => self::get_domain_instance(),
				'license_product_slug' => self::$config['software_slug'],
				'license_product_version' => self::$config['software_version']
			);

			$http_response = wp_remote_post(self::$config['api_base_url'] . 'deactivate-license', array(
				'body' => $http_body
			));

			//check http response
			if (is_wp_error($http_response) || empty($http_response['response']['code'])):
					return new WP_Error(self::ERROR_EXCEPTION, 'Unable to contact license server.');
			endif;

			$response_body = wp_remote_retrieve_body($http_response);
			$response_json = json_decode($response_body, true);

			if (!isset($response_json['code'])) :
				return new WP_Error(self::ERROR_EXCEPTION, 'License server is unable to process.');
			endif;

			if ($http_response['response']['code'] === 200 && $response_json['code'] == 'ok') :

				//successfully deactivated
				return true;

			else:

				return new WP_Error(self::ERROR_EXCEPTION, 'Your license key has been removed but we are unable to deactivate.');

			endif;

			return true;

		} catch (\Exception $e) {

			return new WP_Error(self::ERROR_EXCEPTION, 'Unable to deactivate license status due to code exception.');

		}

	}

	public static function check_license_status($autocheck = true){

		try {

			$license_data = self::get_license_data();

			if (empty($license_data['key'])) :
				return false;
			endif;

			if (empty($license_data['recheck'])) :
				$license_data['recheck'] = 0;
			endif;

			if ($autocheck === true && $license_data['license_status'] == self::STATUS_INACTIVE && absint($license_data['recheck']) >= self::RECHECK_MAX) :
				self::delete_all_transients();
				return false;
			endif;

			$response_json = array();

			if ($autocheck === true) :
				$response_json = get_transient(self::OPTION_KEY_PREFIX . 'license_status_http_response');
			endif;

			if (!isset($response_json['code'])) :

				$http_body= array(
					'license_key' => $license_data['key'],
					'license_domain' => self::get_domain_instance(),
					'license_product_slug' => self::$config['software_slug'],
					'license_product_version' => self::$config['software_version']
				);

				$http_response = wp_remote_get(self::$config['api_base_url'] . 'license-status', array(
					'body' => $http_body
				));

				//check http response
				if (is_wp_error($http_response) || empty($http_response['response']['code'])):
						throw new \Exception('License server is unable to process.');
				endif;

				$response_body = wp_remote_retrieve_body($http_response);
				$response_json = json_decode($response_body, true);

				if (!isset($response_json['code'])) :
					throw new \Exception('License server is unable to process.');
				endif;

				//set transient
				set_transient(self::OPTION_KEY_PREFIX . 'license_status_http_response', $response_json, self::TRANSIENT_EXPIRY);

			endif;

			if ($response_json['code'] == 'ok') :

				if (isset($response_json['data']['license_status']) && in_array($response_json['data']['license_status'], array(self::STATUS_ACTIVE, self::STATUS_INACTIVE))) :

					$license_data['license_status'] = sanitize_text_field($response_json['data']['license_status']);

					if ($license_data['license_status'] == self::STATUS_ACTIVE) :
						$license_data['recheck'] = 0;
					else:
						$license_data['recheck'] += 1;
					endif;

					self::save_license_data($license_data);

				endif;

			endif;

			if ($autocheck === false) :

				if ($license_data['license_status'] == self::STATUS_ACTIVE) :
					return true;
				else:
					return new WP_Error(self::ERROR_LICENSE_INVALID, 'License is invalid / expired / reached the maximum number of domains.');
				endif;

			endif;

			return true;

		} catch (\Exception $e) {

			if ($autocheck === false) :
				return new WP_Error(self::ERROR_EXCEPTION, 'License server is unable to process.');
			endif;

		}

		return false;

	}

	public static function check_software_updates($transient){

		try {

			$license_data = self::get_license_data();

			//dont check for updates
			if ($license_data['license_status'] != self::STATUS_ACTIVE) :
				return $transient;
			endif;

			$response_json = get_transient(self::OPTION_KEY_PREFIX . 'plugin_update_http_response');

			if (!isset($response_json['code'])) :

				$http_body= array(
					'license_key' => $license_data['key'],
					'license_domain' => self::get_domain_instance(),
					'license_product_slug' => self::$config['software_slug'],
					'license_product_version' => self::$config['software_version']
				);

				$http_response = wp_remote_get(self::$config['api_base_url'] . 'plugin-updates', array(
					'body' => $http_body
				));

				//check http response
				if (is_wp_error($http_response) || empty($http_response['response']['code'])):
						throw new \Exception('Unable to contact license server.');
				endif;

				$response_body = wp_remote_retrieve_body($http_response);
				$response_json = json_decode($response_body, true);

				if (!isset($response_json['code'])) :
					throw new \Exception('License server is unable to process.');
				endif;

				//set transient
				set_transient(self::OPTION_KEY_PREFIX . 'plugin_update_http_response', $response_json, self::TRANSIENT_EXPIRY);

			endif;

			$plugin_response = new stdClass();
			$plugin_response->id 										= self::$config['software_basename'];
			$plugin_response->plugin        				= self::$config['software_basename'];
			$plugin_response->slug          				= self::$config['software_slug'];
			$plugin_response->new_version						= self::$config['software_version'];
			$plugin_response->url										= '';
			$plugin_response->package								= '';
			$plugin_response->tested								= '';
			$plugin_response->requires_php					= '';
			$plugin_response->icons									= '';
			$plugin_response->banners								= '';

			$plugin_response->compatibility					= new stdClass();

			if ($response_json['code'] == 'ok') :

				$plugin_response->new_version   				= isset($response_json['data']['version']) ? $response_json['data']['version'] : '';
				$plugin_response->url   								= isset($response_json['data']['url']) ? $response_json['data']['url'] : '';
				$plugin_response->package       				= isset($response_json['data']['package']) ? $response_json['data']['package'] : '';
				$plugin_response->requires        			= isset($response_json['data']['requires']) ? $response_json['data']['requires'] : '';
				$plugin_response->tested        				= isset($response_json['data']['tested']) ? $response_json['data']['tested'] : '';
				$plugin_response->requires_php  				= isset($response_json['data']['requires_php']) ? $response_json['data']['requires_php'] : '';
				$plugin_response->icons         				= isset($response_json['data']['icons']) ? $response_json['data']['icons'] : '';

			endif;

			if (version_compare(self::$config['software_version'], $plugin_response->new_version, '<')) :
				//has update
				$transient->response[self::$config['software_basename']] = $plugin_response;
			else:
				//no update
				$transient->no_update[self::$config['software_basename']] = $plugin_response;
			endif;

			$transient->checked[self::$config['software_basename']] = $plugin_response->new_version;

		} catch (\Exception $e) {

		}

		return $transient;
	}

	public static function get_plugin_information($res, $action, $args){

		try {

			if ($action !== 'plugin_information') :
				return $res;
			endif;

			if (!is_object($args) || !isset($args->slug) || $args->slug !== self::$config['software_slug']):
				return $res;
			endif;

			$license_data = self::get_license_data();

			//dont get plugin info
			if ($license_data['license_status'] != self::STATUS_ACTIVE) :
				return new WP_Error(self::ERROR_API_SERVER, 'Sorry, your license is not active.');
			endif;

			//get transient
			$response_json = get_transient(self::OPTION_KEY_PREFIX . 'plugin_information_http_response');

			if (!isset($response_json['code'])) :

				$http_body = array(
					'license_key' => $license_data['key'],
					'license_product_slug' => self::$config['software_slug'],
					'license_domain' => self::get_domain_instance(),
					'license_product_version' => self::$config['software_version']
				);

				$http_response = wp_remote_get(self::$config['api_base_url'] . 'plugin-information', array(
					'body' => $http_body
				));

				//check http response
				if (is_wp_error($http_response) || empty($http_response['response']['code'])):
						return new WP_Error(self::ERROR_API_SERVER, 'Unable to contact license server.');
				endif;

				$response_body = wp_remote_retrieve_body($http_response);
				$response_json = json_decode($response_body, true);

				if (!isset($response_json['code'])) :
					return new WP_Error(self::ERROR_API_SERVER, 'License server is unable to process.');
				endif;

				set_transient(self::OPTION_KEY_PREFIX . 'plugin_information_http_response', $response_json, self::TRANSIENT_EXPIRY);

			endif;

			if ($response_json['code'] == 'ok') :

				$html_author = isset($response_json['data']['author']) && isset($response_json['data']['author_homepage']) ? sprintf('<a href="%1$s" target="_blank">%2$s</a>', esc_url($response_json['data']['author_homepage']), esc_html($response_json['data']['author'])) : '';

				$plugin_response = new stdClass();
				$plugin_response->id            				= self::$config['software_basename'];
				$plugin_response->slug          				= self::$config['software_slug'];
				$plugin_response->plugin        				= self::$config['software_basename'];
				$plugin_response->name   								= isset($response_json['data']['title']) ? $response_json['data']['title'] : '';
				$plugin_response->version   						= isset($response_json['data']['version']) ? $response_json['data']['version'] : '';
				$plugin_response->download_link  				= isset($response_json['data']['download_link']) ? $response_json['data']['download_link'] : '';
				$plugin_response->requires        			= isset($response_json['data']['requires']) ? $response_json['data']['requires'] : '';
				$plugin_response->tested        				= isset($response_json['data']['tested']) ? $response_json['data']['tested'] : '';
				$plugin_response->requires_php  				= isset($response_json['data']['requires_php']) ? $response_json['data']['requires_php'] : '';
				$plugin_response->last_updated  				= isset($response_json['data']['last_updated']) ? $response_json['data']['last_updated'] : '';
				$plugin_response->icons         				= isset($response_json['data']['icons']) ? $response_json['data']['icons'] : array();
				$plugin_response->banners       				= isset($response_json['data']['banners']) ? $response_json['data']['banners'] : array();
				$plugin_response->banners_rtl   				= isset($response_json['data']['banners_rtl']) ? $response_json['data']['banners_rtl'] : array();
				$plugin_response->author  							= $html_author;
				$plugin_response->homepage  						= isset($response_json['data']['homepage']) ? $response_json['data']['homepage'] : '';
				$plugin_response->sections  						= isset($response_json['data']['sections']) ? $response_json['data']['sections'] : '';
				$plugin_response->compatibility 				= new stdClass();

				return $plugin_response;

			endif;

		} catch (\Exception $e) {

			return new WP_Error(self::ERROR_EXCEPTION, 'Error fetching plugin details from license server.');

		}

		return $res;

	}

	public static function display_license_html_form(){

		self::handle_license_html_form();

		$license_data = self::get_license_data();

		$form = array(
			'form' => '',
			'form_nonce' => '',
			'enable_license_input' => true,
			'submit_button' => '',
			'license_status_style' => 'background-color: #424242;',
			'license_status' => strtoupper(self::STATUS_INACTIVE),
			'license_key' => self::mask_license_key($license_data['key'])
		);

		if ($license_data['license_status'] == self::STATUS_ACTIVE) :

			$form['license_status_style'] = 'background-color: #43a047;';
			$form['license_status'] = strtoupper($license_data['license_status']);

		endif;

		if ($license_data['activation_status'] == self::STATUS_ACTIVATED) :

			$form['form'] = 'afl_wc_utm_form_license_deactivate';
			$form['form_nonce'] = wp_create_nonce('afl_wc_utm_form_license_deactivate');
			$form['submit_button'] = 'Deactivate';
			$form['enable_license_input'] = false;

		else:

			$form['form'] = 'afl_wc_utm_form_license_activate';
			$form['form_nonce'] = wp_create_nonce('afl_wc_utm_form_license_activate');
			$form['submit_button'] = 'Activate';

		endif;

		$html = <<<'EOT'
<h3>Software License Key</h3>
<p>Enter your License Key to access features and receive plugin updates.</p>
<form method="post" id="afl-wc-utm-form-license">
	<input type="text" id="afl-wc-utm-input-license_key" name="license_key" class="regular-text" autocomplete="off" placeholder="Enter your license key here (xxxxx-xxxxx-xxxxx)" style="%1$s">
	<input type="hidden" name="form" value="%2$s">
	<input type="hidden" name="_wpnonce" value="%3$s">
	<div style="margin-top: 1rem"><input type="submit" value="%4$s" class="button button-primary"></div>
	<div style="margin-top: 30px; font-size: 1rem"><b>License Status:</b> <span style="%5$s padding: 2px 12px; color: #fff; border-radius: 12px; font-size: 0.9rem">%6$s</span></div>
	<div style="margin-top: 20px; font-size: 1rem"><b>License Key:</b> <span style="font-size: 0.9rem">%7$s</span></div>
</form>
EOT;

		printf($html,
			esc_attr($form['enable_license_input'] ? '' : 'display:none'),
			esc_attr($form['form']),
			esc_attr($form['form_nonce']),
			esc_attr($form['submit_button']),
			esc_attr($form['license_status_style']),
			esc_attr($form['license_status']),
			esc_attr($form['license_key'])
		);

		if ($license_data['activation_status'] == self::STATUS_ACTIVATED && $license_data['license_status'] != self::STATUS_ACTIVE) :

		$recheck_html = <<<'EOT'
<div>
<form method="post" id="afl-wc-utm-form-license-check-status">
	<input type="hidden" name="form" value="%1$s">
	<input type="hidden" name="_wpnonce" value="%2$s">
	<div style="margin-top: 1rem"><input type="submit" value="%3$s" class="button button-secondary"></div>
</form>
</div>
EOT;

		$recheck_form = array(
			'form' => 'afl_wc_utm_form_license_check_status',
			'form_nonce' => wp_create_nonce('afl_wc_utm_form_license_check_status'),
			'submit_button' => __('Re-check License Status')
		);

		printf($recheck_html,
			esc_attr($recheck_form['form']),
			esc_attr($recheck_form['form_nonce']),
			esc_attr($recheck_form['submit_button'])
		);

		endif;

	}

	public static function handle_license_html_form(){

		if (empty($_POST)) :
			return;
		endif;

		$post = wp_unslash($_POST);

		if (!empty($post['form']) ) :

			$alert = new AFL_WC_UTM_ALERT();

			//verify capability
			if (!current_user_can('manage_options')) :

				$alert->add_error_message('You do not have the permission.');
				$alert->display(true);
				return false;

			endif;

			switch($post['form']):

				//activate
				case 'afl_wc_utm_form_license_activate':

					//verify nonce
					if (!wp_verify_nonce($post['_wpnonce'], 'afl_wc_utm_form_license_activate')) :

						$alert->add_error_message('Your session has expired. Please refresh page.');
						$alert->display(true);
						return false;

					endif;

					$result = self::activate_license($post['license_key']);

					if (is_wp_error($result)) :

						$alert->add_error_message($result->get_error_message());

					elseif (empty($result)) :

						$alert->add_error_message('Failed to activate license.');

					else:

						$alert->add_success_message('Your license has been activated.');

					endif;

					$alert->display(true);

					break;

				//deactivate
				case 'afl_wc_utm_form_license_deactivate':

					//verify nonce
					if (!wp_verify_nonce($post['_wpnonce'], 'afl_wc_utm_form_license_deactivate')) :

						$alert->add_error_message('Your session has expired. Please refresh page.');
						$alert->display(true);
						return false;

					endif;

					$result = self::deactivate_license();

					if (is_wp_error($result)) :

						$alert->add_error_message($result->get_error_message());

					elseif (empty($result)) :

						$alert->add_error_message('Failed to deactivate license.');

					else:

						$alert->add_success_message('Your license has been deactivated.');

					endif;

					$alert->display(true);

					break;

				case 'afl_wc_utm_form_license_check_status':

					//verify nonce
					if (!wp_verify_nonce($post['_wpnonce'], 'afl_wc_utm_form_license_check_status')) :

						$alert->add_error_message('Your session has expired. Please refresh page.');
						$alert->display(true);
						return false;

					endif;

					$result = self::check_license_status(false);

					if (is_wp_error($result)) :

						$alert->add_error_message($result->get_error_message());

					elseif (empty($result)) :

						$alert->add_error_message('Failed to check license status.');

					else:

						$alert->add_success_message('License status is updated.');

					endif;

					$alert->display(true);

					break;
			endswitch;

		endif;

	}

	public static function mask_license_key($key){
		return !empty($key) ? (substr($key, 0, 6) . '************') : '';
	}

}
