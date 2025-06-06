/**
 * MIFECO SEO Admin JavaScript
 *
 * JavaScript for the SEO administration functionality.
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/admin/js
 */

(function( $ ) {
    'use strict';
    
    // Initialize when document is ready
    $(function() {
        
        /**
         * Tab Navigation
         */
        $('.mifeco-admin-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            
            var target = $(this).attr('href').substring(1);
            
            // Hide all tab contents
            $('.mifeco-tab-content').removeClass('active');
            
            // Show target tab content
            $('#' + target).addClass('active');
            
            // Update active class on tabs
            $('.mifeco-admin-tabs .nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Update URL hash
            window.location.hash = target;
        });
        
        // Check for hash in URL and switch to that tab
        if (window.location.hash) {
            var tabId = window.location.hash.substring(1);
            $('.mifeco-admin-tabs .nav-tab[href="#' + tabId + '"]').trigger('click');
        }
        
        /**
         * Meta Box Tabs
         */
        $('.mifeco-tab-button').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Hide all tab contents
            $('.mifeco-tab-content').removeClass('active');
            
            // Show target tab content
            $('.mifeco-tab-content[data-tab="' + tabId + '"]').addClass('active');
            
            // Update active class on tabs
            $('.mifeco-tab-button').removeClass('active');
            $(this).addClass('active');
        });
        
        /**
         * Media Uploader for Image Fields
         */
        $('.mifeco-upload-button').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var field = button.siblings('input[type="text"]');
            var preview = button.siblings('.mifeco-image-preview');
            
            // Create a new media frame
            var frame = wp.media({
                title: 'Select or Upload an Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });
            
            // When an image is selected, run a callback
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                
                field.val(attachment.url);
                
                // Update preview if it exists, or create it
                if (preview.length) {
                    preview.find('img').attr('src', attachment.url);
                } else {
                    preview = $('<div class="mifeco-image-preview"><img src="' + attachment.url + '" alt="Preview"></div>');
                    button.after(preview);
                }
            });
            
            // Finally, open the modal
            frame.open();
        });
        
        /**
         * SEO Meta Box Preview
         */
        // Title length counter
        $('#mifeco_meta_title').on('input', function() {
            var titleLength = $(this).val().length;
            $('.mifeco-title-length span').text(titleLength);
            
            // Add color coding based on length
            if (titleLength < 30) {
                $('.mifeco-title-length span').css('color', '#cc1818');
            } else if (titleLength > 60) {
                $('.mifeco-title-length span').css('color', '#cc1818');
            } else {
                $('.mifeco-title-length span').css('color', '#00a32a');
            }
            
            // Update preview
            updateSEOPreview();
        });
        
        // Description length counter
        $('#mifeco_meta_description').on('input', function() {
            var descriptionLength = $(this).val().length;
            $('.mifeco-description-length span').text(descriptionLength);
            
            // Add color coding based on length
            if (descriptionLength < 120) {
                $('.mifeco-description-length span').css('color', '#cc1818');
            } else if (descriptionLength > 160) {
                $('.mifeco-description-length span').css('color', '#cc1818');
            } else {
                $('.mifeco-description-length span').css('color', '#00a32a');
            }
            
            // Update preview
            updateSEOPreview();
        });
        
        // Initialize counters
        if ($('#mifeco_meta_title').length) {
            $('#mifeco_meta_title').trigger('input');
        }
        
        if ($('#mifeco_meta_description').length) {
            $('#mifeco_meta_description').trigger('input');
        }
        
        // Update SEO preview
        function updateSEOPreview() {
            var title = $('#mifeco_meta_title').val();
            var description = $('#mifeco_meta_description').val();
            
            // Use post title as fallback
            if (!title) {
                title = $('#title').val() + ' | ' + mifecoSEOAdmin.site_name;
            }
            
            // Use post excerpt as fallback
            if (!description && $('#excerpt').length) {
                description = $('#excerpt').val();
            }
            
            // Update the preview
            $('.mifeco-preview-title').text(title);
            $('.mifeco-preview-description').text(description);
        }
        
        // Initialize preview
        if ($('.mifeco-preview-title').length) {
            updateSEOPreview();
        }
        
        /**
         * Social Media Preview
         */
        // Social title update
        $('#mifeco_social_title').on('input', function() {
            var socialTitle = $(this).val();
            
            // Use SEO title or post title as fallback
            if (!socialTitle) {
                socialTitle = $('#mifeco_meta_title').val() || $('#title').val();
            }
            
            // Update the preview
            $('.mifeco-preview-title').text(socialTitle);
        });
        
        // Social description update
        $('#mifeco_social_description').on('input', function() {
            var socialDescription = $(this).val();
            
            // Use meta description or excerpt as fallback
            if (!socialDescription) {
                socialDescription = $('#mifeco_meta_description').val() || $('#excerpt').val();
            }
            
            // Update the preview
            $('.mifeco-preview-description').text(socialDescription);
        });
        
        /**
         * Schema Testing Tool
         */
        $('#mifeco-test-schema').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#mifeco-schema-test-results');
            var schemaType = $('#mifeco-schema-test-type').val();
            var postId = $('#mifeco-schema-test-post').val();
            
            // Show loading state
            $button.attr('disabled', true).text('Testing...');
            $results.html('<div class="mifeco-analyzing"><span class="mifeco-analyzing-spinner"></span> Testing schema markup...</div>');
            
            // Make AJAX request
            $.ajax({
                url: mifecoSEOAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mifeco_test_schema',
                    nonce: mifecoSEOAdmin.nonce,
                    schema_type: schemaType,
                    post_id: postId
                },
                success: function(response) {
                    if (response.success) {
                        // Format JSON with syntax highlighting
                        var schemaJson = response.data.schema;
                        schemaJson = schemaJson.replace(/<script[^>]*>(.*?)<\/script>/is, '$1');
                        
                        var formattedSchema = formatJSON(schemaJson);
                        
                        var resultsHtml = '<div class="mifeco-schema-test-success">' +
                            '<p>' + response.data.message + '</p>' +
                            '<div class="mifeco-schema-preview">' +
                            '<pre>' + formattedSchema + '</pre>' +
                            '</div>' +
                            '<p><a href="' + response.data.testing_url + '" target="_blank" class="button">Test with Google</a></p>' +
                            '</div>';
                        
                        $results.html(resultsHtml);
                    } else {
                        $results.html('<div class="mifeco-admin-notice error"><p>' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $results.html('<div class="mifeco-admin-notice error"><p>Server error. Please try again.</p></div>');
                },
                complete: function() {
                    $button.attr('disabled', false).text('Test Schema');
                }
            });
        });
        
        /**
         * Sitemap Validation Tool
         */
        $('#mifeco-validate-sitemap').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $results = $('#mifeco-sitemap-validation-results');
            
            // Show loading state
            $button.attr('disabled', true).text('Validating...');
            $results.html('<div class="mifeco-analyzing"><span class="mifeco-analyzing-spinner"></span> Validating sitemap...</div>');
            
            // Make AJAX request
            $.ajax({
                url: mifecoSEOAdmin.ajax_url,
                type: 'POST',
                data: {
                    action: 'mifeco_validate_sitemap',
                    nonce: mifecoSEOAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var resultsHtml = '<div class="mifeco-admin-notice success">' +
                            '<p><strong>Success!</strong> ' + response.data.message + '</p>' +
                            '<p><a href="' + response.data.url + '" target="_blank">View Sitemap</a></p>' +
                            '</div>';
                        
                        $results.html(resultsHtml);
                    } else {
                        $results.html('<div class="mifeco-admin-notice error"><p><strong>Error:</strong> ' + response.data.message + '</p></div>');
                    }
                },
                error: function() {
                    $results.html('<div class="mifeco-admin-notice error"><p>Server error. Please try again.</p></div>');
                },
                complete: function() {
                    $button.attr('disabled', false).text('Validate Sitemap');
                }
            });
        });
        
        /**
         * Copy to Clipboard Functionality
         */
        $('.mifeco-copy-button').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var textToCopy = $button.data('clipboard-text');
            var originalText = $button.text();
            
            // Create a temporary textarea element
            var textarea = document.createElement('textarea');
            textarea.value = textToCopy;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'absolute';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            
            // Select and copy
            textarea.select();
            document.execCommand('copy');
            
            // Remove temporary element
            document.body.removeChild(textarea);
            
            // Update button text to indicate success
            $button.text('Copied!');
            
            // Reset button text after a timeout
            setTimeout(function() {
                $button.text(originalText);
            }, 2000);
        });
        
        /**
         * Utilities
         */
        function formatJSON(json) {
            // Parse the JSON
            var obj = JSON.parse(json);
            
            // Convert back to string with indentation
            var formatted = JSON.stringify(obj, null, 2);
            
            // Escape HTML entities
            formatted = formatted.replace(/&/g, '&amp;')
                                 .replace(/</g, '&lt;')
                                 .replace(/>/g, '&gt;')
                                 .replace(/"/g, '&quot;')
                                 .replace(/'/g, '&#39;');
            
            // Add syntax highlighting
            formatted = formatted.replace(/"([^"]+)":/g, '<span class="json-key">"$1"</span>:');
            formatted = formatted.replace(/:(\s*)"([^"]+)"/g, ':<span class="json-string">$1"$2"</span>');
            formatted = formatted.replace(/:(\s*)(true|false|null)/g, ':<span class="json-boolean">$1$2</span>');
            formatted = formatted.replace(/:(\s*)(\d+\.?\d*)/g, ':<span class="json-number">$1$2</span>');
            
            return formatted;
        }
    });
    
})( jQuery );