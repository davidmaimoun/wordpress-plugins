<?php
/**
 * Plugin Name: SmartBiDi RTL Fixer
 * Plugin URI: https://github.com/davidmaimoun/wordpress-plugins/tree/main/smartbidi
 * Description: Detects mixed RTL (Hebrew, Arabic, etc.) and Latin text in WooCommerce products and allows forcing RTL on Navbar, Content, and Footer separately.
 * Version: 2.1.0
 * Author: David Maimoun
 * Author URI: https://github.com/davidmaimoun
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smartbidi
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

class SmartBiDi_Handler {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // WooCommerce product titles
        add_filter('woocommerce_product_get_name', [$this, 'process_bidi_text'], 999, 1);

        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Apply RTL zones
        add_action('wp_head', [$this, 'apply_rtl_zones']);

        // Admin RTL detection
        add_action('admin_footer', [$this, 'admin_input_rtl_script']);

        // Shortcode
        add_shortcode('smartbidi', [$this, 'shortcode_handler']);
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'smartbidi-style',
            plugin_dir_url(__FILE__) . 'css/style.css',
            [],
            '2.1.0'
        );

        wp_enqueue_script(
            'smartbidi-script',
            plugin_dir_url(__FILE__) . 'js/script.js',
            ['jquery'],
            '2.1.0',
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
        $enabled = get_option('smartbidi_force_rtl', '1');
        if ($enabled !== '1') return $content;

        if (is_admin()) {
            return $content;
        }

        if ($this->contains_rtl($content) && $this->contains_latin($content)) {
            return '<span class="bidi-mixed" dir="auto">' . esc_html($content) . '</span>';
        } elseif ($this->contains_rtl($content)) {
            return '<span class="rtl-only" dir="rtl">' . esc_html($content) . '</span>';
        }

        return esc_html($content);
    }

    public function apply_rtl_zones() {
        $css = '';

        if (get_option('smartbidi_rtl_navbar', '0') === '1') {
            $css .= 'header, .navbar, .site-header { direction: rtl !important; text-align: right !important; }';
        }

        if (get_option('smartbidi_rtl_content', '0') === '1') {
            $css .= '
                main, .content-area, .site-content, .main-content, .container, .wrap {
                    direction: rtl !important;
                    text-align: right !important;
                }
                .ct-breadcrumbs {
                    direction: rtl !important;
                    text-align: right !important;
                }
                .ct-breadcrumbs span {
                    display: inline-flex;
                    flex-direction: row !important;
                    align-items: center;
                }
                .ct-breadcrumbs svg.ct-separator {
                    transform: rotate(180deg);
                }
            ';
        }

        if (get_option('smartbidi_rtl_footer', '0') === '1') {
            $css .= 'footer, .site-footer { direction: rtl !important; text-align: right !important; }';
        }

        if (!empty($css)) {
            echo '<style>' . esc_html($css) . '</style>';
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
        register_setting(
            'smartbidi_settings',
            'smartbidi_force_rtl',
            ['sanitize_callback' => [$this, 'sanitize_checkbox']]
        );

        register_setting(
            'smartbidi_settings',
            'smartbidi_rtl_navbar',
            ['sanitize_callback' => [$this, 'sanitize_checkbox']]
        );

        register_setting(
            'smartbidi_settings',
            'smartbidi_rtl_content',
            ['sanitize_callback' => [$this, 'sanitize_checkbox']]
        );

        register_setting(
            'smartbidi_settings',
            'smartbidi_rtl_footer',
            ['sanitize_callback' => [$this, 'sanitize_checkbox']]
        );
    }

    public function sanitize_checkbox($value) {
        return $value === '1' ? '1' : '0';
    }

    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>SmartBiDi Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('smartbidi_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="smartbidi_rtl_navbar">Force RTL Navbar</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_navbar" name="smartbidi_rtl_navbar" value="1"
                            <?php checked(get_option('smartbidi_rtl_navbar','0'),'1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="smartbidi_rtl_content">Force RTL Content</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_content" name="smartbidi_rtl_content" value="1"
                            <?php checked(get_option('smartbidi_rtl_content','0'),'1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="smartbidi_rtl_footer">Force RTL Footer</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_footer" name="smartbidi_rtl_footer" value="1"
                            <?php checked(get_option('smartbidi_rtl_footer','0'),'1'); ?>>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="smartbidi_force_rtl">Enable SmartBiDi</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_force_rtl" name="smartbidi_force_rtl" value="1"
                            <?php checked(get_option('smartbidi_force_rtl','1'),'1'); ?>>
                        </td>
                    </tr>
                </table>

                <h2>Shortcode</h2>
                <code>[smartbidi]Mixed RTL and Latin text[/smartbidi]</code>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function admin_input_rtl_script() {
        ?>
        <script>
        (function($){
            $(document).ready(function(){
                $('input[type="text"], textarea').on('input', function(){
                    var val = $(this).val();
                    if(/[\u0590-\u05FF]/.test(val)) {
                        $(this).css({'direction':'rtl','text-align':'right'});
                    } else {
                        $(this).css({'direction':'ltr','text-align':'left'});
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }
}

function smartbidi_init() {
    return SmartBiDi_Handler::get_instance();
}
add_action('plugins_loaded', 'smartbidi_init');

register_activation_hook(__FILE__, function () {
    add_option('smartbidi_force_rtl','1');
    add_option('smartbidi_rtl_navbar','0');
    add_option('smartbidi_rtl_content','0');
    add_option('smartbidi_rtl_footer','0');
});
