<?php
/**
 * Admin functionality for GW Kit
 *
 * @package GW_Kit
 */

namespace GW_Kit\Admin;

use GW_Kit\Debug;

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class Admin
 */
class Admin {
    /**
     * Initialize admin functionality
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_filter('plugin_action_links_' . GW_KIT_PLUGIN_BASENAME, array(__CLASS__, 'add_settings_link'));
    }

    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            __('Magic Kit', 'gw-kit'),
            __('Magic Kit', 'gw-kit'),
            'manage_options',
            'gw-kit-settings',
            array(__CLASS__, 'render_settings_page'),
            'dashicons-magic',
            30
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        register_setting('gw_kit_settings', 'gw_kit_gtm_enabled');

        add_settings_section(
            'gw_kit_main_section',
            __('General Settings', 'gw-kit'),
            null,
            'gw_kit_settings'
        );

        add_settings_field(
            'gw_kit_gtm_enabled',
            __('GTM Manager', 'gw-kit'),
            array(__CLASS__, 'render_gtm_toggle'),
            'gw_kit_settings',
            'gw_kit_main_section'
        );
    }

    /**
     * Render GTM toggle field
     */
    public static function render_gtm_toggle() {
        $enabled = get_option('gw_kit_gtm_enabled', false);
        ?>
        <label class="gw-kit-toggle">
            <input type="checkbox" name="gw_kit_gtm_enabled" value="1" <?php checked($enabled, true); ?>>
            <span class="gw-kit-toggle-slider"></span>
        </label>
        <p class="description"><?php _e('Enable Google Tag Manager integration', 'gw-kit'); ?></p>
        <?php
    }

    /**
     * Add settings link to plugin listing
     *
     * @param array $links Plugin action links
     * @return array
     */
    public static function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page=gw-kit-settings'),
            __('Settings', 'gw-kit')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Render settings page
     */
    public static function render_settings_page() {
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        $gtm_enabled = get_option('gw_kit_gtm_enabled', false);
        ?>
        <div class="wrap">
            <h1><?php _e('Magic Kit Settings', 'gw-kit'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=gw-kit-settings&tab=general" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'gw-kit'); ?>
                </a>
                <?php if ($gtm_enabled): ?>
                <a href="?page=gw-kit-settings&tab=gtm" 
                   class="nav-tab <?php echo $active_tab === 'gtm' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('GTM Manager', 'gw-kit'); ?>
                </a>
                <?php endif; ?>
            </h2>

            <?php if ($active_tab === 'general'): ?>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('gw_kit_settings');
                    do_settings_sections('gw_kit_settings');
                    submit_button();
                    ?>
                </form>
            <?php elseif ($active_tab === 'gtm' && $gtm_enabled): ?>
                <div class="gtm-settings">
                    <h2><?php _e('Google Tag Manager Settings', 'gw-kit'); ?></h2>
                    <p><?php _e('GTM settings will be added here.', 'gw-kit'); ?></p>
                </div>
            <?php endif; ?>
        </div>

        <style>
            .gw-kit-toggle {
                position: relative;
                display: inline-block;
                width: 50px;
                height: 24px;
            }
            .gw-kit-toggle input {
                opacity: 0;
                width: 0;
                height: 0;
            }
            .gw-kit-toggle-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 24px;
            }
            .gw-kit-toggle-slider:before {
                position: absolute;
                content: "";
                height: 16px;
                width: 16px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }
            .gw-kit-toggle input:checked + .gw-kit-toggle-slider {
                background-color: #2271b1;
            }
            .gw-kit-toggle input:checked + .gw-kit-toggle-slider:before {
                transform: translateX(26px);
            }
        </style>
        <?php
    }
}
