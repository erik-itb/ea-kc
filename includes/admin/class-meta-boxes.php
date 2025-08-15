<?php
/**
 * Meta boxes for admin interface
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Meta boxes class
 */
class Energy_Alabama_KC_Meta_Boxes {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Meta fields instance
     */
    private $meta_fields;

    /**
     * Initialize the class
     */
    public function __construct() {
        $this->meta_fields = Energy_Alabama_KC_Meta_Fields::get_instance();
        
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    /**
     * Get instance (singleton pattern)
     */
    public static function get_instance() {
        static $instance = null;
        if (null === self::$instance) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Add meta boxes to edit screens
     */
    public function add_meta_boxes() {
        // KC Article meta boxes
        add_meta_box(
            'eakc_article_details',
            __('Article Details', 'energy-alabama-kc'),
            array($this, 'render_article_details_meta_box'),
            'kc_article',
            'normal',
            'high'
        );

        add_meta_box(
            'eakc_article_resources',
            __('Resources & Attachments', 'energy-alabama-kc'),
            array($this, 'render_article_resources_meta_box'),
            'kc_article',
            'normal',
            'high'
        );

        add_meta_box(
            'eakc_spanish_content',
            __('Spanish Content', 'energy-alabama-kc'),
            array($this, 'render_spanish_content_meta_box'),
            'kc_article',
            'side',
            'default'
        );

        // Docket meta boxes
        add_meta_box(
            'eakc_docket_details',
            __('Docket Information', 'energy-alabama-kc'),
            array($this, 'render_docket_details_meta_box'),
            'docket',
            'normal',
            'high'
        );

        add_meta_box(
            'eakc_docket_documents',
            __('Document Categories', 'energy-alabama-kc'),
            array($this, 'render_docket_documents_meta_box'),
            'docket',
            'normal',
            'default'
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'))) {
            return;
        }

        global $post_type;
        if (!in_array($post_type, array('kc_article', 'docket'))) {
            return;
        }

        // Enqueue icon picker data
        wp_enqueue_script(
            'eakc-icon-picker-data',
            EAKC_PLUGIN_URL . 'assets/js/icon-picker-data.js',
            array(),
            EAKC_VERSION,
            true
        );
        
        wp_enqueue_script(
            'eakc-meta-boxes',
            EAKC_PLUGIN_URL . 'assets/js/meta-boxes.js',
            array('jquery', 'jquery-ui-sortable', 'eakc-icon-picker-data'),
            EAKC_VERSION,
            true
        );

        wp_enqueue_style(
            'eakc-meta-boxes',
            EAKC_PLUGIN_URL . 'assets/css/meta-boxes.css',
            array(),
            EAKC_VERSION
        );

        // Enqueue Phosphor Icons using script tag method
        wp_enqueue_script(
            'phosphor-icons',
            'https://unpkg.com/@phosphor-icons/web',
            array(),
            '2.1.1',
            true
        );
        
        // Enqueue WordPress color picker
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );

        wp_localize_script('eakc-meta-boxes', 'eakc_meta', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eakc_meta_nonce'),
            'strings' => array(
                'remove_resource' => __('Remove Resource', 'energy-alabama-kc'),
                'add_document' => __('Add Document', 'energy-alabama-kc'),
                'remove_category' => __('Remove Category', 'energy-alabama-kc'),
                'confirm_remove' => __('Are you sure you want to remove this item?', 'energy-alabama-kc')
            )
        ));
    }

