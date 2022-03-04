<?php
function getOrdersWithUTM($request) {
  $data = $request->get_params();
  $valid_utm_params = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
  $utmTag = $data['utm-tag'];

  if (!in_array($utmTag, $valid_utm_params)) {
    return 'Invalid UTM Tag';
  }

  $dateMin = $data['date-min'] ?: strtotime('today');
  $dateMax = $data['date-max'] ?: strtotime('tomorrow');
  $orders = wc_get_orders([
    'limit' => 1000,
    'status' => 'completed',
    'date_created' => $dateMin . '...' . $dateMax,
  ]);

  $utmData = [];

  foreach ($orders as &$order) {
    $total = $order->get_total();
    $utm_source = $order->get_meta('_afl_wc_utm_utm_source_1st');
    $utm_content = $order->get_meta('_afl_wc_utm_' . $utmTag .'_1st');

    $name = empty($utm_content) ? 'No UTM Content' : $utm_content;


    $utmData[$name]['name'] = $name;
    $utmData[$name]['count'] = $utmData[$name]['count'] ? $utmData[$name]['count'] + 1 : 1;
    $utmData[$name]['value'] = $utmData[$name]['value'] ? $utmData[$name]['value'] + $total : $total;
    $utmData[$name]['source'] = empty($utm_source) ? 'No UTM Source' : $utm_source;
  }

  $utmArray = [];

  foreach ($utmData as $utm) {
    $utmArray[] = $utm;
  }

  return  $utmArray;
};

add_action('rest_api_init', function () {
  register_rest_route('jw', '/getOrdersWithUTM', [
    'methods' => 'GET',
    'callback' => 'getOrdersWithUTM',
    'permission_callback' => '__return_true'
  ]);
});
