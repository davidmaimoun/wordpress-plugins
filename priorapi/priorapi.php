<?php
/**
 * Plugin Name: PriorApi – Priority OData Connector (Secure)
 * Description: Secure read-only connector between Priority ERP (OData) and WooCommerce. Token is loaded from wp-config.php only.
 * Version: 1.0.1
 * Author: David Maimoun
 */

if (!defined('ABSPATH')) exit;

define('PRIORAPI_PATH', plugin_dir_path(__FILE__));

require_once PRIORAPI_PATH . 'includes/api.php';
require_once PRIORAPI_PATH . 'includes/admin.php';
require_once PRIORAPI_PATH . 'includes/sync-products.php';

if (!wp_next_scheduled('priorapi_sync_products')) {
    wp_schedule_event(time(), 'hourly', 'priorapi_sync_products');
}
