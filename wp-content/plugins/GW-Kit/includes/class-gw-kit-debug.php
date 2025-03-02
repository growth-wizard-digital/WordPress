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
     * Render debug output in the frontend
     */
    public static function render_debug_output() {
        if (!self::$debug_enabled || is_admin()) return;

        $current_env = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'undefined';
        $environments = get_option('gw_kit_gtm_environments', array());
        $env_list = empty($environments) ? 'none' : implode(', ', array_column($environments, 'id'));

        echo '<div class="gw-kit-debug" style="position: fixed; bottom: 20px; right: 20px; background: rgba(0,0,0,0.8); color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 12px; z-index: 9999;">';
        echo '<strong>GW Kit Debug</strong><br>';
        echo sprintf('Current Environment: %s<br>', esc_html($current_env));
        echo sprintf('Available Environments: %s<br>', esc_html($env_list));
        echo sprintf('WP_DEBUG: %s<br>', defined('WP_DEBUG') && WP_DEBUG ? 'enabled' : 'disabled');
        echo '</div>';
    }
}