    /**
     * Render Article Details meta box
     */
    public function render_article_details_meta_box($post) {
        wp_nonce_field('eakc_article_details_nonce', 'eakc_article_details_nonce');

        $difficulty = get_post_meta($post->ID, '_eakc_difficulty_level', true) ?: 'beginner';
        $featured_icon = get_post_meta($post->ID, '_eakc_featured_icon', true);
        $icon_color = get_post_meta($post->ID, '_eakc_icon_color', true) ?: '#ffffff';
        $read_time = get_post_meta($post->ID, '_eakc_read_time', true);
        ?>
        <table class="form-table eakc-meta-table">
            <tr>
                <th scope="row">
                    <label for="eakc_difficulty_level"><?php _e('Difficulty Level', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <select name="eakc_difficulty_level" id="eakc_difficulty_level" class="regular-text">
                        <option value="beginner" <?php selected($difficulty, 'beginner'); ?>><?php _e('Beginner', 'energy-alabama-kc'); ?></option>
                        <option value="intermediate" <?php selected($difficulty, 'intermediate'); ?>><?php _e('Intermediate', 'energy-alabama-kc'); ?></option>
                        <option value="advanced" <?php selected($difficulty, 'advanced'); ?>><?php _e('Advanced', 'energy-alabama-kc'); ?></option>
                    </select>
                    <p class="description"><?php _e('Select the appropriate difficulty level for this article.', 'energy-alabama-kc'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_featured_icon"><?php _e('Featured Icon', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <div class="eakc-icon-picker">
                        <input type="hidden" name="eakc_featured_icon" id="eakc_featured_icon" value="<?php echo esc_attr($featured_icon); ?>">
                        <div class="eakc-icon-preview" style="background: linear-gradient(135deg, #3b82f6 0%, #6366f1 100%);">
                            <?php if ($featured_icon): ?>
                                <i class="<?php echo esc_attr($featured_icon); ?>" style="font-size: 32px; color: <?php echo esc_attr($icon_color); ?>;"></i>
                            <?php else: ?>
                                <span class="eakc-no-icon"><?php _e('No icon selected', 'energy-alabama-kc'); ?></span>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="button eakc-choose-icon"><?php _e('Choose Icon', 'energy-alabama-kc'); ?></button>
                        <button type="button" class="button eakc-remove-icon" <?php echo $featured_icon ? '' : 'style="display:none;"'; ?>><?php _e('Remove Icon', 'energy-alabama-kc'); ?></button>
                    </div>
                    <div class="eakc-icon-color-picker" style="margin-top: 10px;">
                        <label for="eakc_icon_color"><?php _e('Icon Color:', 'energy-alabama-kc'); ?></label>
                        <input type="text" name="eakc_icon_color" id="eakc_icon_color" class="eakc-color-picker" value="<?php echo esc_attr($icon_color); ?>" data-default-color="#ffffff">
                    </div>
                    <p class="description"><?php _e('Optional icon to represent this article. Choose an icon and customize its color.', 'energy-alabama-kc'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_read_time"><?php _e('Estimated Read Time', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <input type="number" name="eakc_read_time" id="eakc_read_time" value="<?php echo esc_attr($read_time); ?>" class="small-text" min="1" max="60">
                    <span class="description"><?php _e('minutes (leave blank to auto-calculate)', 'energy-alabama-kc'); ?></span>
                    <p class="description"><?php _e('Estimated reading time will be calculated automatically if left blank.', 'energy-alabama-kc'); ?></p>
                </td>
            </tr>
        </table>
        
        <!-- Icon Picker Modal -->
        <div id="eakc-icon-picker-modal" class="eakc-modal" style="display:none;">
            <div class="eakc-modal-content">
                <div class="eakc-modal-header">
                    <h2><?php _e('Choose an Icon', 'energy-alabama-kc'); ?></h2>
                    <button type="button" class="eakc-modal-close">&times;</button>
                </div>
                <div class="eakc-modal-body">
                    <div class="eakc-icon-search">
                        <input type="text" id="eakc-icon-search" placeholder="<?php _e('Search icons...', 'energy-alabama-kc'); ?>" />
                        <select id="eakc-icon-category">
                            <option value=""><?php _e('All Icons', 'energy-alabama-kc'); ?></option>
                            <option value="regular"><?php _e('Regular', 'energy-alabama-kc'); ?></option>
                            <option value="fill"><?php _e('Fill', 'energy-alabama-kc'); ?></option>
                        </select>
                    </div>
                    <div class="eakc-icon-grid" id="eakc-icon-grid">
                        <!-- Icons will be loaded here via JavaScript -->
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render Article Resources meta box
     */
    public function render_article_resources_meta_box($post) {
        wp_nonce_field('eakc_article_resources_nonce', 'eakc_article_resources_nonce');

        $resources = $this->meta_fields->get_article_resources($post->ID);
        ?>
        <div class="eakc-resources-container">
            <div class="eakc-resources-list">
                <?php if (!empty($resources)): ?>
                    <?php foreach ($resources as $index => $resource): ?>
                        <?php $this->render_resource_item($resource, $index); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="eakc-add-resource">
                <button type="button" class="button button-secondary eakc-add-resource-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Add Resource', 'energy-alabama-kc'); ?>
                </button>
            </div>
        </div>

        <!-- Resource template (hidden) -->
        <script type="text/template" id="eakc-resource-template">
            <?php $this->render_resource_item(array(), '{{INDEX}}'); ?>
        </script>
        <?php
    }

    /**
     * Render individual resource item
     */
    private function render_resource_item($resource, $index) {
        $title = isset($resource['title']) ? $resource['title'] : '';
        $type = isset($resource['type']) ? $resource['type'] : 'external';
        $url = isset($resource['url']) ? $resource['url'] : '';
        $description = isset($resource['description']) ? $resource['description'] : '';
        $embed_preference = isset($resource['embed_preference']) ? $resource['embed_preference'] : 'link';
        ?>
        <div class="eakc-resource-item" data-index="<?php echo esc_attr($index); ?>">
            <div class="eakc-resource-header">
                <span class="eakc-resource-handle dashicons dashicons-menu"></span>
                <h4 class="eakc-resource-title-display">
                    <?php echo $title ? esc_html($title) : __('New Resource', 'energy-alabama-kc'); ?>
                </h4>
                <div class="eakc-resource-actions">
                    <button type="button" class="button-link eakc-toggle-resource">
                        <span class="dashicons dashicons-arrow-down"></span>
                    </button>
                    <button type="button" class="button-link eakc-remove-resource">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            
            <div class="eakc-resource-content" style="display: none;">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Title', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="eakc_resources[<?php echo esc_attr($index); ?>][title]" 
                                   value="<?php echo esc_attr($title); ?>" 
                                   class="regular-text eakc-resource-title-input" 
                                   placeholder="<?php esc_attr_e('Resource title', 'energy-alabama-kc'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('Type', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <select name="eakc_resources[<?php echo esc_attr($index); ?>][type]" class="regular-text">
                                <?php foreach ($this->get_resource_types() as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($type, $value); ?>><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('URL or File', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <input type="url" 
                                   name="eakc_resources[<?php echo esc_attr($index); ?>][url]" 
                                   value="<?php echo esc_attr($url); ?>" 
                                   class="regular-text" 
                                   placeholder="<?php esc_attr_e('https://example.com/file.pdf or copy URL from Media Library', 'energy-alabama-kc'); ?>">
                            <p class="description"><?php _e('Paste the file URL here. To get a file URL: go to Media > Library, click the file, and copy the "File URL".', 'energy-alabama-kc'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('Description', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <textarea name="eakc_resources[<?php echo esc_attr($index); ?>][description]" 
                                      class="large-text" 
                                      rows="3" 
                                      placeholder="<?php esc_attr_e('Brief description of this resource', 'energy-alabama-kc'); ?>"><?php echo esc_textarea($description); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('Display Preference', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="radio" 
                                       name="eakc_resources[<?php echo esc_attr($index); ?>][embed_preference]" 
                                       value="embed" 
                                       <?php checked($embed_preference, 'embed'); ?>>
                                <?php _e('Embed (show content inline)', 'energy-alabama-kc'); ?>
                            </label><br>
                            <label>
                                <input type="radio" 
                                       name="eakc_resources[<?php echo esc_attr($index); ?>][embed_preference]" 
                                       value="link" 
                                       <?php checked($embed_preference, 'link'); ?>>
                                <?php _e('Link (show as download/external link)', 'energy-alabama-kc'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Render Spanish Content meta box
     */
    public function render_spanish_content_meta_box($post) {
        wp_nonce_field('eakc_spanish_content_nonce', 'eakc_spanish_content_nonce');

        $spanish_available = get_post_meta($post->ID, '_eakc_spanish_available', true);
        $spanish_post_id = get_post_meta($post->ID, '_eakc_spanish_post_id', true);
        $is_spanish_content = get_post_meta($post->ID, '_eakc_is_spanish_content', true);
        ?>
        <div class="eakc-spanish-content">
            <p>
                <label>
                    <input type="checkbox" 
                           name="eakc_is_spanish_content" 
                           value="1" 
                           <?php checked($is_spanish_content, 1); ?> 
                           class="eakc-spanish-content-toggle">
                    <?php _e('Spanish Content', 'energy-alabama-kc'); ?>
                </label>
            </p>
            <p class="description" style="margin-top: -10px; margin-bottom: 15px; font-style: italic; color: #666;">
                <?php _e('Check this if this article is written in Spanish.', 'energy-alabama-kc'); ?>
            </p>
            
            <p>
                <label>
                    <input type="checkbox" 
                           name="eakc_spanish_available" 
                           value="1" 
                           <?php checked($spanish_available, 1); ?> 
                           class="eakc-spanish-toggle">
                    <?php _e('Spanish version available', 'energy-alabama-kc'); ?>
                </label>
            </p>
            <p class="description" style="margin-top: -10px; margin-bottom: 15px; font-style: italic; color: #666;">
                <?php _e('Check this if a Spanish version of this English article exists.', 'energy-alabama-kc'); ?>
            </p>
            
            <div class="eakc-spanish-fields" style="<?php echo $spanish_available ? '' : 'display: none;'; ?>">
                <p>
                    <label for="eakc_spanish_post_id"><?php _e('Spanish Article', 'energy-alabama-kc'); ?></label>
                    <select name="eakc_spanish_post_id" id="eakc_spanish_post_id" class="widefat">
                        <option value=""><?php _e('Select Spanish Article', 'energy-alabama-kc'); ?></option>
                        <?php
                        // Only show articles that are marked as Spanish content
                        $spanish_articles = get_posts(array(
                            'post_type' => 'kc_article',
                            'posts_per_page' => -1,
                            'post_status' => array('publish', 'draft'),
                            'exclude' => array($post->ID),
                            'meta_query' => array(
                                array(
                                    'key' => '_eakc_is_spanish_content',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            ),
                            'orderby' => 'title',
                            'order' => 'ASC'
                        ));
                        
                        foreach ($spanish_articles as $article) {
                            printf(
                                '<option value="%d" %s>%s</option>',
                                $article->ID,
                                selected($spanish_post_id, $article->ID, false),
                                esc_html($article->post_title)
                            );
                        }
                        ?>
                    </select>
                </p>
                
                <p class="description">
                    <?php _e('Link to the Spanish version of this article. Only articles marked as "Spanish Content" are shown.', 'energy-alabama-kc'); ?>
                </p>
            </div>
        </div>
        <?php
    }

    /**
     * Render Docket Details meta box
     */
    public function render_docket_details_meta_box($post) {
        wp_nonce_field('eakc_docket_details_nonce', 'eakc_docket_details_nonce');

        $docket_number = get_post_meta($post->ID, '_eakc_docket_number', true);
        $status = get_post_meta($post->ID, '_eakc_docket_status', true) ?: 'pending';
        $filing_date = get_post_meta($post->ID, '_eakc_filing_date', true);
        $proceeding_type = get_post_meta($post->ID, '_eakc_proceeding_type', true);
        $related_dockets = get_post_meta($post->ID, '_eakc_related_dockets', true);
        ?>
        <table class="form-table eakc-meta-table">
            <tr>
                <th scope="row">
                    <label for="eakc_docket_number"><?php _e('Docket Number', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="eakc_docket_number" 
                           id="eakc_docket_number" 
                           value="<?php echo esc_attr($docket_number); ?>" 
                           class="regular-text" 
                           placeholder="<?php esc_attr_e('e.g., UD-24-02', 'energy-alabama-kc'); ?>">
                    <p class="description"><?php _e('Official docket number assigned by the regulatory body.', 'energy-alabama-kc'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_docket_status"><?php _e('Status', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <select name="eakc_docket_status" id="eakc_docket_status" class="regular-text">
                        <option value="pending" <?php selected($status, 'pending'); ?>><?php _e('Pending', 'energy-alabama-kc'); ?></option>
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('Active', 'energy-alabama-kc'); ?></option>
                        <option value="on-hold" <?php selected($status, 'on-hold'); ?>><?php _e('On Hold', 'energy-alabama-kc'); ?></option>
                        <option value="closed" <?php selected($status, 'closed'); ?>><?php _e('Closed', 'energy-alabama-kc'); ?></option>
                        <option value="appealed" <?php selected($status, 'appealed'); ?>><?php _e('Appealed', 'energy-alabama-kc'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_filing_date"><?php _e('Filing Date', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <input type="date" 
                           name="eakc_filing_date" 
                           id="eakc_filing_date" 
                           value="<?php echo esc_attr($filing_date); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_proceeding_type"><?php _e('Proceeding Type', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="eakc_proceeding_type" 
                           id="eakc_proceeding_type" 
                           value="<?php echo esc_attr($proceeding_type); ?>" 
                           class="regular-text" 
                           placeholder="<?php esc_attr_e('e.g., Rate Case, Certificate', 'energy-alabama-kc'); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="eakc_related_dockets"><?php _e('Related Dockets', 'energy-alabama-kc'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           name="eakc_related_dockets" 
                           id="eakc_related_dockets" 
                           value="<?php echo esc_attr($related_dockets); ?>" 
                           class="regular-text" 
                           placeholder="<?php esc_attr_e('Comma-separated docket numbers', 'energy-alabama-kc'); ?>">
                    <p class="description"><?php _e('Enter related docket numbers separated by commas.', 'energy-alabama-kc'); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render Docket Documents meta box
     */
    public function render_docket_documents_meta_box($post) {
        wp_nonce_field('eakc_docket_documents_nonce', 'eakc_docket_documents_nonce');

        $categories = $this->meta_fields->get_docket_categories($post->ID);
        ?>
        <div class="eakc-docket-categories">
            <p class="description">
                <?php _e('Organize documents into categories. Each category will display as an accordion section on the docket page.', 'energy-alabama-kc'); ?>
            </p>
            
            <div class="eakc-categories-list">
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $index => $category): ?>
                        <?php $this->render_docket_category($category, $index); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="eakc-add-category">
                <button type="button" class="button button-secondary eakc-add-category-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php _e('Add Document Category', 'energy-alabama-kc'); ?>
                </button>
            </div>
        </div>

        <!-- Category template (hidden) -->
        <script type="text/template" id="eakc-category-template">
            <?php $this->render_docket_category(array(), '{{INDEX}}'); ?>
        </script>
        <?php
    }

    /**
     * Render individual docket category
     */
    private function render_docket_category($category, $index) {
        $name = isset($category['name']) ? $category['name'] : '';
        $description = isset($category['description']) ? $category['description'] : '';
        $documents = isset($category['documents']) ? $category['documents'] : array();
        $order = isset($category['order']) ? $category['order'] : 0;
        ?>
        <div class="eakc-category-item" data-index="<?php echo esc_attr($index); ?>">
            <div class="eakc-category-header">
                <span class="eakc-category-handle dashicons dashicons-menu"></span>
                <h4 class="eakc-category-title-display">
                    <?php echo $name ? esc_html($name) : __('New Category', 'energy-alabama-kc'); ?>
                </h4>
                <div class="eakc-category-actions">
                    <button type="button" class="button-link eakc-toggle-category">
                        <span class="dashicons dashicons-arrow-down"></span>
                    </button>
                    <button type="button" class="button-link eakc-remove-category">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            
            <div class="eakc-category-content" style="display: none;">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label><?php _e('Category Name', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   name="eakc_categories[<?php echo esc_attr($index); ?>][name]" 
                                   value="<?php echo esc_attr($name); ?>" 
                                   class="regular-text eakc-category-title-input" 
                                   placeholder="<?php esc_attr_e('e.g., Initial Filings', 'energy-alabama-kc'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label><?php _e('Description', 'energy-alabama-kc'); ?></label>
                        </th>
                        <td>
                            <textarea name="eakc_categories[<?php echo esc_attr($index); ?>][description]" 
                                      class="large-text" 
                                      rows="2" 
                                      placeholder="<?php esc_attr_e('Optional description for this category', 'energy-alabama-kc'); ?>"><?php echo esc_textarea($description); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <div class="eakc-documents-section">
                    <h5><?php _e('Documents', 'energy-alabama-kc'); ?></h5>
                    <div class="eakc-documents-list">
                        <?php if (!empty($documents)): ?>
                            <?php foreach ($documents as $doc_index => $document): ?>
                                <?php $this->render_document_item($document, $index, $doc_index); ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button button-small eakc-add-document">
                        <span class="dashicons dashicons-plus-alt"></span>
                        <?php _e('Add Document', 'energy-alabama-kc'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render individual document item
     */
    private function render_document_item($document, $category_index, $doc_index) {
        $title = isset($document['title']) ? $document['title'] : '';
        $url = isset($document['url']) ? $document['url'] : '';
        $type = isset($document['type']) ? $document['type'] : 'pdf';
        $date = isset($document['date']) ? $document['date'] : '';
        $file_size = isset($document['file_size']) ? $document['file_size'] : '';
        ?>
        <div class="eakc-document-item">
            <div class="eakc-document-fields">
                <input type="text" 
                       name="eakc_categories[<?php echo esc_attr($category_index); ?>][documents][<?php echo esc_attr($doc_index); ?>][title]" 
                       value="<?php echo esc_attr($title); ?>" 
                       placeholder="<?php esc_attr_e('Document title', 'energy-alabama-kc'); ?>" 
                       class="regular-text">
                
                <select name="eakc_categories[<?php echo esc_attr($category_index); ?>][documents][<?php echo esc_attr($doc_index); ?>][type]">
                    <?php foreach ($this->get_document_types() as $value => $label): ?>
                        <option value="<?php echo esc_attr($value); ?>" <?php selected($type, $value); ?>><?php echo esc_html($label); ?></option>
                    <?php endforeach; ?>
                </select>
                
                <input type="url" 
                       name="eakc_categories[<?php echo esc_attr($category_index); ?>][documents][<?php echo esc_attr($doc_index); ?>][url]" 
                       value="<?php echo esc_attr($url); ?>" 
                       placeholder="<?php esc_attr_e('Document URL', 'energy-alabama-kc'); ?>" 
                       class="regular-text">
                
                <input type="date" 
                       name="eakc_categories[<?php echo esc_attr($category_index); ?>][documents][<?php echo esc_attr($doc_index); ?>][date]" 
                       value="<?php echo esc_attr($date); ?>" 
                       class="regular-text">
                
                <button type="button" class="button-link eakc-remove-document">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_boxes($post_id, $post) {
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check post type
        if (!in_array($post->post_type, array('kc_article', 'docket'))) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if ($post->post_type === 'kc_article') {
            $this->save_article_meta($post_id);
        } elseif ($post->post_type === 'docket') {
            $this->save_docket_meta($post_id);
        }
    }

    /**
     * Save KC Article meta data
     */
    private function save_article_meta($post_id) {
        // Verify nonces
        if (!isset($_POST['eakc_article_details_nonce']) || !wp_verify_nonce($_POST['eakc_article_details_nonce'], 'eakc_article_details_nonce')) {
            return;
        }

        // Save article details
        if (isset($_POST['eakc_difficulty_level'])) {
            update_post_meta($post_id, '_eakc_difficulty_level', sanitize_text_field($_POST['eakc_difficulty_level']));
        }

        if (isset($_POST['eakc_featured_icon'])) {
            update_post_meta($post_id, '_eakc_featured_icon', sanitize_text_field($_POST['eakc_featured_icon']));
        }
        
        if (isset($_POST['eakc_icon_color'])) {
            update_post_meta($post_id, '_eakc_icon_color', sanitize_hex_color($_POST['eakc_icon_color']));
        }

        // Handle read time
        if (isset($_POST['eakc_read_time']) && !empty($_POST['eakc_read_time'])) {
            update_post_meta($post_id, '_eakc_read_time', absint($_POST['eakc_read_time']));
        } else {
            // Auto-calculate read time
            $post = get_post($post_id);
            $read_time = $this->meta_fields->calculate_read_time($post->post_content);
            update_post_meta($post_id, '_eakc_read_time', $read_time);
        }

        // Save resources
        if (isset($_POST['eakc_article_resources_nonce']) && wp_verify_nonce($_POST['eakc_article_resources_nonce'], 'eakc_article_resources_nonce')) {
            $resources = array();
            if (isset($_POST['eakc_resources']) && is_array($_POST['eakc_resources'])) {
                foreach ($_POST['eakc_resources'] as $resource_data) {
                    if (!empty($resource_data['title']) || !empty($resource_data['url'])) {
                        $resources[] = array(
                            'title' => sanitize_text_field(isset($resource_data['title']) ? $resource_data['title'] : ''),
                            'type' => sanitize_text_field(isset($resource_data['type']) ? $resource_data['type'] : 'external'),
                            'url' => esc_url_raw(isset($resource_data['url']) ? $resource_data['url'] : ''),
                            'description' => sanitize_textarea_field(isset($resource_data['description']) ? $resource_data['description'] : ''),
                            'embed_preference' => sanitize_text_field(isset($resource_data['embed_preference']) ? $resource_data['embed_preference'] : 'link')
                        );
                    }
                }
            }
            update_post_meta($post_id, '_eakc_resources', wp_json_encode($resources));
        }

        // Save Spanish content
        if (isset($_POST['eakc_spanish_content_nonce']) && wp_verify_nonce($_POST['eakc_spanish_content_nonce'], 'eakc_spanish_content_nonce')) {
            $is_spanish_content = isset($_POST['eakc_is_spanish_content']);
            $spanish_available = isset($_POST['eakc_spanish_available']);
            
            // Save Spanish content flag
            update_post_meta($post_id, '_eakc_is_spanish_content', $is_spanish_content);
            
            // If marked as Spanish content, cannot have Spanish version available
            if ($is_spanish_content) {
                update_post_meta($post_id, '_eakc_spanish_available', false);
                delete_post_meta($post_id, '_eakc_spanish_post_id');
            } else {
                // Save Spanish version available
                update_post_meta($post_id, '_eakc_spanish_available', $spanish_available);
                
                if ($spanish_available && isset($_POST['eakc_spanish_post_id'])) {
                    update_post_meta($post_id, '_eakc_spanish_post_id', absint($_POST['eakc_spanish_post_id']));
                } else {
                    delete_post_meta($post_id, '_eakc_spanish_post_id');
                }
            }
        }

        // Update last modified by
        update_post_meta($post_id, '_eakc_last_updated_by', get_current_user_id());
    }

    /**
     * Save Docket meta data
     */
    private function save_docket_meta($post_id) {
        // Verify nonces
        if (!isset($_POST['eakc_docket_details_nonce']) || !wp_verify_nonce($_POST['eakc_docket_details_nonce'], 'eakc_docket_details_nonce')) {
            return;
        }

        // Save docket details
        $docket_fields = array(
            'eakc_docket_number' => '_eakc_docket_number',
            'eakc_docket_status' => '_eakc_docket_status',
            'eakc_filing_date' => '_eakc_filing_date',
            'eakc_proceeding_type' => '_eakc_proceeding_type',
            'eakc_related_dockets' => '_eakc_related_dockets'
        );

        foreach ($docket_fields as $field => $meta_key) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$field]));
            }
        }

        // Save document categories
        if (wp_verify_nonce($_POST['eakc_docket_documents_nonce'] ?? '', 'eakc_docket_documents_nonce')) {
            $categories = array();
            if (isset($_POST['eakc_categories']) && is_array($_POST['eakc_categories'])) {
                foreach ($_POST['eakc_categories'] as $category_data) {
                    if (!empty($category_data['name'])) {
                        $documents = array();
                        if (isset($category_data['documents']) && is_array($category_data['documents'])) {
                            foreach ($category_data['documents'] as $doc_data) {
                                if (!empty($doc_data['title']) || !empty($doc_data['url'])) {
                                    $documents[] = array(
                                        'title' => sanitize_text_field($doc_data['title'] ?? ''),
                                        'url' => esc_url_raw($doc_data['url'] ?? ''),
                                        'type' => sanitize_text_field($doc_data['type'] ?? 'pdf'),
                                        'date' => sanitize_text_field($doc_data['date'] ?? ''),
                                        'file_size' => sanitize_text_field($doc_data['file_size'] ?? '')
                                    );
                                }
                            }
                        }

                        $categories[] = array(
                            'name' => sanitize_text_field($category_data['name']),
                            'description' => sanitize_textarea_field($category_data['description'] ?? ''),
                            'documents' => $documents,
                            'order' => count($categories)
                        );
                    }
                }
            }
            update_post_meta($post_id, '_eakc_document_categories', wp_json_encode($categories));
        }
    }

    /**
     * Get resource types for dropdown
     */
    private function get_resource_types() {
        return array(
            'pdf' => __('PDF Document', 'energy-alabama-kc'),
            'doc' => __('Word Document', 'energy-alabama-kc'),
            'sheet' => __('Spreadsheet', 'energy-alabama-kc'),
            'presentation' => __('Presentation', 'energy-alabama-kc'),
            'video' => __('Video', 'energy-alabama-kc'),
            'external' => __('External Link', 'energy-alabama-kc'),
            'google-doc' => __('Google Document', 'energy-alabama-kc'),
            'google-sheet' => __('Google Sheet', 'energy-alabama-kc'),
            'google-slides' => __('Google Slides', 'energy-alabama-kc'),
            'youtube' => __('YouTube Video', 'energy-alabama-kc'),
            'vimeo' => __('Vimeo Video', 'energy-alabama-kc')
        );
    }

    /**
     * Get document types for dropdown
     */
    private function get_document_types() {
        return array(
            'pdf' => __('PDF', 'energy-alabama-kc'),
            'doc' => __('Word Doc', 'energy-alabama-kc'),
            'sheet' => __('Spreadsheet', 'energy-alabama-kc'),
            'presentation' => __('Presentation', 'energy-alabama-kc'),
            'external' => __('External Link', 'energy-alabama-kc')
        );
    }

    /**
     * Get icon SVG (placeholder - will implement icon system later)
     */
    private function get_icon_svg($icon_slug) {
        // Placeholder - return a simple icon for now
        return '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>';
    }
}