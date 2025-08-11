<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://energyalabama.org
 * @since      1.0.0
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for
 * admin area functionality including:
 * - Settings pages
 * - Meta boxes
 * - Admin scripts and styles
 * - Admin notices
 * - Bulk actions
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes/admin
 * @author     Energy Alabama <info@energyalabama.org>
 */
class Energy_Alabama_KC_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The meta boxes handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      Energy_Alabama_KC_Meta_Boxes    $meta_boxes    The meta boxes handler.
     */
    private $meta_boxes;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        $this->load_dependencies();
    }

    /**
     * Load the required dependencies for this class.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        /**
         * The class responsible for defining all meta boxes
         * for the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-meta-boxes.php';
        
        $this->meta_boxes = new Energy_Alabama_KC_Meta_Boxes();
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        /**
         * Enqueue admin styles for all admin pages.
         */
        wp_enqueue_style( 
            $this->plugin_name . '-admin', 
            plugin_dir_url( dirname( __FILE__ ) ) . '../assets/css/meta-boxes.css', 
            array(), 
            $this->version, 
            'all' 
        );

        // Enqueue additional styles on KC post edit screens
        $screen = get_current_screen();
        if ( $screen && in_array( $screen->post_type, array( 'kc_article', 'docket' ) ) ) {
            wp_enqueue_style( 'wp-color-picker' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        /**
         * Enqueue admin scripts for meta boxes.
         */
        wp_enqueue_script( 
            $this->plugin_name . '-admin', 
            plugin_dir_url( dirname( __FILE__ ) ) . '../assets/js/meta-boxes.js', 
            array( 'jquery', 'wp-color-picker' ), 
            $this->version, 
            false 
        );

        // Localize script for AJAX
        wp_localize_script( $this->plugin_name . '-admin', 'eakc_admin_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'eakc_admin_nonce' ),
            'strings' => array(
                'confirm_delete' => __( 'Are you sure you want to delete this item?', 'energy-alabama-kc' ),
                'error_occurred' => __( 'An error occurred. Please try again.', 'energy-alabama-kc' ),
                'saving' => __( 'Saving...', 'energy-alabama-kc' ),
                'saved' => __( 'Saved!', 'energy-alabama-kc' ),
            )
        ));
    }

    /**
     * Add plugin settings page to admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Add dashboard submenu page that combines Dashboard, Import, and Settings
        add_submenu_page(
            'edit.php?post_type=kc_article',                      // Parent slug (existing KC menu)
            __( 'Knowledge Center Dashboard', 'energy-alabama-kc' ), // Page title
            __( 'Dashboard', 'energy-alabama-kc' ),               // Menu title
            'manage_options',                                       // Capability
            'energy-alabama-kc-dashboard',                         // Menu slug
            array( $this, 'display_dashboard_page' )              // Callback function
        );
        
        // Add the "Add New Docket" submenu item
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __( 'Add New Docket', 'energy-alabama-kc' ),
            __( 'Add New Docket', 'energy-alabama-kc' ),
            'edit_posts',
            'post-new.php?post_type=docket'
        );
        
        // Add jurisdictions submenu
        add_submenu_page(
            'edit.php?post_type=kc_article',
            __( 'Jurisdictions', 'energy-alabama-kc' ),
            __( 'Jurisdictions', 'energy-alabama-kc' ),
            'manage_categories',
            'edit-tags.php?taxonomy=docket_jurisdiction&post_type=docket'
        );
    }

    /**
     * Fix parent menu highlighting for taxonomy pages
     *
     * @since    1.0.0
     */
    public function fix_taxonomy_parent_menu( $parent_file ) {
        global $current_screen;
        
        // Check if we're on a docket-related taxonomy page
        if ( $current_screen && $current_screen->taxonomy === 'docket_jurisdiction' ) {
            $parent_file = 'edit.php?post_type=kc_article';
        }
        
        return $parent_file;
    }
    
    /**
     * Fix submenu highlighting for taxonomy pages
     *
     * @since    1.0.0
     */
    public function fix_taxonomy_submenu( $submenu_file ) {
        global $current_screen, $pagenow;
        
        // Check if we're on the docket jurisdiction taxonomy page
        if ( $pagenow === 'edit-tags.php' && isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'docket_jurisdiction' ) {
            $submenu_file = 'edit-tags.php?taxonomy=docket_jurisdiction&post_type=docket';
        }
        
        return $submenu_file;
    }
    
    /**
     * Reorder the Knowledge Center submenu items.
     *
     * @since    1.0.0
     */
    public function reorder_admin_menu() {
        global $submenu;
        
        if ( ! isset( $submenu['edit.php?post_type=kc_article'] ) ) {
            return;
        }

        $kc_submenu = $submenu['edit.php?post_type=kc_article'];
        $new_submenu = array();

        // Define the exact order we want
        $desired_order = array(
            'energy-alabama-kc-dashboard' => 1,
            'edit.php?post_type=kc_article' => 2,
            'post-new.php?post_type=kc_article' => 3,
            'edit-tags.php?taxonomy=kc_category&post_type=kc_article' => 4,
            'edit-tags.php?taxonomy=kc_tags&post_type=kc_article' => 5,
            'edit.php?post_type=docket' => 6,
            'post-new.php?post_type=docket' => 7,
            'edit-tags.php?taxonomy=docket_jurisdiction&post_type=docket' => 8
        );

        // Create array to hold items by their menu slug
        $menu_items = array();
        
        foreach ( $kc_submenu as $item ) {
            $menu_slug = $item[2];
            
            // Handle URL encoded versions
            $menu_slug = str_replace('&amp;', '&', $menu_slug);
            
            $menu_items[$menu_slug] = $item;
        }

        // Build new menu in desired order
        foreach ( $desired_order as $slug => $position ) {
            if ( isset( $menu_items[$slug] ) ) {
                $new_submenu[$position] = $menu_items[$slug];
                unset( $menu_items[$slug] );
            }
        }

        // Add any remaining items that weren't in our desired order
        $next_position = max( array_keys( $new_submenu ) ) + 1;
        foreach ( $menu_items as $item ) {
            $new_submenu[$next_position] = $item;
            $next_position++;
        }

        // Sort by key to ensure proper order
        ksort( $new_submenu );
        
        // Replace the submenu
        $submenu['edit.php?post_type=kc_article'] = $new_submenu;
    }

    /**
     * Register plugin settings.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'energy_alabama_kc_settings',
            'energy_alabama_kc_options',
            array( $this, 'validate_settings' )
        );

        // General Settings Section
        add_settings_section(
            'eakc_general_settings',
            __( 'General Settings', 'energy-alabama-kc' ),
            array( $this, 'general_settings_callback' ),
            'energy-alabama-kc-settings'
        );

        // Search Settings
        add_settings_field(
            'search_results_per_page',
            __( 'Search Results Per Page', 'energy-alabama-kc' ),
            array( $this, 'search_results_per_page_callback' ),
            'energy-alabama-kc-settings',
            'eakc_general_settings'
        );

        add_settings_field(
            'enable_spanish_content',
            __( 'Enable Spanish Content', 'energy-alabama-kc' ),
            array( $this, 'enable_spanish_content_callback' ),
            'energy-alabama-kc-settings',
            'eakc_general_settings'
        );

        // Archive Settings Section
        add_settings_section(
            'eakc_archive_settings',
            __( 'Archive Settings', 'energy-alabama-kc' ),
            array( $this, 'archive_settings_callback' ),
            'energy-alabama-kc-settings'
        );

        add_settings_field(
            'articles_per_page',
            __( 'Articles Per Page', 'energy-alabama-kc' ),
            array( $this, 'articles_per_page_callback' ),
            'energy-alabama-kc-settings',
            'eakc_archive_settings'
        );

        add_settings_field(
            'dockets_per_page',
            __( 'Dockets Per Page', 'energy-alabama-kc' ),
            array( $this, 'dockets_per_page_callback' ),
            'energy-alabama-kc-settings',
            'eakc_archive_settings'
        );
    }

    /**
     * Display the combined Dashboard page with Overview, Import, and Settings.
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'energy-alabama-kc' ) );
        }

        // Get post counts for dashboard
        $kc_articles = wp_count_posts( 'kc_article' );
        $dockets = wp_count_posts( 'docket' );
        
        // Get term counts safely
        $categories_count = wp_count_terms( array( 'taxonomy' => 'kc_category', 'hide_empty' => false ) );
        $categories = is_wp_error( $categories_count ) ? 0 : $categories_count;
        
        $docket_types_count = wp_count_terms( array( 'taxonomy' => 'docket_type', 'hide_empty' => false ) );
        $docket_types = is_wp_error( $docket_types_count ) ? 0 : $docket_types_count;

        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <!-- Tabbed Navigation -->
            <h2 class="nav-tab-wrapper">
                <a href="#overview" class="nav-tab nav-tab-active" data-tab="overview"><?php _e( 'Overview', 'energy-alabama-kc' ); ?></a>
                <a href="#import" class="nav-tab" data-tab="import"><?php _e( 'Import', 'energy-alabama-kc' ); ?></a>
                <a href="#settings" class="nav-tab" data-tab="settings"><?php _e( 'Settings', 'energy-alabama-kc' ); ?></a>
            </h2>
            
            <!-- Overview Tab Content (default visible) -->
            <div class="eakc-dashboard">
                <div class="eakc-dashboard-widgets">
                    
                    <!-- Content Overview -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e( 'Content Overview', 'energy-alabama-kc' ); ?></span></h2>
                        <div class="inside">
                            <div class="eakc-stats-grid">
                                <div class="eakc-stat-item">
                                    <div class="eakc-stat-number"><?php echo esc_html( $kc_articles->publish ); ?></div>
                                    <div class="eakc-stat-label"><?php _e( 'Published Articles', 'energy-alabama-kc' ); ?></div>
                                    <div class="eakc-stat-actions">
                                        <a href="<?php echo admin_url( 'edit.php?post_type=kc_article' ); ?>" class="button">
                                            <?php _e( 'Manage Articles', 'energy-alabama-kc' ); ?>
                                        </a>
                                        <a href="<?php echo admin_url( 'post-new.php?post_type=kc_article' ); ?>" class="button button-primary">
                                            <?php _e( 'Add New', 'energy-alabama-kc' ); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="eakc-stat-item">
                                    <div class="eakc-stat-number"><?php echo esc_html( $dockets->publish ); ?></div>
                                    <div class="eakc-stat-label"><?php _e( 'Published Dockets', 'energy-alabama-kc' ); ?></div>
                                    <div class="eakc-stat-actions">
                                        <a href="<?php echo admin_url( 'edit.php?post_type=docket' ); ?>" class="button">
                                            <?php _e( 'Manage Dockets', 'energy-alabama-kc' ); ?>
                                        </a>
                                        <a href="<?php echo admin_url( 'post-new.php?post_type=docket' ); ?>" class="button button-primary">
                                            <?php _e( 'Add New', 'energy-alabama-kc' ); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="eakc-stat-item">
                                    <div class="eakc-stat-number"><?php echo esc_html( $categories ); ?></div>
                                    <div class="eakc-stat-label"><?php _e( 'Categories', 'energy-alabama-kc' ); ?></div>
                                    <div class="eakc-stat-actions">
                                        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=kc_category&post_type=kc_article' ); ?>" class="button">
                                            <?php _e( 'Manage Categories', 'energy-alabama-kc' ); ?>
                                        </a>
                                    </div>
                                </div>

                                <div class="eakc-stat-item">
                                    <div class="eakc-stat-number"><?php echo esc_html( $docket_types ); ?></div>
                                    <div class="eakc-stat-label"><?php _e( 'Docket Types', 'energy-alabama-kc' ); ?></div>
                                    <div class="eakc-stat-actions">
                                        <a href="<?php echo admin_url( 'edit-tags.php?taxonomy=docket_type&post_type=docket' ); ?>" class="button">
                                            <?php _e( 'Manage Types', 'energy-alabama-kc' ); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e( 'Quick Actions', 'energy-alabama-kc' ); ?></span></h2>
                        <div class="inside">
                            <div class="eakc-quick-actions">
                                <a href="#settings" class="button button-large nav-tab-link" data-tab="settings">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                    <?php _e( 'Plugin Settings', 'energy-alabama-kc' ); ?>
                                </a>
                                
                                <a href="#import" class="button button-large nav-tab-link" data-tab="import">
                                    <span class="dashicons dashicons-upload"></span>
                                    <?php _e( 'Import Content', 'energy-alabama-kc' ); ?>
                                </a>
                                
                                <a href="<?php echo get_post_type_archive_link( 'kc_article' ); ?>" class="button button-large" target="_blank">
                                    <span class="dashicons dashicons-external"></span>
                                    <?php _e( 'View Knowledge Center', 'energy-alabama-kc' ); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="postbox">
                        <h2 class="hndle"><span><?php _e( 'Recent Activity', 'energy-alabama-kc' ); ?></span></h2>
                        <div class="inside">
                            <?php
                            $recent_articles = get_posts( array(
                                'post_type' => 'kc_article',
                                'posts_per_page' => 5,
                                'post_status' => 'publish',
                                'orderby' => 'date',
                                'order' => 'DESC'
                            ) );

                            if ( $recent_articles ) : ?>
                                <ul class="eakc-recent-list">
                                    <?php foreach ( $recent_articles as $article ) : ?>
                                        <li>
                                            <a href="<?php echo get_edit_post_link( $article->ID ); ?>">
                                                <?php echo esc_html( $article->post_title ); ?>
                                            </a>
                                            <span class="eakc-recent-date">
                                                <?php echo human_time_diff( strtotime( $article->post_date ), current_time( 'timestamp' ) ); ?>
                                                <?php _e( 'ago', 'energy-alabama-kc' ); ?>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <p><?php _e( 'No recent articles found.', 'energy-alabama-kc' ); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Import Tab Content -->
            <div id="import-tab" class="tab-content" style="display: none;">
                <?php $this->display_import_tab_content(); ?>
            </div>
            
            <!-- Settings Tab Content -->
            <div id="settings-tab" class="tab-content" style="display: none;">
                <?php $this->display_settings_tab_content(); ?>
            </div>

            <style>
                .eakc-dashboard-widgets {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-top: 20px;
                }
                
                .eakc-stats-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                }
                
                .eakc-stat-item {
                    text-align: center;
                    padding: 20px;
                    background: #f9f9f9;
                    border-radius: 4px;
                }
                
                .eakc-stat-number {
                    font-size: 2.5em;
                    font-weight: bold;
                    color: #2271b1;
                    margin-bottom: 5px;
                }
                
                .eakc-stat-label {
                    font-size: 14px;
                    color: #666;
                    margin-bottom: 15px;
                }
                
                .eakc-stat-actions .button {
                    margin: 0 5px;
                }
                
                .eakc-quick-actions {
                    display: flex;
                    gap: 15px;
                    flex-wrap: wrap;
                }
                
                .eakc-quick-actions .button {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                
                .eakc-recent-list {
                    margin: 0;
                    padding: 0;
                }
                
                .eakc-recent-list li {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 8px 0;
                    border-bottom: 1px solid #eee;
                }
                
                .eakc-recent-list li:last-child {
                    border-bottom: none;
                }
                
                .eakc-recent-date {
                    font-size: 12px;
                    color: #999;
                }
                
                .tab-content {
                    margin-top: 20px;
                }
                
                @media (max-width: 782px) {
                    .eakc-dashboard-widgets {
                        grid-template-columns: 1fr;
                    }
                    
                    .eakc-stats-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .eakc-quick-actions {
                        flex-direction: column;
                    }
                }
            </style>
            
            <script>
            jQuery(document).ready(function($) {
                // Tab switching
                $('.nav-tab, .nav-tab-link').on('click', function(e) {
                    e.preventDefault();
                    var tab = $(this).data('tab');
                    
                    // Update active tab
                    $('.nav-tab').removeClass('nav-tab-active');
                    $('.nav-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');
                    
                    // Show/hide content
                    if (tab === 'overview') {
                        $('.eakc-dashboard').show();
                        $('.tab-content').hide();
                    } else {
                        $('.eakc-dashboard').hide();
                        $('.tab-content').hide();
                        $('#' + tab + '-tab').show();
                    }
                    
                    // Update URL hash
                    window.location.hash = tab;
                });
                
                // Handle initial hash
                var hash = window.location.hash.substring(1);
                if (hash && ['import', 'settings'].includes(hash)) {
                    $('.nav-tab[data-tab="' + hash + '"]').trigger('click');
                }
            });
            </script>
        </div>
        <?php
    }

    /**
     * Display the import tab content.
     *
     * @since    1.0.0
     */
    private function display_import_tab_content() {
        ?>
            <div class="eakc-import-tools">
                <div class="postbox">
                    <h2 class="hndle"><span><?php _e( 'Bulk Import Tools', 'energy-alabama-kc' ); ?></span></h2>
                    <div class="inside">
                        <p><?php _e( 'Import tools will be implemented in a future version. For now, you can:', 'energy-alabama-kc' ); ?></p>
                        <ul>
                            <li><?php _e( 'Create articles and dockets manually using the WordPress admin', 'energy-alabama-kc' ); ?></li>
                            <li><?php _e( 'Use WordPress import/export tools for standard content migration', 'energy-alabama-kc' ); ?></li>
                            <li><?php _e( 'Contact support for custom import assistance', 'energy-alabama-kc' ); ?></li>
                        </ul>
                        
                        <h4><?php _e( 'Planned Import Features:', 'energy-alabama-kc' ); ?></h4>
                        <ul>
                            <li><?php _e( 'CSV import for articles and dockets', 'energy-alabama-kc' ); ?></li>
                            <li><?php _e( 'Bulk resource attachment', 'energy-alabama-kc' ); ?></li>
                            <li><?php _e( 'Spanish content linking', 'energy-alabama-kc' ); ?></li>
                            <li><?php _e( 'Category and tag auto-assignment', 'energy-alabama-kc' ); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php
    }
    
    /**
     * Display the settings tab content.
     *
     * @since    1.0.0
     */
    private function display_settings_tab_content() {
        ?>
            <h2><?php _e( 'Knowledge Center Settings', 'energy-alabama-kc' ); ?></h2>
            
            <form method="post" action="options.php">
                <?php
                settings_fields( 'energy_alabama_kc_settings' );
                do_settings_sections( 'energy-alabama-kc-settings' );
                submit_button();
                ?>
            </form>
            
            <div class="eakc-admin-info">
                <h3><?php _e( 'Plugin Information', 'energy-alabama-kc' ); ?></h3>
                <p><strong><?php _e( 'Version:', 'energy-alabama-kc' ); ?></strong> <?php echo esc_html( $this->version ); ?></p>
                <p><strong><?php _e( 'Documentation:', 'energy-alabama-kc' ); ?></strong> 
                   <a href="https://github.com/erik-itb/ea-kc" target="_blank"><?php _e( 'GitHub Repository', 'energy-alabama-kc' ); ?></a>
                </p>
            </div>
        <?php
    }
    
    /**
     * Display the main Knowledge Center page.
     * Kept for backward compatibility - redirects to dashboard.
     *
     * @since    1.0.0
     * @deprecated 1.0.0 Use display_dashboard_page() instead
     */
    public function display_main_page() {
        $this->display_dashboard_page();
    }
    
    /**
     * Display the import page.
     * Kept for backward compatibility - redirects to dashboard.
     *
     * @since    1.0.0
     * @deprecated 1.0.0 Use display_dashboard_page() instead
     */
    public function display_import_page() {
        wp_redirect( admin_url( 'edit.php?post_type=kc_article&page=energy-alabama-kc-dashboard#import' ) );
        exit;
    }
    
    /**
     * Display the settings page.
     * Kept for backward compatibility - redirects to dashboard.
     *
     * @since    1.0.0
     * @deprecated 1.0.0 Use display_dashboard_page() instead
     */
    public function display_settings_page() {
        wp_redirect( admin_url( 'edit.php?post_type=kc_article&page=energy-alabama-kc-dashboard#settings' ) );
        exit;
    }

    /**
     * Validate settings input.
     *
     * @since    1.0.0
     * @param    array    $input    The input values.
     * @return   array              The validated values.
     */
    public function validate_settings( $input ) {
        $output = array();

        // Validate search results per page
        if ( isset( $input['search_results_per_page'] ) ) {
            $search_results = intval( $input['search_results_per_page'] );
            $output['search_results_per_page'] = ( $search_results > 0 && $search_results <= 100 ) ? $search_results : 10;
        }

        // Validate articles per page
        if ( isset( $input['articles_per_page'] ) ) {
            $articles_per_page = intval( $input['articles_per_page'] );
            $output['articles_per_page'] = ( $articles_per_page > 0 && $articles_per_page <= 100 ) ? $articles_per_page : 12;
        }

        // Validate dockets per page
        if ( isset( $input['dockets_per_page'] ) ) {
            $dockets_per_page = intval( $input['dockets_per_page'] );
            $output['dockets_per_page'] = ( $dockets_per_page > 0 && $dockets_per_page <= 100 ) ? $dockets_per_page : 20;
        }

        // Validate boolean settings
        $output['enable_spanish_content'] = isset( $input['enable_spanish_content'] ) ? 1 : 0;

        return $output;
    }

    /**
     * General settings section callback.
     *
     * @since    1.0.0
     */
    public function general_settings_callback() {
        echo '<p>' . __( 'Configure general settings for the Knowledge Center.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Archive settings section callback.
     *
     * @since    1.0.0
     */
    public function archive_settings_callback() {
        echo '<p>' . __( 'Configure archive page display settings.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Search results per page field callback.
     *
     * @since    1.0.0
     */
    public function search_results_per_page_callback() {
        $options = get_option( 'energy_alabama_kc_options' );
        $value = isset( $options['search_results_per_page'] ) ? $options['search_results_per_page'] : 10;
        echo '<input type="number" name="energy_alabama_kc_options[search_results_per_page]" value="' . esc_attr( $value ) . '" min="1" max="100" />';
        echo '<p class="description">' . __( 'Number of results to show per page in search results.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Enable Spanish content field callback.
     *
     * @since    1.0.0
     */
    public function enable_spanish_content_callback() {
        $options = get_option( 'energy_alabama_kc_options' );
        $value = isset( $options['enable_spanish_content'] ) ? $options['enable_spanish_content'] : 0;
        echo '<input type="checkbox" name="energy_alabama_kc_options[enable_spanish_content]" value="1" ' . checked( 1, $value, false ) . ' />';
        echo '<p class="description">' . __( 'Enable Spanish language content support and toggle functionality.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Articles per page field callback.
     *
     * @since    1.0.0
     */
    public function articles_per_page_callback() {
        $options = get_option( 'energy_alabama_kc_options' );
        $value = isset( $options['articles_per_page'] ) ? $options['articles_per_page'] : 12;
        echo '<input type="number" name="energy_alabama_kc_options[articles_per_page]" value="' . esc_attr( $value ) . '" min="1" max="100" />';
        echo '<p class="description">' . __( 'Number of articles to display per page on archive pages.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Dockets per page field callback.
     *
     * @since    1.0.0
     */
    public function dockets_per_page_callback() {
        $options = get_option( 'energy_alabama_kc_options' );
        $value = isset( $options['dockets_per_page'] ) ? $options['dockets_per_page'] : 20;
        echo '<input type="number" name="energy_alabama_kc_options[dockets_per_page]" value="' . esc_attr( $value ) . '" min="1" max="100" />';
        echo '<p class="description">' . __( 'Number of dockets to display per page on docket archive pages.', 'energy-alabama-kc' ) . '</p>';
    }

    /**
     * Add custom columns to KC Article admin list.
     *
     * @since    1.0.0
     * @param    array    $columns    Existing columns.
     * @return   array                Modified columns.
     */
    public function add_kc_article_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $title ) {
            $new_columns[$key] = $title;
            
            // Add custom columns after title
            if ( $key === 'title' ) {
                $new_columns['kc_category'] = __( 'Category', 'energy-alabama-kc' );
                $new_columns['resources_count'] = __( 'Resources', 'energy-alabama-kc' );
                $new_columns['spanish_linked'] = __( 'Spanish Version', 'energy-alabama-kc' );
            }
        }
        
        return $new_columns;
    }

    /**
     * Populate custom columns for KC Article admin list.
     *
     * @since    1.0.0
     * @param    string    $column     Column name.
     * @param    int       $post_id    Post ID.
     */
    public function populate_kc_article_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'kc_category':
                $terms = get_the_terms( $post_id, 'kc_category' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $category_links = array();
                    foreach ( $terms as $term ) {
                        $category_links[] = sprintf( 
                            '<a href="%s">%s</a>',
                            esc_url( add_query_arg( array( 'kc_category' => $term->slug ), 'edit.php?post_type=kc_article' ) ),
                            esc_html( $term->name )
                        );
                    }
                    echo implode( ', ', $category_links );
                } else {
                    echo '—';
                }
                break;

            case 'resources_count':
                $resources = get_post_meta( $post_id, '_eakc_resources', true );
                if ( is_array( $resources ) && ! empty( $resources ) ) {
                    echo count( $resources );
                } else {
                    echo '0';
                }
                break;

            case 'spanish_linked':
                $spanish_post_id = get_post_meta( $post_id, '_eakc_spanish_post_id', true );
                if ( $spanish_post_id ) {
                    $spanish_post = get_post( $spanish_post_id );
                    if ( $spanish_post ) {
                        printf( 
                            '<a href="%s">%s</a>',
                            esc_url( get_edit_post_link( $spanish_post_id ) ),
                            esc_html( $spanish_post->post_title )
                        );
                    } else {
                        echo '<span style="color: #d63638;">' . __( 'Broken Link', 'energy-alabama-kc' ) . '</span>';
                    }
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Add custom columns to Docket admin list.
     *
     * @since    1.0.0
     * @param    array    $columns    Existing columns.
     * @return   array                Modified columns.
     */
    public function add_docket_columns( $columns ) {
        $new_columns = array();
        
        foreach ( $columns as $key => $title ) {
            $new_columns[$key] = $title;
            
            // Add custom columns after title
            if ( $key === 'title' ) {
                $new_columns['docket_number'] = __( 'Docket #', 'energy-alabama-kc' );
                $new_columns['docket_status'] = __( 'Status', 'energy-alabama-kc' );
                $new_columns['docket_type'] = __( 'Type', 'energy-alabama-kc' );
                $new_columns['documents_count'] = __( 'Documents', 'energy-alabama-kc' );
            }
        }
        
        return $new_columns;
    }

    /**
     * Populate custom columns for Docket admin list.
     *
     * @since    1.0.0
     * @param    string    $column     Column name.
     * @param    int       $post_id    Post ID.
     */
    public function populate_docket_columns( $column, $post_id ) {
        switch ( $column ) {
            case 'docket_number':
                $docket_number = get_post_meta( $post_id, '_eakc_docket_number', true );
                echo $docket_number ? esc_html( $docket_number ) : '—';
                break;

            case 'docket_status':
                $status = get_post_meta( $post_id, '_eakc_docket_status', true );
                if ( $status ) {
                    $status_class = 'eakc-status-' . sanitize_html_class( strtolower( $status ) );
                    echo '<span class="' . esc_attr( $status_class ) . '">' . esc_html( ucfirst( $status ) ) . '</span>';
                } else {
                    echo '—';
                }
                break;

            case 'docket_type':
                $terms = get_the_terms( $post_id, 'docket_type' );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $type_links = array();
                    foreach ( $terms as $term ) {
                        $type_links[] = sprintf( 
                            '<a href="%s">%s</a>',
                            esc_url( add_query_arg( array( 'docket_type' => $term->slug ), 'edit.php?post_type=docket' ) ),
                            esc_html( $term->name )
                        );
                    }
                    echo implode( ', ', $type_links );
                } else {
                    echo '—';
                }
                break;

            case 'documents_count':
                $documents = get_post_meta( $post_id, '_eakc_docket_documents', true );
                if ( is_array( $documents ) && ! empty( $documents ) ) {
                    echo count( $documents );
                } else {
                    echo '0';
                }
                break;
        }
    }

    /**
     * Make custom columns sortable.
     *
     * @since    1.0.0
     * @param    array    $columns    Sortable columns.
     * @return   array                Modified sortable columns.
     */
    public function make_columns_sortable( $columns ) {
        $columns['docket_number'] = 'docket_number';
        $columns['docket_status'] = 'docket_status';
        return $columns;
    }

    /**
     * Handle sorting for custom columns.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The query object.
     */
    public function handle_custom_column_sorting( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        $orderby = $query->get( 'orderby' );

        switch ( $orderby ) {
            case 'docket_number':
                $query->set( 'meta_key', '_eakc_docket_number' );
                $query->set( 'orderby', 'meta_value' );
                break;

            case 'docket_status':
                $query->set( 'meta_key', '_eakc_docket_status' );
                $query->set( 'orderby', 'meta_value' );
                break;
        }
    }

    /**
     * Add admin notices.
     *
     * @since    1.0.0
     */
    public function admin_notices() {
        // Welcome notice removed - not needed for this installation
    }

    /**
     * Handle AJAX request to dismiss admin notices.
     *
     * @since    1.0.0
     */
    public function dismiss_admin_notice() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'eakc_admin_nonce' ) ) {
            wp_die( 'Security check failed' );
        }

        // No notices to dismiss currently
        wp_die();
    }

    /**
     * Add bulk actions for KC Articles.
     *
     * @since    1.0.0
     * @param    array    $actions    Existing bulk actions.
     * @return   array                Modified bulk actions.
     */
    public function add_bulk_actions( $actions ) {
        $actions['link_spanish'] = __( 'Link Spanish Versions', 'energy-alabama-kc' );
        $actions['unlink_spanish'] = __( 'Unlink Spanish Versions', 'energy-alabama-kc' );
        return $actions;
    }

    /**
     * Handle bulk actions for KC Articles.
     *
     * @since    1.0.0
     * @param    string    $redirect_to    Redirect URL.
     * @param    string    $action         Action name.
     * @param    array     $post_ids       Selected post IDs.
     * @return   string                    Modified redirect URL.
     */
    public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
        if ( ! in_array( $action, array( 'link_spanish', 'unlink_spanish' ) ) ) {
            return $redirect_to;
        }

        $processed = 0;

        foreach ( $post_ids as $post_id ) {
            switch ( $action ) {
                case 'unlink_spanish':
                    delete_post_meta( $post_id, '_eakc_spanish_post_id' );
                    $processed++;
                    break;

                case 'link_spanish':
                    // This would need more complex logic to match posts
                    // For now, just count as processed without action
                    $processed++;
                    break;
            }
        }

        $redirect_to = add_query_arg( array(
            'bulk_action' => $action,
            'processed' => $processed
        ), $redirect_to );

        return $redirect_to;
    }

    /**
     * Show bulk action admin notices.
     *
     * @since    1.0.0
     */
    public function bulk_action_notices() {
        if ( ! isset( $_GET['bulk_action'] ) || ! isset( $_GET['processed'] ) ) {
            return;
        }

        $action = sanitize_text_field( $_GET['bulk_action'] );
        $processed = intval( $_GET['processed'] );

        $message = '';
        switch ( $action ) {
            case 'link_spanish':
                $message = sprintf(
                    _n( 
                        'Linked %d article to Spanish version.', 
                        'Linked %d articles to Spanish versions.', 
                        $processed, 
                        'energy-alabama-kc' 
                    ),
                    $processed
                );
                break;

            case 'unlink_spanish':
                $message = sprintf(
                    _n( 
                        'Unlinked %d article from Spanish version.', 
                        'Unlinked %d articles from Spanish versions.', 
                        $processed, 
                        'energy-alabama-kc' 
                    ),
                    $processed
                );
                break;
        }

        if ( $message ) {
            printf( 
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                esc_html( $message )
            );
        }
    }
}