<?php defined( 'ABSPATH' ) || exit; ?>

<div class="afl--page-content">
  <div class="tw-container">

    <h1 class="tw-font-bold tw-mt-0">Settings</h1>

    <?php $afl_alert->display(); ?>

    <form method="post">

    <div class="tw-grid tw-grid-cols-1 lg:tw-grid-cols-2 tw-gap-4 tw-mt-5">
      <?php
        if ($setting_permissions['cookies'] === true) :
          include AFL_WC_UTM_DIR_ADMIN . 'views/settings/cookies.php';
        endif;
      ?>
      <div>

          <h3>Miscellaneous Setting</h3>

          <?php
            if ($setting_permissions['admin_table_integration'] === true) :
              include AFL_WC_UTM_DIR_ADMIN . 'views/settings/admin-table-integration.php';
            endif;

            if ($setting_permissions['export_integration'] === true) :
              include AFL_WC_UTM_DIR_ADMIN . 'views/settings/export-integration.php';
            endif;

            if ($setting_permissions['site_performance'] === true) :
              include AFL_WC_UTM_DIR_ADMIN . 'views/settings/site-performance.php';
            endif;
          ?>

      </div>

    </div><!--/.grid-->

    <div class="tw-mt-6">
      <input type="hidden" name="page" value="afl-wc-utm-settings">
      <input type="hidden" name="form" value="afl_wc_utm_admin_form_save_settings">
      <?php wp_nonce_field(AFL_WC_UTM_SETTINGS::ACTION_SAVE); ?>
      <h3>You must clear all your page cache after saving the settings.</h3>
      <input type="submit" class="button button-primary" value="Save Changes">
    </div>

  </form>

  </div>
</div>
