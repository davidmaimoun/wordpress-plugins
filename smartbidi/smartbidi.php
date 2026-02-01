<?php
/**
 * Plugin Name: SmartBiDi Zones RTL
 * Plugin URI: https://github.com/davidmaimoun/wordpress-plugins/smartbidi
 * Description: Detects mixed RTL (Hebrew, Arabic, etc.) and Latin text in WooCommerce products and allows forcing RTL on Navbar, Content, and Footer separately.
 * Version: 2.0.0
 * Author: David Maimoun
 * Author URI: https://github.com/davidmaimoun
 * License: GPLv2 or later
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
        // Assets
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);

        // WooCommerce product titles (frontend only)
        add_filter('woocommerce_product_get_name', [$this, 'process_bidi_text'], 999, 1);

        // Admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);

        // Apply RTL zones
        add_action('wp_head', [$this, 'apply_rtl_zones']);

        // Admin product input RTL detection
        add_action('admin_footer', [$this, 'admin_input_rtl_script']);

        // Shortcode
        add_shortcode('smartbidi', [$this, 'shortcode_handler']);
    }

    public function enqueue_assets() {
        wp_enqueue_style('smartbidi-style', plugin_dir_url(__FILE__) . 'css/style.css', [], '2.1.0');
        wp_enqueue_script('smartbidi-script', plugin_dir_url(__FILE__) . 'js/script.js', ['jquery'], '2.1.0', true);
    }

    private function contains_rtl($text) {
        return preg_match('/[\x{0590}-\x{08FF}]/u', $text);
    }

    private function contains_latin($text) {
        return preg_match('/[a-zA-Z]/', $text);
    }

    /**
     * Process BiDi content
     * - Frontend: wrap in div/span for RTL/LTR display
     * - Admin: return raw text (no HTML)
     */
    public function process_bidi_text($content) {
        $enabled = get_option('smartbidi_force_rtl', '1');
        if ($enabled !== '1') return $content;

        // ❌ Admin: ne rien ajouter
        if (is_admin()) {
            return $content;
        }

        // Frontend: wrap for display
        if ($this->contains_rtl($content) && $this->contains_latin($content)) {
            return '<span class="bidi-mixed" dir="auto">' . esc_html($content) . '</span>';
        } elseif ($this->contains_rtl($content)) {
            return '<span class="rtl-only" dir="rtl">' . esc_html($content) . '</span>';
        }

        return esc_html($content);
    }

    public function apply_rtl_zones() {
        $css = '';

        if (get_option('smartbidi_rtl_navbar','0') === '1') {
            $css .= 'header, .navbar, .site-header { direction: rtl !important; text-align: right !important; }';
        }


        if (get_option('smartbidi_rtl_content','0') === '1') {
            $css .= '
                main, .content-area, .site-content, .main-content, .container, .wrap {
                    direction: rtl !important;
                    text-align: right !important;
                }
                /* Breadcrumbs RTL fix without reversing elements */
                .ct-breadcrumbs {
                    direction: rtl !important;
                    text-align: right !important;
                }
                .ct-breadcrumbs span {
                    display: inline-flex;
                    flex-direction: row !important; /* keep order */
                    align-items: center;
                }
                .ct-breadcrumbs svg.ct-separator {
                    transform: rotate(180deg); /* flip arrow */
                }
            ';
        }
        

        if (get_option('smartbidi_rtl_footer','0') === '1') {
            $css .= 'footer, .site-footer { direction: rtl !important; text-align: right !important; }';
        }

        if ($css) {
            echo "<style>$css</style>";
        }
    }

    // Shortcode
    public function shortcode_handler($atts, $content = '') {
        return $this->process_bidi_text($content);
    }

    // Admin menu
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
        register_setting('smartbidi_settings', 'smartbidi_force_rtl');
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
                        <th scope="row"><label for="smartbidi_rtl_navbar">Force RTL Navbar</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_navbar" name="smartbidi_rtl_navbar" value="1" <?php checked(get_option('smartbidi_rtl_navbar','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to your site navbar/header.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smartbidi_rtl_content">Force RTL Content</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_content" name="smartbidi_rtl_content" value="1" <?php checked(get_option('smartbidi_rtl_content','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to main content divs, including WooCommerce products.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smartbidi_rtl_footer">Force RTL Footer</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_rtl_footer" name="smartbidi_rtl_footer" value="1" <?php checked(get_option('smartbidi_rtl_footer','0'),'1'); ?>>
                            <p class="description">Apply RTL direction to site footer.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="smartbidi_force_rtl">Enable SmartBiDi</label></th>
                        <td>
                            <input type="checkbox" id="smartbidi_force_rtl" name="smartbidi_force_rtl" value="1" <?php checked(get_option('smartbidi_force_rtl','1'),'1'); ?>>
                            <p class="description">Globally enable SmartBiDi (auto BiDi detection + RTL zones).</p>
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

    // Admin product input RTL detection
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

add_action('plugins_loaded','smartbidi_init');

register_activation_hook(__FILE__, function () {
    add_option('smartbidi_force_rtl','1');
    add_option('smartbidi_rtl_navbar','0');
    add_option('smartbidi_rtl_content','0');
    add_option('smartbidi_rtl_footer','0');
});

register_deactivation_hook(__FILE__, function () {});
