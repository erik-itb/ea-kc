<?php
/**
 * Fired during plugin deactivation
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin deactivator class
 * 
 * This class defines all code necessary to run during plugin deactivation
 */
class Energy_Alabama_KC_Deactivator {

    /**
     * Run deactivation tasks
     * 
     * @since 1.0.0
     */
    public static function deactivate() {
        self::flush_rewrite_rules();
        self::clear_scheduled_events();
        self::cleanup_transients();
    }

    /**
     * Flush rewrite rules on deactivation
     * 
     * @since 1.0.0
     */
    private static function flush_rewrite_rules() {
        flush_rewrite_rules();
    }

    /**
     * Clear any scheduled events
     * 
     * @since 1.0.0
     */
    private static function clear_scheduled_events() {
        // Clear any cron jobs that might be scheduled
        wp_clear_scheduled_hook('eakc_cleanup_expired_cache');
        wp_clear_scheduled_hook('eakc_generate_search_index');
    }

    /**
     * Cleanup transients and temporary data
     * 
     * @since 1.0.0
     */
    private static function cleanup_transients() {
        global $wpdb;
        
        // Delete all plugin-related transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_eakc_%'
            )
        );
        
        // Delete transient timeouts
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_eakc_%'
            )
        );
    }
}