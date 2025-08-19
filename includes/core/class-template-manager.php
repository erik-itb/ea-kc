<?php
/**
 * Template manager for custom templates
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template manager class
 */
class Energy_Alabama_KC_Template_Manager {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_filter('template_include', array($this, 'load_custom_templates'));
        add_action('wp_head', array($this, 'add_structured_data'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_template_assets'));
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
     * Load custom templates for our post types and pages
     */
    public function load_custom_templates($template) {
        global $post, $wp_query;


        // Handle knowledge center landing page
        if (is_page() && $post && $post->post_name === 'knowledge-center') {
            $custom_template = $this->get_template('page-knowledge-center.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        // Handle single KC article
        if (is_singular('kc_article')) {
            $custom_template = $this->get_template('single-kc-article.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        // Handle single docket
        if (is_singular('docket')) {
            $custom_template = $this->get_template('single-docket.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        // Handle KC article archive
        if (is_post_type_archive('kc_article')) {
            $custom_template = $this->get_template('archive-kc-article.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        // Handle docket archive
        if (is_post_type_archive('docket')) {
            $custom_template = $this->get_template('archive-docket.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        // Handle specific taxonomy pages
        if (is_tax('kc_category')) {
            $custom_template = $this->get_template('taxonomy-kc-category.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        if (is_tax('kc_tag')) {
            $custom_template = $this->get_template('taxonomy-kc-tag.php');
            if ($custom_template) {
                return $custom_template;
            }
            
            // Fallback to generic KC taxonomy template
            $custom_template = $this->get_template('taxonomy-kc.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        if (is_tax('docket_jurisdiction')) {
            $custom_template = $this->get_template('taxonomy-docket-jurisdiction.php');
            if ($custom_template) {
                return $custom_template;
            }
            
            // Fallback to generic taxonomy template
            $custom_template = $this->get_template('taxonomy-kc.php');
            if ($custom_template) {
                return $custom_template;
            }
        }

        return $template;
    }

    /**
     * Get template file path
     */
    private function get_template($template_name) {
        // Check in theme first (allows customization)
        $theme_template = locate_template(array(
            'energy-alabama-kc/' . $template_name,
            $template_name
        ));

        if ($theme_template) {
            return $theme_template;
        }

        // Use plugin template
        $plugin_template = EAKC_PLUGIN_DIR . 'templates/' . $template_name;
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return false;
    }

    /**
     * Enqueue template-specific assets - UNIFIED CSS LOADING
     */
    public function enqueue_template_assets() {
        global $post;

        // Enqueue unified frontend CSS on ALL Knowledge Center related pages
        if (is_page() && $post && $post->post_name === 'knowledge-center' ||
            is_singular(array('kc_article', 'docket')) || 
            is_post_type_archive(array('kc_article', 'docket')) || 
            is_tax(array('kc_category', 'kc_tag', 'docket_jurisdiction'))) {
            
            // Use our unified frontend.css for ALL pages
            wp_enqueue_style(
                'eakc-frontend-css',
                EAKC_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                EAKC_VERSION
            );

            wp_enqueue_script(
                'eakc-frontend-js',
                EAKC_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                EAKC_VERSION,
                true
            );

            // Localize script for AJAX
            wp_localize_script('eakc-frontend-js', 'eakc_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eakc_nonce'),
                'strings' => array(
                    'loading' => __('Loading...', 'energy-alabama-kc'),
                    'error' => __('An error occurred. Please try again.', 'energy-alabama-kc'),
                    'search_placeholder' => __('Search knowledge center...', 'energy-alabama-kc'),
                    'no_results' => __('No results found', 'energy-alabama-kc')
                )
            ));
        }
    }

    /**
     * Add structured data for KC articles
     */
    public function add_structured_data() {
        if (is_singular('kc_article')) {
            global $post;
            
            $schema = array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => get_the_title(),
                'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 30),
                'datePublished' => get_the_date('c'),
                'dateModified' => get_the_modified_date('c'),
                'author' => array(
                    '@type' => 'Organization',
                    'name' => 'Energy Alabama'
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => 'Energy Alabama',
                    'url' => home_url()
                ),
                'articleSection' => 'Clean Energy Knowledge Center'
            );

            if (has_post_thumbnail()) {
                $schema['image'] = get_the_post_thumbnail_url($post, 'large');
            }


            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
        }
    }

    /**
     * Get categories for landing page display
     */
    public function get_kc_categories() {
        $categories = get_terms(array(
            'taxonomy' => 'kc_category',
            'hide_empty' => false,
            'meta_key' => 'eakc_category_order',
            'orderby' => 'meta_value_num name',
            'order' => 'ASC'
        ));

        if (is_wp_error($categories)) {
            return array();
        }

        return $categories;
    }

    /**
     * Get recent articles for landing page
     */
    public function get_recent_articles($limit = 6) {
        $query = new WP_Query(array(
            'post_type' => 'kc_article',
            'posts_per_page' => $limit,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_eakc_is_spanish_content',
                        'value' => '1',
                        'compare' => '!='
                    ),
                    array(
                        'key' => '_eakc_is_spanish_content',
                        'compare' => 'NOT EXISTS'
                    )
                )
            )
        ));

        return $query->posts;
    }

    /**
     * Get category article count
     */
    public function get_category_count($category_id) {
        return get_term_meta($category_id, 'article_count', true) ?: 0;
    }
}