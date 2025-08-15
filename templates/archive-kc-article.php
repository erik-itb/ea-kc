<?php
/**
 * Template for KC Article Archive
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<div class="eakc-single-article">
    
    <!-- Hero Section -->
    <section class="eakc-hero">
        <div class="eakc-container">
            <div class="eakc-hero-content">
                <h1 class="eakc-hero-title">Energy Alabama<br>Knowledge Center</h1>
                <p class="eakc-hero-description">
                    <?php _e('Explore our comprehensive collection of articles about clean energy, regulations, and educational resources for Alabama communities.', 'energy-alabama-kc'); ?>
                </p>
                
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

    <!-- Archive Content -->
    <section class="eakc-category-content">
        <div class="eakc-container">
            
            <!-- Archive Stats -->
            <div class="eakc-category-stats" style="text-align: center; margin-bottom: 40px; color: #6b7280;">
                <?php
                $total_posts = wp_count_posts('kc_article');
                $published_count = $total_posts->publish;
                printf(
                    _n('%d article available', '%d articles available', $published_count, 'energy-alabama-kc'),
                    $published_count
                );
                ?>
            </div>
            
            <!-- Filters and Sort Controls -->
            <div class="eakc-category-filters">
                <div class="eakc-filter-controls">
                    <select class="eakc-language-filter" onchange="eakc_filterByLanguage(this.value)">
                        <option value=""><?php _e('All Languages', 'energy-alabama-kc'); ?></option>
                        <option value="english" selected><?php _e('English Only', 'energy-alabama-kc'); ?></option>
                        <option value="spanish"><?php _e('Spanish Only', 'energy-alabama-kc'); ?></option>
                    </select>
                    
                    <select class="eakc-difficulty-filter" onchange="eakc_filterByDifficulty(this.value)">
                        <option value=""><?php _e('All Difficulty Levels', 'energy-alabama-kc'); ?></option>
                        <option value="beginner"><?php _e('Beginner', 'energy-alabama-kc'); ?></option>
                        <option value="intermediate"><?php _e('Intermediate', 'energy-alabama-kc'); ?></option>
                        <option value="advanced"><?php _e('Advanced', 'energy-alabama-kc'); ?></option>
                    </select>
                    
                    <select class="eakc-sort-filter" onchange="eakc_sortArticles(this.value)">
                        <option value="date"><?php _e('Sort by Date', 'energy-alabama-kc'); ?></option>
                        <option value="title"><?php _e('Sort by Title', 'energy-alabama-kc'); ?></option>
                        <option value="difficulty"><?php _e('Sort by Difficulty', 'energy-alabama-kc'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="eakc-articles-grid" id="eakc-articles-container">
                <?php if (have_posts()) : ?>
                    
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        // Get article meta
                        $difficulty = get_post_meta(get_the_ID(), '_eakc_difficulty_level', true);
                        $read_time = get_post_meta(get_the_ID(), '_eakc_read_time', true);
                        $featured_icon = get_post_meta(get_the_ID(), '_eakc_featured_icon', true);
                        $icon_color = get_post_meta(get_the_ID(), '_eakc_icon_color', true) ?: '#ffffff';
                        $is_spanish_content = get_post_meta(get_the_ID(), '_eakc_is_spanish_content', true);
                        
                        // Get categories for this article
                        $categories = get_the_terms(get_the_ID(), 'kc_category');
                        
                        // Determine language for filtering
                        $language = $is_spanish_content ? 'spanish' : 'english';
                        ?>
                        
                        <article class="eakc-article-card" data-difficulty="<?php echo esc_attr($difficulty); ?>" data-language="<?php echo esc_attr($language); ?>">
                            <div class="eakc-card-header">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="eakc-card-image">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                        </a>
                                    </div>
                                <?php elseif ($featured_icon): ?>
                                    <div class="eakc-card-icon">
                                        <i class="<?php echo esc_attr($featured_icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="eakc-card-icon">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="eakc-card-meta">
                                    <?php if ($difficulty): ?>
                                        <span class="eakc-difficulty-badge eakc-difficulty-<?php echo esc_attr($difficulty); ?>">
                                            <?php echo esc_html(ucfirst($difficulty)); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($read_time): ?>
                                        <span class="eakc-read-time">
                                            <?php printf(__('%d min read', 'energy-alabama-kc'), $read_time); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="eakc-card-content">
                                <h3 class="eakc-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <?php if ($categories && !is_wp_error($categories)): ?>
                                    <div class="eakc-card-category">
                                        <a href="<?php echo esc_url(get_term_link($categories[0])); ?>" class="eakc-category-link">
                                            <?php echo esc_html($categories[0]->name); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="eakc-card-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </div>
                                
                                <div class="eakc-card-footer">
                                    <time class="eakc-card-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php echo get_the_date('M j, Y'); ?>
                                    </time>
                                    
                                    <a href="<?php the_permalink(); ?>" class="eakc-read-more">
                                        <?php _e('Read More', 'energy-alabama-kc'); ?>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="7" y1="17" x2="17" y2="7"/>
                                            <polyline points="7,7 17,7 17,17"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </article>
                        
                    <?php endwhile; ?>
                    
                <?php else : ?>
                    
                    <div class="eakc-no-articles">
                        <div class="eakc-no-articles-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>
                        <h3><?php _e('No articles found', 'energy-alabama-kc'); ?></h3>
                        <p><?php _e('There are currently no articles available in the knowledge center.', 'energy-alabama-kc'); ?></p>
                        <a href="<?php echo esc_url(home_url('/knowledge-center/')); ?>" class="eakc-back-link">
                            <?php _e('← Back to Knowledge Center', 'energy-alabama-kc'); ?>
                        </a>
                    </div>
                    
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($wp_query->max_num_pages > 1) : ?>
                <div class="eakc-pagination">
                    <?php
                    echo paginate_links(array(
                        'prev_text' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15,18 9,12 15,6"/></svg> ' . __('Previous', 'energy-alabama-kc'),
                        'next_text' => __('Next', 'energy-alabama-kc') . ' <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9,18 15,12 9,6"/></svg>',
                        'mid_size' => 2,
                        'end_size' => 1
                    ));
                    ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!-- Browse by Categories -->
    <section class="eakc-related-categories">
        <div class="eakc-container">
            <h3><?php _e('Browse by Category', 'energy-alabama-kc'); ?></h3>
            
            <div class="eakc-categories-grid">
                <?php
                // Get category icon and color functions
                function eakc_get_archive_category_icon($slug) {
                    $icons = array(
                        'clean-energy-101' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>',
                        'educator-resources' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>',
                        'legal-regulatory' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>',
                        'presentation-library' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
                        'faqs' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>'
                    );
                    
                    return isset($icons[$slug]) ? $icons[$slug] : '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
                }

                function eakc_get_archive_category_color($slug) {
                    $colors = array(
                        'clean-energy-101' => '#3b82f6',
                        'educator-resources' => '#10b981', 
                        'legal-regulatory' => '#6366f1',
                        'presentation-library' => '#8b5cf6',
                        'faqs' => '#f59e0b'
                    );
                    
                    return isset($colors[$slug]) ? $colors[$slug] : '#6b7280';
                }

                $all_categories = get_terms(array(
                    'taxonomy' => 'kc_category',
                    'hide_empty' => true
                ));
                
                if ($all_categories && !is_wp_error($all_categories)) :
                    foreach ($all_categories as $category) :
                        $cat_color = eakc_get_archive_category_color($category->slug);
                        $cat_count = $category->count;
                ?>
                    <div class="eakc-category-card" style="--category-color: <?php echo esc_attr($cat_color); ?>;">
                        <a href="<?php echo esc_url(get_term_link($category)); ?>">
                            <div class="eakc-category-card-icon">
                                <?php echo eakc_get_archive_category_icon($category->slug); ?>
                            </div>
                            <h4><?php echo esc_html($category->name); ?></h4>
                            <p><?php echo wp_trim_words($category->description, 15, '...'); ?></p>
                            <span class="eakc-category-count">
                                <?php printf(_n('%d article', '%d articles', $cat_count, 'energy-alabama-kc'), $cat_count); ?>
                            </span>
                        </a>
                    </div>
                <?php 
                    endforeach;
                endif; 
                ?>
            </div>
        </div>
    </section>

</div>

<script>
// Simple filtering functions for the archive page
function eakc_filterByLanguage(language) {
    const articles = document.querySelectorAll('.eakc-article-card');
    
    articles.forEach(function(article) {
        if (language === '' || article.getAttribute('data-language') === language) {
            article.style.display = 'block';
        } else {
            article.style.display = 'none';
        }
    });
}

function eakc_filterByDifficulty(difficulty) {
    const articles = document.querySelectorAll('.eakc-article-card');
    
    articles.forEach(function(article) {
        if (difficulty === '' || article.getAttribute('data-difficulty') === difficulty) {
            article.style.display = 'block';
        } else {
            article.style.display = 'none';
        }
    });
}

function eakc_sortArticles(sortBy) {
    const container = document.getElementById('eakc-articles-container');
    const articles = Array.from(container.querySelectorAll('.eakc-article-card'));
    
    articles.sort(function(a, b) {
        if (sortBy === 'title') {
            const titleA = a.querySelector('.eakc-card-title a').textContent.toLowerCase();
            const titleB = b.querySelector('.eakc-card-title a').textContent.toLowerCase();
            return titleA.localeCompare(titleB);
        } else if (sortBy === 'difficulty') {
            const difficultyOrder = { 'beginner': 1, 'intermediate': 2, 'advanced': 3 };
            const diffA = difficultyOrder[a.getAttribute('data-difficulty')] || 0;
            const diffB = difficultyOrder[b.getAttribute('data-difficulty')] || 0;
            return diffA - diffB;
        }
        // Default: sort by date (newest first)
        return 0; // Keep original order for date sorting
    });
    
    // Re-append sorted articles
    articles.forEach(function(article) {
        container.appendChild(article);
    });
}

// Initialize with English-only filter on page load
document.addEventListener('DOMContentLoaded', function() {
    eakc_filterByLanguage('english');
});
</script>

<style>
.eakc-card-category {
    margin-bottom: 0.5rem;
}

.eakc-category-link {
    display: inline-block;
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s;
}

.eakc-category-link:hover {
    background: #1e40af;
    color: white;
}
</style>

<?php get_footer(); ?>