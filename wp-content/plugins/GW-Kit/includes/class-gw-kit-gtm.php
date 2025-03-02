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
        
        // Add environment logging to footer
        add_action('wp_footer', array(__CLASS__, 'add_environment_script'));
        
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info('GTM Module: Initialized successfully');
        }
    }

    /**
     * Output GTM code in head
     */
    public static function output_gtm_head_code() {
        $gtm_code = get_option('gw_kit_gtm_head_code', '');
        if (!empty($gtm_code)) {
            if (class_exists('GW_Kit_Debug')) {
                GW_Kit_Debug::info('GTM Module: Outputting GTM code in head');
            }
            echo $gtm_code; // Already sanitized during save, includes script tags
        }
    }

    /**
     * Add environment script to footer
     */
    public static function add_environment_script() {
        $environment = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production';
        
        if (class_exists('GW_Kit_Debug')) {
            GW_Kit_Debug::info('GTM Module: Adding environment script for: ' . $environment);
        }
        
        ?>
        <script>
            console.log('WordPress Environment: <?php echo esc_js($environment); ?>');
        </script>
        <?php
    }
}
