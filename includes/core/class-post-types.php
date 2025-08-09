<?php
/**
 * Register custom post types for the plugin
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Custom post types class
 */
class Energy_Alabama_KC_Post_Types {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('admin_menu', array($this, 'add_docket_submenu'), 999); // Run late to ensure proper order
        add_filter('post_updated_messages', array($this, 'updated_messages'));
        
        // Check if we need to flush rewrite rules
        add_action('init', array($this, 'maybe_flush_rewrite_rules'));
        
        // Set flag to flush rewrite rules after enabling docket archives
        add_action('init', array($this, 'check_docket_archive_change'), 11);
    }

    /**
     * Get instance (singleton pattern)
     */
    public static function get_instance() {
        static $instance = null;
        if (null === $instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Register all custom post types
     */
    public function register_post_types() {
        $this->register_kc_article();
        $this->register_docket();
    }

    /**
     * Register Knowledge Center Article post type
     */
    private function register_kc_article() {
        $labels = array(
            'name'                  => _x('KC Articles', 'Post type general name', 'energy-alabama-kc'),
            'singular_name'         => _x('KC Article', 'Post type singular name', 'energy-alabama-kc'),
            'menu_name'             => _x('Knowledge Center', 'Admin Menu text', 'energy-alabama-kc'),
            'name_admin_bar'        => _x('KC Article', 'Add New on Toolbar', 'energy-alabama-kc'),
            'add_new'               => __('Add New', 'energy-alabama-kc'),
            'add_new_item'          => __('Add New KC Article', 'energy-alabama-kc'),
            'new_item'              => __('New KC Article', 'energy-alabama-kc'),
            'edit_item'             => __('Edit KC Article', 'energy-alabama-kc'),
            'view_item'             => __('View KC Article', 'energy-alabama-kc'),
            'all_items'             => __('All KC Articles', 'energy-alabama-kc'),
            'search_items'          => __('Search KC Articles', 'energy-alabama-kc'),
            'parent_item_colon'     => __('Parent KC Articles:', 'energy-alabama-kc'),
            'not_found'             => __('No KC articles found.', 'energy-alabama-kc'),
            'not_found_in_trash'    => __('No KC articles found in Trash.', 'energy-alabama-kc'),
            'featured_image'        => _x('Featured Image', 'Overrides the "Featured Image" phrase', 'energy-alabama-kc'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'energy-alabama-kc'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'energy-alabama-kc'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'energy-alabama-kc'),
            'archives'              => _x('KC Article archives', 'The post type archive label', 'energy-alabama-kc'),
            'insert_into_item'      => _x('Insert into KC article', 'Overrides the "Insert into post" phrase', 'energy-alabama-kc'),
            'uploaded_to_this_item' => _x('Uploaded to this KC article', 'Overrides the "Uploaded to this post" phrase', 'energy-alabama-kc'),
            'filter_items_list'     => _x('Filter KC articles list', 'Screen reader text for the filter links', 'energy-alabama-kc'),
            'items_list_navigation' => _x('KC articles list navigation', 'Screen reader text for the pagination', 'energy-alabama-kc'),
            'items_list'            => _x('KC articles list', 'Screen reader text for the items list', 'energy-alabama-kc'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug'       => 'knowledge-center',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => 'kc-articles', // Changed from 'knowledge-center' to avoid conflict
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-book-alt',
            'supports'           => array(
                'title',
                'editor',
                'excerpt',
                'thumbnail',
                'revisions',
                'custom-fields'
            ),
            'show_in_rest'       => true,
            'rest_base'          => 'kc-articles',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'taxonomies'         => array('kc_category', 'kc_tag'),
            'template'           => array(
                array('core/paragraph', array(
                    'placeholder' => __('Start writing your knowledge center article...', 'energy-alabama-kc')
                ))
            )
        );

        register_post_type('kc_article', $args);
    }

    /**
     * Register Docket post type
     */
    private function register_docket() {
        $labels = array(
            'name'                  => _x('Dockets', 'Post type general name', 'energy-alabama-kc'),
            'singular_name'         => _x('Docket', 'Post type singular name', 'energy-alabama-kc'),
            'menu_name'             => _x('Dockets', 'Admin Menu text', 'energy-alabama-kc'),
            'name_admin_bar'        => _x('Docket', 'Add New on Toolbar', 'energy-alabama-kc'),
            'add_new'               => __('Add New', 'energy-alabama-kc'),
            'add_new_item'          => __('Add New Docket', 'energy-alabama-kc'),
            'new_item'              => __('New Docket', 'energy-alabama-kc'),
            'edit_item'             => __('Edit Docket', 'energy-alabama-kc'),
            'view_item'             => __('View Docket', 'energy-alabama-kc'),
            'all_items'             => __('All Dockets', 'energy-alabama-kc'),
            'search_items'          => __('Search Dockets', 'energy-alabama-kc'),
            'parent_item_colon'     => __('Parent Dockets:', 'energy-alabama-kc'),
            'not_found'             => __('No dockets found.', 'energy-alabama-kc'),
            'not_found_in_trash'    => __('No dockets found in Trash.', 'energy-alabama-kc'),
            'featured_image'        => _x('Featured Image', 'Overrides the "Featured Image" phrase', 'energy-alabama-kc'),
            'set_featured_image'    => _x('Set featured image', 'Overrides the "Set featured image" phrase', 'energy-alabama-kc'),
            'remove_featured_image' => _x('Remove featured image', 'Overrides the "Remove featured image" phrase', 'energy-alabama-kc'),
            'use_featured_image'    => _x('Use as featured image', 'Overrides the "Use as featured image" phrase', 'energy-alabama-kc'),
            'archives'              => _x('Docket archives', 'The post type archive label', 'energy-alabama-kc'),
            'insert_into_item'      => _x('Insert into docket', 'Overrides the "Insert into post" phrase', 'energy-alabama-kc'),
            'uploaded_to_this_item' => _x('Uploaded to this docket', 'Overrides the "Uploaded to this post" phrase', 'energy-alabama-kc'),
            'filter_items_list'     => _x('Filter dockets list', 'Screen reader text for the filter links', 'energy-alabama-kc'),
            'items_list_navigation' => _x('Dockets list navigation', 'Screen reader text for the pagination', 'energy-alabama-kc'),
            'items_list'            => _x('Dockets list', 'Screen reader text for the items list', 'energy-alabama-kc'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'edit.php?post_type=kc_article',
            'show_in_admin_bar'  => true,
            'show_in_nav_menus'  => true,
            'can_export'         => true,
            'query_var'          => true,
            'rewrite'            => array(
                'slug'       => 'docket',
                'with_front' => false
            ),
            'capability_type'    => 'post',
            'has_archive'        => 'dockets',
            'hierarchical'       => false,
            'supports'           => array(
                'title',
                'editor',
                'excerpt',
                'revisions',
                'custom-fields'
            ),
            'show_in_rest'       => true,
            'rest_base'          => 'dockets',
            'rest_controller_class' => 'WP_REST_Posts_Controller',
            'taxonomies'         => array('docket_jurisdiction', 'kc_tag'),
            'template'           => array(
                array('core/paragraph', array(
                    'placeholder' => __('Enter docket description...', 'energy-alabama-kc')
                ))
            )
        );

        register_post_type('docket', $args);
    }

    /**
     * Add docket submenu items manually
     */
    public function add_docket_submenu() {
        global $submenu;
        
        // Add the "Add New Docket" submenu item after "All Dockets"
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Add New Docket', 'energy-alabama-kc'),
            __('Add New Docket', 'energy-alabama-kc'),
            'edit_posts',
            'post-new.php?post_type=docket'
        );
        
        // Add jurisdictions submenu at the end
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __('Jurisdictions', 'energy-alabama-kc'),
            __('Jurisdictions', 'energy-alabama-kc'),
            'manage_categories',
            'edit-tags.php?taxonomy=docket_jurisdiction&post_type=docket'
        );
        
        // Reorder the submenu items to get the desired order
        if (isset($submenu['edit.php?post_type=kc_article'])) {
            $kc_submenu = $submenu['edit.php?post_type=kc_article'];
            $reordered = array();
            
            // Expected order based on WordPress default positions:
            // All KC Articles (5)
            // Add New KC Article (10) 
            // Categories (15)
            // Tags (16)
            // All Dockets (appears automatically)
            // Add New Docket (we'll position this)
            // Jurisdictions (we'll position this)
            
            foreach ($kc_submenu as $position => $item) {
                if (strpos($item[2], 'post-new.php?post_type=docket') !== false) {
                    // Move "Add New Docket" to position after "All Dockets"
                    $reordered[25] = $item;
                } elseif (strpos($item[2], 'edit-tags.php?taxonomy=docket_jurisdiction') !== false) {
                    // Move "Jurisdictions" to the end
                    $reordered[30] = $item;
                } else {
                    // Keep other items in their original positions
                    $reordered[$position] = $item;
                }
            }
            
            // Sort by position and reassign
            ksort($reordered);
            $submenu['edit.php?post_type=kc_article'] = $reordered;
        }
    }

    /**
     * Maybe flush rewrite rules if needed
     */
    public function maybe_flush_rewrite_rules() {
        if (get_option('eakc_flush_rewrite_rules')) {
            flush_rewrite_rules();
            delete_option('eakc_flush_rewrite_rules');
        }
    }

    /**
     * Check if docket archive setting has changed and flush rules if needed
     */
    public function check_docket_archive_change() {
        $current_setting = get_option('eakc_docket_has_archive', false);
        
        // If the setting hasn't been stored yet or is different from current, update it
        if (!$current_setting) {
            update_option('eakc_docket_has_archive', true);
            update_option('eakc_flush_rewrite_rules', true);
        }
    }

    /**
     * Modify post type messages
     */
    public function updated_messages($messages) {
        $post = get_post();
        $post_type = get_post_type($post);
        $post_type_object = get_post_type_object($post_type);

        $messages['kc_article'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => __('KC Article updated.', 'energy-alabama-kc'),
            2  => __('Custom field updated.', 'energy-alabama-kc'),
            3  => __('Custom field deleted.', 'energy-alabama-kc'),
            4  => __('KC Article updated.', 'energy-alabama-kc'),
            5  => isset($_GET['revision']) ? sprintf(__('KC Article restored to revision from %s', 'energy-alabama-kc'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => __('KC Article published.', 'energy-alabama-kc'),
            7  => __('KC Article saved.', 'energy-alabama-kc'),
            8  => __('KC Article submitted.', 'energy-alabama-kc'),
            9  => sprintf(
                __('KC Article scheduled for: <strong>%1$s</strong>.', 'energy-alabama-kc'),
                date_i18n(__('M j, Y @ G:i', 'energy-alabama-kc'), strtotime($post->post_date))
            ),
            10 => __('KC Article draft updated.', 'energy-alabama-kc')
        );

        $messages['docket'] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => __('Docket updated.', 'energy-alabama-kc'),
            2  => __('Custom field updated.', 'energy-alabama-kc'),
            3  => __('Custom field deleted.', 'energy-alabama-kc'),
            4  => __('Docket updated.', 'energy-alabama-kc'),
            5  => isset($_GET['revision']) ? sprintf(__('Docket restored to revision from %s', 'energy-alabama-kc'), wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6  => __('Docket published.', 'energy-alabama-kc'),
            7  => __('Docket saved.', 'energy-alabama-kc'),
            8  => __('Docket submitted.', 'energy-alabama-kc'),
            9  => sprintf(
                __('Docket scheduled for: <strong>%1$s</strong>.', 'energy-alabama-kc'),
                date_i18n(__('M j, Y @ G:i', 'energy-alabama-kc'), strtotime($post->post_date))
            ),
            10 => __('Docket draft updated.', 'energy-alabama-kc')
        );

        return $messages;
    }
}