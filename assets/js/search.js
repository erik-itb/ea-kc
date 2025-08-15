/**
 * AJAX Search Functionality
 * Handles live search with dropdown results
 */

(function($) {
    'use strict';

    let searchTimeout;
    let currentRequest;
    
    $(document).ready(function() {
        initAjaxSearch();
    });

    function initAjaxSearch() {
        const $searchInputs = $('.eakc-search-input');
        
        $searchInputs.each(function() {
            const $input = $(this);
            const $form = $input.closest('.eakc-search-form');
            const $results = $form.find('.eakc-search-results');
            
            // Initialize results container
            $results.html('').hide();
            
            // Handle input events
            $input.on('input', function() {
                const query = $(this).val().trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Cancel previous request
                if (currentRequest) {
                    currentRequest.abort();
                }
                
                if (query.length < 2) {
                    hideResults($results);
                    return;
                }
                
                // Debounce search
                searchTimeout = setTimeout(function() {
                    performSearch(query, $form, $results);
                }, 300);
            });
            
            // Handle focus
            $input.on('focus', function() {
                const query = $(this).val().trim();
                if (query.length >= 2) {
                    $results.show();
                }
            });
            
            // Handle form submission
            $form.on('submit', function(e) {
                hideResults($results);
            });
        });
        
        // Hide results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.eakc-search-container').length) {
                $('.eakc-search-results').hide();
            }
        });
        
        // Handle keyboard navigation
        $(document).on('keydown', '.eakc-search-input', function(e) {
            const $results = $(this).closest('.eakc-search-form').find('.eakc-search-results');
            const $items = $results.find('.eakc-search-result-item');
            const $active = $items.filter('.active');
            
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if ($active.length === 0) {
                    $items.first().addClass('active');
                } else {
                    $active.removeClass('active');
                    const $next = $active.next('.eakc-search-result-item');
                    if ($next.length) {
                        $next.addClass('active');
                    } else {
                        $items.first().addClass('active');
                    }
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if ($active.length === 0) {
                    $items.last().addClass('active');
                } else {
                    $active.removeClass('active');
                    const $prev = $active.prev('.eakc-search-result-item');
                    if ($prev.length) {
                        $prev.addClass('active');
                    } else {
                        $items.last().addClass('active');
                    }
                }
            } else if (e.key === 'Enter' && $active.length) {
                e.preventDefault();
                window.location.href = $active.find('a').attr('href');
            } else if (e.key === 'Escape') {
                hideResults($results);
                $(this).blur();
            }
        });
        
        // Handle mouse hover
        $(document).on('mouseenter', '.eakc-search-result-item', function() {
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
        });
    }

    function performSearch(query, $form, $results) {
        // Show loading state
        showLoading($results);
        
        // Get post type from hidden field
        const postType = $form.find('input[name="post_type"]').val() || 'kc_article';
        
        // Get taxonomy filters if present
        const categoryFilter = $form.find('input[name="kc_category"]').val();
        const tagFilter = $form.find('input[name="kc_tag"]').val();
        const jurisdictionFilter = $form.find('input[name="docket_jurisdiction"]').val();
        
        const data = {
            action: 'eakc_ajax_search',
            query: query,
            post_type: postType,
            nonce: eakc_search.nonce
        };
        
        // Add taxonomy filters
        if (categoryFilter) data.category = categoryFilter;
        if (tagFilter) data.tag = tagFilter;
        if (jurisdictionFilter) data.jurisdiction = jurisdictionFilter;
        
        currentRequest = $.ajax({
            url: eakc_search.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                currentRequest = null;
                
                if (response.success) {
                    displayResults(response.data, $results, query);
                } else {
                    showError($results, response.data || 'Search failed');
                }
            },
            error: function(xhr) {
                currentRequest = null;
                
                if (xhr.statusText !== 'abort') {
                    showError($results, 'Search request failed');
                }
            }
        });
    }

    function showLoading($results) {
        const loadingHtml = `
            <div class="eakc-search-loading">
                <div class="eakc-loading-spinner"></div>
                <span>Searching...</span>
            </div>
        `;
        
        $results.html(loadingHtml).slideDown(200);
    }

    function displayResults(results, $results, query) {
        if (!results || results.length === 0) {
            showNoResults($results, query);
            return;
        }
        
        let html = '<div class="eakc-search-results-list">';
        
        results.forEach(function(item) {
            const thumbnail = item.thumbnail || generatePlaceholder(item.title);
            const excerpt = item.excerpt || '';
            const typeLabel = item.post_type === 'docket' ? 'Docket' : 'Article';
            
            html += `
                <div class="eakc-search-result-item">
                    <a href="${item.url}" class="eakc-search-result-link">
                        <div class="eakc-search-result-thumbnail">
                            <img src="${thumbnail}" alt="${item.title}" loading="lazy">
                        </div>
                        <div class="eakc-search-result-content">
                            <div class="eakc-search-result-meta">
                                <span class="eakc-search-result-type">${typeLabel}</span>
                                ${item.category ? `<span class="eakc-search-result-category">${item.category}</span>` : ''}
                            </div>
                            <h4 class="eakc-search-result-title">${highlightQuery(item.title, query)}</h4>
                            ${excerpt ? `<p class="eakc-search-result-excerpt">${highlightQuery(excerpt, query)}</p>` : ''}
                        </div>
                    </a>
                </div>
            `;
        });
        
        html += '</div>';
        
        // Add "View All Results" link
        const postType = results[0].post_type || 'kc_article';
        const searchUrl = eakc_search.home_url + '/?s=' + encodeURIComponent(query) + '&post_type=' + postType;
        
        html += `
            <div class="eakc-search-footer">
                <a href="${searchUrl}" class="eakc-view-all-results">
                    View all results for "${query}"
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="7" y1="17" x2="17" y2="7"/>
                        <polyline points="7,7 17,7 17,17"/>
                    </svg>
                </a>
            </div>
        `;
        
        $results.html(html).slideDown(200);
    }

    function showNoResults($results, query) {
        const html = `
            <div class="eakc-search-no-results">
                <div class="eakc-no-results-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <p>No results found for "<strong>${query}</strong>"</p>
                <small>Try searching with different keywords</small>
            </div>
        `;
        
        $results.html(html).slideDown(200);
    }

    function showError($results, message) {
        const html = `
            <div class="eakc-search-error">
                <p>Error: ${message}</p>
            </div>
        `;
        
        $results.html(html).slideDown(200);
    }

    function hideResults($results) {
        $results.slideUp(200);
        $results.find('.eakc-search-result-item').removeClass('active');
    }

    function generatePlaceholder(title) {
        const firstLetter = title.charAt(0).toUpperCase();
        const size = '60x60';
        const bgColor = '3b82f6';
        const textColor = 'ffffff';
        
        return `https://placehold.it/${size}/${bgColor}/${textColor}?text=${firstLetter}`;
    }

    function highlightQuery(text, query) {
        if (!query || query.length < 2) return text;
        
        const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function escapeRegex(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

})(jQuery);