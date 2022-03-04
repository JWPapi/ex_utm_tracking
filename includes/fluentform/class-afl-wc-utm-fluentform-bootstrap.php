<?php defined( 'ABSPATH' ) || exit;

use FluentForm\App\Services\Integrations\IntegrationManager;
use FluentForm\Framework\Foundation\Application;

/**
 *
 */
class AFL_WC_UTM_FLUENTFORM_BOOTSTRAP extends IntegrationManager
{

  public $hasGlobalMenu = false;
  public $disableGlobalSettings = 'yes';

  public function __construct(Application $app)
  {
      parent::__construct(
          $app,
          'AFL UTM Tracker',
          'afl_wc_utm',
          'afl_wc_utm_fluentform_settings',
          'afl_wc_utm_feed',
          1
      );

      $this->logo = AFL_WC_UTM_URL_PLUGIN . '/admin/img/afl_wc_utm_fluentform.png';

      $this->description = "AFL UTM Tracker is a conversion attribution plugin for WordPress. Instantly know your visitor's UTM parameters, landing page and website referrer and more upon form submission.";

      $this->registerAdminHooks();

      //merge tag as default value
      add_filter('fluentform_insert_response_data', 'AFL_WC_UTM_FLUENTFORM::filter_insert_response_data', 10, 3);

      //form submit
      add_action('fluentform_before_form_actions_processing', 'AFL_WC_UTM_FLUENTFORM::action_form_submit', 10, 3);

      //form integration
      add_filter('fluentform_webhook_request_data', 'AFL_WC_UTM_FLUENTFORM::filter_fluentform_webhook_request_data', 10, 5);
      add_filter('fluentform_integration_data_zapier', 'AFL_WC_UTM_FLUENTFORM::filter_fluentform_integration_data_zapier', 10, 4);

      //merge tags
      add_filter('fluentform_form_settings_smartcodes', 'AFL_WC_UTM_FLUENTFORM::filter_register_smartcodes', 10, 2);
      add_filter('fluentform_smartcode_group_afl_wc_utm', 'AFL_WC_UTM_FLUENTFORM::filter_get_smartcode_value', 10, 2);

      //admin - export form entries
      add_filter('fluentform_export_data', 'AFL_WC_UTM_FLUENTFORM::filter_export_data', 10, 4);

      //admin - view all entries
      add_filter('fluentform_all_entry_labels', 'AFL_WC_UTM_FLUENTFORM::filter_fluentform_all_entry_labels', 10, 2);
      add_filter('fluentform_all_entries', 'AFL_WC_UTM_FLUENTFORM::filter_fluentform_all_entries', 10, 1);

      //admin - view single entry
      add_action('wp_ajax_afl_wc_utm_admin_fluentform_get_entry_attribution', 'AFL_WC_UTM_FLUENTFORM::action_ajax_get_entry_attribution');
      add_action('ff_fluentform_form_application_view_entries', 'AFL_WC_UTM_FLUENTFORM::action_ff_fluentform_form_application_view_entries', 10, 1);

      //admin - before save form settings
      add_filter('fluentform_save_integration_value_' . $this->integrationKey, 'AFL_WC_UTM_FLUENTFORM::validate_form_settings', 10, 3);

  }

  public function isConfigured(){
    return true;
  }

  public function getIntegrationDefaults($settings, $formId)
  {
      return [
          'name' => '',
          'enabled' => true,
          'conversion_type' => AFL_WC_UTM_FLUENTFORM::DEFAULT_CONVERSION_TYPE,
          'cookie_expiry' => AFL_WC_UTM_FLUENTFORM::DEFAULT_COOKIE_EXPIRY
      ];
  }

  public function pushIntegration($integrations, $formId)
  {

      $integrations[$this->integrationKey] = [
          'title'                 => $this->title . ' Integration',
          'logo'                  => $this->logo,
          'is_active'             => $this->isConfigured(),
          'disable_global_settings' => 'yes'
      ];

      return $integrations;
  }

  public function getSettingsFields($settings, $formId)
  {
      return [
          'fields'              => [
              [
                  'key'         => 'name',
                  'label'       => 'Name',
                  'required'    => true,
                  'placeholder' => 'Your Feed Name',
                  'component'   => 'text'
              ],
              [
                  'key'            => 'enabled',
                  'label'          => 'Status',
                  'component'      => 'checkbox-single',
                  'checkbox_label' => 'Enabled',
                  'tips'           => __( 'Do you want to save the visitor\'s conversion attribution for this form?', AFL_WC_UTM_TEXTDOMAIN )
              ],
              [
                  'key'            => 'conversion_type',
                  'label'          => 'Conversion Type',
                  'component'      => 'select',
                  'options'       => [
                    'lead' => 'Lead',
                    'order' => 'Order'
                  ],
                  'tips' => __('Default: Lead')
              ],
              [
                  'key'            => 'cookie_expiry',
                  'label'          => __( 'Reset Attribution after Form Submission (days)', AFL_WC_UTM_TEXTDOMAIN ),
                  'component'      => 'number',
                  'tips'           => __( "When the user submits the form, reset the visitor's attribution after number of days of inactivity. This allows a new attribution session to start. Recommended Values. Lead: 30 days | Order: 7 days | Minimum 1 day.", AFL_WC_UTM_TEXTDOMAIN )
              ]
          ],
          'integration_title'   => $this->title
      ];
  }

  public function getMergeFields($list = false, $listId = false, $formId = false)
  {
      return array();
  }

  public function notify($feed, $formData, $entry, $form)
  {

    if (!isset($entry->id)) :
      return false;
    endif;

    $version = AFL_WC_UTM_FLUENTFORM::get_meta($entry->id, 'version');

    if (!empty($version)) :
      do_action('ff_integration_action_result', $feed, 'success', 'AFL UTM Tracker success.');
      return true;
    endif;

    do_action('ff_integration_action_result', $feed, 'failed', 'AFL UTM Tracker failed.');
    return false;

  }

}
