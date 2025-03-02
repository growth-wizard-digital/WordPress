<?php
/**
 * Plugin Name: GW Kit
 * Plugin URI: https://growthwizarddigital.com/gw-kit
 * Description: Enterprise-level WordPress toolkit with advanced form functionality and security features
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Growth Wizard Digital
 * Author URI: https://growthwizarddigital.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: gw-kit
 * Domain Path: /languages
 *
 * @package GW_Kit
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

// Define plugin constants
define('GW_KIT_VERSION', '1.0.0');
define('GW_KIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('GW_KIT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('GW_KIT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Required files
require_once GW_KIT_PLUGIN_DIR . 'includes/class-gw-kit-autoloader.php';
require_once GW_KIT_PLUGIN_DIR . 'includes/class-gw-kit-settings.php';
require_once GW_KIT_PLUGIN_DIR . 'includes/class-gw-kit-debug.php';
require_once GW_KIT_PLUGIN_DIR . 'includes/class-gw-kit-gtm.php';

/**
 * Main plugin class
 */
final class GW_Kit {
    /**
     * Singleton instance
     *
     * @var GW_Kit
     */
    private static $instance = null;

    /**
     * Get singleton instance
     *
     * @return GW_Kit
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Load text domain for internationalization
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Initialize components
        $this->init_components();
        
        // Register activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain('gw-kit', false, dirname(GW_KIT_PLUGIN_BASENAME) . '/languages');
    }

    /**
     * Initialize plugin components
     */
    private function init_components() {
        // Initialize debug first
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::init();
            GW_Kit_Debug::info('GW Kit: Plugin initialization started');
        }

        // Initialize GTM functionality
        if (class_exists('GW_Kit_GTM')) {
            GW_Kit_GTM::init();
        }

        if (is_admin()) {
            // Initialize settings page
            if (class_exists('GW_Kit_Settings')) {
                GW_Kit_Settings::init();
            }
        }

        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info('GW Kit: Plugin initialization completed');
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Activation tasks
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Deactivation tasks
        flush_rewrite_rules();
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets() {
        $screen = get_current_screen();
        if (!$screen || !strpos($screen->id, 'gw-kit')) {
            return;
        }

        wp_enqueue_style(
            'gw-kit-admin',
            plugins_url('assets/css/admin.css', __FILE__),
            array(),
            filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
        );

        wp_enqueue_script(
            'gw-kit-admin',
            plugins_url('assets/js/admin.js', __FILE__),
            array('jquery'),
            filemtime(plugin_dir_path(__FILE__) . 'assets/js/admin.js'),
            true
        );
    }
}

// Initialize the plugin
GW_Kit::get_instance();
