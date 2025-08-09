<?php
/**
 * Plugin Name: Energy Alabama Knowledge Center
 * Plugin URI: https://energyalabama.org
 * Description: Comprehensive knowledge center system for clean energy resources, dockets, and educational materials.
 * Version: 1.1.0
 * Author: ehanson
 * License: GPL v2 or later
 * Text Domain: energy-alabama-kc
 * Domain Path: /languages
 * 
 * @package Energy_Alabama_KC
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EAKC_VERSION', '1.0.9');
define('EAKC_PLUGIN_FILE', __FILE__);
define('EAKC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EAKC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EAKC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Enqueue admin assets
 */
function eakc_enqueue_admin_assets($hook) {
    // Get current screen
    $screen = get_current_screen();
    
    // Only enqueue on KC-related admin pages
    if ($screen && (
        $screen->post_type === 'kc_article' || 
        $screen->post_type === 'docket' ||
        strpos($hook, 'energy-alabama-kc') !== false
    )) {
        // Enqueue admin CSS
        wp_enqueue_style(
            'eakc-admin-css',
            EAKC_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            EAKC_VERSION
        );
        
        // Enqueue admin JS
        wp_enqueue_script(
            'eakc-admin-js',
            EAKC_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'wp-util'),
            EAKC_VERSION,
            true
        );
        
        // Localize admin script
        wp_localize_script('eakc-admin-js', 'eakc_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eakc_admin_nonce'),
            'strings' => array(
                'confirm_delete' => __('Are you sure you want to delete this item?', 'energy-alabama-kc'),
                'saving' => __('Saving...', 'energy-alabama-kc'),
                'saved' => __('Saved!', 'energy-alabama-kc'),
            )
        ));
    }
}
add_action('admin_enqueue_scripts', 'eakc_enqueue_admin_assets');

/**
 * Load plugin textdomain for translations
 */
function eakc_load_textdomain() {
    load_plugin_textdomain(
        'energy-alabama-kc',
        false,
        dirname(EAKC_PLUGIN_BASENAME) . '/languages'
    );
}
add_action('plugins_loaded', 'eakc_load_textdomain');

/**
 * Load core plugin class
 */
require_once EAKC_PLUGIN_DIR . 'includes/class-plugin.php';

/**
 * Initialize the plugin
 */
function run_energy_alabama_kc() {
    $plugin = new Energy_Alabama_KC();
    $plugin->run();
}

/**
 * Plugin activation hook
 */
function activate_energy_alabama_kc() {
    require_once EAKC_PLUGIN_DIR . 'includes/class-activator.php';
    Energy_Alabama_KC_Activator::activate();
}

/**
 * Plugin deactivation hook
 */
function deactivate_energy_alabama_kc() {
    require_once EAKC_PLUGIN_DIR . 'includes/class-deactivator.php';
    Energy_Alabama_KC_Deactivator::deactivate();
}

/**
 * Add plugin action links
 */
function eakc_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=energy-alabama-kc') . '">' . __('Settings', 'energy-alabama-kc') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . EAKC_PLUGIN_BASENAME, 'eakc_plugin_action_links');

/**
 * Check if required PHP version is met
 */
function eakc_check_php_version() {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            printf(
                __('Energy Alabama Knowledge Center requires PHP 7.4 or higher. You are running PHP %s. Please update PHP to use this plugin.', 'energy-alabama-kc'),
                PHP_VERSION
            );
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'activate_energy_alabama_kc');
register_deactivation_hook(__FILE__, 'deactivate_energy_alabama_kc');

// Check PHP version before starting plugin
if (eakc_check_php_version()) {
    // Start the plugin
    run_energy_alabama_kc();
}