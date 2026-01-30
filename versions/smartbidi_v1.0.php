<?php
/**
 * Plugin Name: SmartBiDi
 * Plugin URI:https://github.com/davidmaimoun/wordpress-plugins/smartbidi
 * Description: Automatically detects mixed RTL (Hebrew, Arabic, etc.) and Latin text and applies proper BiDi formatting for optimal display when adding Woo products. Optional "Force RTL" mode for all pages.
 * Version: 1.0.0
 * Author: David Maimoun
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: smartbidi
 * Domain Path: /languages
 */

// Security: Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class SmartBidi_Handler {

    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        // Load frontend & admin assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

        // Content filters
        add_filter('the_content', array($this, 'process_bidi_text'), 999);
        add_filter('the_title', array($this, 'process_bidi_text'), 999);
        add_filter('the_excerpt', array($this, 'process_bidi_text'), 999);
        add_filter('comment_text', array($this, 'process_bidi_text'), 999);
        add_filter('widget_text', array($this, 'process_bidi_text'), 999);

        // Admin settings
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));

        // Shortcode
        add_shortcode('smartbidi', array($this, 'shortcode_handler'));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'smartbidi-style',
            plugin_dir_url(__FILE__) . 'css/style.css',
            array(),
            '1.1.0'
        );

        wp_enqueue_script(
            'smartbidi-script',
            plugin_dir_url(__FILE__) . 'js/script.js',
            array('jquery'),
            '1.1.0',
            true
        );
    }

    /**
     * Detect RTL characters (Hebrew, Arabic, etc.)
     */
    private function contains_rtl($text) {
        return preg_match('/[\x{0590}-\x{08FF}]/u', $text);
    }

    /**
     * Detect Latin characters
     */
    private function contains_latin($text) {
        return preg_match('/[a-zA-Z]/', $text);
    }

    /**
     * Process BiDi content (RTL + LTR)
     */
    public function process_bidi_text($content) {
        $enabled = get_option('smartbidi_enable', '1');
        $force_rtl = get_option('smartbidi_force_rtl', '0');

        if ($enabled !== '1') {
            return $content;
        }

        // If Force RTL is enabled, wrap all content as RTL
        if ($force_rtl === '1') {
            return '<div class="rtl-only" dir="rtl">' . $content . '</div>';
        }

        // Normal BiDi detection
        if ($this->contains_rtl($content) && $this->contains_latin($content)) {
            $content = '<div class="bidi-mixed" dir="auto">' . $content . '</div>';
        } elseif ($this->contains_rtl($content)) {
            $content = '<div class="rtl-only" dir="rtl">' . $content . '</div>';
        }

        return $content;
    }

    /**
     * Shortcode handler
     */
    public function shortcode_handler($atts, $content = '') {
        return $this->process_bidi_text($content);
    }

    /**
     * Add admin settings page
     */
    public function add_admin_menu() {
        add_options_page(
            'SmartBiDi Settings',
            'SmartBiDi',
            'manage_options',
            'smartbidi',
            array($this, 'admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('smartbidi_settings', 'smartbidi_enable');
        register_setting('smartbidi_settings', 'smartbidi_force_rtl');
    }

    /**
     * Admin settings page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>SmartBiDi Settings</h1>

            <form method="post" action="options.php">
                <?php settings_fields('smartbidi_settings'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="smartbidi_enable">Enable automatic BiDi detection</label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   id="smartbidi_enable"
                                   name="smartbidi_enable"
                                   value="1"
                                   <?php checked(get_option('smartbidi_enable', '1'), '1'); ?>>
                            <p class="description">
                                Automatically format mixed RTL (Hebrew, Arabic) and Latin text.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="smartbidi_force_rtl">Force RTL for all content</label>
                        </th>
                        <td>
                            <input type="checkbox"
                                   id="smartbidi_force_rtl"
                                   name="smartbidi_force_rtl"
                                   value="1"
                                   <?php checked(get_option('smartbidi_force_rtl', '0'), '1'); ?>>
                            <p class="description">
                                When enabled, all content will be wrapped as RTL regardless of detected text.
                            </p>
                        </td>
                    </tr>
                </table>

                <h2>Shortcode</h2>
                <code>[smartbidi]Mixed RTL and Latin text here[/smartbidi]</code>

                <h2>Live Example</h2>
                <div style="margin:20px 0;padding:20px;background:#f0f0f0;border-radius:6px;">
                    <p class="bidi-mixed" dir="auto">
                        Hello שלום مرحبا World עולם
                    </p>
                    <small>This example shows automatic BiDi handling.</small>
                </div>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

/**
 * Initialize plugin
 */
function smartbidi_init() {
    return SmartBidi_Handler::get_instance();
}
add_action('plugins_loaded', 'smartbidi_init');

/**
 * Activation hook
 */
register_activation_hook(__FILE__, function () {
    add_option('smartbidi_enable', '1');
    add_option('smartbidi_force_rtl', '0');
});

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, function () {
    // Optional cleanup
});
