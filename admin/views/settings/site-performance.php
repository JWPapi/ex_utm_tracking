<?php defined( 'ABSPATH' ) || exit; ?>

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Site Performance</h2></div>

    <div class="tw-p-3">

      <div>If you're running a high traffic website on a web server with low specifications, choose a faster performance option below which can reduce the database load and processing time. Do consult us if you're not sure how the following options affects your website and integrations.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-attribution_format">Attribution Data Format</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-attribution_format" name="attribution_format">
            <option value="separate" <?php selected($afl_form_values['attribution_format'], 'separate', true); ?>>Standard - Save each value in separate meta rows</option>
            <option value="json" <?php selected($afl_form_values['attribution_format'], 'json', true); ?>>Fastest - Save as JSON in a single meta row</option>
          </select>
          <p><b>Standard (default)</b> - <i>uses more database read / writes but allows non-supported plugins to easily access attribution data through the individual meta rows.</i></p>
          <p><b>Fastest</b> - <i>uses less database read / writes but the drawback is that <b class="tw-text-red-600">non-supported plugins may not be able to access the attribution data and the search feature in our Reports section will be limited.</b></i></p>
          <p>Note: Changes to this setting will only affect new conversions. Older conversion attribution format will remain unchanged.</p>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-active_attribution">Active Attribution <br>(for logged-in users)</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-active_attribution" name="active_attribution">
            <option value="1" <?php selected($afl_form_values['active_attribution'], '1', true); ?>>Standard - Enable Feature</option>
            <option value="0" <?php selected($afl_form_values['active_attribution'], '0', true); ?>>Fastest - Disable Feature</option>
          </select>
          <p><b>Standard (default)</b> - <i>the user's latest attribution data will be sync across devices which they are logged-in even when they have not converted yet. It provides a more accurate attribution like when the user visits your marketing campaign on their mobile device but completes the purchase on their desktop.</i></p>
          <p><b>Fastest</b> - <i>this feature will be disabled.</i></p>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_renewal">Server-side Cookie Renewal</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-cookie_renewal" name="cookie_renewal" style="max-width: 100%">
            <option value="force" <?php selected($afl_form_values['cookie_renewal'], 'force', true); ?>>Standard - Renew cookies on every page view</option>
            <option value="update" <?php selected($afl_form_values['cookie_renewal'], 'update', true); ?>>Fastest - Renew cookies only when there are attribution changes</option>
          </select>
          <p>This feature is enabled to bypass privacy-based browsers that limits the cookie lifetime on the browser to less than 7-days. Only our plugin cookies will be renewed.</p>
          <p><b>Standard (default)</b> - <i>Cookie expiry date will be renewed every time the visitor visits your website. This has been the default setting since our first plugin version.</i></p>
          <p><b>Fastest</b> - <i>Cookie expiry date will only be renewed when there are changes in the attribution data.</i></p>
        </div>
      </div>

    </div>

</div><!--/.card-->
