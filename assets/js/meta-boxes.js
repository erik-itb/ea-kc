/**
 * Meta Boxes JavaScript
 * Handles dynamic functionality for KC and Docket meta boxes
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initResourceManagement();
        initDocketCategoryManagement();
        initSpanishContentToggle();
        initSortable();
        initIconPicker();
        initFileUpload();
    });

    /**
     * Initialize resource management for KC articles
     */
    function initResourceManagement() {
        // Add new resource
        $(document).on('click', '.eakc-add-resource-btn', function(e) {
            e.preventDefault();
            addResource();
        });

        // Remove resource
        $(document).on('click', '.eakc-remove-resource', function(e) {
            e.preventDefault();
            if (confirm(eakc_meta.strings.confirm_remove)) {
                $(this).closest('.eakc-resource-item').remove();
                reindexResources();
            }
        });

        // Toggle resource content
        $(document).on('click', '.eakc-toggle-resource', function(e) {
            e.preventDefault();
            var $content = $(this).closest('.eakc-resource-item').find('.eakc-resource-content');
            var $icon = $(this).find('.dashicons');
            
            $content.slideToggle();
            $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
        });

        // Update resource title display when typing
        $(document).on('input', '.eakc-resource-title-input', function() {
            var title = $(this).val() || eakc_meta.strings.new_resource || 'New Resource';
            $(this).closest('.eakc-resource-item').find('.eakc-resource-title-display').text(title);
        });
    }

    /**
     * Initialize docket category management
     */
    function initDocketCategoryManagement() {
        // Add new category
        $(document).on('click', '.eakc-add-category-btn', function(e) {
            e.preventDefault();
            addDocketCategory();
        });

        // Remove category
        $(document).on('click', '.eakc-remove-category', function(e) {
            e.preventDefault();
            if (confirm(eakc_meta.strings.confirm_remove)) {
                $(this).closest('.eakc-category-item').remove();
                reindexCategories();
            }
        });

        // Toggle category content
        $(document).on('click', '.eakc-toggle-category', function(e) {
            e.preventDefault();
            var $content = $(this).closest('.eakc-category-item').find('.eakc-category-content');
            var $icon = $(this).find('.dashicons');
            
            $content.slideToggle();
            $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
        });

        // Update category title display
        $(document).on('input', '.eakc-category-title-input', function() {
            var title = $(this).val() || 'New Category';
            $(this).closest('.eakc-category-item').find('.eakc-category-title-display').text(title);
        });

        // Add new document to category
        $(document).on('click', '.eakc-add-document', function(e) {
            e.preventDefault();
            addDocument($(this));
        });

        // Remove document
        $(document).on('click', '.eakc-remove-document', function(e) {
            e.preventDefault();
            if (confirm(eakc_meta.strings.confirm_remove)) {
                $(this).closest('.eakc-document-item').remove();
            }
        });
    }

    /**
     * Initialize Spanish content toggle
     */
    function initSpanishContentToggle() {
        // Handle Spanish version available toggle
        $(document).on('change', '.eakc-spanish-toggle', function() {
            var $fields = $(this).closest('.eakc-spanish-content').find('.eakc-spanish-fields');
            
            if ($(this).is(':checked')) {
                $fields.slideDown();
            } else {
                $fields.slideUp();
            }
        });
        
        // Handle Spanish content toggle (mutual exclusion)
        $(document).on('change', '.eakc-spanish-content-toggle', function() {
            var $container = $(this).closest('.eakc-spanish-content');
            var $spanishAvailableToggle = $container.find('.eakc-spanish-toggle');
            var $spanishFields = $container.find('.eakc-spanish-fields');
            
            if ($(this).is(':checked')) {
                // If marking as Spanish content, disable and uncheck Spanish version available
                $spanishAvailableToggle.prop('checked', false).prop('disabled', true);
                $spanishFields.slideUp();
                
                // Clear the selected Spanish post
                $container.find('#eakc_spanish_post_id').val('');
            } else {
                // If unchecking Spanish content, re-enable Spanish version available
                $spanishAvailableToggle.prop('disabled', false);
            }
        });
        
        // Handle Spanish version available toggle (mutual exclusion)
        $(document).on('change', '.eakc-spanish-toggle', function() {
            var $container = $(this).closest('.eakc-spanish-content');
            var $spanishContentToggle = $container.find('.eakc-spanish-content-toggle');
            
            if ($(this).is(':checked')) {
                // If marking Spanish version available, disable Spanish content
                $spanishContentToggle.prop('disabled', true);
            } else {
                // If unchecking Spanish version available, re-enable Spanish content
                $spanishContentToggle.prop('disabled', false);
            }
        });
        
        // Initialize states on page load
        $(document).ready(function() {
            $('.eakc-spanish-content').each(function() {
                var $container = $(this);
                var $spanishContentToggle = $container.find('.eakc-spanish-content-toggle');
                var $spanishAvailableToggle = $container.find('.eakc-spanish-toggle');
                
                // Set initial disabled states based on checked status
                if ($spanishContentToggle.is(':checked')) {
                    $spanishAvailableToggle.prop('disabled', true);
                } else if ($spanishAvailableToggle.is(':checked')) {
                    $spanishContentToggle.prop('disabled', true);
                }
            });
        });
    }

    /**
     * Initialize sortable functionality
     */
    function initSortable() {
        // Make resources sortable
        $('.eakc-resources-list').sortable({
            handle: '.eakc-resource-handle',
            placeholder: 'eakc-sortable-placeholder',
            update: function() {
                reindexResources();
            }
        });

        // Make categories sortable
        $('.eakc-categories-list').sortable({
            handle: '.eakc-category-handle',
            placeholder: 'eakc-sortable-placeholder',
            update: function() {
                reindexCategories();
            }
        });
    }

    /**
     * Initialize icon picker (placeholder for future implementation)
     */
    function initIconPicker() {
        $(document).on('click', '.eakc-choose-icon', function(e) {
            e.preventDefault();
            // TODO: Implement icon picker modal
            alert('Icon picker will be implemented in the next phase');
        });

        $(document).on('click', '.eakc-remove-icon', function(e) {
            e.preventDefault();
            $('#eakc_featured_icon').val('');
            $('.eakc-icon-preview').empty();
            $(this).hide();
        });
    }

    /**
     * Initialize file upload functionality
     */
    function initFileUpload() {
        $(document).on('click', '.eakc-upload-file', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $input = $button.siblings('input[type="url"]');
            
            // Create WordPress media uploader
            var frame = wp.media({
                title: 'Select or Upload File',
                button: {
                    text: 'Use this file'
                },
                multiple: false
            });

            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $input.val(attachment.url);
                
                // Try to auto-detect file type based on URL
                var fileExtension = attachment.url.split('.').pop().toLowerCase();
                var $typeSelect = $input.closest('tr').find('select[name*="[type]"]');
                
                if ($typeSelect.length) {
                    var typeMapping = {
                        'pdf': 'pdf',
                        'doc': 'doc',
                        'docx': 'doc',
                        'xls': 'sheet',
                        'xlsx': 'sheet',
                        'csv': 'sheet',
                        'ppt': 'presentation',
                        'pptx': 'presentation',
                        'mp4': 'video',
                        'avi': 'video',
                        'mov': 'video',
                        'mp3': 'audio',
                        'wav': 'audio',
                        'jpg': 'image',
                        'jpeg': 'image',
                        'png': 'image',
                        'gif': 'image'
                    };
                    
                    if (typeMapping[fileExtension]) {
                        $typeSelect.val(typeMapping[fileExtension]);
                    }
                }
            });

            frame.open();
        });
    }

    /**
     * Add new resource
     */
    function addResource() {
        var $container = $('.eakc-resources-list');
        var index = $container.find('.eakc-resource-item').length;
        var template = $('#eakc-resource-template').html();
        
        // Replace template placeholders
        template = template.replace(/\{\{INDEX\}\}/g, index);
        
        var $newItem = $(template);
        $container.append($newItem);
        
        // Show the content immediately for new items
        $newItem.find('.eakc-resource-content').show();
        $newItem.find('.eakc-toggle-resource .dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
        
        // Focus on title field
        $newItem.find('.eakc-resource-title-input').focus();
        
        // Update sortable
        $container.sortable('refresh');
    }

    /**
     * Add new docket category
     */
    function addDocketCategory() {
        var $container = $('.eakc-categories-list');
        var index = $container.find('.eakc-category-item').length;
        var template = $('#eakc-category-template').html();
        
        // Replace template placeholders
        template = template.replace(/\{\{INDEX\}\}/g, index);
        
        var $newItem = $(template);
        $container.append($newItem);
        
        // Show the content immediately for new items
        $newItem.find('.eakc-category-content').show();
        $newItem.find('.eakc-toggle-category .dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
        
        // Focus on title field
        $newItem.find('.eakc-category-title-input').focus();
        
        // Update sortable
        $container.sortable('refresh');
    }

    /**
     * Add new document to category
     */
    function addDocument($addButton) {
        var $categoryItem = $addButton.closest('.eakc-category-item');
        var $documentsList = $categoryItem.find('.eakc-documents-list');
        var categoryIndex = $categoryItem.data('index');
        var docIndex = $documentsList.find('.eakc-document-item').length;
        
        var documentHtml = `
            <div class="eakc-document-item">
                <div class="eakc-document-fields">
                    <input type="text" 
                           name="eakc_categories[${categoryIndex}][documents][${docIndex}][title]" 
                           placeholder="Document title" 
                           class="regular-text">
                    
                    <select name="eakc_categories[${categoryIndex}][documents][${docIndex}][type]">
                        <option value="pdf">PDF</option>
                        <option value="doc">Word Doc</option>
                        <option value="sheet">Spreadsheet</option>
                        <option value="presentation">Presentation</option>
                        <option value="external">External Link</option>
                    </select>
                    
                    <input type="url" 
                           name="eakc_categories[${categoryIndex}][documents][${docIndex}][url]" 
                           placeholder="Document URL" 
                           class="regular-text">
                    
                    <input type="date" 
                           name="eakc_categories[${categoryIndex}][documents][${docIndex}][date]" 
                           class="regular-text">
                    
                    <button type="button" class="button-link eakc-remove-document">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
        `;
        
        $documentsList.append(documentHtml);
        
        // Focus on the new document title field
        $documentsList.find('.eakc-document-item').last().find('input[type="text"]').first().focus();
    }

    /**
     * Reindex resources after sorting/removing
     */
    function reindexResources() {
        $('.eakc-resources-list .eakc-resource-item').each(function(index) {
            $(this).attr('data-index', index);
            
            // Update all input names
            $(this).find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name && name.indexOf('eakc_resources[') === 0) {
                    var newName = name.replace(/eakc_resources\[\d+\]/, 'eakc_resources[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    /**
     * Reindex categories after sorting/removing
     */
    function reindexCategories() {
        $('.eakc-categories-list .eakc-category-item').each(function(categoryIndex) {
            $(this).attr('data-index', categoryIndex);
            
            // Update category input names
            $(this).find('input, select, textarea').each(function() {
                var name = $(this).attr('name');
                if (name && name.indexOf('eakc_categories[') === 0) {
                    var newName = name.replace(/eakc_categories\[\d+\]/, 'eakc_categories[' + categoryIndex + ']');
                    $(this).attr('name', newName);
                }
            });
            
            // Reindex documents within this category
            $(this).find('.eakc-document-item').each(function(docIndex) {
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name && name.indexOf('documents[') > -1) {
                        var newName = name.replace(/documents\[\d+\]/, 'documents[' + docIndex + ']');
                        $(this).attr('name', newName);
                    }
                });
            });
        });
    }

    /**
     * Show loading state for buttons
     */
    function showButtonLoading($button, text) {
        text = text || 'Loading...';
        $button.prop('disabled', true).text(text);
    }

    /**
     * Hide loading state for buttons
     */
    function hideButtonLoading($button, originalText) {
        $button.prop('disabled', false).text(originalText);
    }

})(jQuery);