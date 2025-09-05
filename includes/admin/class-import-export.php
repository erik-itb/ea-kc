<?php
/**
 * Import/Export functionality for Energy Alabama KC
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Energy_Alabama_KC_Import_Export {

    /**
     * Initialize the class
     */
    public function __construct() {
        add_action('admin_init', array($this, 'handle_export'));
        add_action('admin_init', array($this, 'handle_import'));
        add_action('wp_ajax_eakc_process_import', array($this, 'ajax_process_import'));
    }

    /**
     * Handle export requests
     */
    public function handle_export() {
        if (!isset($_POST['eakc_export']) || !isset($_POST['export_type'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['eakc_export_nonce'], 'eakc_export_action')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to export data');
        }

        $export_type = sanitize_text_field($_POST['export_type']);
        
        switch ($export_type) {
            case 'articles':
                $this->export_articles();
                break;
            case 'dockets':
                $this->export_dockets();
                break;
            case 'all':
                $this->export_all();
                break;
        }
    }

    /**
     * Export articles to CSV
     */
    private function export_articles() {
        $filename = 'kc-articles-' . date('Y-m-d-His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Title',
            'Content',
            'Excerpt',
            'Status',
            'Author',
            'Date',
            'Categories',
            'Tags',
            'Featured Icon',
            'Icon Color',
            'Read Time',
            'Is Spanish',
            'Spanish Link ID',
            'Resources JSON',
            'Featured Image URL'
        ));

        // Get articles
        $args = array(
            'post_type' => 'kc_article',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft', 'pending', 'private')
        );
        
        $articles = get_posts($args);
        
        foreach ($articles as $article) {
            $categories = wp_get_post_terms($article->ID, 'kc_category', array('fields' => 'names'));
            $tags = wp_get_post_terms($article->ID, 'kc_tag', array('fields' => 'names'));
            $resources = get_post_meta($article->ID, '_eakc_resources', true);
            $featured_image = get_the_post_thumbnail_url($article->ID, 'full');
            
            $row = array(
                $article->ID,
                $article->post_title,
                $article->post_content,
                $article->post_excerpt,
                $article->post_status,
                get_the_author_meta('user_login', $article->post_author),
                $article->post_date,
                implode('|', $categories),
                implode('|', $tags),
                get_post_meta($article->ID, '_eakc_featured_icon', true),
                get_post_meta($article->ID, '_eakc_icon_color', true),
                get_post_meta($article->ID, '_eakc_read_time', true),
                get_post_meta($article->ID, '_eakc_is_spanish_content', true),
                get_post_meta($article->ID, '_eakc_spanish_post_id', true),
                $resources ? json_encode($resources) : '',
                $featured_image ?: ''
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export dockets to CSV
     */
    private function export_dockets() {
        $filename = 'kc-dockets-' . date('Y-m-d-His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Title',
            'Content',
            'Status',
            'Author',
            'Date',
            'Docket Number',
            'Docket Status',
            'Jurisdictions',
            'Documents JSON'
        ));

        // Get dockets
        $args = array(
            'post_type' => 'docket',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft', 'pending', 'private')
        );
        
        $dockets = get_posts($args);
        
        foreach ($dockets as $docket) {
            $jurisdictions = wp_get_post_terms($docket->ID, 'docket_jurisdiction', array('fields' => 'names'));
            $documents = get_post_meta($docket->ID, '_eakc_docket_documents', true);
            
            $row = array(
                $docket->ID,
                $docket->post_title,
                $docket->post_content,
                $docket->post_status,
                get_the_author_meta('user_login', $docket->post_author),
                $docket->post_date,
                get_post_meta($docket->ID, '_eakc_docket_number', true),
                get_post_meta($docket->ID, '_eakc_docket_status', true),
                implode('|', $jurisdictions),
                $documents ? json_encode($documents) : ''
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export all content
     */
    private function export_all() {
        // Create a ZIP file with both exports
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/eakc-exports';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $zip_filename = 'kc-export-all-' . date('Y-m-d-His') . '.zip';
        $zip_path = $export_dir . '/' . $zip_filename;
        
        $zip = new ZipArchive();
        if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
            wp_die('Cannot create export file');
        }
        
        // Export articles to temp file
        ob_start();
        $this->export_articles_to_stream();
        $articles_csv = ob_get_clean();
        $zip->addFromString('articles.csv', $articles_csv);
        
        // Export dockets to temp file
        ob_start();
        $this->export_dockets_to_stream();
        $dockets_csv = ob_get_clean();
        $zip->addFromString('dockets.csv', $dockets_csv);
        
        $zip->close();
        
        // Send file to browser
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($zip_path));
        readfile($zip_path);
        
        // Clean up
        unlink($zip_path);
        exit;
    }

    /**
     * Handle import uploads
     */
    public function handle_import() {
        if (!isset($_POST['eakc_import']) || !isset($_FILES['import_file'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['eakc_import_nonce'], 'eakc_import_action')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to import data');
        }

        $uploaded_file = $_FILES['import_file'];
        
        // SECURITY FIX: Enhanced file upload validation with MIME type checking
        if ($uploaded_file['error'] !== UPLOAD_ERR_OK) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>File upload failed.</p></div>';
            });
            return;
        }

        // Validate file extension
        $file_type = wp_check_filetype($uploaded_file['name']);
        if ($file_type['ext'] !== 'csv') {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>Please upload a CSV file only.</p></div>';
            });
            return;
        }

        // SECURITY FIX: Validate MIME type beyond just file extension
        $allowed_mime_types = array('text/csv', 'application/csv');
        $file_mime_type = mime_content_type($uploaded_file['tmp_name']);
        
        if (!in_array($file_mime_type, $allowed_mime_types, true)) {
            add_action('admin_notices', function() use ($file_mime_type) {
                echo '<div class="notice notice-error"><p>Invalid file type. Expected CSV file, got: ' . esc_html($file_mime_type) . '</p></div>';
            });
            return;
        }

        // SECURITY FIX: Additional MIME type validation using WordPress function
        $wp_filetype = wp_check_filetype_and_ext($uploaded_file['tmp_name'], $uploaded_file['name']);
        if (!$wp_filetype['type'] || !in_array($wp_filetype['type'], $allowed_mime_types, true)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>File validation failed. Please ensure you are uploading a valid CSV file.</p></div>';
            });
            return;
        }

        // Store file temporarily in WordPress temp directory (more secure)
        $upload = wp_handle_upload($uploaded_file, array(
            'test_form' => false,
            'mimes' => array(
                'csv' => 'text/csv',
            )
        ));
        
        if (isset($upload['error'])) {
            add_action('admin_notices', function() use ($upload) {
                echo '<div class="notice notice-error"><p>' . esc_html($upload['error']) . '</p></div>';
            });
            return;
        }

        // Process the import
        $import_type = sanitize_text_field($_POST['import_type']);
        $result = $this->process_import($upload['file'], $import_type);
        
        // Clean up
        @unlink($upload['file']);
        
        if ($result['success']) {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
            });
        }
    }

    /**
     * Process import file
     */
    private function process_import($file_path, $import_type) {
        $file = fopen($file_path, 'r');
        if (!$file) {
            return array('success' => false, 'message' => 'Could not open file');
        }

        $headers = fgetcsv($file);
        if (!$headers) {
            fclose($file);
            return array('success' => false, 'message' => 'Invalid CSV file');
        }

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if ($import_type === 'articles') {
                $result = $this->import_article_row($headers, $row);
            } else {
                $result = $this->import_docket_row($headers, $row);
            }

            if ($result === 'imported') {
                $imported++;
            } elseif ($result === 'skipped') {
                $skipped++;
            } else {
                $errors++;
            }
        }

        fclose($file);

        $message = sprintf(
            'Import complete: %d imported, %d skipped, %d errors',
            $imported,
            $skipped,
            $errors
        );

        return array(
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        );
    }

    /**
     * Import a single article row
     */
    private function import_article_row($headers, $row) {
        $data = array_combine($headers, $row);
        
        // Check if article exists (by title)
        $existing = get_page_by_title($data['Title'], OBJECT, 'kc_article');
        if ($existing) {
            return 'skipped';
        }

        // Create article
        $post_data = array(
            'post_title' => $data['Title'],
            'post_content' => $data['Content'],
            'post_excerpt' => $data['Excerpt'],
            'post_status' => $data['Status'] ?: 'draft',
            'post_type' => 'kc_article',
            'post_author' => get_current_user_id()
        );

        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return 'error';
        }

        // Set categories
        if (!empty($data['Categories'])) {
            $categories = explode('|', $data['Categories']);
            $term_ids = array();
            foreach ($categories as $cat_name) {
                $term = term_exists($cat_name, 'kc_category');
                if (!$term) {
                    $term = wp_insert_term($cat_name, 'kc_category');
                }
                if (!is_wp_error($term)) {
                    $term_ids[] = intval($term['term_id']);
                }
            }
            wp_set_post_terms($post_id, $term_ids, 'kc_category');
        }

        // Set tags
        if (!empty($data['Tags'])) {
            $tags = explode('|', $data['Tags']);
            wp_set_post_terms($post_id, $tags, 'kc_tag');
        }

        // Set meta fields
        if (!empty($data['Featured Icon'])) {
            update_post_meta($post_id, '_eakc_featured_icon', $data['Featured Icon']);
        }
        if (!empty($data['Icon Color'])) {
            update_post_meta($post_id, '_eakc_icon_color', $data['Icon Color']);
        }
        if (!empty($data['Read Time'])) {
            update_post_meta($post_id, '_eakc_read_time', $data['Read Time']);
        }
        if (!empty($data['Is Spanish'])) {
            update_post_meta($post_id, '_eakc_is_spanish_content', $data['Is Spanish']);
        }
        if (!empty($data['Spanish Link ID'])) {
            update_post_meta($post_id, '_eakc_spanish_post_id', $data['Spanish Link ID']);
        }
        if (!empty($data['Resources JSON'])) {
            $resources = json_decode($data['Resources JSON'], true);
            if ($resources) {
                update_post_meta($post_id, '_eakc_resources', $resources);
            }
        }

        return 'imported';
    }

    /**
     * Import a single docket row
     */
    private function import_docket_row($headers, $row) {
        $data = array_combine($headers, $row);
        
        // Check if docket exists (by title)
        $existing = get_page_by_title($data['Title'], OBJECT, 'docket');
        if ($existing) {
            return 'skipped';
        }

        // Create docket
        $post_data = array(
            'post_title' => $data['Title'],
            'post_content' => $data['Content'],
            'post_status' => $data['Status'] ?: 'draft',
            'post_type' => 'docket',
            'post_author' => get_current_user_id()
        );

        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return 'error';
        }

        // Set jurisdictions
        if (!empty($data['Jurisdictions'])) {
            $jurisdictions = explode('|', $data['Jurisdictions']);
            wp_set_post_terms($post_id, $jurisdictions, 'docket_jurisdiction');
        }

        // Set meta fields
        if (!empty($data['Docket Number'])) {
            update_post_meta($post_id, '_eakc_docket_number', $data['Docket Number']);
        }
        if (!empty($data['Docket Status'])) {
            update_post_meta($post_id, '_eakc_docket_status', $data['Docket Status']);
        }
        if (!empty($data['Documents JSON'])) {
            $documents = json_decode($data['Documents JSON'], true);
            if ($documents) {
                update_post_meta($post_id, '_eakc_docket_documents', $documents);
            }
        }

        return 'imported';
    }

    /**
     * Export articles to stream (for ZIP)
     */
    private function export_articles_to_stream() {
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Title',
            'Content',
            'Excerpt',
            'Status',
            'Author',
            'Date',
            'Categories',
            'Tags',
            'Featured Icon',
            'Icon Color',
            'Read Time',
            'Is Spanish',
            'Spanish Link ID',
            'Resources JSON',
            'Featured Image URL'
        ));

        // Get articles
        $args = array(
            'post_type' => 'kc_article',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft', 'pending', 'private')
        );
        
        $articles = get_posts($args);
        
        foreach ($articles as $article) {
            $categories = wp_get_post_terms($article->ID, 'kc_category', array('fields' => 'names'));
            $tags = wp_get_post_terms($article->ID, 'kc_tag', array('fields' => 'names'));
            $resources = get_post_meta($article->ID, '_eakc_resources', true);
            $featured_image = get_the_post_thumbnail_url($article->ID, 'full');
            
            $row = array(
                $article->ID,
                $article->post_title,
                $article->post_content,
                $article->post_excerpt,
                $article->post_status,
                get_the_author_meta('user_login', $article->post_author),
                $article->post_date,
                implode('|', $categories),
                implode('|', $tags),
                get_post_meta($article->ID, '_eakc_featured_icon', true),
                get_post_meta($article->ID, '_eakc_icon_color', true),
                get_post_meta($article->ID, '_eakc_read_time', true),
                get_post_meta($article->ID, '_eakc_is_spanish_content', true),
                get_post_meta($article->ID, '_eakc_spanish_post_id', true),
                $resources ? json_encode($resources) : '',
                $featured_image ?: ''
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
    }

    /**
     * Export dockets to stream (for ZIP)
     */
    private function export_dockets_to_stream() {
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Title',
            'Content',
            'Status',
            'Author',
            'Date',
            'Docket Number',
            'Docket Status',
            'Jurisdictions',
            'Documents JSON'
        ));

        // Get dockets
        $args = array(
            'post_type' => 'docket',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft', 'pending', 'private')
        );
        
        $dockets = get_posts($args);
        
        foreach ($dockets as $docket) {
            $jurisdictions = wp_get_post_terms($docket->ID, 'docket_jurisdiction', array('fields' => 'names'));
            $documents = get_post_meta($docket->ID, '_eakc_docket_documents', true);
            
            $row = array(
                $docket->ID,
                $docket->post_title,
                $docket->post_content,
                $docket->post_status,
                get_the_author_meta('user_login', $docket->post_author),
                $docket->post_date,
                get_post_meta($docket->ID, '_eakc_docket_number', true),
                get_post_meta($docket->ID, '_eakc_docket_status', true),
                implode('|', $jurisdictions),
                $documents ? json_encode($documents) : ''
            );
            
            fputcsv($output, $row);
        }
        
        fclose($output);
    }
}

// Initialize the class
new Energy_Alabama_KC_Import_Export();