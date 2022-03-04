<?php defined( 'ABSPATH' ) || exit; ?>

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Admin Table Integration</h2></div>

    <div class="tw-p-3">

      <div>Show or hide the following admin table columns in the plugin's table list (e.g. WooCommerce > Orders / Gravity Forms > Entries)</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-admin_column_conversion_lag">Conversion Lag</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-admin_column_conversion_lag" name="admin_column_conversion_lag">
            <option value="1" <?php selected($afl_form_values['admin_column_conversion_lag'], '1', true); ?>>o --- Show Column</option>
            <option value="0" <?php selected($afl_form_values['admin_column_conversion_lag'], '0', true); ?>>x --- Hide Column</option>
          </select>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-admin_column_utm_first">UTM (First)</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-admin_column_utm_first" name="admin_column_utm_first">
            <option value="1" <?php selected($afl_form_values['admin_column_utm_first'], '1', true); ?>>o --- Show Column</option>
            <option value="0" <?php selected($afl_form_values['admin_column_utm_first'], '0', true); ?>>x --- Hide Column</option>
          </select>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-admin_column_utm_last">UTM (Last)</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-admin_column_utm_last" name="admin_column_utm_last">
            <option value="1" <?php selected($afl_form_values['admin_column_utm_last'], '1', true); ?>>o --- Show Column</option>
            <option value="0" <?php selected($afl_form_values['admin_column_utm_last'], '0', true); ?>>x --- Hide Column</option>
          </select>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-admin_column_sess_referer">Website Referrer</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-admin_column_sess_referer" name="admin_column_sess_referer">
            <option value="1" <?php selected($afl_form_values['admin_column_sess_referer'], '1', true); ?>>o --- Show Column</option>
            <option value="0" <?php selected($afl_form_values['admin_column_sess_referer'], '0', true); ?>>x --- Hide Column</option>
          </select>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-admin_column_clid">Click Identifier</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-admin_column_clid" name="admin_column_clid">
            <option value="1" <?php selected($afl_form_values['admin_column_clid'], '1', true); ?>>o --- Show Column</option>
            <option value="0" <?php selected($afl_form_values['admin_column_clid'], '0', true); ?>>x --- Hide Column</option>
          </select>
        </div>
      </div>

    </div>

</div><!--/.card-->
