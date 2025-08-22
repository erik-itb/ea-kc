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
        
        /**
         * The class responsible for import/export functionality
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-import-export.php';
        
        /**
         * The class responsible for category ordering functionality
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-category-ordering.php';

        /**
         * The class responsible for FAQ ordering functionality
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-faq-ordering.php';

        /**
         * The class responsible for admin menu hierarchy styling
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-menu-hierarchy.php';
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
        if ( $screen && in_array( $screen->post_type, array( 'kc_article', 'docket', 'glossary' ) ) ) {
            wp_enqueue_style( 'wp-color-picker' );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Get current screen
        $screen = get_current_screen();
        
        // Only enqueue on KC post edit screens where meta boxes are shown
        if ( $screen && in_array( $screen->post_type, array( 'kc_article', 'docket', 'glossary' ) ) ) {
            // Enqueue jQuery UI sortable for the repeater fields
            wp_enqueue_script( 'jquery-ui-sortable' );
        }
        
        /**
         * Enqueue admin scripts for meta boxes.
         */
        wp_enqueue_script( 
            $this->plugin_name . '-admin', 
            plugin_dir_url( dirname( __FILE__ ) ) . '../assets/js/meta-boxes.js', 
            array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), 
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
        // Menu structure is now handled by class-menu-hierarchy.php
        // This method only handles the callback functions for custom pages
        
        // Dashboard callback is registered through the hierarchy class
        // Import Definitions callback is registered through the hierarchy class
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
            
            <div class="eakc-dashboard">
                <div class="eakc-dashboard-widgets">
                    
                    <!-- First Row: Content Overview and Settings -->
                    <div class="eakc-dashboard-row">
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
                                
                                <!-- View Knowledge Center Button -->
                                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                    <a href="<?php echo home_url( '/knowledge-center' ); ?>" class="button button-primary button-large" target="_blank">
                                        <span class="dashicons dashicons-external" style="vertical-align: middle; margin-right: 5px;"></span>
                                        <?php _e( 'View Knowledge Center', 'energy-alabama-kc' ); ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Knowledge Center Settings', 'energy-alabama-kc' ); ?></span></h2>
                            <div class="inside">
                                <form method="post" action="options.php">
                                    <?php
                                    settings_fields( 'energy_alabama_kc_settings' );
                                    do_settings_sections( 'energy-alabama-kc-settings' );
                                    submit_button();
                                    ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Second Row: Import/Export Tools and Format Guide -->
                    <div class="eakc-dashboard-row">
                        <!-- Import/Export Tools -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Import/Export Tools', 'energy-alabama-kc' ); ?></span></h2>
                            <div class="inside">
                                
                                <!-- Export Section -->
                                <div style="margin-bottom: 30px; padding-bottom: 30px; border-bottom: 1px solid #e5e7eb;">
                                    <h3><?php _e( 'Export Content', 'energy-alabama-kc' ); ?></h3>
                                    <p><?php _e( 'Export your Knowledge Center content to CSV format for backup or migration.', 'energy-alabama-kc' ); ?></p>
                                    
                                    <form method="post" action="">
                                        <?php wp_nonce_field( 'eakc_export_action', 'eakc_export_nonce' ); ?>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <label for="export_type" style="display: block; margin-bottom: 5px;">
                                                <strong><?php _e( 'Export Type:', 'energy-alabama-kc' ); ?></strong>
                                            </label>
                                            <select name="export_type" id="export_type" style="min-width: 200px;">
                                                <option value="articles"><?php _e( 'Articles Only', 'energy-alabama-kc' ); ?></option>
                                                <option value="dockets"><?php _e( 'Dockets Only', 'energy-alabama-kc' ); ?></option>
                                                <option value="all"><?php _e( 'All Content (ZIP)', 'energy-alabama-kc' ); ?></option>
                                            </select>
                                        </div>
                                        
                                        <input type="submit" name="eakc_export" value="<?php esc_attr_e( 'Export Content', 'energy-alabama-kc' ); ?>" class="button button-primary">
                                    </form>
                                </div>
                                
                                <!-- Import Section -->
                                <div>
                                    <h3><?php _e( 'Import Content', 'energy-alabama-kc' ); ?></h3>
                                    <p><?php _e( 'Import articles or dockets from a CSV file. Existing content with the same title will be skipped.', 'energy-alabama-kc' ); ?></p>
                                    
                                    <form method="post" action="" enctype="multipart/form-data">
                                        <?php wp_nonce_field( 'eakc_import_action', 'eakc_import_nonce' ); ?>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <label for="import_type" style="display: block; margin-bottom: 5px;">
                                                <strong><?php _e( 'Import Type:', 'energy-alabama-kc' ); ?></strong>
                                            </label>
                                            <select name="import_type" id="import_type" style="min-width: 200px;">
                                                <option value="articles"><?php _e( 'Articles', 'energy-alabama-kc' ); ?></option>
                                                <option value="dockets"><?php _e( 'Dockets', 'energy-alabama-kc' ); ?></option>
                                            </select>
                                        </div>
                                        
                                        <div style="margin-bottom: 15px;">
                                            <label for="import_file" style="display: block; margin-bottom: 5px;">
                                                <strong><?php _e( 'CSV File:', 'energy-alabama-kc' ); ?></strong>
                                            </label>
                                            <input type="file" name="import_file" id="import_file" accept=".csv" required>
                                        </div>
                                        
                                        <input type="submit" name="eakc_import" value="<?php esc_attr_e( 'Import Content', 'energy-alabama-kc' ); ?>" class="button button-primary">
                                    </form>
                                </div>
                                
                            </div>
                        </div>

                        <!-- Import Format Guide -->
                        <div class="postbox">
                            <h2 class="hndle"><span><?php _e( 'Import Format Guide', 'energy-alabama-kc' ); ?></span></h2>
                            <div class="inside">
                                <p><?php _e( 'Your CSV file should include the following columns:', 'energy-alabama-kc' ); ?></p>
                                
                                <div style="margin-bottom: 20px;">
                                    <h4><?php _e( 'For Articles:', 'energy-alabama-kc' ); ?></h4>
                                    <ul style="margin-top: 10px;">
                                        <li><strong>Title:</strong> Article title (required)</li>
                                        <li><strong>Content:</strong> Full article content</li>
                                        <li><strong>Excerpt:</strong> Short description</li>
                                        <li><strong>Status:</strong> publish, draft, pending, or private</li>
                                        <li><strong>Author:</strong> Username of the author</li>
                                        <li><strong>Date:</strong> Publication date (YYYY-MM-DD HH:MM:SS)</li>
                                        <li><strong>Categories:</strong> Pipe-separated (Cat1|Cat2)</li>
                                        <li><strong>Tags:</strong> Pipe-separated (Tag1|Tag2)</li>
                                        <li><strong>Featured Icon:</strong> Icon class name</li>
                                        <li><strong>Icon Color:</strong> Hex color code</li>
                                        <li><strong>Read Time:</strong> Estimated read time</li>
                                        <li><strong>Is Spanish:</strong> 1 for Spanish, 0 for English</li>
                                        <li><strong>Spanish Link ID:</strong> ID of linked translation</li>
                                        <li><strong>Resources JSON:</strong> JSON array of resources</li>
                                        <li><strong>Featured Image URL:</strong> Full URL to image</li>
                                    </ul>
                                </div>
                                
                                <div style="margin-bottom: 20px;">
                                    <h4><?php _e( 'For Dockets:', 'energy-alabama-kc' ); ?></h4>
                                    <ul style="margin-top: 10px;">
                                        <li><strong>Title:</strong> Docket title (required)</li>
                                        <li><strong>Content:</strong> Full docket description</li>
                                        <li><strong>Status:</strong> publish, draft, pending, or private</li>
                                        <li><strong>Author:</strong> Username of the author</li>
                                        <li><strong>Date:</strong> Publication date</li>
                                        <li><strong>Docket Number:</strong> Official docket number</li>
                                        <li><strong>Docket Status:</strong> Current status</li>
                                        <li><strong>Jurisdictions:</strong> Pipe-separated list</li>
                                        <li><strong>Documents JSON:</strong> JSON array of documents</li>
                                    </ul>
                                </div>
                                
                                <div style="padding: 15px; background: #f0f8ff; border-left: 4px solid #2271b1; border-radius: 4px;">
                                    <p style="margin: 0;">
                                        <strong><?php _e( 'Tip:', 'energy-alabama-kc' ); ?></strong>
                                        <?php _e( 'Export existing content first to see the exact format required. The export will create a properly formatted CSV file that can be used as a template for your imports.', 'energy-alabama-kc' ); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <style>
                .eakc-dashboard-widgets {
                    margin-top: 20px;
                }
                
                .eakc-dashboard-row {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px;
                    margin-bottom: 20px;
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
                
                @media (max-width: 1200px) {
                    .eakc-dashboard-row {
                        grid-template-columns: 1fr;
                    }
                }
                
                @media (max-width: 782px) {
                    .eakc-stats-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
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

    /**
     * Display glossary import page
     */
    public function display_glossary_import_page() {
        // Handle import processing
        if (isset($_POST['eakc_import_glossary']) && !empty($_FILES['glossary_file']['tmp_name'])) {
            if (!wp_verify_nonce($_POST['eakc_glossary_import_nonce'], 'eakc_glossary_import_action')) {
                wp_die('Security check failed');
            }

            if (!current_user_can('manage_options')) {
                wp_die('You do not have permission to import glossary data');
            }

            $result = $this->process_glossary_import();
            if ($result['success']) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }
        ?>
        <div class="wrap">
            <h1><?php _e('Import Glossary Definitions', 'energy-alabama-kc'); ?></h1>
            
            <div class="card" style="max-width: 800px;">
                <h2><?php _e('CSV Import Instructions', 'energy-alabama-kc'); ?></h2>
                <p><?php _e('Upload a CSV file with glossary definitions. The CSV should have the following columns:', 'energy-alabama-kc'); ?></p>
                <ul style="margin-left: 20px;">
                    <li><strong>Term:</strong> <?php _e('The glossary term or word', 'energy-alabama-kc'); ?></li>
                    <li><strong>Definition:</strong> <?php _e('The definition or explanation', 'energy-alabama-kc'); ?></li>
                    <li><strong>Source/Link:</strong> <?php _e('Optional URL to additional information', 'energy-alabama-kc'); ?></li>
                    <li><strong>Link Button:</strong> <?php _e('Optional button text (defaults to "Learn More")', 'energy-alabama-kc'); ?></li>
                </ul>
                <p><strong><?php _e('Note:', 'energy-alabama-kc'); ?></strong> <?php _e('Letter divider rows (A, B, C...) with empty definitions will be skipped automatically.', 'energy-alabama-kc'); ?></p>
            </div>

            <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
                <?php wp_nonce_field('eakc_glossary_import_action', 'eakc_glossary_import_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="glossary_file"><?php _e('CSV File', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <input type="file" name="glossary_file" id="glossary_file" accept=".csv" required>
                            <p class="description"><?php _e('Select a CSV file to import glossary definitions.', 'energy-alabama-kc'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="import_mode"><?php _e('Import Mode', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <select name="import_mode" id="import_mode">
                                <option value="skip"><?php _e('Skip existing terms', 'energy-alabama-kc'); ?></option>
                                <option value="update"><?php _e('Update existing terms', 'energy-alabama-kc'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose how to handle terms that already exist.', 'energy-alabama-kc'); ?></p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Import Glossary', 'energy-alabama-kc'), 'primary', 'eakc_import_glossary'); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Process glossary CSV import
     */
    private function process_glossary_import() {
        if (empty($_FILES['glossary_file']['tmp_name'])) {
            return array('success' => false, 'message' => 'No file uploaded.');
        }

        $file = $_FILES['glossary_file']['tmp_name'];
        $import_mode = sanitize_text_field($_POST['import_mode'] ?? 'skip');
        
        // Read CSV file
        $handle = fopen($file, 'r');
        if (!$handle) {
            return array('success' => false, 'message' => 'Could not read the uploaded file.');
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $line = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $line++;
            
            // Skip header row
            if ($line === 1) {
                continue;
            }

            // Ensure we have at least 2 columns (Term, Definition)
            if (count($data) < 2) {
                continue;
            }

            $term = trim($data[0] ?? '');
            $definition = trim($data[1] ?? '');
            $source_link = trim($data[2] ?? '');
            $button_text = trim($data[3] ?? '');

            // Skip if term or definition is empty
            if (empty($term) || empty($definition)) {
                $skipped++;
                continue;
            }

            // Skip letter divider rows (single letters with empty definitions)
            if (strlen($term) === 1 && ctype_alpha($term)) {
                $skipped++;
                continue;
            }

            // Check if term already exists
            $existing_query = new WP_Query(array(
                'post_type' => 'glossary',
                'title' => $term,
                'post_status' => array('publish', 'draft', 'private'),
                'posts_per_page' => 1,
                'no_found_rows' => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false
            ));
            $existing_post = $existing_query->have_posts() ? $existing_query->posts[0] : null;
            wp_reset_postdata();
            
            if ($existing_post) {
                if ($import_mode === 'skip') {
                    $skipped++;
                    continue;
                } else {
                    // Update existing post
                    $post_data = array(
                        'ID' => $existing_post->ID,
                        'post_content' => $definition,
                        'post_status' => 'publish'
                    );
                    
                    $post_id = wp_update_post($post_data);
                    if ($post_id) {
                        // Update meta fields
                        if ($source_link) {
                            update_post_meta($post_id, '_eakc_source_link', esc_url_raw($source_link));
                        }
                        if ($button_text) {
                            update_post_meta($post_id, '_eakc_button_text', sanitize_text_field($button_text));
                        }
                        $updated++;
                    } else {
                        $errors++;
                    }
                }
            } else {
                // Create new post
                $post_data = array(
                    'post_title' => $term,
                    'post_content' => $definition,
                    'post_type' => 'glossary',
                    'post_status' => 'publish'
                );
                
                $post_id = wp_insert_post($post_data);
                if ($post_id) {
                    // Add meta fields
                    if ($source_link) {
                        update_post_meta($post_id, '_eakc_source_link', esc_url_raw($source_link));
                    }
                    if ($button_text) {
                        update_post_meta($post_id, '_eakc_button_text', sanitize_text_field($button_text));
                    }
                    $imported++;
                } else {
                    $errors++;
                }
            }
        }

        fclose($handle);

        $message = sprintf(
            'Import complete: %d imported, %d updated, %d skipped, %d errors',
            $imported,
            $updated, 
            $skipped,
            $errors
        );

        return array('success' => true, 'message' => $message);
    }
}