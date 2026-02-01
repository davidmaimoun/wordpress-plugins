<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {

    add_menu_page(
        'PriorApi',
        'PriorApi',
        'manage_options',
        'priorapi',
        'priorapi_settings_page',
        'dashicons-database',
        56
    );

    add_submenu_page(
        'priorapi',
        'Settings',
        'Settings',
        'manage_options',
        'priorapi',
        'priorapi_settings_page'
    );
});

function priorapi_settings_page() {

    if (!current_user_can('manage_options')) return;

    if (isset($_POST['priorapi_save'])) {

        check_admin_referer('priorapi_save_settings');

        update_option('priorapi_base_url', sanitize_text_field($_POST['base_url']));

        echo '<div class="updated notice"><p>Settings saved</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>PriorApi – Settings</h1>

        <form method="post">
            <?php wp_nonce_field('priorapi_save_settings'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">Base OData URL</th>
                    <td>
                        <input type="text"
                               name="base_url"
                               class="regular-text"
                               value="<?php echo esc_attr(get_option('priorapi_base_url')); ?>">
                        <p class="description">
                            Example: https://server/odata/Priority/tabula.ini/environment
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button('Save Settings', 'primary', 'priorapi_save'); ?>
        </form>

        <hr>
        <p><strong>Security note:</strong> API token must be defined in <code>wp-config.php</code> using <code>PRIORAPI_TOKEN</code>.</p>
    </div>
    <?php
}
