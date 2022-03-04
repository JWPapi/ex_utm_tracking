<?php defined( 'ABSPATH' ) || exit; ?>

<div class="afl--page-content">
  <h1 class="tw-font-bold tw-mt-0">Attribution Reports</h1>

  <?php include AFL_WC_UTM_DIR_ADMIN . 'views/reports/tabs.php'; ?>

  <div class="tw-text-lg tw-mb-6">Shows the conversion attribution for WordPress User sign-up.</div>

  <?php $table->display(); ?>
</div>
