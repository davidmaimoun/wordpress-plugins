<?php
/**
 * Plugin Name: SmartBiDi Zones RTL
 * Plugin URI: https://github.com/davidmaimoun/wordpress-plugins/smartbidi
 * Description: Detects mixed RTL (Hebrew, Arabic, etc.) and Latin text in WooCommerce products and allows forcing RTL on Navbar, Content, and Footer separately.
 * Version: 2.0.0
 * Author: David Maimoun
 * License: GPL v2 or later
 * Text Domain: smartbidi
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smartbidi
 * Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

class SmartBiDi_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // WooCommerce product titles
        add_filter('the_title', [$this, 'process_bidi_text'], 999, 1);
        add_filter('woocommerce_product_get_name', [$this, 'process_bidi_text'], 999, 1);

        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Apply RTL zones
        add_action('wp_head', [$this, 'apply_rtl_zones']);

        // Shortcode
        add_shortcode('smartbidi', [$this, 'shortcode_handler']);
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'smartbidi-style',
            plugin_dir_url(__FILE__) . 'css/style.css',
            [],
            '1.5.0'
        );
        wp_enqueue_script(
            'smartbidi-script',
            plugin_dir_url(__FILE__) . 'js/script.js',
            ['jquery'],
            '1.5.0',
            true
        );
    }

    private function contains_rtl($text) {
        return preg_match('/[\x{0590}-\x{08FF}]/u', $text);
    }

    private function contains_latin($text) {
        return preg_match('/[a-zA-Z]/', $text);
    }

    public function process_bidi_text($content) {
        $enabled = get_option('smartbidi_enable', '1');
        if ($enabled !== '1') return $content;

        if ($this->contains_rtl($content) && $this->contains_latin($content)) {
            $content = '<div class="bidi-mixed" dir="auto">' . $content . '</div>';
        } elseif ($this->contains_rtl($content)) {
            $content = '<div class="rtl-only" dir="rtl">' . $content . '</div>';
        }
        return $content;
    }

    public function apply_rtl_zones() {
        $css = '';
        if (get_option('smartbidi_rtl_navbar','0') === '1') {
            $css .= 'header, .navbar, .site-header { direction: rtl !important; text-align: right !important; }';
        }
        if (get_option('smartbidi_rtl_content','0') === '1') {
            $css .= 'main, .content-area, .site-content, main, .main-content, .container, .wrap { 
                direction: rtl !important; text-align: right !important; 
                }';
        }
        if (get_option('smartbidi_rtl_footer','0') === '1') {
            $css .= 'footer, .site-footer { direction: rtl !important; text-align: right !important; }';
        }
        if ($css) {
            echo "<style>$css</style>";
        }
    }

    public function shortcode_handler($atts, $content = '') {
        return $this->process_bidi_text($content);
    }

    public function add_admin_menu() {
        add_menu_page(
            'SmartBiDi',
            'SmartBiDi',
            'manage_options',
            'smartbidi',
            [$this, 'admin_page'],
            'dashicons-editor-alignleft',
            55
        );
    }

    public function register_settings() {
        register_setting('smartbidi_settings', 'smartbidi_enable');
        register_setting('smartbidi_settings', 'smartbidi_rtl_navbar');
        register_setting('smartbidi_settings', 'smartbidi_rtl_content');
        register_setting('smartbidi_settings', 'smartbidi_rtl_footer');
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>SmartBiDi Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('smartbidi_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="smartbidi_enable">Enable automatic BiDi detection for WooCommerce product titles</label>
                        </th>
                        <td>
                            <input type="checkbox" id="smartbidi_enable" name="smartbidi_enable" value="1" <?php checked(get_option('smartbidi_enable','1'),'1'); ?>>
                            <p class="description">Automatically format mixed RTL (Hebrew, Arabic, etc.) and Latin text in WooCommerce products.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="smartbidi_rtl_navbar">Force RTL Navbar</label>
                        </th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_navbar" name="smartbidi_rtl_navbar" value="1" <?php checked(get_option('smartbidi_rtl_navbar','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to your site navbar/header.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="smartbidi_rtl_content">Force RTL Content</label>
                        </th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_content" name="smartbidi_rtl_content" value="1" <?php checked(get_option('smartbidi_rtl_content','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to main content divs.</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="smartbidi_rtl_footer">Force RTL Footer</label>
                        </th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_footer" name="smartbidi_rtl_footer" value="1" <?php checked(get_option('smartbidi_rtl_footer','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to site footer.</p>
                        </td>
                    </tr>
                </table>

                <h2>Shortcode</h2>
                <code>[smartbidi]Mixed RTL and Latin text here[/smartbidi]</code>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

// Initialize
function smartbidi_init() {
    return SmartBiDi_Handler::get_instance();
}
add_action('plugins_loaded','smartbidi_init');

// Activation hook
register_activation_hook(__FILE__, function () {
    add_option('smartbidi_enable','1');
    add_option('smartbidi_rtl_navbar','0');
    add_option('smartbidi_rtl_content','0');
    add_option('smartbidi_rtl_footer','0');
});

// Deactivation hook
register_deactivation_hook(__FILE__, function () {});
