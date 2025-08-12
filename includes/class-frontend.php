<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://energyalabama.org
 * @since      1.0.0
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing
 * side of the site including:
 * - AJAX search functionality
 * - Resource display
 * - Spanish content toggling
 * - Frontend assets management
 * - Shortcode handling
 *
 * @package    Energy_Alabama_KC
 * @subpackage Energy_Alabama_KC/includes
 * @author     Energy Alabama <info@energyalabama.org>
 */
class Energy_Alabama_KC_Frontend {

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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name    The name of the plugin.
     * @param    string    $version        The version of this plugin.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Only enqueue on KC-related pages
        if ( ! $this->is_kc_page() ) {
            return;
        }

        wp_enqueue_style( 
            $this->plugin_name, 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/frontend.css', 
            array(), 
            $this->version, 
            'all' 
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Only enqueue on KC-related pages
        if ( ! $this->is_kc_page() ) {
            return;
        }

        wp_enqueue_script( 
            $this->plugin_name, 
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/frontend.js', 
            array( 'jquery' ), 
            $this->version, 
            true 
        );

        // Localize script for AJAX
        wp_localize_script( $this->plugin_name, 'eakc_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'eakc_ajax_nonce' ),
            'strings' => array(
                'loading' => __( 'Loading...', 'energy-alabama-kc' ),
                'error' => __( 'An error occurred. Please try again.', 'energy-alabama-kc' ),
                'search_placeholder' => __( 'Search knowledge center...', 'energy-alabama-kc' ),
                'no_results' => __( 'No results found', 'energy-alabama-kc' ),
                'view_more' => __( 'View More', 'energy-alabama-kc' ),
                'view_less' => __( 'View Less', 'energy-alabama-kc' ),
                'download' => __( 'Download', 'energy-alabama-kc' ),
                'open_link' => __( 'Open Link', 'energy-alabama-kc' ),
            )
        ));
    }

    /**
     * Check if current page is KC-related
     *
     * @since    1.0.0
     * @return   bool    True if KC page, false otherwise.
     */
    private function is_kc_page() {
        global $post;

        // Check for KC article or docket single/archive pages
        if ( is_singular( array( 'kc_article', 'docket' ) ) || 
             is_post_type_archive( array( 'kc_article', 'docket' ) ) ) {
            return true;
        }

        // Check for KC taxonomies
        if ( is_tax( array( 'kc_category', 'kc_tags', 'docket_jurisdiction' ) ) ) {
            return true;
        }

        // Check for knowledge center page
        if ( is_page() && $post && $post->post_name === 'knowledge-center' ) {
            return true;
        }

        // Check if shortcode is present on the page
        if ( is_singular() && $post ) {
            if ( has_shortcode( $post->post_content, 'eakc_search' ) ||
                 has_shortcode( $post->post_content, 'eakc_categories' ) ||
                 has_shortcode( $post->post_content, 'eakc_articles' ) ||
                 has_shortcode( $post->post_content, 'eakc_dockets' ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle AJAX search request
     *
     * @since    1.0.0
     */
    public function ajax_search() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'eakc_ajax_nonce' ) ) {
            wp_die( json_encode( array( 'error' => 'Security check failed' ) ) );
        }

        $search_term = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
        $post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'kc_article';
        $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;

        // Build query arguments
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            's' => $search_term,
            'posts_per_page' => 10,
            'paged' => $paged,
            'orderby' => 'relevance',
            'order' => 'DESC'
        );

        // Add category filter if specified
        if ( $category > 0 ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'kc_category',
                    'field' => 'term_id',
                    'terms' => $category
                )
            );
        }

        // Perform the search
        $query = new WP_Query( $args );
        $results = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'url' => get_permalink(),
                    'date' => get_the_date(),
                    'categories' => $this->get_post_categories( get_the_ID() ),
                    'difficulty' => get_post_meta( get_the_ID(), '_eakc_difficulty_level', true ),
                    'type' => get_post_type()
                );
            }
            wp_reset_postdata();
        }

        // Return results
        wp_send_json( array(
            'success' => true,
            'results' => $results,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $paged
        ));
    }

    /**
     * Handle AJAX load more articles
     *
     * @since    1.0.0
     */
    public function ajax_load_more() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'eakc_ajax_nonce' ) ) {
            wp_die( json_encode( array( 'error' => 'Security check failed' ) ) );
        }

        $post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( $_POST['post_type'] ) : 'kc_article';
        $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 2;
        $posts_per_page = isset( $_POST['posts_per_page'] ) ? intval( $_POST['posts_per_page'] ) : 12;

        // Build query arguments
        $args = array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        // Add category filter if specified
        if ( $category > 0 ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'kc_category',
                    'field' => 'term_id',
                    'terms' => $category
                )
            );
        }

        // Perform the query
        $query = new WP_Query( $args );
        $html = '';

        if ( $query->have_posts() ) {
            ob_start();
            while ( $query->have_posts() ) {
                $query->the_post();
                
                // Load article card template
                $this->get_template_part( 'partials/article-card' );
            }
            $html = ob_get_clean();
            wp_reset_postdata();
        }

        // Return results
        wp_send_json( array(
            'success' => true,
            'html' => $html,
            'has_more' => ( $paged < $query->max_num_pages )
        ));
    }

    /**
     * Handle AJAX filter articles
     *
     * @since    1.0.0
     */
    public function ajax_filter_articles() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'eakc_ajax_nonce' ) ) {
            wp_die( json_encode( array( 'error' => 'Security check failed' ) ) );
        }

        $category = isset( $_POST['category'] ) ? intval( $_POST['category'] ) : 0;
        $tag = isset( $_POST['tag'] ) ? intval( $_POST['tag'] ) : 0;
        $difficulty = isset( $_POST['difficulty'] ) ? sanitize_text_field( $_POST['difficulty'] ) : '';
        $sort = isset( $_POST['sort'] ) ? sanitize_text_field( $_POST['sort'] ) : 'date';
        $paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;

        // Build query arguments
        $args = array(
            'post_type' => 'kc_article',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $paged
        );

        // Add sorting
        switch ( $sort ) {
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            case 'popular':
                $args['orderby'] = 'comment_count';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }

        // Build tax query
        $tax_query = array();

        if ( $category > 0 ) {
            $tax_query[] = array(
                'taxonomy' => 'kc_category',
                'field' => 'term_id',
                'terms' => $category
            );
        }

        if ( $tag > 0 ) {
            $tax_query[] = array(
                'taxonomy' => 'kc_tags',
                'field' => 'term_id',
                'terms' => $tag
            );
        }

        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        // Add difficulty meta query
        if ( ! empty( $difficulty ) ) {
            $args['meta_query'] = array(
                array(
                    'key' => '_eakc_difficulty_level',
                    'value' => $difficulty,
                    'compare' => '='
                )
            );
        }

        // Perform the query
        $query = new WP_Query( $args );
        $html = '';

        if ( $query->have_posts() ) {
            ob_start();
            while ( $query->have_posts() ) {
                $query->the_post();
                
                // Load article card template
                $this->get_template_part( 'partials/article-card' );
            }
            $html = ob_get_clean();
            wp_reset_postdata();
        } else {
            $html = '<p class="eakc-no-results">' . __( 'No articles found matching your criteria.', 'energy-alabama-kc' ) . '</p>';
        }

        // Return results
        wp_send_json( array(
            'success' => true,
            'html' => $html,
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages
        ));
    }

    /**
     * Handle AJAX get resource content
     *
     * @since    1.0.0
     */
    public function ajax_get_resource() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'eakc_ajax_nonce' ) ) {
            wp_die( json_encode( array( 'error' => 'Security check failed' ) ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        $resource_index = isset( $_POST['resource_index'] ) ? intval( $_POST['resource_index'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( 'Invalid post ID' );
        }

        $resources = get_post_meta( $post_id, '_eakc_resources', true );

        if ( ! is_array( $resources ) || ! isset( $resources[ $resource_index ] ) ) {
            wp_send_json_error( 'Resource not found' );
        }

        $resource = $resources[ $resource_index ];

        // Build response based on resource type
        $response = array(
            'success' => true,
            'type' => $resource['type'],
            'title' => $resource['title']
        );

        switch ( $resource['type'] ) {
            case 'document':
                if ( ! empty( $resource['file_id'] ) ) {
                    $response['url'] = wp_get_attachment_url( $resource['file_id'] );
                    $response['filename'] = basename( get_attached_file( $resource['file_id'] ) );
                }
                break;

            case 'video':
                $response['embed_url'] = $resource['embed_url'];
                // Convert YouTube URL to embed format if needed
                if ( strpos( $resource['embed_url'], 'youtube.com/watch' ) !== false ) {
                    parse_str( parse_url( $resource['embed_url'], PHP_URL_QUERY ), $vars );
                    if ( isset( $vars['v'] ) ) {
                        $response['embed_url'] = 'https://www.youtube.com/embed/' . $vars['v'];
                    }
                }
                break;

            case 'link':
                $response['url'] = $resource['url'];
                break;

            case 'embedded':
                $response['content'] = $resource['content'];
                break;
        }

        wp_send_json( $response );
    }

    /**
     * Handle Spanish content toggle
     *
     * @since    1.0.0
     */
    public function handle_spanish_toggle() {
        // Check if Spanish content is enabled
        $options = get_option( 'energy_alabama_kc_options' );
        if ( ! isset( $options['enable_spanish_content'] ) || ! $options['enable_spanish_content'] ) {
            return;
        }

        // Add Spanish toggle button to appropriate pages
        add_action( 'eakc_before_content', array( $this, 'render_spanish_toggle' ) );
    }

    /**
     * Render Spanish toggle button
     *
     * @since    1.0.0
     */
    public function render_spanish_toggle() {
        global $post;

        // Check if this post has a Spanish version
        $spanish_post_id = get_post_meta( $post->ID, '_eakc_spanish_post_id', true );
        
        if ( ! $spanish_post_id ) {
            return;
        }

        // Check if the Spanish post still exists
        $spanish_post = get_post( $spanish_post_id );
        if ( ! $spanish_post || $spanish_post->post_status !== 'publish' ) {
            return;
        }

        // Determine if we're currently viewing Spanish version
        $is_spanish = isset( $_GET['lang'] ) && $_GET['lang'] === 'es';
        
        ?>
        <div class="eakc-language-toggle">
            <?php if ( $is_spanish ) : ?>
                <a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" class="eakc-lang-switch">
                    <?php _e( 'View in English', 'energy-alabama-kc' ); ?>
                </a>
            <?php else : ?>
                <a href="<?php echo esc_url( add_query_arg( 'lang', 'es', get_permalink( $spanish_post_id ) ) ); ?>" class="eakc-lang-switch">
                    <?php _e( 'Ver en Español', 'energy-alabama-kc' ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Add Open Graph tags for KC articles
     *
     * @since    1.0.0
     */
    public function add_open_graph_tags() {
        if ( ! is_singular( 'kc_article' ) ) {
            return;
        }

        global $post;
        
        ?>
        <meta property="og:title" content="<?php echo esc_attr( get_the_title() ); ?>" />
        <meta property="og:type" content="article" />
        <meta property="og:url" content="<?php echo esc_url( get_permalink() ); ?>" />
        <meta property="og:description" content="<?php echo esc_attr( get_the_excerpt() ); ?>" />
        <?php if ( has_post_thumbnail() ) : ?>
            <meta property="og:image" content="<?php echo esc_url( get_the_post_thumbnail_url( $post, 'large' ) ); ?>" />
        <?php endif; ?>
        <meta property="og:site_name" content="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
        <meta property="article:published_time" content="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" />
        <meta property="article:modified_time" content="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>" />
        <?php
    }

    /**
     * Add structured data for KC articles
     *
     * @since    1.0.0
     */
    public function add_structured_data() {
        if ( ! is_singular( array( 'kc_article', 'docket' ) ) ) {
            return;
        }

        global $post;
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => get_the_title(),
            'description' => get_the_excerpt() ?: wp_trim_words( get_the_content(), 30 ),
            'datePublished' => get_the_date( 'c' ),
            'dateModified' => get_the_modified_date( 'c' ),
            'author' => array(
                '@type' => 'Organization',
                'name' => 'Energy Alabama'
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => 'Energy Alabama',
                'url' => home_url()
            )
        );

        if ( has_post_thumbnail() ) {
            $schema['image'] = get_the_post_thumbnail_url( $post, 'large' );
        }

        // Add specific fields for KC articles
        if ( get_post_type() === 'kc_article' ) {
            $difficulty = get_post_meta( $post->ID, '_eakc_difficulty_level', true );
            if ( $difficulty ) {
                $schema['educationalLevel'] = ucfirst( $difficulty );
            }
            
            $schema['articleSection'] = 'Clean Energy Knowledge Center';
        }

        // Add specific fields for dockets
        if ( get_post_type() === 'docket' ) {
            $docket_number = get_post_meta( $post->ID, '_eakc_docket_number', true );
            if ( $docket_number ) {
                $schema['identifier'] = $docket_number;
            }
            
            $schema['articleSection'] = 'Legal and Regulatory Dockets';
        }

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
    }

    /**
     * Modify main query for archives
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The main query object.
     */
    public function modify_archive_query( $query ) {
        // Only modify main query on frontend
        if ( ! is_admin() && $query->is_main_query() ) {
            
            // Set posts per page for KC article archives
            if ( $query->is_post_type_archive( 'kc_article' ) ) {
                $options = get_option( 'energy_alabama_kc_options' );
                $per_page = isset( $options['articles_per_page'] ) ? $options['articles_per_page'] : 12;
                $query->set( 'posts_per_page', $per_page );
            }
            
            // Set posts per page for docket archives
            if ( $query->is_post_type_archive( 'docket' ) ) {
                $options = get_option( 'energy_alabama_kc_options' );
                $per_page = isset( $options['dockets_per_page'] ) ? $options['dockets_per_page'] : 20;
                $query->set( 'posts_per_page', $per_page );
            }
            
            // Set posts per page for taxonomy archives
            if ( $query->is_tax( array( 'kc_category', 'kc_tags', 'docket_jurisdiction' ) ) ) {
                $options = get_option( 'energy_alabama_kc_options' );
                $per_page = isset( $options['articles_per_page'] ) ? $options['articles_per_page'] : 12;
                $query->set( 'posts_per_page', $per_page );
            }
        }
    }

    /**
     * Add custom body classes
     *
     * @since    1.0.0
     * @param    array    $classes    Existing body classes.
     * @return   array                Modified body classes.
     */
    public function add_body_classes( $classes ) {
        // Add class for KC pages
        if ( $this->is_kc_page() ) {
            $classes[] = 'eakc-page';
        }

        // Add specific classes for different KC pages
        if ( is_singular( 'kc_article' ) ) {
            $classes[] = 'eakc-single-article';
            
            // Add difficulty level class
            $difficulty = get_post_meta( get_the_ID(), '_eakc_difficulty_level', true );
            if ( $difficulty ) {
                $classes[] = 'eakc-difficulty-' . sanitize_html_class( $difficulty );
            }
        }

        if ( is_singular( 'docket' ) ) {
            $classes[] = 'eakc-single-docket';
            
            // Add status class
            $status = get_post_meta( get_the_ID(), '_eakc_docket_status', true );
            if ( $status ) {
                $classes[] = 'eakc-docket-status-' . sanitize_html_class( $status );
            }
        }

        if ( is_post_type_archive( 'kc_article' ) ) {
            $classes[] = 'eakc-archive-articles';
        }

        if ( is_post_type_archive( 'docket' ) ) {
            $classes[] = 'eakc-archive-dockets';
        }

        if ( is_tax( 'kc_category' ) ) {
            $classes[] = 'eakc-category-archive';
        }

        return $classes;
    }

    /**
     * Get post categories for display
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     * @return   array              Array of category data.
     */
    private function get_post_categories( $post_id ) {
        $categories = array();
        $terms = get_the_terms( $post_id, 'kc_category' );
        
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $categories[] = array(
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'url' => get_term_link( $term )
                );
            }
        }
        
        return $categories;
    }

    /**
     * Load a template part
     *
     * @since    1.0.0
     * @param    string    $slug    Template slug.
     * @param    string    $name    Template name.
     */
    private function get_template_part( $slug, $name = null ) {
        $templates = array();
        
        if ( isset( $name ) ) {
            $templates[] = "{$slug}-{$name}.php";
        }
        
        $templates[] = "{$slug}.php";
        
        // Look in theme first
        $template = locate_template( array_map( function( $template ) {
            return 'energy-alabama-kc/' . $template;
        }, $templates ) );
        
        // Fall back to plugin templates
        if ( ! $template ) {
            foreach ( $templates as $template_name ) {
                $plugin_template = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/' . $template_name;
                if ( file_exists( $plugin_template ) ) {
                    $template = $plugin_template;
                    break;
                }
            }
        }
        
        if ( $template ) {
            include $template;
        }
    }

    /**
     * Add print styles for articles
     *
     * @since    1.0.0
     */
    public function add_print_styles() {
        if ( ! is_singular( array( 'kc_article', 'docket' ) ) ) {
            return;
        }
        ?>
        <style type="text/css" media="print">
            .eakc-language-toggle,
            .eakc-resource-buttons,
            .eakc-share-buttons,
            .eakc-related-articles,
            .site-header,
            .site-footer,
            .sidebar {
                display: none !important;
            }
            
            .eakc-article-content {
                width: 100% !important;
                max-width: none !important;
            }
            
            .eakc-article-header {
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }
            
            .eakc-resources-list {
                page-break-inside: avoid;
            }
        </style>
        <?php
    }
}