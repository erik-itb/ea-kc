<?php
/**
 * Template for single Docket
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get meta fields instance
$meta_fields = Energy_Alabama_KC_Meta_Fields::get_instance();
$docket_number = get_post_meta(get_the_ID(), '_eakc_docket_number', true);
$docket_status = get_post_meta(get_the_ID(), '_eakc_docket_status', true);
$filing_date = get_post_meta(get_the_ID(), '_eakc_filing_date', true);
$proceeding_type = get_post_meta(get_the_ID(), '_eakc_proceeding_type', true);
$document_categories = $meta_fields->get_docket_categories(get_the_ID());
$related_dockets = get_post_meta(get_the_ID(), '_eakc_related_dockets', true);

// Helper function for docket status styling
function eakc_get_docket_status_class($status) {
    $classes = array(
        'active' => 'eakc-status-active',
        'closed' => 'eakc-status-closed',
        'pending' => 'eakc-status-pending',
        'on-hold' => 'eakc-status-on-hold',
        'appealed' => 'eakc-status-appealed'
    );
    
    return isset($classes[$status]) ? $classes[$status] : 'eakc-status-pending';
}

// Helper function for document icons
function eakc_get_document_icon($type) {
    $icons = array(
        'pdf' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
        'doc' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
        'external' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/></svg>'
    );
    
    return isset($icons[$type]) ? $icons[$type] : $icons['external'];
}
?>

<div class="eakc-single-docket">
    
    <?php while (have_posts()) : the_post(); ?>
        
    </div> <!-- Close any container before hero -->
        
        <!-- Hero Section - EXACT COPY from main Knowledge Center page -->
        <section class="eakc-hero">
            <div class="eakc-container">
                <div class="eakc-hero-content">
                    
                    <h1 class="eakc-hero-title">Energy Alabama<br>Knowledge Center</h1>
                    
                    <!-- Search Form -->
                    <div class="eakc-search-container">
                        <form class="eakc-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <div class="eakc-search-wrapper">
                                <input type="search" 
                                        class="eakc-search-input" 
                                        placeholder="<?php esc_attr_e('Search knowledge center...', 'energy-alabama-kc'); ?>"
                                        value="<?php echo get_search_query(); ?>" 
                                        name="s" 
                                        autocomplete="off"
                                        aria-label="<?php esc_attr_e('Search knowledge center', 'energy-alabama-kc'); ?>">
                                <input type="hidden" name="post_type" value="kc_article">
                                <button type="submit" class="eakc-search-button" aria-label="<?php esc_attr_e('Search', 'energy-alabama-kc'); ?>">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="m21 21-4.35-4.35"></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="eakc-search-results" style="display: none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Breadcrumbs -->
        <?php 
        $template_manager = Energy_Alabama_KC_Template_Manager::get_instance();
        echo $template_manager->render_breadcrumbs(); 
        ?>

        <!-- Docket Meta Information (below hero) -->
        <section class="eakc-docket-meta-section">
            <div class="eakc-container">
                <!-- Docket Info Pills -->
                <div class="eakc-docket-meta">
                    <?php if ($docket_number): ?>
                        <span class="eakc-docket-number">
                            Docket #<?php echo esc_html($docket_number); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($docket_status): ?>
                        <span class="eakc-docket-status <?php echo esc_attr(eakc_get_docket_status_class($docket_status)); ?>">
                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $docket_status))); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($proceeding_type): ?>
                        <span class="eakc-proceeding-type">
                            <?php echo esc_html($proceeding_type); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="eakc-docket-details">
                    <?php if ($filing_date): ?>
                        <span class="eakc-filing-date">
                            <strong><?php _e('Filed:', 'energy-alabama-kc'); ?></strong> 
                            <?php echo esc_html(date('M j, Y', strtotime($filing_date))); ?>
                        </span>
                    <?php endif; ?>
                    
                    <span class="eakc-filing-date">
                        <strong><?php _e('Last Updated:', 'energy-alabama-kc'); ?></strong> 
                        <?php echo get_the_modified_date('M j, Y'); ?>
                    </span>
                </div>
            </div>
        </section>
    
    <div class="eakc-container"> <!-- Reopen container for rest of content -->

        <!-- Docket Content -->
        <section class="eakc-docket-content">
            <div class="eakc-main-content">
                <?php the_content(); ?>
            </div>
            
            <!-- Document Categories Section -->
            <?php if (!empty($document_categories)): ?>
                <div class="eakc-documents-section">
                    <h3><?php _e('Documents & Filings', 'energy-alabama-kc'); ?></h3>
                    
                    <?php
                    // Sort categories by order field
                    usort($document_categories, function($a, $b) {
                        $order_a = isset($a['order']) ? (int)$a['order'] : 0;
                        $order_b = isset($b['order']) ? (int)$b['order'] : 0;
                        return $order_a - $order_b;
                    });
                    
                    foreach ($document_categories as $category_index => $category): 
                        if (!empty($category['documents']) && is_array($category['documents'])):
                    ?>
                        <div class="eakc-document-category">
                            <div class="eakc-category-header" data-accordion-target="category-<?php echo esc_attr($category_index); ?>">
                                <h4 class="eakc-category-title">
                                    <?php echo esc_html($category['name']); ?>
                                </h4>
                                <?php if (!empty($category['description'])): ?>
                                    <p class="eakc-category-description">
                                        <?php echo esc_html($category['description']); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Accordion Icon -->
                                <svg class="eakc-accordion-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6,9 12,15 18,9"></polyline>
                                </svg>
                            </div>
                            
                            <div class="eakc-accordion-content" id="category-<?php echo esc_attr($category_index); ?>">
                                <div class="eakc-documents-list">
                                    <?php foreach ($category['documents'] as $document): ?>
                                        <div class="eakc-document-item">
                                            <div class="eakc-document-icon">
                                                <?php echo eakc_get_document_icon($document['type']); ?>
                                            </div>
                                            
                                            <div class="eakc-document-content">
                                                <h5 class="eakc-document-title">
                                                    <a href="<?php echo esc_url($document['url']); ?>" target="_blank" rel="noopener">
                                                        <?php echo esc_html($document['title']); ?>
                                                    </a>
                                                </h5>
                                                
                                                <div class="eakc-document-meta">
                                                    <?php if (!empty($document['date'])): ?>
                                                        <span class="eakc-document-date">
                                                            <strong><?php _e('Date:', 'energy-alabama-kc'); ?></strong>
                                                            <?php echo esc_html(date('M j, Y', strtotime($document['date']))); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($document['file_size'])): ?>
                                                        <span class="eakc-document-size">
                                                            <strong><?php _e('Size:', 'energy-alabama-kc'); ?></strong>
                                                            <?php echo esc_html($document['file_size']); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    
                                                    <span class="eakc-document-type">
                                                        <?php echo esc_html($meta_fields->get_resource_type_display_name($document['type'])); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="eakc-document-actions">
                                                <a href="<?php echo esc_url($document['url']); ?>" target="_blank" class="eakc-document-download">
                                                    <?php _e('View', 'energy-alabama-kc'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Related Dockets -->
        <?php 
        if (!empty($related_dockets)):
            $related_ids = array_map('trim', explode(',', $related_dockets));
            $related_query = new WP_Query(array(
                'post_type' => 'docket',
                'post__in' => $related_ids,
                'posts_per_page' => -1,
                'post_status' => 'publish'
            ));
            
            if ($related_query->have_posts()):
        ?>
            <section class="eakc-related-dockets">
                <div class="eakc-container">
                    <h3><?php _e('Related Dockets', 'energy-alabama-kc'); ?></h3>
                    <div class="eakc-related-list">
                        <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                            <div class="eakc-related-item">
                                <a href="<?php the_permalink(); ?>">
                                    <h4><?php the_title(); ?></h4>
                                    <p><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
        <?php 
            endif;
            wp_reset_postdata();
        endif; 
        ?>

    </div> <!-- Close container -->

    <?php endwhile; ?>
    
</div>

<?php get_footer(); ?>