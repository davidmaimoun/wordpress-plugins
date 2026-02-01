<?php
if (!defined('ABSPATH')) exit;

add_action('priorapi_sync_products', 'priorapi_sync_products');

function priorapi_sync_products() {

    if (!function_exists('wc_get_product_id_by_sku')) return;

    $data = priorapi_request('ITEMS?$select=PARTNAME,PARTDES,PRICE');

    if (!$data || empty($data['value'])) return;

    foreach ($data['value'] as $item) {

        $sku   = $item['PARTNAME'] ?? '';
        $name  = $item['PARTDES'] ?? '';
        $price = $item['PRICE'] ?? '';

        if (!$sku) continue;

        $product_id = wc_get_product_id_by_sku($sku);
        if (!$product_id) continue;

        $product = wc_get_product($product_id);
        $product->set_name($name);
        $product->set_regular_price($price);
        $product->save();
    }
}
