<?php
/**
 * Template for single KC Article
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
$difficulty = get_post_meta(get_the_ID(), '_eakc_difficulty_level', true);
$read_time = get_post_meta(get_the_ID(), '_eakc_read_time', true);
$resources = $meta_fields->get_article_resources(get_the_ID());
$spanish_available = get_post_meta(get_the_ID(), '_eakc_spanish_available', true);
$spanish_post_id = get_post_meta(get_the_ID(), '_eakc_spanish_post_id', true);

// Helper function for resource icons
function eakc_get_resource_icon($type) {
    $icons = array(
        'pdf' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
        'doc' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
        'sheet' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"/></svg>',
        'video' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8,5.14V19.14L19,12.14L8,5.14Z"/></svg>',
        'external' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z"/></svg>'
    );
    
    return isset($icons[$type]) ? $icons[$type] : $icons['external'];
}
?>

<div class="eakc-single-article">
    
    <?php while (have_posts()) : the_post(); ?>

        <!-- Hero Section - EXACT COPY from main Knowledge Center page -->
        <section class="eakc-hero">
            <div class="eakc-container">
                <div class="eakc-hero-content">
                    <h1 class="eakc-hero-title"><?php the_title(); ?></h1>
                    
                    <?php if (has_excerpt()): ?>
                        <p class="eakc-hero-description">
                            <?php the_excerpt(); ?>
                        </p>
                    <?php endif; ?>
                    
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

        <!-- Article Meta Information (below hero) -->
        <section class="eakc-article-meta-section">
            <div class="eakc-container">
                <!-- Pills Section -->
                <div class="eakc-article-meta">
                    <?php
                    $categories = get_the_terms(get_the_ID(), 'kc_category');
                    if ($categories && !is_wp_error($categories)):
                    ?>
                        <span class="eakc-article-category">
                            <a href="<?php echo esc_url(get_term_link($categories[0])); ?>">
                                <?php echo esc_html($categories[0]->name); ?>
                            </a>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($difficulty): ?>
                        <span class="eakc-article-difficulty eakc-difficulty-<?php echo esc_attr($difficulty); ?>">
                            <?php echo esc_html(ucfirst($difficulty)); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($read_time): ?>
                        <span class="eakc-article-read-time">
                            <?php printf(__('%d min read', 'energy-alabama-kc'), $read_time); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="eakc-article-details">
                    <?php
                    // Get author information
                    $author_id = get_the_author_meta('ID');
                    $author_name = get_the_author_meta('display_name');
                    $author_avatar = get_avatar_url($author_id, array('size' => 40));
                    ?>
                    
                    <div class="eakc-author-info">
                        <img src="<?php echo esc_url($author_avatar); ?>" alt="<?php echo esc_attr($author_name); ?>" class="eakc-author-avatar">
                        <span class="eakc-author-name"><?php echo esc_html($author_name); ?></span>
                    </div>
                    
                    <span class="eakc-article-date">
                        <?php echo get_the_date('M j, Y'); ?>
                    </span>
                    
                    <?php if ($spanish_available && $spanish_post_id): ?>
                        <a href="<?php echo esc_url(get_permalink($spanish_post_id)); ?>" class="eakc-spanish-link">
                            <?php _e('Ver en español', 'energy-alabama-kc'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Article Content -->
        <section class="eakc-article-content">
            <div class="eakc-container">
                <div class="eakc-main-content">
                    <?php the_content(); ?>
                </div>
                
                <!-- Resources Section -->
                <?php if (!empty($resources)): ?>
                    <div class="eakc-resources-section">
                        <h3><?php _e('Resources & Downloads', 'energy-alabama-kc'); ?></h3>
                        
                        <div class="eakc-resources-list">
                            <?php foreach ($resources as $resource): ?>
                                <div class="eakc-resource-item">
                                    <div class="eakc-resource-icon">
                                        <?php echo eakc_get_resource_icon($resource['type']); ?>
                                    </div>
                                    
                                    <div class="eakc-resource-content">
                                        <h4 class="eakc-resource-title">
                                            <a href="<?php echo esc_url($resource['url']); ?>" target="_blank" rel="noopener">
                                                <?php echo esc_html($resource['title']); ?>
                                            </a>
                                        </h4>
                                        
                                        <?php if (!empty($resource['description'])): ?>
                                            <p class="eakc-resource-description">
                                                <?php echo esc_html($resource['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <span class="eakc-resource-type">
                                            <?php echo esc_html($meta_fields->get_resource_type_display_name($resource['type'])); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="eakc-resource-actions">
                                        <a href="<?php echo esc_url($resource['url']); ?>" target="_blank" class="eakc-resource-download">
                                            <?php _e('Download', 'energy-alabama-kc'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Tags -->
        <?php if (has_term('', 'kc_tag')): ?>
            <section class="eakc-article-tags">
                <div class="eakc-container">
                    <h3><?php _e('Tags', 'energy-alabama-kc'); ?></h3>
                    <div class="eakc-tags-list">
                        <?php
                        $tags = get_the_terms(get_the_ID(), 'kc_tag');
                        if ($tags && !is_wp_error($tags)):
                            foreach ($tags as $tag):
                        ?>
                            <a href="<?php echo esc_url(get_term_link($tag)); ?>" class="eakc-tag">
                                <?php echo esc_html($tag->name); ?>
                            </a>
                        <?php 
                            endforeach;
                        endif; 
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

    <?php endwhile; ?>
    
</div>

<?php get_footer(); ?>