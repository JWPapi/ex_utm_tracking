<?php defined( 'ABSPATH' ) || exit; ?>

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Export Integrations</h2></div>

    <div class="tw-p-3">

      <div>Plugins like Gravity Forms and Fluent Forms have built-in export feature. You can export the conversion attribution values into the CSV. Below is a setting which you can apply on the exported results.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-export_blank">Replace blank value with this text:</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-full md:tw-w-1/2" type="text" id="input-export_blank" name="export_blank" value="<?php echo esc_attr($afl_form_values['export_blank']); ?>">
          <div class="tw-mt-2"><i>Setting a value makes it easier for you to use the pilot table feature in Excel.</i></div>
        </div>
      </div>

    </div>

</div><!--/.card-->
