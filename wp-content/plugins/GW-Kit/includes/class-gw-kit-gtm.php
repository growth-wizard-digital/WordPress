<?php
/**
 * GTM functionality for GW Kit
 *
 * @package GW_Kit
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class GW_Kit_GTM
 */
class GW_Kit_GTM {
    /**
     * Initialize GTM functionality
     */
    public static function init() {
        // Log that GTM module is being initialized
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info('GTM Module: Initializing');
        }
        
        // Only proceed if GTM is enabled
        if (!get_option('gw_kit_gtm_enabled', false)) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::info('GTM Module: Disabled via settings');
            }
            return;
        }

        // Add GTM code to head
        add_action('wp_head', array(__CLASS__, 'output_gtm_head_code'), 1);
        
        // Add GTM code to body
        add_action('wp_body_open', array(__CLASS__, 'output_gtm_body_code'), 1);
        
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info('GTM Module: Initialized successfully');
        }
    }

    /**
     * Get GTM code for current environment
     * 
     * @param string $code_type Either 'head' or 'body'
     * @return string|false Returns code if found, false if not found
     */
    private static function get_environment_code($code_type) {
        // Always log environment info
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info(sprintf(
                'GTM Module: Checking environment. WP_ENVIRONMENT_TYPE: %s, Code Type: %s, Is Admin: %s',
                defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'undefined',
                $code_type,
                is_admin() ? 'yes' : 'no'
            ));
        }

        // Don't load GTM code in admin
        if (is_admin()) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::info('GTM Module: Skipping GTM code in admin area');
            }
            return false;
        }

        $environments = get_option('gw_kit_gtm_environments', array());
        
        // Log available environments
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info(sprintf(
                'GTM Module: Available environments: %s',
                empty($environments) ? 'none' : print_r(array_column($environments, 'id'), true)
            ));
        }
        
        // Get WP environment type
        $current_env = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : false;
        
        // Check if WP_ENVIRONMENT_TYPE is defined
        if (!$current_env) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::error('GTM Module: WP_ENVIRONMENT_TYPE not defined in wp-config.php. Add define("WP_ENVIRONMENT_TYPE", "production"); to wp-config.php');
            }
            return false;
        }
        
        // Check if we have any environments configured
        if (empty($environments)) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::error('GTM Module: No environments configured in settings');
            }
            return false;
        }
        
        // Look for exact environment match
        $environment_found = false;
        foreach ($environments as $env) {
            $env_id = strtolower($env['id']);
            $current_env_lower = strtolower($current_env);
            
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::debug(sprintf(
                    'GTM Module: Comparing environment IDs - Current: %s, Config: %s',
                    $current_env_lower,
                    $env_id
                ));
            }
            
            if ($env_id === $current_env_lower) {
                $environment_found = true;
                $code_key = $code_type . '_code';
                $code = isset($env[$code_key]) ? $env[$code_key] : '';
                
                if (!empty($code)) {
                    if (class_exists('GW_Kit_Debug')) {
                        GW_Kit_Debug::info(sprintf(
                            'GTM Module: Found %s code for environment: %s',
                            $code_type,
                            $current_env
                        ));
                    }
                    return $code;
                } else {
                    if (class_exists('GW_Kit_Debug')) {
                        GW_Kit_Debug::warning(sprintf(
                            'GTM Module: Environment %s found but no %s code configured',
                            $current_env,
                            $code_type
                        ));
                    }
                }
                break;
            }
        }
        
        // If no matching environment found
        if (!$environment_found) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::error(sprintf(
                    'GTM Module: No matching environment found. Current environment from WP_ENVIRONMENT_TYPE: %s. Available environments: %s',
                    $current_env,
                    implode(', ', array_column($environments, 'id'))
                ));
            }
        }
        
        return false;
    }

    /**
     * Output GTM code in head
     */
    public static function output_gtm_head_code() {
        $code = self::get_environment_code('head');
        if ($code !== false) {
            echo $code; // Already sanitized during save
        }
    }

    /**
     * Output GTM code in body
     */
    public static function output_gtm_body_code() {
        $code = self::get_environment_code('body');
        if ($code !== false) {
            echo $code; // Already sanitized during save
        }
    }
}
