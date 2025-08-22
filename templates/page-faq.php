<?php
/**
 * Template for FAQ Page
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Get all FAQ posts ordered by menu_order
$faqs = get_posts(array(
    'post_type' => 'faq',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    'orderby' => 'menu_order',
    'order' => 'ASC'
));

?>

<div class="eakc-faq-page">
    
    <!-- Hero Section -->
    <section class="eakc-hero">
        <div class="eakc-container">
            <div class="eakc-hero-content">
                <h1 class="eakc-hero-title">
                    <?php _e('Frequently Asked Questions', 'energy-alabama-kc'); ?>
                </h1>
                <p class="eakc-hero-description">
                    <?php _e('Find answers to common questions about clean energy, energy efficiency, and Alabama\'s energy landscape.', 'energy-alabama-kc'); ?>
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

    <!-- Breadcrumbs -->
    <?php 
    $template_manager = Energy_Alabama_KC_Template_Manager::get_instance();
    echo $template_manager->render_breadcrumbs(); 
    ?>

    <!-- FAQ Content -->
    <section class="eakc-faq-content">
        <div class="eakc-container">
            
            <!-- FAQ List -->
            <div class="eakc-faq-list" id="eakc-faq-container">
                <?php if (!empty($faqs)): ?>
                    <div class="eakc-faq-group">
                        <?php foreach ($faqs as $index => $faq): ?>
                            <div class="eakc-faq-item" data-question="<?php echo esc_attr(strtolower($faq->post_title)); ?>" data-answer="<?php echo esc_attr(strtolower(wp_strip_all_tags($faq->post_content))); ?>">
                                <div class="eakc-faq-header" role="button" tabindex="0" aria-expanded="false">
                                    <h3 class="eakc-faq-question"><?php echo esc_html($faq->post_title); ?></h3>
                                    <span class="eakc-accordion-icon">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="6,9 12,15 18,9"/>
                                        </svg>
                                    </span>
                                </div>
                                
                                <div class="eakc-faq-answer" style="display: none;">
                                    <div class="eakc-faq-answer-text">
                                        <?php echo wpautop($faq->post_content); ?>
                                    </div>
                                </div>
                            </div>
                            
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- No FAQ Message -->
                    <div class="eakc-no-faqs">
                        <div class="eakc-no-faqs-icon">
                            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                        <h3><?php _e('No FAQs found', 'energy-alabama-kc'); ?></h3>
                        <p><?php _e('Check back soon for frequently asked questions and answers.', 'energy-alabama-kc'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

</div>

<script>
// FAQ functionality
document.addEventListener('DOMContentLoaded', function() {
    const faqHeaders = document.querySelectorAll('.eakc-faq-header');
    
    // Accordion functionality
    faqHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('.eakc-accordion-icon svg');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Collapse
                answer.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                icon.style.transform = 'rotate(0deg)';
            } else {
                // Expand
                answer.style.display = 'block';
                this.setAttribute('aria-expanded', 'true');
                icon.style.transform = 'rotate(180deg)';
            }
        });
        
        // Keyboard support
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<?php get_footer(); ?>