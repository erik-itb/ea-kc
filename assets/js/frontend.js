/**
 * Energy Alabama Knowledge Center - Frontend JavaScript
 * 
 * @package Energy_Alabama_KC
 * @since   1.0.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Initialize accordion functionality for document categories
     */
    function initializeAccordions() {
        const accordionHeaders = document.querySelectorAll('.eakc-category-header[data-accordion-target]');
        
        accordionHeaders.forEach(function(header) {
            header.addEventListener('click', function() {
                const targetId = this.getAttribute('data-accordion-target');
                const content = document.getElementById(targetId);
                const icon = this.querySelector('.eakc-accordion-icon');
                
                if (!content) return;
                
                // Toggle active states
                const isActive = content.classList.contains('active');
                
                if (isActive) {
                    // Close accordion
                    content.classList.remove('active');
                    this.classList.remove('active');
                    if (icon) icon.classList.remove('active');
                } else {
                    // Open accordion
                    content.classList.add('active');
                    this.classList.add('active');
                    if (icon) icon.classList.add('active');
                }
                
                // Add ARIA attributes for accessibility
                this.setAttribute('aria-expanded', !isActive);
                content.setAttribute('aria-hidden', isActive);
            });
            
            // Set initial ARIA attributes
            const targetId = header.getAttribute('data-accordion-target');
            const content = document.getElementById(targetId);
            
            if (content) {
                header.setAttribute('aria-expanded', 'false');
                header.setAttribute('role', 'button');
                header.setAttribute('tabindex', '0');
                content.setAttribute('aria-hidden', 'true');
                content.setAttribute('role', 'region');
                
                // Add keyboard support
                header.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            }
        });
    }
    
    /**
     * Initialize search functionality (if needed in future)
     */
    function initializeSearch() {
        const searchForms = document.querySelectorAll('.eakc-search-form');
        
        searchForms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const searchField = this.querySelector('.eakc-search-field');
                if (searchField && searchField.value.trim() === '') {
                    e.preventDefault();
                    searchField.focus();
                }
            });
        });
    }
    
    /**
     * Initialize smooth scrolling for anchor links
     */
    function initializeSmoothScroll() {
        const anchorLinks = document.querySelectorAll('a[href^="#"]');
        
        anchorLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }
    
    /**
     * Initialize responsive navigation (if needed)
     */
    function initializeResponsiveFeatures() {
        // Handle mobile-specific interactions
        if (window.innerWidth <= 768) {
            // Add any mobile-specific functionality here
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            // Debounce resize events
            clearTimeout(window.resizeTimeout);
            window.resizeTimeout = setTimeout(function() {
                // Add resize-specific functionality here
            }, 250);
        });
    }
    
    /**
     * Initialize all frontend functionality
     */
    function init() {
        initializeAccordions();
        initializeSearch();
        initializeSmoothScroll();
        initializeResponsiveFeatures();
        
        // Log initialization for debugging
        if (window.console && console.log) {
            console.log('Energy Alabama KC: Frontend initialized');
        }
    }
    
    // Initialize everything
    init();
    
    /**
     * Expose public methods for external use
     */
    window.EAKC = window.EAKC || {};
    window.EAKC.accordion = {
        init: initializeAccordions,
        openAll: function() {
            const accordions = document.querySelectorAll('.eakc-accordion-content');
            const headers = document.querySelectorAll('.eakc-category-header[data-accordion-target]');
            const icons = document.querySelectorAll('.eakc-accordion-icon');
            
            accordions.forEach(function(accordion) {
                accordion.classList.add('active');
                accordion.setAttribute('aria-hidden', 'false');
            });
            
            headers.forEach(function(header) {
                header.classList.add('active');
                header.setAttribute('aria-expanded', 'true');
            });
            
            icons.forEach(function(icon) {
                icon.classList.add('active');
            });
        },
        closeAll: function() {
            const accordions = document.querySelectorAll('.eakc-accordion-content');
            const headers = document.querySelectorAll('.eakc-category-header[data-accordion-target]');
            const icons = document.querySelectorAll('.eakc-accordion-icon');
            
            accordions.forEach(function(accordion) {
                accordion.classList.remove('active');
                accordion.setAttribute('aria-hidden', 'true');
            });
            
            headers.forEach(function(header) {
                header.classList.remove('active');
                header.setAttribute('aria-expanded', 'false');
            });
            
            icons.forEach(function(icon) {
                icon.classList.remove('active');
            });
        }
    };
});