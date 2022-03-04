<?php defined( 'ABSPATH' ) || exit; ?>

<?php if (!empty($user->ID)) : ?>

<div class="afl--page-content">
  <h3 class="tw-my-0"><?php esc_html_e('User Report #', AFL_WC_UTM_TEXTDOMAIN)?><?php echo esc_html($user->ID); ?></h3>

  <hr class="my-3">

  <h1>Conversion Attribution</h1>

  <div class="tw-grid lg:tw-grid-cols-3 tw-gap-4 tw-mt-5">

    <?php do_action('afl_wc_utm_action_admin_user_report_conversion_attributions', $user); ?>

  </div><!--/.grid-->
</div>

<?php else: ?>
<div class="afl-wc-utm-admin-page tw-pr-3 tw-pt-3">
  <h3><?php esc_html_e('User not found.', AFL_WC_UTM_TEXTDOMAIN)?></h3>
</div>
<?php endif;
