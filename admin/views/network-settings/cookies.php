<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Cross-Subdomain Tracking Cookies (optional)</h2></div>

    <div class="tw-p-3">

      <p class="tw-text-red-600 tw-mt-0"><b>WARNING:</b> Only set this up if all of your websites under the WordPress Multisite uses the same root domain.</p>
      <p class="tw-text-red-600">By setting the cookie domain, there are a few things which you should take note:</p>

      <ul>
        <li>- DO NOT set this up if the user's privacy should be separate by each site.</li>
        <li>- Your user's visited pages information will be shared across all the sites in the Multisite.</li>
        <li>- The logged-in user's active attribution will be stored in the global user database and accessible by all sites.</li>
        <li>- The cookie settings under the individual site will be DISABLED.</li>
      </ul>

      <hr>
      <div>Get your visitors to use the same conversion attribution journey for all your websites under the WordPress Multisite.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_domain">Cookie Domain</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-full md:tw-w-1/2" type="text" id="input-cookie_domain" name="cookie_domain" placeholder="yourdomain.com" value="<?php echo esc_attr($afl_form_values['cookie_domain']); ?>">
          <div class="tw-mt-2"><i>Enter your root domain. Leave blank to disable.<br>Example: yourdomain.com (without the https://www.)</i></div>
        </div>
      </div>
    </div>

</div><!--/.card-->

<?php if(!empty($afl_form_values['cookie_domain'])) : ?>

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Tracking Cookies</h2></div>

    <div class="tw-p-3">
      <div>When a visitor first visits your website, tracking cookies will be generated on the visitor's browser to start a new attribution session. It will track the visitor's first landing page, website referrer, UTM parameters and several others for the number of days below.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_attribution_window">Attribution Window</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-1/3" type="number" id="input-cookie_attribution_window" name="cookie_attribution_window" value="<?php echo esc_attr($afl_form_values['cookie_attribution_window']); ?>" min="1"> day(s)
          <div class="tw-mt-2"><i>Your visitor's cookies will be renewed each time the visitor visits your website for the number of days above but it will reset to start a new attribution session after the visitor has not visited for the number of days above. (Default is 90 days)</i></div>
        </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4">
        <div class="tw-w-full md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_last_touch_window">Last Touch Window</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-1/3" type="number" id="input-cookie_last_touch_window" name="cookie_last_touch_window" value="<?php echo esc_attr($afl_form_values['cookie_last_touch_window']); ?>" min="1"> minute(s)
          <div class="tw-mt-2"><i>If your visitor visits the same URL again (only URLs with UTM, gclid or fbclid parameter) after the number of minutes above have passed then update the visited date and time. (Default is 30 minutes)</i></div>
        </div>
      </div>
    </div>

</div><!--/.card-->

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Reset Cookies After Conversion</h2></div>

    <div class="tw-p-3">
      <div>Once a visitor converts, reset the visitor's attribution cookies after a certain number of days. This will allow your visitor's next visit to have a new attribution cookies. Adjust the values accordingly to your visitors purchase pattern for better conversion attribution.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4">
        <div class="md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_conversion_account">When user registers, reset after</label></div>
        <div class="md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-1/3" type="number" id="input-cookie_conversion_account" name="cookie_conversion_account" value="<?php echo esc_attr($afl_form_values['cookie_conversion_account']); ?>" min="1"> day(s) of inactivity
           <div class="tw-mt-2"><i>Reset the user's attribution sesssion after the user has not visited your website for the number of days above. (Default is 30 days)</i></div>
         </div>
      </div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4">
        <div class="md:tw-w-1/3"><label class="tw-font-bold" for="input-cookie_conversion_order">When user placed an order, reset after</label></div>
        <div class="md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <input class="tw-w-1/3" type="number" id="input-cookie_conversion_order" name="cookie_conversion_order" value="<?php echo esc_attr($afl_form_values['cookie_conversion_order']); ?>" min="1"> day(s) of inactivity
          <div class="tw-mt-2"><i>Reset the user's attribution sesssion after the user has not visited your website for the number of days above. (Default is 7 days)</i></div>
        </div>
      </div>
    </div>

</div><!--/.card-->

<div class="tw-border tw-border-solid tw-border-gray-400 tw-bg-white tw-mb-5">

    <div class="tw-p-3 tw-border-0 tw-border-solid tw-border-gray-400 tw-border-b"><h2 class="tw-mb-1 tw-mt-0 tw-font-bold">Cookie Consent Integration (optional)</h2></div>

    <div class="tw-p-3">

      <div>To integrate our plugin with other cookie consent / banner plugins, please install the <a href="https://wordpress.org/plugins/wp-consent-api/" target="
        _blank" rel="noreferrer noopenner" style="font-weight:bold">WP Consent API plugin</a> from the official WordPress repository. The WP Consent API plugin acts as a standard middleman between our plugin and supported cookie consent plugins like Complianz and CookieBot.</div>

      <div class="tw-flex tw-flex-col md:tw-flex-row tw-mt-4 tw-mb-4">
        <div class="tw-w-full md:tw-w-1/3 tw-pr-3"><label class="tw-font-bold" for="input-cookie_consent_statistics">Allow tracking only when the visitor has given consent to this consent category:</label></div>
        <div class="tw-w-full md:tw-w-2/3 tw-mt-2 md:tw-mt-0">
          <select class="tw-w-full" id="input-cookie_consent_category" name="cookie_consent_category">
            <option value="statistics" <?php selected($afl_form_values['cookie_consent_category'], 'statistics', true); ?>>Statistics</option>
            <option value="marketing" <?php selected($afl_form_values['cookie_consent_category'], 'marketing', true); ?>>Marketing</option>
          </select>
        </div>
      </div>
      <hr>
      <p><b>Statistics</b> - <i>Select this if you will be using the attribution data for analytics purposes only.</i></p>
      <p><b>Marketing</b> - <i>Select this if you will be using the attribution data for marketing purposes.</i></p>
    </div>

</div><!--/.card-->

<?php endif; ?>
