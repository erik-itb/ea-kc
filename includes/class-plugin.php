<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://energyalabama.org
 * @since      1.0.0
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 * @author     Energy Alabama <info@energyalabama.org>
 */
class Energy_Alabama_KC {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The admin class instance.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Admin    $plugin_admin    The admin class instance.
	 */
	protected $plugin_admin;

	/**
	 * The post types handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Post_Types    $post_types    The post types handler.
	 */
	protected $post_types;

	/**
	 * The taxonomies handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Taxonomies    $taxonomies    The taxonomies handler.
	 */
	protected $taxonomies;

	/**
	 * The meta fields handler.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Meta_Fields    $meta_fields    The meta fields handler.
	 */
	protected $meta_fields;

	/**
	 * The template manager.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Energy_Alabama_KC_Template_Manager    $template_manager    The template manager.
	 */
	protected $template_manager;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ENERGY_ALABAMA_KC_VERSION' ) ) {
			$this->version = ENERGY_ALABAMA_KC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'energy-alabama-kc';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_core_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Energy_Alabama_KC_Loader. Orchestrates the hooks of the plugin.
	 * - Energy_Alabama_KC_i18n. Defines internationalization functionality.
	 * - Energy_Alabama_KC_Admin. Defines all hooks for the admin area.
	 * - Energy_Alabama_KC_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/admin/class-admin.php';

		/**
		 * The class responsible for defining custom post types.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-post-types.php';

		/**
		 * The class responsible for defining custom taxonomies.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-taxonomies.php';

		/**
		 * The class responsible for handling meta fields.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-meta-fields.php';

		/**
		 * The class responsible for managing templates.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/core/class-template-manager.php';

		$this->loader = new Energy_Alabama_KC_Loader();

		// Initialize core components
		$this->post_types = new Energy_Alabama_KC_Post_Types();
		$this->taxonomies = new Energy_Alabama_KC_Taxonomies();
		$this->meta_fields = new Energy_Alabama_KC_Meta_Fields();
		$this->template_manager = new Energy_Alabama_KC_Template_Manager();

		// Initialize admin only in admin context
		if ( is_admin() ) {
			$this->plugin_admin = new Energy_Alabama_KC_Admin( $this->get_plugin_name(), $this->get_version() );
		}

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Energy_Alabama_KC_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Energy_Alabama_KC_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		if ( ! is_admin() || ! $this->plugin_admin ) {
			return;
		}

		// Admin styles and scripts
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts' );

		// Meta boxes functionality (handled through admin class)
		// The meta boxes class is instantiated within the admin class and handles its own hooks

		// Admin menu and settings
		$this->loader->add_action( 'admin_menu', $this->plugin_admin, 'add_admin_menu' );
		$this->loader->add_action( 'admin_menu', $this->plugin_admin, 'reorder_admin_menu', 999 );
		$this->loader->add_action( 'admin_init', $this->plugin_admin, 'register_settings' );

		// Custom admin columns for KC Articles
		$this->loader->add_filter( 'manage_kc_article_posts_columns', $this->plugin_admin, 'add_kc_article_columns' );
		$this->loader->add_action( 'manage_kc_article_posts_custom_column', $this->plugin_admin, 'populate_kc_article_columns', 10, 2 );

		// Custom admin columns for Dockets
		$this->loader->add_filter( 'manage_docket_posts_columns', $this->plugin_admin, 'add_docket_columns' );
		$this->loader->add_action( 'manage_docket_posts_custom_column', $this->plugin_admin, 'populate_docket_columns', 10, 2 );

		// Make custom columns sortable
		$this->loader->add_filter( 'manage_edit-docket_sortable_columns', $this->plugin_admin, 'make_columns_sortable' );
		$this->loader->add_action( 'pre_get_posts', $this->plugin_admin, 'handle_custom_column_sorting' );

		// Admin notices
		$this->loader->add_action( 'admin_notices', $this->plugin_admin, 'admin_notices' );
		$this->loader->add_action( 'wp_ajax_dismiss_eakc_notice', $this->plugin_admin, 'dismiss_admin_notice' );

		// Bulk actions
		$this->loader->add_filter( 'bulk_actions-edit-kc_article', $this->plugin_admin, 'add_bulk_actions' );
		$this->loader->add_filter( 'handle_bulk_actions-edit-kc_article', $this->plugin_admin, 'handle_bulk_actions', 10, 3 );
		$this->loader->add_action( 'admin_notices', $this->plugin_admin, 'bulk_action_notices' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		// TODO: Implement when class-frontend.php is created
		// $plugin_public = new Energy_Alabama_KC_Public( $this->get_plugin_name(), $this->get_version() );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the core functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_core_hooks() {

		// Post Types
		$this->loader->add_action( 'init', $this->post_types, 'register_post_types' );

		// Taxonomies
		$this->loader->add_action( 'init', $this->taxonomies, 'register_taxonomies' );

		// Meta Fields
		$this->loader->add_action( 'add_meta_boxes', $this->meta_fields, 'add_meta_boxes' );
		
		// Note: Meta field saving should be handled by the respective classes that create the meta boxes

		// Template Manager
		$this->loader->add_filter( 'template_include', $this->template_manager, 'load_template' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->template_manager, 'enqueue_template_assets' );

		// Modify main query for archives
		$this->loader->add_action( 'pre_get_posts', $this, 'modify_main_query' );

		// Add rewrite rules for custom URLs
		$this->loader->add_action( 'init', $this, 'add_rewrite_rules' );

		// Flush rewrite rules on activation
		$this->loader->add_action( 'wp_loaded', $this, 'flush_rewrite_rules_maybe' );

	}

	/**
	 * Modify the main query for archive pages.
	 *
	 * @since    1.0.0
	 * @param    WP_Query    $query    The query object.
	 */
	public function modify_main_query( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$options = get_option( 'energy_alabama_kc_options' );

		// Modify KC Article archive query
		if ( is_post_type_archive( 'kc_article' ) ) {
			$posts_per_page = isset( $options['articles_per_page'] ) ? intval( $options['articles_per_page'] ) : 12;
			$query->set( 'posts_per_page', $posts_per_page );
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
		}

		// Modify Docket archive query
		if ( is_post_type_archive( 'docket' ) ) {
			$posts_per_page = isset( $options['dockets_per_page'] ) ? intval( $options['dockets_per_page'] ) : 20;
			$query->set( 'posts_per_page', $posts_per_page );
			$query->set( 'orderby', 'date' );
			$query->set( 'order', 'DESC' );
		}

		// Modify taxonomy archive queries
		if ( is_tax( 'kc_category' ) ) {
			$posts_per_page = isset( $options['articles_per_page'] ) ? intval( $options['articles_per_page'] ) : 12;
			$query->set( 'posts_per_page', $posts_per_page );
		}
	}

