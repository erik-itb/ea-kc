<?php
/**
 * Meta fields registration and management
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Meta fields class
 */
class Energy_Alabama_KC_Meta_Fields {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('init', array($this, 'register_meta_fields'));
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
     * Register all meta fields
     */
    public function register_meta_fields() {
        $this->register_kc_article_meta();
        $this->register_docket_meta();
    }

    /**
     * Register KC Article meta fields
     */
    private function register_kc_article_meta() {
        // Difficulty level
        register_meta('post', '_eakc_difficulty_level', array(
            'type' => 'string',
            'description' => 'Article difficulty level',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => array($this, 'sanitize_difficulty_level')
        ));

        // Spanish content availability
        register_meta('post', '_eakc_spanish_available', array(
            'type' => 'boolean',
            'description' => 'Whether Spanish version is available',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => 'rest_sanitize_boolean'
        ));

        // Spanish post ID link
        register_meta('post', '_eakc_spanish_post_id', array(
            'type' => 'integer',
            'description' => 'ID of Spanish version post',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => 'absint'
        ));

        // Featured icon
        register_meta('post', '_eakc_featured_icon', array(
            'type' => 'string',
            'description' => 'Featured icon slug',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Resources (stored as JSON)
        register_meta('post', '_eakc_resources', array(
            'type' => 'string',
            'description' => 'Article resources as JSON',
            'single' => true,
            'show_in_rest' => false, // Complex data, handle separately
            'object_subtype' => 'kc_article',
            'sanitize_callback' => array($this, 'sanitize_resources_json')
        ));

        // Estimated read time (auto-calculated)
        register_meta('post', '_eakc_read_time', array(
            'type' => 'integer',
            'description' => 'Estimated read time in minutes',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => 'absint'
        ));

        // Last updated by
        register_meta('post', '_eakc_last_updated_by', array(
            'type' => 'integer',
            'description' => 'User ID who last updated the article',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'kc_article',
            'sanitize_callback' => 'absint'
        ));
    }

    /**
     * Register Docket meta fields
     */
    private function register_docket_meta() {
        // Docket number
        register_meta('post', '_eakc_docket_number', array(
            'type' => 'string',
            'description' => 'Official docket number',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'docket',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Docket status
        register_meta('post', '_eakc_docket_status', array(
            'type' => 'string',
            'description' => 'Current status of the docket',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'docket',
            'sanitize_callback' => array($this, 'sanitize_docket_status')
        ));

        // Filing date
        register_meta('post', '_eakc_filing_date', array(
            'type' => 'string',
            'description' => 'Date docket was filed',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'docket',
            'sanitize_callback' => array($this, 'sanitize_date')
        ));

        // Proceeding type
        register_meta('post', '_eakc_proceeding_type', array(
            'type' => 'string',
            'description' => 'Type of legal proceeding',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'docket',
            'sanitize_callback' => 'sanitize_text_field'
        ));

        // Document categories (stored as JSON)
        register_meta('post', '_eakc_document_categories', array(
            'type' => 'string',
            'description' => 'Document categories and files as JSON',
            'single' => true,
            'show_in_rest' => false,
            'object_subtype' => 'docket',
            'sanitize_callback' => array($this, 'sanitize_document_categories_json')
        ));

        // Related dockets
        register_meta('post', '_eakc_related_dockets', array(
            'type' => 'string',
            'description' => 'Comma-separated list of related docket IDs',
            'single' => true,
            'show_in_rest' => true,
            'object_subtype' => 'docket',
            'sanitize_callback' => array($this, 'sanitize_comma_separated_ids')
        ));
    }

    /**
     * Sanitization callbacks
     */

    /**
     * Sanitize difficulty level
     */
    public function sanitize_difficulty_level($value) {
        $allowed_values = array('beginner', 'intermediate', 'advanced');
        return in_array($value, $allowed_values) ? $value : 'beginner';
    }

    /**
     * Sanitize docket status
     */
    public function sanitize_docket_status($value) {
        $allowed_statuses = array('active', 'closed', 'pending', 'on-hold', 'appealed');
        return in_array($value, $allowed_statuses) ? $value : 'pending';
    }

    /**
     * Sanitize date field
     */
    public function sanitize_date($value) {
        if (empty($value)) {
            return '';
        }
        
        // Try to parse and reformat date
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return '';
        }
        
        return date('Y-m-d', $timestamp);
    }

    /**
     * Sanitize resources JSON
     */
    public function sanitize_resources_json($value) {
        if (empty($value)) {
            return '';
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        // Sanitize each resource
        $sanitized_resources = array();
        if (is_array($decoded)) {
            foreach ($decoded as $resource) {
                if (is_array($resource)) {
                    $sanitized_resource = array(
                        'title' => isset($resource['title']) ? sanitize_text_field($resource['title']) : '',
                        'type' => isset($resource['type']) ? $this->sanitize_resource_type($resource['type']) : 'external',
                        'url' => isset($resource['url']) ? esc_url_raw($resource['url']) : '',
                        'description' => isset($resource['description']) ? sanitize_textarea_field($resource['description']) : '',
                        'embed_preference' => isset($resource['embed_preference']) ? ($resource['embed_preference'] ? 'embed' : 'link') : 'link',
                        'file_size' => isset($resource['file_size']) ? sanitize_text_field($resource['file_size']) : '',
                        'download_count' => isset($resource['download_count']) ? absint($resource['download_count']) : 0
                    );
                    $sanitized_resources[] = $sanitized_resource;
                }
            }
        }

        return wp_json_encode($sanitized_resources);
    }

    /**
     * Sanitize document categories JSON
     */
    public function sanitize_document_categories_json($value) {
        if (empty($value)) {
            return '';
        }

        $decoded = json_decode($value, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        // Sanitize each category
        $sanitized_categories = array();
        if (is_array($decoded)) {
            foreach ($decoded as $category) {
                if (is_array($category)) {
                    $sanitized_documents = array();
                    if (isset($category['documents']) && is_array($category['documents'])) {
                        foreach ($category['documents'] as $document) {
                            if (is_array($document)) {
                                $sanitized_documents[] = array(
                                    'title' => isset($document['title']) ? sanitize_text_field($document['title']) : '',
                                    'url' => isset($document['url']) ? esc_url_raw($document['url']) : '',
                                    'type' => isset($document['type']) ? $this->sanitize_resource_type($document['type']) : 'pdf',
                                    'date' => isset($document['date']) ? $this->sanitize_date($document['date']) : '',
                                    'file_size' => isset($document['file_size']) ? sanitize_text_field($document['file_size']) : ''
                                );
                            }
                        }
                    }

                    $sanitized_categories[] = array(
                        'name' => isset($category['name']) ? sanitize_text_field($category['name']) : '',
                        'description' => isset($category['description']) ? sanitize_textarea_field($category['description']) : '',
                        'documents' => $sanitized_documents,
                        'order' => isset($category['order']) ? absint($category['order']) : 0
                    );
                }
            }
        }

        return wp_json_encode($sanitized_categories);
    }

    /**
     * Sanitize resource type
     */
    private function sanitize_resource_type($value) {
        $allowed_types = array(
            'pdf', 'doc', 'docx', 'sheet', 'xlsx', 'csv',
            'presentation', 'ppt', 'pptx', 'video', 'audio',
            'image', 'external', 'google-doc', 'google-sheet',
            'google-slides', 'youtube', 'vimeo'
        );
        
        return in_array($value, $allowed_types) ? $value : 'external';
    }

    /**
     * Sanitize comma-separated IDs
     */
    public function sanitize_comma_separated_ids($value) {
        if (empty($value)) {
            return '';
        }

        $ids = explode(',', $value);
        $sanitized_ids = array();
        
        foreach ($ids as $id) {
            $clean_id = absint(trim($id));
            if ($clean_id > 0) {
                $sanitized_ids[] = $clean_id;
            }
        }

        return implode(',', $sanitized_ids);
    }

    /**
     * Helper functions for getting meta values
     */

    /**
     * Get article resources
     */
    public function get_article_resources($post_id) {
        $resources_json = get_post_meta($post_id, '_eakc_resources', true);
        
        if (empty($resources_json)) {
            return array();
        }

        $resources = json_decode($resources_json, true);
        return is_array($resources) ? $resources : array();
    }

    /**
     * Get docket document categories
     */
    public function get_docket_categories($post_id) {
        $categories_json = get_post_meta($post_id, '_eakc_document_categories', true);
        
        if (empty($categories_json)) {
            return array();
        }

        $categories = json_decode($categories_json, true);
        return is_array($categories) ? $categories : array();
    }

    /**
     * Auto-calculate read time based on content
     */
    public function calculate_read_time($content) {
        $word_count = str_word_count(strip_tags($content));
        $words_per_minute = 200; // Average reading speed
        
        $read_time = ceil($word_count / $words_per_minute);
        return max(1, $read_time); // Minimum 1 minute
    }

    /**
     * Get difficulty level display name
     */
    public function get_difficulty_display_name($level) {
        $names = array(
            'beginner' => __('Beginner', 'energy-alabama-kc'),
            'intermediate' => __('Intermediate', 'energy-alabama-kc'),
            'advanced' => __('Advanced', 'energy-alabama-kc')
        );

        return isset($names[$level]) ? $names[$level] : $names['beginner'];
    }

    /**
     * Get resource type display name
     */
    public function get_resource_type_display_name($type) {
        $names = array(
            'pdf' => __('PDF Document', 'energy-alabama-kc'),
            'doc' => __('Word Document', 'energy-alabama-kc'),
            'docx' => __('Word Document', 'energy-alabama-kc'),
            'sheet' => __('Spreadsheet', 'energy-alabama-kc'),
            'xlsx' => __('Excel Spreadsheet', 'energy-alabama-kc'),
            'csv' => __('CSV File', 'energy-alabama-kc'),
            'presentation' => __('Presentation', 'energy-alabama-kc'),
            'ppt' => __('PowerPoint', 'energy-alabama-kc'),
            'pptx' => __('PowerPoint', 'energy-alabama-kc'),
            'video' => __('Video', 'energy-alabama-kc'),
            'audio' => __('Audio', 'energy-alabama-kc'),
            'image' => __('Image', 'energy-alabama-kc'),
            'external' => __('External Link', 'energy-alabama-kc'),
            'google-doc' => __('Google Document', 'energy-alabama-kc'),
            'google-sheet' => __('Google Sheet', 'energy-alabama-kc'),
            'google-slides' => __('Google Slides', 'energy-alabama-kc'),
            'youtube' => __('YouTube Video', 'energy-alabama-kc'),
            'vimeo' => __('Vimeo Video', 'energy-alabama-kc')
        );

        return isset($names[$type]) ? $names[$type] : $names['external'];
    }
}