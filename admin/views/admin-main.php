<?php defined( 'ABSPATH' ) || exit; ?>

<div class="afl--page-content">

  <div class="tw-grid tw-grid-cols-12 tw-mt-5">
    <div class="tw-col-span-12 xl:tw-col-span-8 xl:tw-col-start-3 tw-border tw-border-solid tw-border-gray-400 tw-bg-white">

      <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-12">
        <div class="tw-col-span-1 xl:tw-col-span-6 tw-p-5 tw-text-center">
          <img class="tw-w-7/12 tw-mt-4" src="<?php echo AFL_WC_UTM_ADMIN::get_img_url('afl_wc_utm_product_icon.png'); ?>">
          <div class="tw-text-3xl tw-mt-5 tw-font-bold">AFL UTM Tracker</div>
          <div class="tw-text-xl tw-mt-4 tw-mb-4"><i>Conversion Tracking and Attribution</i></div>
        </div>
        <div class="tw-col-span-1 xl:tw-col-span-6 tw-p-5">
          <h2 class="tw-text-2xl tw-mt-2">Getting Started</h2>

          <div class="tw-text-justify">
            <p>AFL UTM Tracker is a lightweight conversion tracking and attribution plugin for your website. It tracks and saves your visitor's first landing page, website referrer, UTM parameters and click identifiers upon conversion.</p>
            <h4>Checklist</h4>

            <ol>
              <li>Make sure that your website is in HTTPS mode.</li>
              <li>Once you have activated the plugin, please make sure you have clear all page cache so that our javascript file will load.</li>
              <li>Adjust <a href="<?php echo AFL_WC_UTM_ADMIN::get_url('settings'); ?>">Settings</a> for a more accurate attribution report.</li>
              <li>Use Google's <a href="https://ga-dev-tools.appspot.com/campaign-url-builder/" target="_blank" rel="noreferrer noopener">Campaign URL Builder</a> to generate a URL.</li>
              <li>Try it out by visiting your website with UTM parameters and placing a WooCommerce order or submitting a Gravity Forms. </li>
              <li>Check the Attribution Report in the WooCommerce Orders page or Gravity Forms Entries page.</li>
              <li>Remember to clear your page cache when you update our plugin version.</li>
              <li>Join our <a href="https://www.facebook.com/groups/appfromlab/" target="_blank" rel="noopener">Facebook Group</a> to report bugs, share tips and request features.</li>
            </ol>

            <h4>Changelog</h4>
            <p>Please view the latest <a href="https://www.appfromlab.com/woocommerce-utm-tracker-changelog/" target="_blank" rel="noopener">changelog</a> on our website.</p>

          </div>
        </div>
      </div>

    </div>
  </div><!--/.tw-grid-->

  <div class="tw-grid tw-grid-cols-12 tw-mt-5">

    <div class="tw-col-span-12 xl:tw-col-span-8 xl:tw-col-start-3"><h3>Supported Plugin Integrations</h3></div>
    <div class="tw-col-span-12 xl:tw-col-span-8 xl:tw-col-start-3">

      <div class="tw-grid tw-grid-cols-1 xl:tw-grid-cols-3 tw-gap-4">
        <div class="tw-p-5 tw-text-center tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-text-xl">
          WooCommerce
        </div>

        <div class="tw-p-5 tw-text-center tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-text-xl">
          Gravity Forms
        </div>

        <div class="tw-p-5 tw-text-center tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-text-xl">
          WP Fluent Forms
        </div>

        <div class="tw-p-5 tw-text-center tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-text-xl">
          WP Consent API
        </div>
      </div>

    </div>
  </div><!--/.tw-grid-->

</div>
