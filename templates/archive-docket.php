<?php
/**
 * Template for Docket Archive
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Helper function for docket status styling (same as single-docket.php)
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

// Helper function for docket icons
function eakc_get_docket_icon($proceeding_type) {
    $icons = array(
        'rate-case' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>',
        'certificate' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
        'investigation' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>',
        'complaint' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'rulemaking' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7.07 16.97 2.51-7.39 7.39-2.51L3 3z"/><path d="M13 13l6 6"/></svg>'
    );
    
    return isset($icons[$proceeding_type]) ? $icons[$proceeding_type] : '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
}
?>

<div class="eakc-docket-archive">
    
    <!-- Hero Section -->
    <section class="eakc-hero">
        <div class="eakc-container">
            <div class="eakc-hero-content">
                <!-- Back to Knowledge Center Button -->
                <div class="eakc-back-navigation">
                    <a href="<?php echo esc_url(home_url('/knowledge-center/')); ?>" class="eakc-back-button">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"/>
                        </svg>
                        <?php _e('Back to Knowledge Center', 'energy-alabama-kc'); ?>
                    </a>
                </div>
                
                <h1 class="eakc-hero-title">
                    <?php _e('Browse Dockets', 'energy-alabama-kc'); ?>
                </h1>
                <p class="eakc-hero-description">
                    <?php _e('Access legal and regulatory dockets, filings, and documents from Alabama energy proceedings.', 'energy-alabama-kc'); ?>
                </p>
                
                <!-- Search Form -->
                <div class="eakc-search-container">
                    <form class="eakc-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <div class="eakc-search-wrapper">
                            <input type="search" 
                                    class="eakc-search-input" 
                                    placeholder="<?php esc_attr_e('Search dockets...', 'energy-alabama-kc'); ?>"
                                    value="<?php echo get_search_query(); ?>" 
                                    name="s" 
                                    autocomplete="off"
                                    aria-label="<?php esc_attr_e('Search dockets', 'energy-alabama-kc'); ?>">
                            <input type="hidden" name="post_type" value="docket">
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

    <!-- Docket Content -->
    <section class="eakc-docket-content">
        <div class="eakc-container">
            
            <!-- Docket Stats -->
            <div class="eakc-docket-stats" style="text-align: center; margin-bottom: 40px; color: #6b7280;">
                <?php
                $total_posts = $wp_query->found_posts;
                printf(
                    _n('%d docket available', '%d dockets available', $total_posts, 'energy-alabama-kc'),
                    $total_posts
                );
                ?>
            </div>
            
            <!-- Filters and Sort Controls -->
            <div class="eakc-docket-filters">
                <div class="eakc-filter-controls">
                    <select class="eakc-status-filter" onchange="eakc_filterByStatus(this.value)">
                        <option value=""><?php _e('All Status Types', 'energy-alabama-kc'); ?></option>
                        <option value="active"><?php _e('Active', 'energy-alabama-kc'); ?></option>
                        <option value="pending"><?php _e('Pending', 'energy-alabama-kc'); ?></option>
                        <option value="closed"><?php _e('Closed', 'energy-alabama-kc'); ?></option>
                        <option value="on-hold"><?php _e('On Hold', 'energy-alabama-kc'); ?></option>
                        <option value="appealed"><?php _e('Appealed', 'energy-alabama-kc'); ?></option>
                    </select>
                    
                    <select class="eakc-jurisdiction-filter" onchange="eakc_filterByJurisdiction(this.value)">
                        <option value=""><?php _e('All Jurisdictions', 'energy-alabama-kc'); ?></option>
                        <?php
                        $jurisdictions = get_terms(array(
                            'taxonomy' => 'docket_jurisdiction',
                            'hide_empty' => true
                        ));
                        if ($jurisdictions && !is_wp_error($jurisdictions)) :
                            foreach ($jurisdictions as $jurisdiction) :
                        ?>
                            <option value="<?php echo esc_attr($jurisdiction->slug); ?>">
                                <?php echo esc_html($jurisdiction->name); ?>
                            </option>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </select>
                    
                    <select class="eakc-sort-filter" onchange="eakc_sortDockets(this.value)">
                        <option value="date"><?php _e('Sort by Date', 'energy-alabama-kc'); ?></option>
                        <option value="title"><?php _e('Sort by Title', 'energy-alabama-kc'); ?></option>
                        <option value="docket-number"><?php _e('Sort by Docket Number', 'energy-alabama-kc'); ?></option>
                        <option value="status"><?php _e('Sort by Status', 'energy-alabama-kc'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Dockets Grid -->
            <div class="eakc-articles-grid" id="eakc-dockets-container">
                <?php if (have_posts()) : ?>
                    
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        // Get docket meta
                        $docket_number = get_post_meta(get_the_ID(), '_eakc_docket_number', true);
                        $docket_status = get_post_meta(get_the_ID(), '_eakc_docket_status', true);
                        $filing_date = get_post_meta(get_the_ID(), '_eakc_filing_date', true);
                        $proceeding_type = get_post_meta(get_the_ID(), '_eakc_proceeding_type', true);
                        $featured_icon = get_post_meta(get_the_ID(), '_eakc_featured_icon', true);
                        $icon_color = get_post_meta(get_the_ID(), '_eakc_icon_color', true) ?: '#ffffff';
                        
                        // Get jurisdictions
                        $jurisdictions = get_the_terms(get_the_ID(), 'docket_jurisdiction');
                        $jurisdiction_names = array();
                        if ($jurisdictions && !is_wp_error($jurisdictions)) {
                            foreach ($jurisdictions as $jurisdiction) {
                                $jurisdiction_names[] = $jurisdiction->name;
                            }
                        }
                        ?>
                        
                        <article class="eakc-article-card" 
                                 data-status="<?php echo esc_attr($docket_status); ?>"
                                 data-jurisdiction="<?php echo esc_attr($jurisdictions && !is_wp_error($jurisdictions) ? $jurisdictions[0]->slug : ''); ?>"
                                 data-docket-number="<?php echo esc_attr($docket_number); ?>">
                            
                            <div class="eakc-card-header">
                                <?php if (has_post_thumbnail()): ?>
                                    <div class="eakc-card-image">
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('medium', array('loading' => 'lazy')); ?>
                                        </a>
                                    </div>
                                <?php elseif ($featured_icon): ?>
                                    <a href="<?php the_permalink(); ?>" class="eakc-card-icon">
                                        <i class="<?php echo esc_attr($featured_icon); ?>" style="color: <?php echo esc_attr($icon_color); ?>;"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="<?php the_permalink(); ?>" class="eakc-card-icon">
                                        <?php echo eakc_get_docket_icon($proceeding_type); ?>
                                    </a>
                                <?php endif; ?>
                                
                                <div class="eakc-card-meta">
                                    <?php if ($docket_number): ?>
                                        <span class="eakc-docket-number">
                                            <?php printf(__('Docket #%s', 'energy-alabama-kc'), esc_html($docket_number)); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($docket_status): ?>
                                        <span class="eakc-docket-status <?php echo esc_attr(eakc_get_docket_status_class($docket_status)); ?>">
                                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $docket_status))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="eakc-card-content">
                                <h3 class="eakc-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="eakc-card-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </div>
                                
                                <div class="eakc-card-footer">
                                    <?php if ($filing_date): ?>
                                        <time class="eakc-filing-date" datetime="<?php echo esc_attr($filing_date); ?>">
                                            <?php echo esc_html(date('M j, Y', strtotime($filing_date))); ?>
                                        </time>
                                    <?php else: ?>
                                        <time class="eakc-card-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                            <?php echo get_the_date('M j, Y'); ?>
                                        </time>
                                    <?php endif; ?>
                                    
                                    <a href="<?php the_permalink(); ?>" class="eakc-read-more">
                                        <?php _e('View Docket', 'energy-alabama-kc'); ?>
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
                    
                    <div class="eakc-no-dockets">
                        <div class="eakc-no-dockets-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                        </div>
                        <h3><?php _e('No dockets found', 'energy-alabama-kc'); ?></h3>
                        <p><?php _e('There are currently no dockets available.', 'energy-alabama-kc'); ?></p>
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

    <!-- Related Categories -->
    <section class="eakc-related-categories">
        <div class="eakc-container">
            <h3><?php _e('Related Resources', 'energy-alabama-kc'); ?></h3>
            
            <div class="eakc-categories-grid">
                <?php
                $legal_category = get_term_by('slug', 'legal-regulatory', 'kc_category');
                $faqs_category = get_term_by('slug', 'faqs', 'kc_category');
                
                $related_resources = array(
                    array(
                        'name' => 'Legal & Regulatory Articles',
                        'url' => $legal_category ? get_term_link($legal_category) : '#',
                        'description' => 'Browse our collection of legal and regulatory information.',
                        'icon' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
                        'count' => $legal_category ? $legal_category->count : 0
                    ),
                    array(
                        'name' => 'Knowledge Center Home',
                        'url' => home_url('/knowledge-center/'),
                        'description' => 'Return to the main knowledge center to explore all resources.',
                        'icon' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
                        'count' => ''
                    ),
                    array(
                        'name' => 'Frequently Asked Questions',
                        'url' => $faqs_category ? get_term_link($faqs_category) : '#',
                        'description' => 'Find answers to common questions about energy in Alabama.',
                        'icon' => '<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
                        'count' => $faqs_category ? $faqs_category->count : 0
                    )
                );
                
                foreach ($related_resources as $resource) :
                ?>
                    <div class="eakc-category-card" style="--category-color: #6366f1;">
                        <a href="<?php echo esc_url($resource['url']); ?>">
                            <div class="eakc-category-card-icon">
                                <?php echo $resource['icon']; ?>
                            </div>
                            <h4><?php echo esc_html($resource['name']); ?></h4>
                            <p><?php echo esc_html($resource['description']); ?></p>
                            <?php if ($resource['count']): ?>
                                <span class="eakc-category-count">
                                    <?php printf(_n('%d article', '%d articles', $resource['count'], 'energy-alabama-kc'), $resource['count']); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

</div>

<script>
// Filtering and sorting functions for the docket archive page
function eakc_filterByStatus(status) {
    const dockets = document.querySelectorAll('.eakc-docket-card');
    
    dockets.forEach(function(docket) {
        if (status === '' || docket.getAttribute('data-status') === status) {
            docket.style.display = 'block';
        } else {
            docket.style.display = 'none';
        }
    });
}

function eakc_filterByJurisdiction(jurisdiction) {
    const dockets = document.querySelectorAll('.eakc-docket-card');
    
    dockets.forEach(function(docket) {
        if (jurisdiction === '' || docket.getAttribute('data-jurisdiction') === jurisdiction) {
            docket.style.display = 'block';
        } else {
            docket.style.display = 'none';
        }
    });
}

function eakc_sortDockets(sortBy) {
    const container = document.getElementById('eakc-dockets-container');
    const dockets = Array.from(container.querySelectorAll('.eakc-docket-card'));
    
    dockets.sort(function(a, b) {
        if (sortBy === 'title') {
            const titleA = a.querySelector('.eakc-docket-card-title a').textContent.toLowerCase();
            const titleB = b.querySelector('.eakc-docket-card-title a').textContent.toLowerCase();
            return titleA.localeCompare(titleB);
        } else if (sortBy === 'docket-number') {
            const numA = a.getAttribute('data-docket-number') || '';
            const numB = b.getAttribute('data-docket-number') || '';
            return numA.localeCompare(numB);
        } else if (sortBy === 'status') {
            const statusA = a.getAttribute('data-status') || '';
            const statusB = b.getAttribute('data-status') || '';
            return statusA.localeCompare(statusB);
        }
        // Default: sort by date (keep original order)
        return 0;
    });
    
    // Re-append sorted dockets
    dockets.forEach(function(docket) {
        container.appendChild(docket);
    });
}
</script>

<?php get_footer(); ?>