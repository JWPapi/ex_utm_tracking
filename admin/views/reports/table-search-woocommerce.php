<?php defined( 'ABSPATH' ) || exit; ?>

<div>
  <form method="get">
    <input type="hidden" name="page" value="afl-wc-utm-reports">
    <input type="hidden" name="tab" value="woocommerce">
    <input type="hidden" name="afl_wc_utm_form" value="afl_wc_utm_admin_search_woocommerce_orders">

    <div class="tw-grid lg:tw-grid-cols-4 tw-gap-4 tw-bg-white tw-border-solid tw-border tw-border-gray-400 tw-p-5 tw-mt-4">

      <div class="">
        <div class="tw-text-lg tw-font-bold">Search</div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_order_id"><?php esc_html_e('Order ID', AFL_WC_UTM_TEXTDOMAIN); ?></label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_order_id" name="s_order_id" value="<?php echo esc_attr($form_values['s_order_id']); ?>"></div>
        </div>

        <?php if ($attribution_format === 'json') : ?>
          <p>Limited search functionality because the Attribution Data Format is set to JSON. If you need search by UTM values, go to the Settings page to change the Attribution Data Format to Standard.</p>
        <?php endif; ?>

        <?php if ($attribution_format === 'separate') : ?>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_gclid"><?php esc_html_e('Google (gclid)', AFL_WC_UTM_TEXTDOMAIN); ?></label></div>
          <div class="tw-mt-2">
            <select class="tw-w-full" id="input-s_gclid" name="s_gclid">
              <option value="">-- Please select --</option>
              <option value="yes" <?php selected($form_values['s_gclid'], 'yes', true); ?>>Yes</option>
              <option value="no" <?php selected($form_values['s_gclid'], 'no', true); ?>>No</option>
            </select>
          </div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_fbclid"><?php esc_html_e('Facebook (fbclid)', AFL_WC_UTM_TEXTDOMAIN); ?></label></div>
          <div class="tw-mt-2">
            <select class="tw-w-full" id="input-s_fbclid" name="s_fbclid">
              <option value="">-- Please select --</option>
              <option value="yes" <?php selected($form_values['s_fbclid'], 'yes', true); ?>>Yes</option>
              <option value="no" <?php selected($form_values['s_fbclid'], 'no', true); ?>>No</option>
            </select>
          </div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_msclkid"><?php esc_html_e('Microsoft (msclkid)', AFL_WC_UTM_TEXTDOMAIN); ?></label></div>
          <div class="tw-mt-2">
            <select class="tw-w-full" id="input-s_msclkid" name="s_msclkid">
              <option value="">-- Please select --</option>
              <option value="yes" <?php selected($form_values['s_msclkid'], 'yes', true); ?>>Yes</option>
              <option value="no" <?php selected($form_values['s_msclkid'], 'no', true); ?>>No</option>
            </select>
          </div>
        </div>

        <?php endif;//separate ?>

      </div>

      <?php if ($attribution_format === 'separate') : ?>

      <div class="">
        <div class="tw-text-lg tw-font-bold"><?php esc_html_e('UTM Parameters', AFL_WC_UTM_TEXTDOMAIN); ?></div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_utm_source">UTM Source</label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_utm_source" name="s_utm[source]" value="<?php echo esc_attr($form_values['s_utm']['source']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_utm_medium">UTM Medium</label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_utm_medium" name="s_utm[medium]" value="<?php echo esc_attr($form_values['s_utm']['medium']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_utm_campaign">UTM Campaign</label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_utm_campaign" name="s_utm[campaign]" value="<?php echo esc_attr($form_values['s_utm']['campaign']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_utm_term">UTM Term</label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_utm_term" name="s_utm[term]" value="<?php echo esc_attr($form_values['s_utm']['term']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_utm_content">UTM Content</label></div>
          <div class="tw-mt-2"><input type="text" class="tw-w-full" id="input-s_utm_content" name="s_utm[content]" value="<?php echo esc_attr($form_values['s_utm']['content']); ?>"></div>
        </div>
      </div>

      <?php endif;//separate ?>

      <div class="">
        <div class="tw-text-lg tw-font-bold"><?php esc_html_e('Date Ordered', AFL_WC_UTM_TEXTDOMAIN); ?></div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_date_registered_from">From Date</label></div>
          <div class="tw-mt-2"><input type="date" class="tw-w-full" id="input-s_date_registered_from"  name="s_date_ordered[from]" value="<?php echo esc_attr($form_values['s_date_ordered']['from']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_date_ordered_to">To Date</label></div>
          <div class="tw-mt-2"><input type="date" class="tw-w-full" id="input-s_date_ordered_to"  name="s_date_ordered[to]" value="<?php echo esc_attr($form_values['s_date_ordered']['to']); ?>"></div>
        </div>

        <?php if ($attribution_format === 'separate') : ?>

        <div class="tw-text-lg tw-font-bold tw-mt-4"><?php esc_html_e('Date First Visit', AFL_WC_UTM_TEXTDOMAIN); ?></div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_sess_visit_from">From Date</label></div>
          <div class="tw-mt-2"><input type="date" class="tw-w-full" id="input-s_sess_visit_from"  name="s_sess_visit[from]" value="<?php echo esc_attr($form_values['s_sess_visit']['from']); ?>"></div>
        </div>

        <div class="tw-flex tw-flex-col tw-mt-4">
          <div class=""><label class="tw-font-bold tw-text-gray-600" for="input-s_sess_visit_to">To Date</label></div>
          <div class="tw-mt-2"><input type="date" class="tw-w-full" id="input-s_sess_visit_to"  name="s_sess_visit[to]" value="<?php echo esc_attr($form_values['s_sess_visit']['to']); ?>"></div>
        </div>

        <?php endif;//separate ?>

        <div class="tw-mt-3">
          <button type="submit" class="button">Search</button>

        </div>
      </div>

    </div>
  </form>
</div>
