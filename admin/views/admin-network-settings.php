<?php defined( 'ABSPATH' ) || exit; ?>

<div class="afl--page-content">
  <div class="tw-container">

    <h1 class="tw-font-bold tw-mt-0">Network Settings</h1>
    <p>The global settings for all sites under the network.</p>

    <?php $afl_alert->display(); ?>

    <form method="post">

        <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-4 tw-mt-5">

          <div>
            <h3>Cookies Setting</h3>

            <?php include AFL_WC_UTM_DIR_ADMIN . 'views/network-settings/cookies.php'; ?>
          </div>

          <div>
            <h3>Miscellaneous Setting</h3>

            <?php include AFL_WC_UTM_DIR_ADMIN . 'views/settings/site-performance.php'; ?>
          </div>

        </div><!--/.grid-->

        <div class="tw-mt-6">
          <input type="hidden" name="page" value="afl-wc-utm-network-settings">
          <input type="hidden" name="form" value="afl_wc_utm_admin_network_form_save_settings">
          <?php wp_nonce_field(AFL_WC_UTM_NETWORK_SETTINGS::ACTION_SAVE); ?>
          <h3>You must clear all your page cache after saving the settings.</h3>
          <input type="submit" class="button button-primary" value="Save Changes">
        </div>

    </form>

  </div>
</div>
