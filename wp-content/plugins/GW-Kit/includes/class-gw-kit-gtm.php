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
        GW_Kit_Debug::info('GTM Module: Initializing');
        
        // Only proceed if GTM is enabled
        if (!get_option('gw_kit_gtm_enabled', false)) {
            GW_Kit_Debug::info('GTM Module: Disabled via settings');
            return;
        }

        // Add script to footer
        add_action('wp_footer', array(__CLASS__, 'add_environment_script'));
        GW_Kit_Debug::info('GTM Module: Initialized successfully');
    }

    /**
     * Add environment script to footer
     */
    public static function add_environment_script() {
        $environment = defined('WP_ENVIRONMENT_TYPE') ? WP_ENVIRONMENT_TYPE : 'production';
        GW_Kit_Debug::info('GTM Module: Adding environment script for: ' . $environment);
        
        ?>
        <script>
            console.log('WordPress Environment: <?php echo esc_js($environment); ?>');
        </script>
        <?php
    }
}
