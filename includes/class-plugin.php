<?php
/**
 * The core plugin class
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin class
 */
class Energy_Alabama_KC {

    /**
     * The loader that's responsible for maintaining and registering all hooks
     *
     * @var Energy_Alabama_KC_Loader
     */
    protected $loader;

    /**
     * The unique identifier of this plugin
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * The current version of the plugin
     *
     * @var string
     */
    protected $version;

    /**
     * Define the core functionality of the plugin
     */
    public function __construct() {
        $this->version = EAKC_VERSION;
        $this->plugin_name = 'energy-alabama-kc';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters
        require_once EAKC_PLUGIN_DIR . 'includes/class-loader.php';

        // The class responsible for defining internationalization functionality
        require_once EAKC_PLUGIN_DIR . 'includes/class-i18n.php';

        // Core functionality
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-post-types.php';
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-taxonomies.php';
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-template-manager.php';
        require_once EAKC_PLUGIN_DIR . 'includes/core/class-meta-fields.php';

        // Admin functionality 
        require_once EAKC_PLUGIN_DIR . 'includes/admin/class-meta-boxes.php';

        // TODO: Load these as we create them
        // require_once EAKC_PLUGIN_DIR . 'includes/core/class-search-handler.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/admin/class-admin.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/admin/class-settings.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/admin/class-icon-manager.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/frontend/class-frontend.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/frontend/class-ajax-handlers.php';
        // require_once EAKC_PLUGIN_DIR . 'includes/utils/class-helpers.php';

        $this->loader = new Energy_Alabama_KC_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization
     */
    private function set_locale() {
        $plugin_i18n = new Energy_Alabama_KC_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     */
    private function define_admin_hooks() {
        // Post types and taxonomies - use singleton pattern
        Energy_Alabama_KC_Post_Types::get_instance();
        Energy_Alabama_KC_Taxonomies::get_instance();

        // Meta fields and boxes
        Energy_Alabama_KC_Meta_Fields::get_instance();
        Energy_Alabama_KC_Meta_Boxes::get_instance();

        // TODO: Add these hooks as we create the classes
        /*
        // Core admin functionality
        $plugin_admin = new Energy_Alabama_KC_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

        // Settings page
        $settings = new Energy_Alabama_KC_Settings();
        $this->loader->add_action('admin_menu', $settings, 'add_options_page');
        $this->loader->add_action('admin_init', $settings, 'register_settings');

        // Icon manager
        $icon_manager = new Energy_Alabama_KC_Icon_Manager();
        $this->loader->add_action('wp_ajax_eakc_get_icons', $icon_manager, 'ajax_get_icons');
        */
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     */
    private function define_public_hooks() {
        // Template manager
        Energy_Alabama_KC_Template_Manager::get_instance();

        // TODO: Add these hooks as we create the classes
        /*
        // Frontend functionality
        $plugin_public = new Energy_Alabama_KC_Frontend($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        // Search functionality
        $search_handler = new Energy_Alabama_KC_Search_Handler();
        $this->loader->add_action('wp_ajax_eakc_search', $search_handler, 'ajax_search');
        $this->loader->add_action('wp_ajax_nopriv_eakc_search', $search_handler, 'ajax_search');

        // AJAX handlers
        $ajax_handlers = new Energy_Alabama_KC_Ajax_Handlers();
        $this->loader->add_action('wp_ajax_eakc_load_resources', $ajax_handlers, 'load_resources');
        $this->loader->add_action('wp_ajax_nopriv_eakc_load_resources', $ajax_handlers, 'load_resources');
        */
    }

    /**
     * Run the loader to execute all of the hooks with WordPress
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks
     *
     * @return Energy_Alabama_KC_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
}
