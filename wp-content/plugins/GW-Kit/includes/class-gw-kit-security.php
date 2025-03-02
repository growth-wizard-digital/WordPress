<?php
/**
 * Security functionality for GW Kit
 *
 * @package GW_Kit
 */

namespace GW_Kit;

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class Security
 */
class Security {
    /**
     * Initialize security measures
     */
    public static function init() {
        // Add security headers
        add_action('send_headers', array(__CLASS__, 'add_security_headers'));
        
        // Add nonce field to forms
        add_action('admin_init', array(__CLASS__, 'add_nonce_to_forms'));
        
        // Prevent XML-RPC attacks if not needed
        add_filter('xmlrpc_enabled', '__return_false');
        
        // Disable file editing in admin
        if (!defined('DISALLOW_FILE_EDIT')) {
            define('DISALLOW_FILE_EDIT', true);
        }
    }

    /**
     * Add security headers
     */
    public static function add_security_headers() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Only add HSTS if SSL is enabled
        if (is_ssl()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    /**
     * Add nonce field to forms
     */
    public static function add_nonce_to_forms() {
        // Add nonce field to forms
    }

    /**
     * Validate nonce
     *
     * @param string $nonce Nonce to validate
     * @param string $action Action to check
     * @return bool
     */
    public static function verify_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            Debug::error('Security check failed.');
            return false;
        }
        return true;
    }

    /**
     * Sanitize input data
     *
     * @param mixed $data Data to sanitize
     * @return mixed
     */
    public static function sanitize_input($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::sanitize_input($value);
            }
            return $data;
        }
        
        return sanitize_text_field($data);
    }

    /**
     * Check user capabilities
     *
     * @param string $capability Capability to check
     * @return bool
     */
    public static function check_capability($capability) {
        if (!current_user_can($capability)) {
            Debug::error('Insufficient permissions.');
            return false;
        }
        return true;
    }

    /**
     * Generate secure random string
     *
     * @param int $length Length of string
     * @return string
     */
    public static function generate_random_string($length = 32) {
        return bin2hex(random_bytes($length));
    }
}
