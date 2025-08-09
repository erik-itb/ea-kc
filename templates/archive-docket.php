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
            <div class="eakc-dockets-grid" id="eakc-dockets-container">
                <?php if (have_posts()) : ?>
                    
                    <?php while (have_posts()) : the_post(); ?>
                        <?php
                        // Get docket meta
                        $docket_number = get_post_meta(get_the_ID(), '_eakc_docket_number', true);
                        $docket_status = get_post_meta(get_the_ID(), '_eakc_docket_status', true);
                        $filing_date = get_post_meta(get_the_ID(), '_eakc_filing_date', true);
                        $proceeding_type = get_post_meta(get_the_ID(), '_eakc_proceeding_type', true);
                        
                        // Get jurisdictions
                        $jurisdictions = get_the_terms(get_the_ID(), 'docket_jurisdiction');
                        $jurisdiction_names = array();
                        if ($jurisdictions && !is_wp_error($jurisdictions)) {
                            foreach ($jurisdictions as $jurisdiction) {
                                $jurisdiction_names[] = $jurisdiction->name;
                            }
                        }
                        ?>
                        
                        <article class="eakc-docket-card" 
                                 data-status="<?php echo esc_attr($docket_status); ?>"
                                 data-jurisdiction="<?php echo esc_attr($jurisdictions && !is_wp_error($jurisdictions) ? $jurisdictions[0]->slug : ''); ?>"
                                 data-docket-number="<?php echo esc_attr($docket_number); ?>">
                            
                            <div class="eakc-docket-card-header">
                                <div class="eakc-docket-icon">
                                    <?php echo eakc_get_docket_icon($proceeding_type); ?>
                                </div>
                                
                                <div class="eakc-docket-meta">
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
                                    
                                    <?php if (!empty($jurisdiction_names)): ?>
                                        <span class="eakc-jurisdictions">
                                            <strong><?php _e('Jurisdiction:', 'energy-alabama-kc'); ?></strong>
                                            <?php echo esc_html(implode(', ', $jurisdiction_names)); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="eakc-docket-card-footer">
                                    <?php if ($filing_date): ?>
                                        <time class="eakc-filing-date" datetime="<?php echo esc_attr($filing_date); ?>">
                                            <strong><?php _e('Filed:', 'energy-alabama-kc'); ?></strong>
                                            <?php echo esc_html(date('M j, Y', strtotime($filing_date))); ?>
                                        </time>
                                    <?php endif; ?>
                                    
                                    <time class="eakc-updated-date" datetime="<?php echo esc_attr(get_the_modified_date('c')); ?>">
                                        <strong><?php _e('Updated:', 'energy-alabama-kc'); ?></strong>
                                        <?php echo get_the_modified_date('M j, Y'); ?>
                                    </time>
                                    
                                    <a href="<?php the_permalink(); ?>" class="eakc-view-docket">
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

    <!-- Quick Actions -->
    <section class="eakc-docket-quick-actions">
        <div class="eakc-container">
            <h3><?php _e('Quick Access', 'energy-alabama-kc'); ?></h3>
            
            <div class="eakc-quick-actions-grid">
                <a href="<?php echo esc_url(get_term_link(get_term_by('slug', 'legal-regulatory', 'kc_category'))); ?>" class="eakc-quick-action">
                    <div class="eakc-quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                        </svg>
                    </div>
                    <span><?php _e('Legal & Regulatory Articles', 'energy-alabama-kc'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(home_url('/knowledge-center/')); ?>" class="eakc-quick-action">
                    <div class="eakc-quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <span><?php _e('Knowledge Center Home', 'energy-alabama-kc'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(get_term_link(get_term_by('slug', 'faqs', 'kc_category'))); ?>" class="eakc-quick-action">
                    <div class="eakc-quick-action-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <span><?php _e('Frequently Asked Questions', 'energy-alabama-kc'); ?></span>
                </a>
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