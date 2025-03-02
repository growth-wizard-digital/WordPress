<?php
/**
 * Settings page functionality for GW Kit
 *
 * @package GW_Kit
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class GW_Kit_Settings
 */
class GW_Kit_Settings {
    /**
     * Initialize settings
     */
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('admin_init', array(__CLASS__, 'register_settings'));
        add_filter('plugin_action_links_' . plugin_basename(GW_KIT_PLUGIN_DIR . 'gw-kit.php'), array(__CLASS__, 'add_settings_link'));
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
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="white" d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"/></svg>'),
            30
        );
    }

    /**
     * Register settings
     */
    public static function register_settings() {
        // General Settings
        register_setting('gw_kit_settings', 'gw_kit_gtm_enabled');

        add_settings_section(
            'gw_kit_main_section',
            __('General Settings', 'gw-kit'),
            array(__CLASS__, 'render_main_section'),
            'gw_kit_settings'
        );

        add_settings_field(
            'gw_kit_gtm_enabled',
            __('GTM Manager', 'gw-kit'),
            array(__CLASS__, 'render_gtm_toggle'),
            'gw_kit_settings',
            'gw_kit_main_section'
        );

        // GTM Settings
        register_setting('gw_kit_gtm_settings', 'gw_kit_gtm_environments', array(
            'sanitize_callback' => array(__CLASS__, 'sanitize_gtm_environments'),
            'default' => array(
                array(
                    'id' => 'production',
                    'name' => 'Production',
                    'head_code' => '',
                    'body_code' => ''
                )
            )
        ));

        add_settings_section(
            'gw_kit_gtm_section',
            __('GTM Settings', 'gw-kit'),
            array(__CLASS__, 'render_gtm_section'),
            'gw_kit_gtm_settings'
        );

        add_settings_field(
            'gw_kit_gtm_environments',
            __('GTM Environments', 'gw-kit'),
            array(__CLASS__, 'render_gtm_environments_field'),
            'gw_kit_gtm_settings',
            'gw_kit_gtm_section'
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
                    <form method="post" action="options.php">
                        <?php
                        settings_fields('gw_kit_gtm_settings');
                        do_settings_sections('gw_kit_gtm_settings');
                        submit_button();
                        ?>
                    </form>
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
            .gw-kit-code-editor {
                width: 100%;
                min-height: 150px;
                font-family: monospace;
                white-space: pre;
                overflow: auto;
                padding: 10px;
                background: #f5f5f5;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
        </style>
        <?php
    }

    /**
     * Render GTM code field
     */
    public static function render_gtm_code_field() {
        $code = get_option('gw_kit_gtm_head_code', '');
        ?>
        <textarea name="gw_kit_gtm_head_code" 
                  class="gw-kit-code-editor" 
                  placeholder="<?php _e('Paste your GTM head code here...', 'gw-kit'); ?>"><?php echo esc_textarea($code); ?></textarea>
        <p class="description"><?php _e('Paste your Google Tag Manager code here (including the script tags).', 'gw-kit'); ?></p>
        <?php
    }

    /**
     * Sanitize GTM code
     *
     * @param string $input The input to sanitize
     * @return string
     */
    /**
     * Render main settings section
     *
     * @param array $args Arguments passed to the callback
     */
    public static function render_main_section($args) {
        echo '<p>' . esc_html__('Configure general settings for the Magic Kit plugin.', 'gw-kit') . '</p>';
    }

    /**
     * Render GTM settings section
     *
     * @param array $args Arguments passed to the callback
     */
    public static function render_gtm_section($args) {
        echo '<p>' . esc_html__('Configure your Google Tag Manager integration.', 'gw-kit') . '</p>';
    }

    /**
     * Render GTM environments field
     */
    public static function render_gtm_environments_field() {
        $environments = get_option('gw_kit_gtm_environments', array(
            array(
                'id' => 'production',
                'name' => 'Production',
                'head_code' => '',
                'body_code' => ''
            )
        ));
        
        $active_env = isset($_GET['env']) ? sanitize_text_field($_GET['env']) : 'production';
        ?>
        <div class="gw-kit-vertical-tabs">
            <div class="gw-kit-tabs-header">
                <h3><?php _e('Environments', 'gw-kit'); ?></h3>
                <button type="button" class="button" id="gw-kit-manage-envs">
                    <span class="dashicons dashicons-admin-generic"></span>
                    <?php _e('Manage', 'gw-kit'); ?>
                </button>
            </div>
            
            <div class="gw-kit-tabs-container">
                <div class="gw-kit-tabs-list">
                    <?php foreach ($environments as $env): ?>
                        <button type="button" 
                                class="gw-kit-tab <?php echo $active_env === $env['id'] ? 'active' : ''; ?>" 
                                data-env="<?php echo esc_attr($env['id']); ?>">
                            <?php echo esc_html($env['name']); ?>
                            <span class="dashicons dashicons-trash delete-env hidden"></span>
                        </button>
                    <?php endforeach; ?>
                    
                    <div class="gw-kit-new-env hidden">
                        <input type="text" class="new-env-name" placeholder="<?php _e('Environment name', 'gw-kit'); ?>">
                        <div class="new-env-actions">
                            <button type="button" class="button add-env"><?php _e('Add', 'gw-kit'); ?></button>
                            <button type="button" class="button cancel-env"><?php _e('Cancel', 'gw-kit'); ?></button>
                        </div>
                    </div>
                    
                    <button type="button" class="button add-env-button">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Environment', 'gw-kit'); ?>
                    </button>
                </div>
                
                <div class="gw-kit-tabs-content">
                    <?php foreach ($environments as $env): ?>
                        <div class="gw-kit-tab-content <?php echo $active_env === $env['id'] ? 'active' : ''; ?>" 
                             data-env="<?php echo esc_attr($env['id']); ?>">
                            <div class="gw-kit-code-field">
                                <label><?php _e('GTM Head Code', 'gw-kit'); ?></label>
                                <textarea name="gw_kit_gtm_environments[<?php echo esc_attr($env['id']); ?>][head_code]" 
                                          class="gw-kit-code-editor" 
                                          placeholder="<?php _e('Paste your GTM head code here...', 'gw-kit'); ?>"><?php echo esc_textarea($env['head_code']); ?></textarea>
                            </div>
                            
                            <div class="gw-kit-code-field">
                                <label><?php _e('GTM Body Code', 'gw-kit'); ?></label>
                                <textarea name="gw_kit_gtm_environments[<?php echo esc_attr($env['id']); ?>][body_code]" 
                                          class="gw-kit-code-editor" 
                                          placeholder="<?php _e('Paste your GTM body code here...', 'gw-kit'); ?>"><?php echo esc_textarea($env['body_code']); ?></textarea>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Sanitize GTM environments
     *
     * @param array $input The environments array to sanitize
     * @return array
     */
    public static function sanitize_gtm_environments($input) {
        if (!is_array($input)) {
            return array(
                array(
                    'id' => 'production',
                    'name' => 'Production',
                    'head_code' => '',
                    'body_code' => ''
                )
            );
        }

        $allowed_html = array(
            'script' => array(
                'async' => array(),
                'src' => array()
            ),
            'noscript' => array(),
            'iframe' => array(
                'src' => array(),
                'height' => array(),
                'width' => array(),
                'style' => array()
            )
        );

        $sanitized = array();
        foreach ($input as $env) {
            if (!isset($env['id']) || !isset($env['name'])) continue;

            $sanitized[] = array(
                'id' => sanitize_key($env['id']),
                'name' => sanitize_text_field($env['name']),
                'head_code' => wp_kses(isset($env['head_code']) ? $env['head_code'] : '', $allowed_html),
                'body_code' => wp_kses(isset($env['body_code']) ? $env['body_code'] : '', $allowed_html)
            );
        }

        // Ensure we always have at least production environment
        if (empty($sanitized)) {
            $sanitized[] = array(
                'id' => 'production',
                'name' => 'Production',
                'head_code' => '',
                'body_code' => ''
            );
        }

        return $sanitized;
    }
}
