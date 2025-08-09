<?php
/**
 * Define the internationalization functionality
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation
 */
class Energy_Alabama_KC_i18n {

    /**
     * Load the plugin text domain for translation
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'energy-alabama-kc',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}