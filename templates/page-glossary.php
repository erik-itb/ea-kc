<?php
/**
 * Template for Glossary Page
 *
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

get_header();

// Helper function to organize definitions by first letter
function eakc_organize_definitions_by_letter() {
    $definitions = get_posts(array(
        'post_type' => 'glossary',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    
    $organized = array();
    $letters = array_merge(range('A', 'Z'), array('#'));
    
    // Initialize all letters
    foreach ($letters as $letter) {
        $organized[$letter] = array();
    }
    
    foreach ($definitions as $definition) {
        $first_char = strtoupper(substr($definition->post_title, 0, 1));
        
        // Numbers go to #
        if (is_numeric($first_char)) {
            $organized['#'][] = $definition;
        } elseif (in_array($first_char, range('A', 'Z'))) {
            $organized[$first_char][] = $definition;
        } else {
            // Special characters also go to #
            $organized['#'][] = $definition;
        }
    }
    
    return $organized;
}

$definitions_by_letter = eakc_organize_definitions_by_letter();
?>

<div class="eakc-glossary-page">
    
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
                    <?php _e('Glossary', 'energy-alabama-kc'); ?>
                </h1>
                <p class="eakc-hero-description">
                    <?php _e('Find definitions for clean energy terms, policy concepts, and technical terminology used throughout Alabama\'s energy landscape.', 'energy-alabama-kc'); ?>
                </p>
                
                <!-- Search Form -->
                <div class="eakc-search-container">
                    <div class="eakc-search-wrapper">
                        <input type="search" 
                               id="eakc-glossary-search" 
                               class="eakc-search-input" 
                               placeholder="<?php esc_attr_e('Search definitions...', 'energy-alabama-kc'); ?>"
                               autocomplete="off"
                               aria-label="<?php esc_attr_e('Search glossary definitions', 'energy-alabama-kc'); ?>">
                        <button type="button" id="eakc-clear-search" class="eakc-search-button" style="display: none;" aria-label="<?php esc_attr_e('Clear search', 'energy-alabama-kc'); ?>">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Glossary Content -->
    <section class="eakc-glossary-content">
        <div class="eakc-container">
            
            <!-- Letter Navigation -->
            <div class="eakc-letter-navigation" id="eakc-letter-nav">
                <div class="eakc-letter-nav-wrapper">
                    <?php foreach (array_merge(range('A', 'Z'), array('#')) as $letter): ?>
                        <?php $has_definitions = !empty($definitions_by_letter[$letter]); ?>
                        <button type="button" 
                                class="eakc-letter-btn <?php echo $has_definitions ? 'has-definitions' : 'no-definitions'; ?>" 
                                data-letter="<?php echo esc_attr($letter); ?>"
                                <?php echo $has_definitions ? '' : 'disabled'; ?>>
                            <?php echo esc_html($letter); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Definitions List -->
            <div class="eakc-definitions-list" id="eakc-definitions-container">
                <?php foreach ($definitions_by_letter as $letter => $definitions): ?>
                    <?php if (!empty($definitions)): ?>
                        <div class="eakc-letter-section" id="letter-<?php echo esc_attr($letter); ?>" data-letter="<?php echo esc_attr($letter); ?>">
                            <h2 class="eakc-letter-heading"><?php echo esc_html($letter); ?></h2>
                            
                            <div class="eakc-definitions-group">
                                <?php foreach ($definitions as $definition): ?>
                                    <?php
                                    $source_link = get_post_meta($definition->ID, '_eakc_source_link', true);
                                    $button_text = get_post_meta($definition->ID, '_eakc_button_text', true);
                                    $default_button_text = $button_text ?: 'Learn More';
                                    ?>
                                    
                                    <?php
                                    $clean_content = preg_replace('/\s+/', ' ', wp_strip_all_tags($definition->post_content));
                                    $clean_content = trim(strtolower($clean_content));
                                    ?>
                                    <div class="eakc-definition-item" data-term="<?php echo esc_attr(strtolower($definition->post_title)); ?>" data-content="<?php echo esc_attr($clean_content); ?>">
                                        <div class="eakc-definition-header" role="button" tabindex="0" aria-expanded="false">
                                            <h3 class="eakc-definition-term"><?php echo esc_html($definition->post_title); ?></h3>
                                            <span class="eakc-accordion-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <polyline points="6,9 12,15 18,9"/>
                                                </svg>
                                            </span>
                                        </div>
                                        
                                        <div class="eakc-definition-content" style="display: none;">
                                            <div class="eakc-definition-text">
                                                <?php echo wpautop($definition->post_content); ?>
                                            </div>
                                            
                                            <?php if ($source_link): ?>
                                                <div class="eakc-definition-source">
                                                    <a href="<?php echo esc_url($source_link); ?>" 
                                                       class="eakc-source-button" 
                                                       target="_blank" 
                                                       rel="noopener noreferrer">
                                                        <?php echo esc_html($default_button_text); ?>
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <line x1="7" y1="17" x2="17" y2="7"/>
                                                            <polyline points="7,7 17,7 17,17"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- No Results Message -->
                <div class="eakc-no-results" id="eakc-no-results" style="display: none;">
                    <div class="eakc-no-results-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                    </div>
                    <h3><?php _e('No definitions found', 'energy-alabama-kc'); ?></h3>
                    <p><?php _e('Try adjusting your search terms or browse by letter.', 'energy-alabama-kc'); ?></p>
                </div>
            </div>

        </div>
    </section>

</div>

<script>
// Glossary functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('eakc-glossary-search');
    const clearButton = document.getElementById('eakc-clear-search');
    const letterNav = document.getElementById('eakc-letter-nav');
    const definitionsContainer = document.getElementById('eakc-definitions-container');
    const noResults = document.getElementById('eakc-no-results');
    const letterButtons = document.querySelectorAll('.eakc-letter-btn');
    const definitionItems = document.querySelectorAll('.eakc-definition-item');
    const definitionHeaders = document.querySelectorAll('.eakc-definition-header');
    
    let searchTimeout;
    let isSearching = false;
    
    // Make letter navigation sticky
    function makeLetterNavSticky() {
        const navTop = letterNav.offsetTop;
        
        function checkSticky() {
            if (window.pageYOffset >= navTop) {
                letterNav.classList.add('sticky');
            } else {
                letterNav.classList.remove('sticky');
            }
        }
        
        window.addEventListener('scroll', checkSticky);
        checkSticky();
    }
    
    // Search functionality
    function performSearch(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        
        if (term === '') {
            clearSearch();
            return;
        }
        
        isSearching = true;
        letterNav.style.display = 'none';
        clearButton.style.display = 'block';
        
        let hasResults = false;
        let termMatches = [];
        let contentMatches = [];
        
        console.log('Searching for:', term); // Debug log
        
        // Separate term matches from content matches
        definitionItems.forEach(function(item) {
            const itemTerm = item.getAttribute('data-term') || '';
            const itemContent = item.getAttribute('data-content') || '';
            
            console.log('Checking item:', itemTerm, 'content preview:', itemContent.substring(0, 50)); // Debug log
            
            // Reset item styles first
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            
            const termMatch = itemTerm.includes(term);
            const contentMatch = itemContent.includes(term);
            
            if (termMatch) {
                console.log('Term match found:', itemTerm); // Debug log
                termMatches.push(item);
                hasResults = true;
            } else if (contentMatch) {
                console.log('Content match found:', itemTerm); // Debug log
                contentMatches.push(item);
                hasResults = true;
            } else {
                // Fade out non-matching items
                item.style.opacity = '0';
                item.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    item.style.display = 'none';
                }, 300);
            }
        });
        
        console.log('Total matches found:', termMatches.length + contentMatches.length); // Debug log
        
        // Hide letter sections
        document.querySelectorAll('.eakc-letter-section').forEach(function(section) {
            section.style.display = 'none';
        });
        
        if (hasResults) {
            noResults.style.display = 'none';
            
            // Show results in order: term matches first, then content matches
            const allMatches = [...termMatches, ...contentMatches];
            allMatches.forEach(function(item, index) {
                setTimeout(() => {
                    item.style.display = 'block';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 50);
            });
        } else {
            noResults.style.display = 'block';
        }
    }
    
    function clearSearch() {
        isSearching = false;
        searchInput.value = '';
        clearButton.style.display = 'none';
        letterNav.style.display = 'block';
        noResults.style.display = 'none';
        
        // Show letter sections
        document.querySelectorAll('.eakc-letter-section').forEach(function(section) {
            section.style.display = 'block';
        });
        
        // Show all definition items
        definitionItems.forEach(function(item, index) {
            setTimeout(() => {
                item.style.display = 'block';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            }, index * 30);
        });
    }
    
    // Search input handler
    searchInput.addEventListener('input', function(e) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(e.target.value);
        }, 300);
    });
    
    // Clear search button
    clearButton.addEventListener('click', clearSearch);
    
    // Letter navigation
    letterButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            if (isSearching) return;
            
            const letter = this.getAttribute('data-letter');
            const targetSection = document.getElementById('letter-' + letter);
            
            if (targetSection) {
                const navHeight = letterNav.offsetHeight + 20;
                const targetTop = targetSection.offsetTop - navHeight;
                
                window.scrollTo({
                    top: targetTop,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Accordion functionality
    definitionHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            const content = this.nextElementSibling;
            const icon = this.querySelector('.eakc-accordion-icon svg');
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            if (isExpanded) {
                // Collapse
                content.style.display = 'none';
                this.setAttribute('aria-expanded', 'false');
                icon.style.transform = 'rotate(0deg)';
            } else {
                // Expand
                content.style.display = 'block';
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
    
    // Initialize sticky navigation
    makeLetterNavSticky();
});
</script>

<?php get_footer(); ?>