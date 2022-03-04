<?php defined( 'ABSPATH' ) || exit; ?>

<div class="afl--page-content">
  <h1 class="tw-font-bold tw-mt-0">Attribution Reports</h1>

  <?php include AFL_WC_UTM_DIR_ADMIN . 'views/reports/tabs.php'; ?>

  <div class="tw-text-lg tw-mb-6">Shows the latest attribution for a WordPress User even when the user has not convert.</div>
  <?php
    if (!empty($active_attribution_setting)) :
      $table->display();
    else:
  ?>
  <div class="tw-text-lg tw-text-red-600">This report is not shown because the Active Attribution setting is disabled.</div>
  <?php endif; ?>
</div>
