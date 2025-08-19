<?php
/**
 * Template for Docket Jurisdiction Archive
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get current jurisdiction
$current_jurisdiction = get_queried_object();
$jurisdiction_name = $current_jurisdiction->name;
$jurisdiction_description = $current_jurisdiction->description;
$jurisdiction_slug = $current_jurisdiction->slug;

// Helper function for docket status styling
function eakc_get_jurisdiction_docket_status_class($status) {
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
function eakc_get_jurisdiction_docket_icon($proceeding_type) {
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
                    <?php echo esc_html($jurisdiction_name); ?> Dockets
                </h1>
                <p class="eakc-hero-description">
                    <?php 
                    if ($jurisdiction_description) {
                        echo esc_html($jurisdiction_description);
                    } else {
                        printf(__('Browse all dockets and proceedings within the %s jurisdiction', 'energy-alabama-kc'), esc_html($jurisdiction_name));
                    }
                    ?>
                </p>
                
                <!-- Search Form -->
                <div class="eakc-search-container">
                    <form class="eakc-search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                        <div class="eakc-search-wrapper">
                            <input type="search" 
                                    class="eakc-search-input" 
                                    placeholder="<?php printf(__('Search %s dockets...', 'energy-alabama-kc'), esc_attr($jurisdiction_name)); ?>"
                                    value="<?php echo get_search_query(); ?>" 
                                    name="s" 
                                    autocomplete="off"
                                    aria-label="<?php printf(__('Search %s dockets', 'energy-alabama-kc'), esc_attr($jurisdiction_name)); ?>">
                            <input type="hidden" name="post_type" value="docket">
                            <input type="hidden" name="docket_jurisdiction" value="<?php echo esc_attr($jurisdiction_slug); ?>">
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

    <!-- Jurisdiction Content -->
    <section class="eakc-docket-content">
        <div class="eakc-container">
            
            <!-- Jurisdiction Stats -->
            <div class="eakc-docket-stats" style="text-align: center; margin-bottom: 40px; color: #6b7280;">
                <?php
                $total_posts = $wp_query->found_posts;
                printf(
                    _n('%d docket in jurisdiction', '%d dockets in jurisdiction', $total_posts, 'energy-alabama-kc'),
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
                    
                    <select class="eakc-sort-filter" onchange="eakc_sortDockets(this.value)">
                        <option value="date"><?php _e('Sort by Date', 'energy-alabama-kc'); ?></option>
                        <option value="title"><?php _e('Sort by Title', 'energy-alabama-kc'); ?></option>
                        <option value="docket-number"><?php _e('Sort by Docket Number', 'energy-alabama-kc'); ?></option>
                        <option value="status"><?php _e('Sort by Status', 'energy-alabama-kc'); ?></option>
                    </select>
                </div>
            </div>

            <!-- Dockets Grid -->
            <div class="eakc-dockets-grid" id="eakc-dockets-container">
                <?php if (have_posts()) : ?>
                    
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        // Get docket meta
                        $docket_number = get_post_meta(get_the_ID(), '_eakc_docket_number', true);
                        $docket_status = get_post_meta(get_the_ID(), '_eakc_docket_status', true);
                        $filing_date = get_post_meta(get_the_ID(), '_eakc_filing_date', true);
                        $proceeding_type = get_post_meta(get_the_ID(), '_eakc_proceeding_type', true);
                        ?>
                        
                        <article class="eakc-docket-card" 
                                 data-status="<?php echo esc_attr($docket_status); ?>"
                                 data-docket-number="<?php echo esc_attr($docket_number); ?>">
                            
                            <div class="eakc-docket-card-header">
                                <div class="eakc-docket-icon">
                                    <?php echo eakc_get_jurisdiction_docket_icon($proceeding_type); ?>
                                </div>
                                
                                <div class="eakc-docket-meta">
                                    <?php if ($docket_number): ?>
                                        <span class="eakc-docket-number">
                                            <?php printf(__('Docket #%s', 'energy-alabama-kc'), esc_html($docket_number)); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($docket_status): ?>
                                        <span class="eakc-docket-status <?php echo esc_attr(eakc_get_jurisdiction_docket_status_class($docket_status)); ?>">
                                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $docket_status))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="eakc-docket-card-content">
                                <h3 class="eakc-docket-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                
                                <div class="eakc-docket-card-excerpt">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </div>
                                
                                <div class="eakc-docket-card-details">
                                    <?php if ($proceeding_type): ?>
                                        <span class="eakc-proceeding-type">
                                            <strong><?php _e('Type:', 'energy-alabama-kc'); ?></strong>
                                            <?php echo esc_html(ucfirst(str_replace('-', ' ', $proceeding_type))); ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($filing_date): ?>
                                        <span class="eakc-filing-date">
                                            <strong><?php _e('Filed:', 'energy-alabama-kc'); ?></strong>
                                            <?php echo esc_html(date('M j, Y', strtotime($filing_date))); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="eakc-docket-card-footer">
                                    <time class="eakc-docket-date" datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                        <?php _e('Updated:', 'energy-alabama-kc'); ?> <?php echo get_the_date('M j, Y'); ?>
                                    </time>
                                    
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
                    
                    <div class="eakc-no-articles">
                        <div class="eakc-no-articles-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                        </div>
                        <h3><?php _e('No dockets found', 'energy-alabama-kc'); ?></h3>
                        <p><?php printf(__('There are currently no dockets in the %s jurisdiction.', 'energy-alabama-kc'), esc_html($jurisdiction_name)); ?></p>
                        <a href="<?php echo esc_url(get_post_type_archive_link('docket')); ?>" class="eakc-back-link">
                            <?php _e('← Back to All Dockets', 'energy-alabama-kc'); ?>
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

    <!-- Related Jurisdictions -->
    <section class="eakc-related-categories">
        <div class="eakc-container">
            <h3><?php _e('Other Jurisdictions', 'energy-alabama-kc'); ?></h3>
            
            <div class="eakc-jurisdictions-grid">
                <?php
                $all_jurisdictions = get_terms(array(
                    'taxonomy' => 'docket_jurisdiction',
                    'hide_empty' => true,
                    'exclude' => array($current_jurisdiction->term_id)
                ));
                
                if ($all_jurisdictions && !is_wp_error($all_jurisdictions)) :
                ?>
                    <div class="eakc-categories-grid">
                        <?php foreach ($all_jurisdictions as $jurisdiction) : 
                            $juris_count = $jurisdiction->count;
                        ?>
                            <div class="eakc-category-card" style="--category-color: #6366f1;">
                                <a href="<?php echo esc_url(get_term_link($jurisdiction)); ?>">
                                    <div class="eakc-category-card-icon">
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polygon points="3,6 9,1 15,6 21,1 21,15 15,10 9,15 3,10"/>
                                        </svg>
                                    </div>
                                    <h4><?php echo esc_html($jurisdiction->name); ?></h4>
                                    <p><?php echo wp_trim_words($jurisdiction->description, 15, '...'); ?></p>
                                    <span class="eakc-category-count">
                                        <?php printf(_n('%d docket', '%d dockets', $juris_count, 'energy-alabama-kc'), $juris_count); ?>
                                    </span>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p class="eakc-no-jurisdictions"><?php _e('No other jurisdictions available.', 'energy-alabama-kc'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </section>

</div>

<script>
// Simple filtering functions for the jurisdiction page
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

function eakc_sortDockets(sortBy) {
    const container = document.getElementById('eakc-dockets-container');
    const dockets = Array.from(container.querySelectorAll('.eakc-docket-card'));
    
    dockets.sort(function(a, b) {
        if (sortBy === 'title') {
            const titleA = a.querySelector('.eakc-docket-card-title a').textContent.toLowerCase();
            const titleB = b.querySelector('.eakc-docket-card-title a').textContent.toLowerCase();
            return titleA.localeCompare(titleB);
        } else if (sortBy === 'docket-number') {
            const numberA = a.getAttribute('data-docket-number') || '';
            const numberB = b.getAttribute('data-docket-number') || '';
            return numberA.localeCompare(numberB);
        } else if (sortBy === 'status') {
            const statusA = a.getAttribute('data-status') || '';
            const statusB = b.getAttribute('data-status') || '';
            return statusA.localeCompare(statusB);
        }
        // Default: sort by date (newest first)
        return 0; // Keep original order for date sorting
    });
    
    // Re-append sorted dockets
    dockets.forEach(function(docket) {
        container.appendChild(docket);
    });
}
</script>

<style>
.eakc-dockets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.eakc-docket-card {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.3s ease;
    height: fit-content;
}

.eakc-docket-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border-color: #6366f1;
}

.eakc-docket-card-header {
    padding: 20px 25px 15px;
    display: flex;
    align-items: center;
    gap: 15px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.eakc-docket-icon {
    width: 50px;
    height: 50px;
    background: #e0e7ff;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6366f1;
    flex-shrink: 0;
}

.eakc-docket-meta {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.eakc-docket-card-content {
    padding: 25px;
}

.eakc-docket-card-title {
    margin: 0 0 15px 0;
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.3;
}

.eakc-docket-card-title a {
    color: #1f2937;
    text-decoration: none;
    transition: color 0.2s;
}

.eakc-docket-card-title a:hover {
    color: #6366f1;
}

.eakc-docket-card-excerpt {
    color: #6b7280;
    line-height: 1.6;
    margin-bottom: 20px;
}

.eakc-docket-card-details {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    font-size: 14px;
}

.eakc-docket-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #f3f4f6;
}

.eakc-docket-date {
    color: #9ca3af;
    font-size: 14px;
}

.eakc-docket-filters {
    display: flex;
    justify-content: center;
    margin-bottom: 40px;
}

.eakc-filter-controls {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.eakc-status-filter,
.eakc-sort-filter {
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    font-size: 14px;
    cursor: pointer;
    transition: border-color 0.2s;
    min-width: 180px;
}

.eakc-status-filter:focus,
.eakc-sort-filter:focus {
    outline: none;
    border-color: #6366f1;
}

.eakc-no-jurisdictions {
    text-align: center;
    color: #6b7280;
    font-style: italic;
}

@media (max-width: 768px) {
    .eakc-dockets-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .eakc-filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .eakc-status-filter,
    .eakc-sort-filter {
        min-width: auto;
    }
    
    .eakc-docket-card-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .eakc-docket-card-details {
        flex-direction: column;
        gap: 8px;
    }
    
    .eakc-docket-card-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
}
</style>

<?php get_footer(); ?>