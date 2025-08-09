<?php
/**
 * Fired during plugin activation
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin activator class
 * 
 * This class defines all code necessary to run during plugin activation
 */
class Energy_Alabama_KC_Activator {

    /**
     * Run activation tasks
     * 
     * @since 1.0.0
     */
    public static function activate() {
        self::check_requirements();
        self::register_post_types_and_taxonomies();
        self::create_default_options();
        self::create_default_pages();
        self::flush_rewrite_rules();
        self::set_default_capabilities();
    }

    /**
     * Check if system requirements are met
     * 
     * @since 1.0.0
     */
    private static function check_requirements() {
        // Check PHP version
        if (version_compare(phpversion(), '7.4', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('Energy Alabama Knowledge Center requires PHP 7.4 or higher. You are running version ', 'energy-alabama-kc') . phpversion(),
                __('Plugin Activation Error', 'energy-alabama-kc'),
                array('back_link' => true)
            );
        }

        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('Energy Alabama Knowledge Center requires WordPress 5.0 or higher. You are running version ', 'energy-alabama-kc') . get_bloginfo('version'),
                __('Plugin Activation Error', 'energy-alabama-kc'),
                array('back_link' => true)
            );
        }
    }

    /**
     * Register post types and taxonomies for activation
     * 
     * @since 1.0.0
     */
    private static function register_post_types_and_taxonomies() {
        // Load the post types and taxonomies classes
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-post-types.php';
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-taxonomies.php';

        // Register post types and taxonomies
        $post_types = new Energy_Alabama_KC_Post_Types();
        $post_types->register_post_types();

        $taxonomies = new Energy_Alabama_KC_Taxonomies();
        $taxonomies->register_taxonomies();
    }

    /**
     * Create default plugin options
     * 
     * @since 1.0.0
     */
    private static function create_default_options() {
        $default_options = array(
            'template_mode'              => 'php',
            'search_results_per_page'    => 10,
            'enable_spanish'             => false,
            'performance_mode'           => true,
            'icon_library'               => 'default',
            'hide_from_visitors'         => true, // Hide during development
            'enable_structured_data'     => true,
            'enable_breadcrumbs'         => true,
            'search_placeholder'         => __('Search knowledge center...', 'energy-alabama-kc'),
            'difficulty_levels'          => array(
                'beginner'     => __('Beginner', 'energy-alabama-kc'),
                'intermediate' => __('Intermediate', 'energy-alabama-kc'),
                'advanced'     => __('Advanced', 'energy-alabama-kc')
            ),
            'resource_types'             => array(
                'pdf'          => __('PDF Document', 'energy-alabama-kc'),
                'doc'          => __('Word Document', 'energy-alabama-kc'),
                'sheet'        => __('Spreadsheet', 'energy-alabama-kc'),
                'presentation' => __('Presentation', 'energy-alabama-kc'),
                'video'        => __('Video', 'energy-alabama-kc'),
                'external'     => __('External Link', 'energy-alabama-kc')
            )
        );

        add_option('eakc_options', $default_options);
        add_option('eakc_version', EAKC_VERSION);
        add_option('eakc_activation_date', current_time('timestamp'));
    }

    /**
     * Create default knowledge center pages
     * 
     * @since 1.0.0
     */
    private static function create_default_pages() {
        // Check if knowledge center landing page exists
        $kc_page = get_page_by_path('knowledge-center');
        
        if (!$kc_page) {
            $kc_page_id = wp_insert_post(array(
                'post_title'   => __('Knowledge Center', 'energy-alabama-kc'),
                'post_name'    => 'knowledge-center',
                'post_content' => self::get_default_page_content(),
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_author'  => get_current_user_id(),
                'meta_input'   => array(
                    '_eakc_is_landing_page' => true
                )
            ));

            // Store the page ID for future reference
            update_option('eakc_landing_page_id', $kc_page_id);
        }
    }

    /**
     * Get default content for knowledge center landing page
     * 
     * @since 1.0.0
     * @return string
     */
    private static function get_default_page_content() {
        return '<!-- wp:heading -->
<h2>' . __('Welcome to the Energy Alabama Knowledge Center', 'energy-alabama-kc') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . __('Find comprehensive information about clean energy, educational resources, and regulatory documents.', 'energy-alabama-kc') . '</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[eakc_search_form]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[eakc_category_grid]
<!-- /wp:shortcode -->';
    }

    /**
     * Flush rewrite rules after activation
     * 
     * @since 1.0.0
     */
    private static function flush_rewrite_rules() {
        flush_rewrite_rules();
        
        // Also update option to track that we need to flush rules
        update_option('eakc_flush_rewrite_rules', true);
    }

    /**
     * Set default capabilities for the plugin
     * 
     * @since 1.0.0
     */
    private static function set_default_capabilities() {
        // Get administrator role
        $admin_role = get_role('administrator');
        
        if ($admin_role) {
            // Add capabilities for managing KC content
            $admin_role->add_cap('manage_energy_alabama_kc');
            $admin_role->add_cap('edit_kc_articles');
            $admin_role->add_cap('edit_others_kc_articles');
            $admin_role->add_cap('publish_kc_articles');
            $admin_role->add_cap('read_private_kc_articles');
            $admin_role->add_cap('delete_kc_articles');
            $admin_role->add_cap('delete_others_kc_articles');
            $admin_role->add_cap('edit_dockets');
            $admin_role->add_cap('edit_others_dockets');
            $admin_role->add_cap('publish_dockets');
            $admin_role->add_cap('read_private_dockets');
            $admin_role->add_cap('delete_dockets');
            $admin_role->add_cap('delete_others_dockets');
        }

        // Get editor role
        $editor_role = get_role('editor');
        
        if ($editor_role) {
            // Add capabilities for editing KC content
            $editor_role->add_cap('edit_kc_articles');
            $editor_role->add_cap('edit_others_kc_articles');
            $editor_role->add_cap('publish_kc_articles');
            $editor_role->add_cap('read_private_kc_articles');
            $editor_role->add_cap('delete_kc_articles');
            $editor_role->add_cap('edit_dockets');
            $editor_role->add_cap('edit_others_dockets');
            $editor_role->add_cap('publish_dockets');
            $editor_role->add_cap('read_private_dockets');
            $editor_role->add_cap('delete_dockets');
        }
    }
}