<?php
/**
 * Debug functionality for GW Kit
 *
 * @package GW_Kit
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class Debug
 */
class GW_Kit_Debug {
    /**
     * Whether debug mode is enabled
     *
     * @var bool
     */
    private static $debug_enabled = false;

    /**
     * Log file path
     *
     * @var string
     */
    private static $log_file = '';

    /**
     * Initialize debug functionality
     */
    public static function init() {
        // Check if WP_DEBUG is enabled
        self::$debug_enabled = (defined('WP_DEBUG') && WP_DEBUG);
        
        // Set up log file in wp-content/uploads/gw-kit/logs
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/gw-kit/logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
            
            // Create .htaccess to prevent direct access
            file_put_contents($log_dir . '/.htaccess', 'deny from all');
        }
        
        self::$log_file = $log_dir . '/debug.log';

        // Add frontend debug output if WP_DEBUG is enabled
        if (self::$debug_enabled && !is_admin()) {
            add_action('wp_footer', array(__CLASS__, 'render_debug_output'));
            add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_debug_scripts'));
        }
    }

    /**
     * Log a message
     *
     * @param mixed  $message Message to log
     * @param string $level   Log level (debug, info, warning, error)
     */
    public static function log($message, $level = 'debug') {
        if (!self::$debug_enabled) {
            return;
        }

        if (is_array($message) || is_object($message)) {
            $message = print_r($message, true);
        }

        $timestamp = current_time('mysql');
        $formatted_message = sprintf('[%s] [%s] %s' . PHP_EOL, 
            $timestamp, 
            strtoupper($level), 
            $message
        );

        error_log($formatted_message, 3, self::$log_file);

        // If WP_DEBUG_LOG is enabled, also log to WordPress debug.log
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log($formatted_message);
        }
    }

    /**
     * Log debug message
     *
     * @param mixed $message Message to log
     */
    public static function debug($message) {
        self::log($message, 'debug');
    }

    /**
     * Log info message
     *
     * @param mixed $message Message to log
     */
    public static function info($message) {
        self::log($message, 'info');
    }

    /**
     * Log warning message
     *
     * @param mixed $message Message to log
     */
    public static function warning($message) {
        self::log($message, 'warning');
    }

    /**
     * Log error message
     *
     * @param mixed $message Message to log
     */
    public static function error($message) {
        self::log($message, 'error');
    }

    /**
     * Enqueue debug scripts
     */
    /**
     * Get plugin root directory
     */
    private static function get_plugin_root() {
        return dirname(dirname(__DIR__));
    }

    /**
     * Enqueue debug scripts
     */
    public static function enqueue_debug_scripts() {
        if (!self::$debug_enabled || is_admin()) return;

        // Get the correct path to debug.js
        $debug_js_path = plugins_url('assets/js/debug.js', self::get_plugin_root() . '/gw-kit.php');

        wp_enqueue_script(
            'gw-kit-debug',
            $debug_js_path,
            array('jquery'),
            '1.0.0',
            true
        );

        $debug_data = array(
            'currentEnv' => defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'undefined',
            'environments' => array_column(get_option('gw_kit_gtm_environments', array()), 'id'),
            'wpDebug' => defined('WP_DEBUG') && WP_DEBUG,
            'isAdmin' => is_admin()
        );

        wp_localize_script('gw-kit-debug', 'gwKitDebug', $debug_data);
    }

    /**
     * Render debug output in the frontend
     */
    public static function render_debug_output() {
        // No need for additional script tag as debug.js will handle the console output
        return;
    }
}
