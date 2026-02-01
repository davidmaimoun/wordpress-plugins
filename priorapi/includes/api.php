<?php
if (!defined('ABSPATH')) exit;

function priorapi_request($endpoint) {

    if (!defined('PRIORAPI_TOKEN')) {
        return false; // Token must be defined in wp-config.php
    }

    $base_url = get_option('priorapi_base_url');
    if (!$base_url) return false;

    $auth = base64_encode(PRIORAPI_TOKEN . ':PAT');

    $response = wp_remote_get(
        trailingslashit($base_url) . $endpoint,
        [
            'headers' => [
                'Authorization' => 'Basic ' . $auth,
                'Accept'        => 'application/json'
            ],
            'timeout' => 20
        ]
    );

    if (is_wp_error($response)) {
        return false;
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}
