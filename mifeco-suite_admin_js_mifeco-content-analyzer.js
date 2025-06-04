/**
 * MIFECO Content Analyzer
 *
 * JavaScript for the content analysis functionality in the post editor.
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/admin/js
 */

(function( $ ) {
    'use strict';
    
    // Initialize when document is ready
    $(function() {
        var contentAnalyzer = {
            
            init: function() {
                this.initVariables();
                this.bindEvents();
                this.initTabs();
                this.initMetaInputCounters();
            },
            
            initVariables: function() {
                this.$postForm = $('#post');
                this.$postContent = $('#content');
                this.$title = $('#title');
                this.$excerpt = $('#excerpt');
                this.$metaTitle = $('#mifeco_meta_title');
                this.$metaDescription = $('#mifeco_meta_description');
                this.$metaTitleLength = $('.mifeco-title-length span');
                this.$metaDescriptionLength = $('.mifeco-description-length span');
                this.$analyzeButton = $('#mifeco-analyze-content');
                this.$analysisResults = $('#mifeco-analysis-results');
                this.$scoreIndicator = $('#mifeco-seo-score');
                this.$scoreLabel = $('#mifeco-score-label');
                this.$scoreCircle = $('#mifeco-score-circle');
                this.$focusKeyword = $('#mifeco_focus_keyword');
                
                this.postId = $('#post_ID').val();
                this.analyzing = false;
                this.analysisTimer = null;
            },
            
            bindEvents: function() {
                var self = this;
                
                // Analyze content on button click
                this.$analyzeButton.on('click', function(e) {
                    e.preventDefault();
                    self.analyzeContent();
                });
                
                // Auto-update title and description lengths
                this.$metaTitle.on('input', function() {
                    self.updateTitleLength();
                });
                
                this.$metaDescription.on('input', function() {
                    self.updateDescriptionLength();
                });
                
                // Delayed content analysis when content is updated
                this.$postContent.on('input', this.debounce(function() {
                    self.$analyzeButton.html(mifecoSEO.content_updated);
                }, 500));
                
                // Update preview when title or description changes
                this.$metaTitle.on('input', this.debounce(function() {
                    self.updatePreview();
                }, 300));
                
                this.$metaDescription.on('input', this.debounce(function() {
                    self.updatePreview();
                }, 300));
                
                // Initialize preview on page load
                this.updatePreview();
                
                // Store focus keyword when changed
                this.$focusKeyword.on('input', this.debounce(function() {
                    self.saveFocusKeyword();
                }, 1000));
            },
            
            initTabs: function() {
                $('.mifeco-tab-button').on('click', function() {
                    var tabId = $(this).data('tab');
                    
                    // Hide all tab contents and deactivate all tabs
                    $('.mifeco-tab-content').removeClass('active');
                    $('.mifeco-tab-button').removeClass('active');
                    
                    // Show the selected tab content and activate the tab
                    $('.mifeco-tab-content[data-tab="' + tabId + '"]').addClass('active');
                    $(this).addClass('active');
                });
            },
            
            initMetaInputCounters: function() {
                this.updateTitleLength();
                this.updateDescriptionLength();
            },
            
            updateTitleLength: function() {
                var titleLength = this.$metaTitle.val().length;
                this.$metaTitleLength.text(titleLength);
                
                // Add color coding based on length
                if (titleLength < 30) {
                    this.$metaTitleLength.css('color', '#cc1818');
                } else if (titleLength > 60) {
                    this.$metaTitleLength.css('color', '#cc1818');
                } else {
                    this.$metaTitleLength.css('color', '#00a32a');
                }
            },
            
            updateDescriptionLength: function() {
                var descriptionLength = this.$metaDescription.val().length;
                this.$metaDescriptionLength.text(descriptionLength);
                
                // Add color coding based on length
                if (descriptionLength < 120) {
                    this.$metaDescriptionLength.css('color', '#cc1818');
                } else if (descriptionLength > 160) {
                    this.$metaDescriptionLength.css('color', '#cc1818');
                } else {
                    this.$metaDescriptionLength.css('color', '#00a32a');
                }
            },
            
            updatePreview: function() {
                var title = this.$metaTitle.val();
                if (!title) {
                    title = this.$title.val() + ' | ' + mifecoSEO.site_name;
                }
                
                var description = this.$metaDescription.val();
                if (!description) {
                    description = this.$excerpt.val();
                }
                
                $('.mifeco-preview-title').text(title);
                $('.mifeco-preview-description').text(description);
            },
            
            analyzeContent: function() {
                var self = this;
                
                if (this.analyzing) {
                    return;
                }
                
                this.analyzing = true;
                
                // Set analyzing state
                this.$analyzeButton.html('<span class="mifeco-analyzing-spinner"></span> ' + mifecoSEO.analyzing);
                this.$analyzeButton.attr('disabled', 'disabled');
                
                // Get focus keyword
                var focusKeyword = this.$focusKeyword.val();
                
                // Make AJAX request
                $.ajax({
                    url: mifecoSEO.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mifeco_analyze_content',
                        nonce: mifecoSEO.nonce,
                        post_id: this.postId,
                        focus_keyword: focusKeyword
                    },
                    success: function(response) {
                        if (response.success) {
                            self.displayAnalysisResults(response.data);
                        } else {
                            self.displayError(response.data.message || 'An error occurred during analysis.');
                        }
                    },
                    error: function() {
                        self.displayError('Server error. Please try again.');
                    },
                    complete: function() {
                        self.analyzing = false;
                        self.$analyzeButton.removeAttr('disabled');
                        self.$analyzeButton.text('Analyze Content');
                    }
                });
            },
            
            displayAnalysisResults: function(data) {
                var html = '';
                var results = data.results;
                
                // Update score indicator
                this.$scoreCircle.css('background-color', data.score_color);
                this.$scoreCircle.text(data.score);
                this.$scoreLabel.text(data.score_label);
                
                // Generate HTML for each section
                
                // Content Analysis
                if (results.content) {
                    html += this.getAnalysisSectionHtml('Content Analysis', {
                        'content_length': {
                            title: 'Content Length',
                            status: results.content.status,
                            message: results.content.message
                        }
                    });
                }
                
                // Keyword Analysis
                if (results.keyword) {
                    var keywordItems = {
                        'keyword_title': {
                            title: 'Focus Keyword in Title',
                            status: results.keyword.title.status,
                            message: results.keyword.title.message
                        },
                        'keyword_first_paragraph': {
                            title: 'Focus Keyword in First Paragraph',
                            status: results.keyword.first_paragraph.status,
                            message: results.keyword.first_paragraph.message
                        },
                        'keyword_density': {
                            title: 'Keyword Density',
                            status: results.keyword.density.status,
                            message: results.keyword.density.message
                        },
                        'keyword_headings': {
                            title: 'Focus Keyword in Headings',
                            status: results.keyword.headings.status,
                            message: results.keyword.headings.message
                        }
                    };
                    
                    if (results.keyword.excerpt) {
                        keywordItems['keyword_excerpt'] = {
                            title: 'Focus Keyword in Excerpt',
                            status: results.keyword.excerpt.status,
                            message: results.keyword.excerpt.message
                        };
                    }
                    
                    if (results.keyword.url) {
                        keywordItems['keyword_url'] = {
                            title: 'Focus Keyword in URL',
                            status: results.keyword.url.status,
                            message: results.keyword.url.message
                        };
                    }
                    
                    html += this.getAnalysisSectionHtml('Keyword Analysis', keywordItems);
                }
                
                // Readability Analysis
                if (results.readability) {
                    html += this.getAnalysisSectionHtml('Readability Analysis', {
                        'readability_score': {
                            title: 'Readability Score',
                            status: results.readability.status,
                            message: results.readability.message
                        },
                        'paragraph_length': {
                            title: 'Paragraph Length',
                            status: results.readability.paragraphs.status,
                            message: results.readability.paragraphs.message
                        },
                        'sentence_length': {
                            title: 'Sentence Length',
                            status: results.readability.sentences.status,
                            message: results.readability.sentences.message
                        }
                    });
                }
                
                // Content Structure Analysis
                if (results.structure) {
                    var structureItems = {
                        'headings': {
                            title: 'Headings',
                            status: results.structure.headings.status,
                            message: results.structure.headings.message
                        },
                        'paragraphs': {
                            title: 'Paragraphs',
                            status: results.structure.paragraphs.status,
                            message: results.structure.paragraphs.message
                        }
                    };
                    
                    if (results.structure.lists) {
                        structureItems['lists'] = {
                            title: 'Lists',
                            status: results.structure.lists.status,
                            message: results.structure.lists.message
                        };
                    }
                    
                    html += this.getAnalysisSectionHtml('Content Structure', structureItems);
                }
                
                // Link Analysis
                if (results.links) {
                    html += this.getAnalysisSectionHtml('Link Analysis', {
                        'link_count': {
                            title: 'Link Count',
                            status: results.links.count.status,
                            message: results.links.count.message
                        },
                        'nofollow': {
                            title: 'External Links (Nofollow)',
                            status: results.links.nofollow.status,
                            message: results.links.nofollow.message
                        },
                        'anchor_text': {
                            title: 'Anchor Text',
                            status: results.links.anchor_text.status,
                            message: results.links.anchor_text.message
                        }
                    });
                }
                
                // Image Analysis
                if (results.images) {
                    var imageItems = {
                        'image_count': {
                            title: 'Image Count',
                            status: results.images.count.status,
                            message: results.images.count.message
                        }
                    };
                    
                    if (results.images.alt) {
                        imageItems['image_alt'] = {
                            title: 'Image Alt Text',
                            status: results.images.alt.status,
                            message: results.images.alt.message
                        };
                    }
                    
                    if (results.images.keyword_alt) {
                        imageItems['keyword_alt'] = {
                            title: 'Focus Keyword in Alt Text',
                            status: results.images.keyword_alt.status,
                            message: results.images.keyword_alt.message
                        };
                    }
                    
                    if (results.images.size) {
                        imageItems['image_size'] = {
                            title: 'Image Size',
                            status: results.images.size.status,
                            message: results.images.size.message
                        };
                    }
                    
                    html += this.getAnalysisSectionHtml('Image Analysis', imageItems);
                }
                
                // Update results container
                this.$analysisResults.html(html);
                
                // Show results section
                $('.mifeco-content-analysis').show();
            },
            
            getAnalysisSectionHtml: function(sectionTitle, items) {
                var html = '<div class="mifeco-analysis-section">';
                html += '<h3 class="mifeco-analysis-section-title">' + sectionTitle + '</h3>';
                
                // Add items
                for (var key in items) {
                    if (items.hasOwnProperty(key)) {
                        var item = items[key];
                        var icon = '';
                        
                        switch (item.status) {
                            case 'good':
                                icon = '<span class="dashicons dashicons-yes-alt"></span>';
                                break;
                            case 'ok':
                                icon = '<span class="dashicons dashicons-info"></span>';
                                break;
                            case 'warning':
                                icon = '<span class="dashicons dashicons-warning"></span>';
                                break;
                            default:
                                icon = '<span class="dashicons dashicons-info"></span>';
                        }
                        
                        html += '<div class="mifeco-analysis-item ' + item.status + '">';
                        html += '<div class="mifeco-analysis-item-icon">' + icon + '</div>';
                        html += '<div class="mifeco-analysis-item-content">';
                        html += '<div class="mifeco-analysis-item-title">' + item.title + '</div>';
                        html += '<div class="mifeco-analysis-item-message">' + item.message + '</div>';
                        html += '</div>'; // End item content
                        html += '</div>'; // End item
                    }
                }
                
                html += '</div>'; // End section
                
                return html;
            },
            
            displayError: function(message) {
                this.$analysisResults.html('<div class="mifeco-admin-notice error"><p>' + message + '</p></div>');
            },
            
            saveFocusKeyword: function() {
                var self = this;
                var focusKeyword = this.$focusKeyword.val();
                
                // If we have a timer, clear it
                if (this.analysisTimer) {
                    clearTimeout(this.analysisTimer);
                }
                
                // Save the focus keyword
                $.ajax({
                    url: mifecoSEO.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mifeco_save_focus_keyword',
                        nonce: mifecoSEO.nonce,
                        post_id: this.postId,
                        focus_keyword: focusKeyword
                    }
                });
                
                // Queue up an analysis
                this.analysisTimer = setTimeout(function() {
                    self.$analyzeButton.trigger('click');
                }, 1500);
            },
            
            // Utility function to debounce events
            debounce: function(func, wait, immediate) {
                var timeout;
                return function() {
                    var context = this, args = arguments;
                    var later = function() {
                        timeout = null;
                        if (!immediate) func.apply(context, args);
                    };
                    var callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func.apply(context, args);
                };
            }
        };
        
        // Initialize content analyzer
        contentAnalyzer.init();
    });
    
})( jQuery );