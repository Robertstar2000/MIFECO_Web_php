/**
 * All of the JavaScript code for the SaaS public-facing functionality.
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/public/js
 */

(function($) {
    'use strict';

    // Product Showcase Modal Functionality
    function initProductModal() {
        const modal = document.getElementById('mifeco-product-modal');
        const modalContent = modal.querySelector('.mifeco-product-modal-body');
        const closeBtn = modal.querySelector('.mifeco-product-modal-close');
        
        // Close modal when clicking the X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // "Learn More" links
        document.querySelectorAll('.mifeco-product-learn-more').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const productId = this.getAttribute('data-product-id');
                showProductDetails(productId, modalContent);
                modal.style.display = 'block';
            });
        });
    }
    
    // Show product details in modal
    function showProductDetails(productId, container) {
        // In a real implementation, this would fetch data via AJAX
        // For demonstration, we'll use placeholder content
        
        let productName, productDescription, productFeatures, productLevel;
        
        switch (productId) {
            case 'advanced-research-tool':
                productName = 'Advanced Research Tool';
                productDescription = 'Powerful research tool for business intelligence and market analysis.';
                productFeatures = [
                    'Data collection and analysis',
                    'Market trends visualization',
                    'Competitor intelligence',
                    'Industry reports generation',
                    'Custom data exports'
                ];
                productLevel = 'Basic Plan & Above';
                break;
                
            case 'business-problem-solver':
                productName = 'Ultimate Business Problem Solver';
                productDescription = 'Advanced problem-solving system for identifying root causes and generating effective solutions to business challenges.';
                productFeatures = [
                    'Root cause analysis',
                    'Solution generation framework',
                    'Impact assessment tools',
                    'Implementation roadmap planner',
                    'Decision support system',
                    'Collaborative workspace'
                ];
                productLevel = 'Premium Plan & Above';
                break;
                
            case 'proposal-evaluation-tool':
                productName = 'Proposal Evaluation Tool';
                productDescription = 'Comprehensive system for evaluating, scoring, and optimizing business proposals and RFPs.';
                productFeatures = [
                    'Multi-criteria evaluation framework',
                    'Scoring and weighting system',
                    'Comparative analysis',
                    'Risk assessment',
                    'ROI calculator',
                    'Proposal optimization',
                    'Team collaboration tools'
                ];
                productLevel = 'Enterprise Plan Only';
                break;
                
            default:
                productName = 'Product Details';
                productDescription = 'No product information available.';
                productFeatures = [];
                productLevel = '';
        }
        
        // Build the features list HTML
        let featuresHtml = '';
        if (productFeatures.length > 0) {
            featuresHtml = '<h3>Key Features</h3><ul>';
            productFeatures.forEach(function(feature) {
                featuresHtml += '<li>' + feature + '</li>';
            });
            featuresHtml += '</ul>';
        }
        
        // Set content
        container.innerHTML = `
            <div class="mifeco-modal-product-details">
                <h2>${productName}</h2>
                <div class="mifeco-modal-product-level">${productLevel}</div>
                <p class="mifeco-modal-product-description">${productDescription}</p>
                ${featuresHtml}
                <div class="mifeco-modal-product-actions">
                    <a href="/products/${productId}" class="mifeco-btn mifeco-btn-primary">Learn More</a>
                </div>
            </div>
        `;
    }
    
    // Payment Modal Functionality
    function initPaymentModal() {
        const modal = document.getElementById('mifeco-payment-modal');
        if (!modal) return;
        
        const closeBtn = modal.querySelector('.mifeco-payment-modal-close');
        const cancelBtn = document.getElementById('mifeco-cancel-payment');
        const planNameElement = document.getElementById('mifeco-plan-name');
        const planIdInput = document.getElementById('mifeco-plan-id');
        const priceIdInput = document.getElementById('mifeco-price-id');
        
        // Close modal when clicking the X
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
        
        // Close modal when clicking Cancel button
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
        
        // Subscribe buttons
        document.querySelectorAll('.mifeco-subscribe-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const plan = this.getAttribute('data-plan');
                const priceId = this.getAttribute('data-price-id');
                let planName = '';
                
                switch (plan) {
                    case 'basic':
                        planName = 'Basic Plan';
                        break;
                    case 'premium':
                        planName = 'Premium Plan';
                        break;
                    case 'enterprise':
                        planName = 'Enterprise Plan';
                        break;
                }
                
                planNameElement.textContent = planName;
                planIdInput.value = plan;
                priceIdInput.value = priceId;
                
                modal.style.display = 'block';
                
                // In a real implementation, initialize Stripe Elements here
                initStripeElements();
            });
        });
    }
    
    // Initialize Stripe Elements
    function initStripeElements() {
        // This function would normally initialize Stripe Elements for payment
        // For demonstration, we'll just log a message
        console.log('Stripe Elements would be initialized here in a real implementation');
    }
    
    // Handle subscription management
    function initSubscriptionManagement() {
        // Cancel subscription
        document.querySelectorAll('.mifeco-cancel-subscription-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel your subscription? You will lose access to your subscribed products at the end of your billing period.')) {
                    // In a real implementation, this would make an AJAX call to cancel
                    // For demonstration, we'll show an alert
                    alert('Subscription cancellation would be processed here in a real implementation');
                }
            });
        });
        
        // Manage subscription
        document.querySelectorAll('.mifeco-manage-subscription-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                // In a real implementation, this would redirect to a Stripe Customer Portal
                // For demonstration, we'll show an alert
                alert('This would redirect to Stripe Customer Portal in a real implementation');
            });
        });
    }
    
    // Initialize SaaS product interface navigation
    function initProductInterface() {
        // Check if we're on a product interface page
        const productInterface = document.querySelector('.mifeco-product-interface');
        if (!productInterface) return;
        
        // Nav menu functionality
        document.querySelectorAll('.mifeco-nav-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                const sectionId = this.getAttribute('data-section');
                const targetSection = document.getElementById(sectionId + '-section');
                
                if (targetSection) {
                    // Update active navigation item
                    document.querySelectorAll('.mifeco-nav-item').forEach(function(item) {
                        item.classList.remove('active');
                    });
                    this.closest('.mifeco-nav-item').classList.add('active');
                    
                    // Update active section
                    document.querySelectorAll('.mifeco-section').forEach(function(section) {
                        section.classList.remove('active');
                    });
                    targetSection.classList.add('active');
                }
            });
        });
        
        // Action cards on dashboard
        document.querySelectorAll('.mifeco-action-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                e.preventDefault();
                
                const sectionId = this.getAttribute('data-section');
                const navLink = document.querySelector(`.mifeco-nav-link[data-section="${sectionId}"]`);
                
                if (navLink) {
                    navLink.click();
                }
            });
        });
        
        // Research Tool functionality
        const trendsForm = document.getElementById('market-trends-form');
        if (trendsForm) {
            trendsForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // In a real implementation, this would make an AJAX call
                // For demonstration, we'll show sample results
                showTrendsResults();
            });
        }
        
        // Competitor Analysis functionality
        const competitorForm = document.getElementById('competitor-form');
        if (competitorForm) {
            competitorForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // In a real implementation, this would make an AJAX call
                // For demonstration, we'll show sample results
                showCompetitorResults();
            });
            
            // Add competitor button
            const addCompetitorBtn = competitorForm.querySelector('.mifeco-add-competitor');
            if (addCompetitorBtn) {
                addCompetitorBtn.addEventListener('click', function() {
                    const competitorInputs = competitorForm.querySelector('.mifeco-competitor-inputs');
                    const newInput = document.createElement('div');
                    newInput.className = 'mifeco-competitor-input';
                    
                    const count = competitorInputs.children.length + 1;
                    newInput.innerHTML = `<input type="text" name="competitors[]" placeholder="Competitor ${count}">`;
                    
                    competitorInputs.appendChild(newInput);
                });
            }
        }
    }
    
    // Show sample market trends results
    function showTrendsResults() {
        const resultsContainer = document.getElementById('trends-results');
        if (!resultsContainer) return;
        
        // Build sample results HTML
        resultsContainer.innerHTML = `
            <div class="mifeco-results-header">
                <h3>Market Trends Analysis Results</h3>
                <div class="mifeco-results-actions">
                    <button class="mifeco-btn mifeco-btn-outline">
                        <span class="dashicons dashicons-download"></span> Export
                    </button>
                    <button class="mifeco-btn mifeco-btn-outline">
                        <span class="dashicons dashicons-saved"></span> Save
                    </button>
                </div>
            </div>
            
            <div class="mifeco-results-content">
                <div class="mifeco-results-chart">
                    <h4>Trend Visualization</h4>
                    <div class="mifeco-chart-placeholder">
                        <img src="/wp-content/plugins/mifeco-suite/public/images/trend-chart-sample.png" alt="Trend Chart" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                
                <div class="mifeco-results-insights">
                    <h4>Key Insights</h4>
                    <ul class="mifeco-insights-list">
                        <li>Steady growth in Q1 2025</li>
                        <li>Market disruption in March 2025</li>
                        <li>Recovery trend beginning in May 2025</li>
                        <li>Overall growth rate of 12.3%</li>
                        <li>Emerging trend in sustainable technologies</li>
                    </ul>
                </div>
                
                <div class="mifeco-results-forecast">
                    <h4>Market Forecast</h4>
                    <table class="mifeco-forecast-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Growth Rate</th>
                                <th>Market Value</th>
                                <th>Confidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Q2 2025</td>
                                <td>4.2%</td>
                                <td>$5.1B</td>
                                <td>High</td>
                            </tr>
                            <tr>
                                <td>Q3 2025</td>
                                <td>3.8%</td>
                                <td>$5.3B</td>
                                <td>Medium</td>
                            </tr>
                            <tr>
                                <td>Q4 2025</td>
                                <td>5.1%</td>
                                <td>$5.6B</td>
                                <td>Medium</td>
                            </tr>
                            <tr>
                                <td>Q1 2026</td>
                                <td>6.3%</td>
                                <td>$5.9B</td>
                                <td>Low</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    // Show sample competitor analysis results
    function showCompetitorResults() {
        const resultsContainer = document.getElementById('competitor-results');
        if (!resultsContainer) return;
        
        // Build sample results HTML
        resultsContainer.innerHTML = `
            <div class="mifeco-results-header">
                <h3>Competitor Analysis Results</h3>
                <div class="mifeco-results-actions">
                    <button class="mifeco-btn mifeco-btn-outline">
                        <span class="dashicons dashicons-download"></span> Export
                    </button>
                    <button class="mifeco-btn mifeco-btn-outline">
                        <span class="dashicons dashicons-saved"></span> Save
                    </button>
                </div>
            </div>
            
            <div class="mifeco-results-content">
                <div class="mifeco-results-chart">
                    <h4>Market Share Comparison</h4>
                    <div class="mifeco-chart-placeholder">
                        <img src="/wp-content/plugins/mifeco-suite/public/images/market-share-sample.png" alt="Market Share Chart" style="max-width: 100%; height: auto;">
                    </div>
                </div>
                
                <div class="mifeco-competitor-details">
                    <h4>Competitor Details</h4>
                    
                    <div class="mifeco-competitor-cards">
                        <div class="mifeco-competitor-card">
                            <div class="mifeco-competitor-header">
                                <h5>Company A</h5>
                                <span class="mifeco-market-share">35%</span>
                            </div>
                            <div class="mifeco-competitor-metrics">
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Growth</span>
                                    <span class="mifeco-metric-value">+5.2%</span>
                                </div>
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Innovation</span>
                                    <span class="mifeco-metric-value">High</span>
                                </div>
                            </div>
                            <div class="mifeco-competitor-strengths">
                                <h6>Strengths</h6>
                                <p>Strong brand recognition, innovative product lineup</p>
                            </div>
                            <div class="mifeco-competitor-weaknesses">
                                <h6>Weaknesses</h6>
                                <p>Higher price point, limited geographic reach</p>
                            </div>
                        </div>
                        
                        <div class="mifeco-competitor-card">
                            <div class="mifeco-competitor-header">
                                <h5>Company B</h5>
                                <span class="mifeco-market-share">28%</span>
                            </div>
                            <div class="mifeco-competitor-metrics">
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Growth</span>
                                    <span class="mifeco-metric-value">+2.1%</span>
                                </div>
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Innovation</span>
                                    <span class="mifeco-metric-value">Medium</span>
                                </div>
                            </div>
                            <div class="mifeco-competitor-strengths">
                                <h6>Strengths</h6>
                                <p>Cost leadership, extensive distribution network</p>
                            </div>
                            <div class="mifeco-competitor-weaknesses">
                                <h6>Weaknesses</h6>
                                <p>Slower innovation cycle, quality concerns</p>
                            </div>
                        </div>
                        
                        <div class="mifeco-competitor-card">
                            <div class="mifeco-competitor-header">
                                <h5>Company C</h5>
                                <span class="mifeco-market-share">18%</span>
                            </div>
                            <div class="mifeco-competitor-metrics">
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Growth</span>
                                    <span class="mifeco-metric-value">+7.8%</span>
                                </div>
                                <div class="mifeco-metric">
                                    <span class="mifeco-metric-label">Innovation</span>
                                    <span class="mifeco-metric-value">High</span>
                                </div>
                            </div>
                            <div class="mifeco-competitor-strengths">
                                <h6>Strengths</h6>
                                <p>Disruptive technology, strong growth</p>
                            </div>
                            <div class="mifeco-competitor-weaknesses">
                                <h6>Weaknesses</h6>
                                <p>Limited product range, new market entrant</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mifeco-competitive-analysis">
                    <h4>Competitive Landscape Analysis</h4>
                    <div class="mifeco-analysis-content">
                        <p>The market is experiencing consolidation with three major players dominating. Company A maintains the largest market share but is seeing slower growth compared to the agile newcomer Company C.</p>
                        <p>Innovation in AI and machine learning is driving product development across all competitors, with Companies A and C leading the innovation race.</p>
                        <p>Price competition is intense, with Company B using cost leadership to maintain its position despite slower growth and innovation pace.</p>
                    </div>
                </div>
            </div>
        `;
    }
    
    // Initialize everything on document ready
    $(document).ready(function() {
        // Initialize product showcase modal
        initProductModal();
        
        // Initialize payment modal
        initPaymentModal();
        
        // Initialize subscription management
        initSubscriptionManagement();
        
        // Initialize product interface
        initProductInterface();
    });

})(jQuery);