	/**
	 * Add custom rewrite rules.
	 *
	 * @since    1.0.0
	 */
	public function add_rewrite_rules() {
		// Add custom rewrite rules if needed
		// Example: add_rewrite_rule( '^knowledge-center/search/?', 'index.php?eakc_search=1', 'top' );
		
		// For now, we'll rely on standard WordPress URL structure
		// Custom rules can be added here as needed
	}

	/**
	 * Flush rewrite rules if needed.
	 *
	 * @since    1.0.0
	 */
	public function flush_rewrite_rules_maybe() {
		if ( get_option( 'eakc_flush_rewrite_rules' ) ) {
			flush_rewrite_rules();
			delete_option( 'eakc_flush_rewrite_rules' );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Energy_Alabama_KC_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get plugin options with defaults.
	 *
	 * @since    1.0.0
	 * @return   array    Plugin options with defaults applied.
	 */
	public function get_plugin_options() {
		$defaults = array(
			'search_results_per_page' => 10,
			'enable_spanish_content' => 0,
			'articles_per_page' => 12,
			'dockets_per_page' => 20,
		);

		$options = get_option( 'energy_alabama_kc_options', array() );
		return wp_parse_args( $options, $defaults );
	}

	/**
	 * Get the admin class instance.
	 *
	 * @since    1.0.0
	 * @return   Energy_Alabama_KC_Admin|null    The admin class instance or null if not loaded.
	 */
	public function get_admin() {
		return $this->plugin_admin;
	}

	/**
	 * Get the post types handler.
	 *
	 * @since    1.0.0
	 * @return   Energy_Alabama_KC_Post_Types    The post types handler.
	 */
	public function get_post_types() {
		return $this->post_types;
	}

	/**
	 * Get the taxonomies handler.
	 *
	 * @since    1.0.0
	 * @return   Energy_Alabama_KC_Taxonomies    The taxonomies handler.
	 */
	public function get_taxonomies() {
		return $this->taxonomies;
	}

	/**
	 * Get the meta fields handler.
	 *
	 * @since    1.0.0
	 * @return   Energy_Alabama_KC_Meta_Fields    The meta fields handler.
	 */
	public function get_meta_fields() {
		return $this->meta_fields;
	}

	/**
	 * Get the template manager.
	 *
	 * @since    1.0.0
	 * @return   Energy_Alabama_KC_Template_Manager    The template manager.
	 */
	public function get_template_manager() {
		return $this->template_manager;
	}

}