<?php
/**
 * Autoloader class for GW Kit
 *
 * @package GW_Kit
 */

if (!defined('ABSPATH')) {
    exit('Direct access denied.');
}

/**
 * Class GW_Kit_Autoloader
 */
class GW_Kit_Autoloader {
    /**
     * Register autoloader
     */
    public static function register() {
        spl_autoload_register(array(new self(), 'autoload'));
    }

    /**
     * Autoload classes
     *
     * @param string $class Class name.
     */
    private function autoload($class) {
        // Project-specific namespace prefix
        $prefix = 'GW_Kit\\';

        // Base directory for the namespace prefix
        $base_dir = GW_KIT_PLUGIN_DIR . 'includes/';

        // Check if the class uses the namespace prefix
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        // Get the relative class name
        $relative_class = substr($class, $len);

        // Replace namespace separators with directory separators
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Load the file if it exists
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Register the autoloader
GW_Kit_Autoloader::register();
