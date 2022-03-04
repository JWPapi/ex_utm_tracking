<?php defined( 'ABSPATH' ) || exit; ?>

<ul class="tw-flex tw-border-gray-500 tw-border tw-border-solid tw-border-r-0 tw-border-l-0 tw-border-t-0">
  
  <li class="tw-mb-0">
    <a class="tw-text-lg tw-inline-block tw-py-2 tw-px-4 <?php echo esc_attr(AFL_WC_UTM_UTIL::get_tab_css('', 'tw-border-gray-500 tw-border-solid tw-border tw-border-b-0 tw-rounded-t tw-bg-gray-200 tw-text-gray-700 tw-font-semibold tw-no-underline', 'hover:tw-text-blue-800 tw-no-underline')); ?>" href="<?php echo AFL_WC_UTM_ADMIN::get_url('reports'); ?>">Active</a>
  </li>

  <li class="tw-mb-0">
    <a class="tw-text-lg tw-inline-block tw-py-2 tw-px-4 <?php echo esc_attr(AFL_WC_UTM_UTIL::get_tab_css('registered', 'tw-border-gray-500 tw-border-solid tw-border tw-border-b-0 tw-rounded-t tw-bg-gray-200 tw-text-gray-700 tw-font-semibold tw-no-underline', 'hover:tw-text-blue-800 tw-no-underline')); ?>" href="<?php echo AFL_WC_UTM_ADMIN::get_url('reports', array('tab' => 'registered')); ?>">Sign-up</a>
  </li>

  <?php if (AFL_WC_UTM_UTIL::is_plugin_installed('woocommerce')) : ?>
  <li class="tw-mb-0">
    <a class="tw-text-lg tw-inline-block tw-py-2 tw-px-4 <?php echo esc_attr(AFL_WC_UTM_UTIL::get_tab_css('woocommerce', 'tw-border-gray-500 tw-border-solid tw-border tw-border-b-0 tw-rounded-t tw-bg-gray-200 tw-text-gray-700 tw-font-semibold tw-no-underline', 'hover:tw-text-blue-800 tw-no-underline')); ?>" href="<?php echo AFL_WC_UTM_ADMIN::get_url('reports', array('tab' => 'woocommerce')); ?>">WooCommerce</a>
  </li>
  <?php endif; ?>

  <?php if (AFL_WC_UTM_UTIL::is_plugin_installed('gravityforms')) : ?>
  <li class="tw-mb-0">
    <a class="tw-text-lg tw-inline-block tw-py-2 tw-px-4 <?php echo esc_attr(AFL_WC_UTM_UTIL::get_tab_css('gravityforms', 'tw-border-gray-500 tw-border-solid tw-border tw-border-b-0 tw-rounded-t tw-bg-gray-200 tw-text-gray-700 tw-font-semibold tw-no-underline', 'hover:tw-text-blue-800 tw-no-underline')); ?>" href="<?php echo AFL_WC_UTM_ADMIN::get_url('reports', array('tab' => 'gravityforms')); ?>">Gravity Forms</a>
  </li>
  <?php endif; ?>

</ul>
