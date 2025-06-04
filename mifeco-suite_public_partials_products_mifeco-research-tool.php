<?php
/**
 * Provide a public-facing view for the Advanced Research Tool
 *
 * This file is used to markup the public-facing aspects of the Advanced Research Tool.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    Mifeco_Suite
 * @subpackage Mifeco_Suite/public/partials/products
 */

// Get current user
$user = wp_get_current_user();
?>

<div class="mifeco-product-interface mifeco-research-tool">
    <div class="mifeco-product-header">
        <div class="mifeco-product-logo">
            <span class="mifeco-logo-icon">🔍</span>
            <h1><?php _e('Advanced Research Tool', 'mifeco-suite'); ?></h1>
        </div>
        
        <div class="mifeco-user-controls">
            <div class="mifeco-user-info">
                <?php echo get_avatar($user->ID, 32); ?>
                <span class="mifeco-username"><?php echo esc_html($user->display_name); ?></span>
            </div>
            
            <div class="mifeco-controls">
                <a href="<?php echo esc_url(site_url('/my-account/')); ?>" class="mifeco-control-link" title="<?php _e('Account', 'mifeco-suite'); ?>">
                    <span class="dashicons dashicons-admin-users"></span>
                </a>
                <a href="<?php echo esc_url(site_url('/')); ?>" class="mifeco-control-link" title="<?php _e('Back to Site', 'mifeco-suite'); ?>">
                    <span class="dashicons dashicons-admin-home"></span>
                </a>
                <a href="<?php echo esc_url(wp_logout_url(site_url('/'))); ?>" class="mifeco-control-link" title="<?php _e('Logout', 'mifeco-suite'); ?>">
                    <span class="dashicons dashicons-exit"></span>
                </a>
            </div>
        </div>
    </div>
    
    <div class="mifeco-product-content">
        <div class="mifeco-sidebar">
            <div class="mifeco-navigation">
                <ul class="mifeco-nav-menu">
                    <li class="mifeco-nav-item active">
                        <a href="#dashboard" class="mifeco-nav-link" data-section="dashboard">
                            <span class="dashicons dashicons-dashboard"></span>
                            <?php _e('Dashboard', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#market-trends" class="mifeco-nav-link" data-section="market-trends">
                            <span class="dashicons dashicons-chart-line"></span>
                            <?php _e('Market Trends', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#competitor-analysis" class="mifeco-nav-link" data-section="competitor-analysis">
                            <span class="dashicons dashicons-groups"></span>
                            <?php _e('Competitor Analysis', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#market-size" class="mifeco-nav-link" data-section="market-size">
                            <span class="dashicons dashicons-chart-pie"></span>
                            <?php _e('Market Size', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#reports" class="mifeco-nav-link" data-section="reports">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php _e('Reports', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#saved-research" class="mifeco-nav-link" data-section="saved-research">
                            <span class="dashicons dashicons-saved"></span>
                            <?php _e('Saved Research', 'mifeco-suite'); ?>
                        </a>
                    </li>
                    <li class="mifeco-nav-item">
                        <a href="#settings" class="mifeco-nav-link" data-section="settings">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Settings', 'mifeco-suite'); ?>
                        </a>
                    </li>
                </ul>
            </div>
            
            <div class="mifeco-subscription-info">
                <div class="mifeco-plan-badge">
                    <?php 
                        $saas_user = new Mifeco_SaaS_User();
                        $plan = $saas_user->get_user_subscription_plan();
                        
                        switch ($plan) {
                            case 'basic':
                                _e('Basic Plan', 'mifeco-suite');
                                break;
                            case 'premium':
                                _e('Premium Plan', 'mifeco-suite');
                                break;
                            case 'enterprise':
                                _e('Enterprise Plan', 'mifeco-suite');
                                break;
                            default:
                                _e('No Subscription', 'mifeco-suite');
                        }
                    ?>
                </div>
                
                <a href="<?php echo esc_url(site_url('/subscriptions/')); ?>" class="mifeco-upgrade-link">
                    <?php _e('Upgrade', 'mifeco-suite'); ?> ↗
                </a>
            </div>
        </div>
        
        <div class="mifeco-main-content">
            <!-- Dashboard Section -->
            <div class="mifeco-section active" id="dashboard-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Research Dashboard', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-dashboard-overview">
                    <div class="mifeco-overview-stat">
                        <div class="mifeco-stat-value">3</div>
                        <div class="mifeco-stat-label"><?php _e('Saved Reports', 'mifeco-suite'); ?></div>
                    </div>
                    
                    <div class="mifeco-overview-stat">
                        <div class="mifeco-stat-value">12</div>
                        <div class="mifeco-stat-label"><?php _e('Recent Searches', 'mifeco-suite'); ?></div>
                    </div>
                    
                    <div class="mifeco-overview-stat">
                        <div class="mifeco-stat-value">5</div>
                        <div class="mifeco-stat-label"><?php _e('Tracked Industries', 'mifeco-suite'); ?></div>
                    </div>
                </div>
                
                <div class="mifeco-dashboard-actions">
                    <h3><?php _e('Quick Actions', 'mifeco-suite'); ?></h3>
                    
                    <div class="mifeco-action-cards">
                        <a href="#market-trends" class="mifeco-action-card" data-section="market-trends">
                            <div class="mifeco-action-icon">
                                <span class="dashicons dashicons-chart-line"></span>
                            </div>
                            <div class="mifeco-action-label"><?php _e('Analyze Market Trends', 'mifeco-suite'); ?></div>
                        </a>
                        
                        <a href="#competitor-analysis" class="mifeco-action-card" data-section="competitor-analysis">
                            <div class="mifeco-action-icon">
                                <span class="dashicons dashicons-groups"></span>
                            </div>
                            <div class="mifeco-action-label"><?php _e('Competitor Analysis', 'mifeco-suite'); ?></div>
                        </a>
                        
                        <a href="#market-size" class="mifeco-action-card" data-section="market-size">
                            <div class="mifeco-action-icon">
                                <span class="dashicons dashicons-chart-pie"></span>
                            </div>
                            <div class="mifeco-action-label"><?php _e('Estimate Market Size', 'mifeco-suite'); ?></div>
                        </a>
                        
                        <a href="#reports" class="mifeco-action-card" data-section="reports">
                            <div class="mifeco-action-icon">
                                <span class="dashicons dashicons-media-document"></span>
                            </div>
                            <div class="mifeco-action-label"><?php _e('Generate a Report', 'mifeco-suite'); ?></div>
                        </a>
                    </div>
                </div>
                
                <div class="mifeco-dashboard-recent">
                    <h3><?php _e('Recent Research', 'mifeco-suite'); ?></h3>
                    
                    <div class="mifeco-recent-items">
                        <div class="mifeco-recent-item">
                            <div class="mifeco-recent-icon">
                                <span class="dashicons dashicons-chart-line"></span>
                            </div>
                            <div class="mifeco-recent-content">
                                <div class="mifeco-recent-title"><?php _e('Technology Industry Trends', 'mifeco-suite'); ?></div>
                                <div class="mifeco-recent-meta"><?php _e('2 days ago', 'mifeco-suite'); ?></div>
                            </div>
                            <div class="mifeco-recent-actions">
                                <button class="mifeco-btn mifeco-btn-text" title="<?php _e('View', 'mifeco-suite'); ?>">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mifeco-recent-item">
                            <div class="mifeco-recent-icon">
                                <span class="dashicons dashicons-groups"></span>
                            </div>
                            <div class="mifeco-recent-content">
                                <div class="mifeco-recent-title"><?php _e('Competitor Analysis: Financial Sector', 'mifeco-suite'); ?></div>
                                <div class="mifeco-recent-meta"><?php _e('1 week ago', 'mifeco-suite'); ?></div>
                            </div>
                            <div class="mifeco-recent-actions">
                                <button class="mifeco-btn mifeco-btn-text" title="<?php _e('View', 'mifeco-suite'); ?>">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mifeco-recent-item">
                            <div class="mifeco-recent-icon">
                                <span class="dashicons dashicons-chart-pie"></span>
                            </div>
                            <div class="mifeco-recent-content">
                                <div class="mifeco-recent-title"><?php _e('Market Size: Renewable Energy', 'mifeco-suite'); ?></div>
                                <div class="mifeco-recent-meta"><?php _e('2 weeks ago', 'mifeco-suite'); ?></div>
                            </div>
                            <div class="mifeco-recent-actions">
                                <button class="mifeco-btn mifeco-btn-text" title="<?php _e('View', 'mifeco-suite'); ?>">
                                    <span class="dashicons dashicons-visibility"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Market Trends Section -->
            <div class="mifeco-section" id="market-trends-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Market Trends Analysis', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-search-form">
                    <form id="market-trends-form">
                        <div class="mifeco-form-row">
                            <div class="mifeco-form-field">
                                <label for="trends-industry"><?php _e('Industry', 'mifeco-suite'); ?></label>
                                <select id="trends-industry" name="industry">
                                    <option value=""><?php _e('Select Industry', 'mifeco-suite'); ?></option>
                                    <option value="technology"><?php _e('Technology', 'mifeco-suite'); ?></option>
                                    <option value="healthcare"><?php _e('Healthcare', 'mifeco-suite'); ?></option>
                                    <option value="finance"><?php _e('Finance', 'mifeco-suite'); ?></option>
                                    <option value="retail"><?php _e('Retail', 'mifeco-suite'); ?></option>
                                    <option value="manufacturing"><?php _e('Manufacturing', 'mifeco-suite'); ?></option>
                                    <option value="energy"><?php _e('Energy', 'mifeco-suite'); ?></option>
                                </select>
                            </div>
                            
                            <div class="mifeco-form-field">
                                <label for="trends-query"><?php _e('Search Term', 'mifeco-suite'); ?></label>
                                <input type="text" id="trends-query" name="query" placeholder="<?php _e('e.g., Artificial Intelligence', 'mifeco-suite'); ?>">
                            </div>
                            
                            <div class="mifeco-form-field">
                                <label for="trends-date-range"><?php _e('Date Range', 'mifeco-suite'); ?></label>
                                <select id="trends-date-range" name="date_range">
                                    <option value="all"><?php _e('All Time', 'mifeco-suite'); ?></option>
                                    <option value="year"><?php _e('Last Year', 'mifeco-suite'); ?></option>
                                    <option value="6months"><?php _e('Last 6 Months', 'mifeco-suite'); ?></option>
                                    <option value="3months"><?php _e('Last 3 Months', 'mifeco-suite'); ?></option>
                                    <option value="month"><?php _e('Last Month', 'mifeco-suite'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mifeco-form-actions">
                            <button type="submit" class="mifeco-btn mifeco-btn-primary">
                                <span class="dashicons dashicons-search"></span>
                                <?php _e('Search', 'mifeco-suite'); ?>
                            </button>
                            
                            <button type="reset" class="mifeco-btn mifeco-btn-text">
                                <?php _e('Reset', 'mifeco-suite'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="mifeco-research-results" id="trends-results">
                    <div class="mifeco-results-placeholder">
                        <div class="mifeco-placeholder-icon">
                            <span class="dashicons dashicons-chart-line"></span>
                        </div>
                        <div class="mifeco-placeholder-text">
                            <?php _e('Enter search parameters above to analyze market trends', 'mifeco-suite'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Competitor Analysis Section -->
            <div class="mifeco-section" id="competitor-analysis-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Competitor Analysis', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-search-form">
                    <form id="competitor-form">
                        <div class="mifeco-form-row">
                            <div class="mifeco-form-field">
                                <label for="competitor-industry"><?php _e('Industry', 'mifeco-suite'); ?></label>
                                <select id="competitor-industry" name="industry">
                                    <option value=""><?php _e('Select Industry', 'mifeco-suite'); ?></option>
                                    <option value="technology"><?php _e('Technology', 'mifeco-suite'); ?></option>
                                    <option value="healthcare"><?php _e('Healthcare', 'mifeco-suite'); ?></option>
                                    <option value="finance"><?php _e('Finance', 'mifeco-suite'); ?></option>
                                    <option value="retail"><?php _e('Retail', 'mifeco-suite'); ?></option>
                                    <option value="manufacturing"><?php _e('Manufacturing', 'mifeco-suite'); ?></option>
                                    <option value="energy"><?php _e('Energy', 'mifeco-suite'); ?></option>
                                </select>
                            </div>
                            
                            <div class="mifeco-form-field">
                                <label for="competitor-company"><?php _e('Primary Company', 'mifeco-suite'); ?></label>
                                <input type="text" id="competitor-company" name="company" placeholder="<?php _e('e.g., Acme Corporation', 'mifeco-suite'); ?>">
                            </div>
                        </div>
                        
                        <div class="mifeco-form-field">
                            <label><?php _e('Competitors to Include', 'mifeco-suite'); ?></label>
                            <div class="mifeco-competitor-inputs">
                                <div class="mifeco-competitor-input">
                                    <input type="text" name="competitors[]" placeholder="<?php _e('Competitor 1', 'mifeco-suite'); ?>">
                                </div>
                                <div class="mifeco-competitor-input">
                                    <input type="text" name="competitors[]" placeholder="<?php _e('Competitor 2', 'mifeco-suite'); ?>">
                                </div>
                                <div class="mifeco-competitor-input">
                                    <input type="text" name="competitors[]" placeholder="<?php _e('Competitor 3', 'mifeco-suite'); ?>">
                                </div>
                            </div>
                            <button type="button" class="mifeco-btn mifeco-btn-text mifeco-add-competitor">
                                <span class="dashicons dashicons-plus"></span>
                                <?php _e('Add Competitor', 'mifeco-suite'); ?>
                            </button>
                        </div>
                        
                        <div class="mifeco-form-actions">
                            <button type="submit" class="mifeco-btn mifeco-btn-primary">
                                <span class="dashicons dashicons-search"></span>
                                <?php _e('Analyze Competitors', 'mifeco-suite'); ?>
                            </button>
                            
                            <button type="reset" class="mifeco-btn mifeco-btn-text">
                                <?php _e('Reset', 'mifeco-suite'); ?>
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="mifeco-research-results" id="competitor-results">
                    <div class="mifeco-results-placeholder">
                        <div class="mifeco-placeholder-icon">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <div class="mifeco-placeholder-text">
                            <?php _e('Enter search parameters above to analyze competitors', 'mifeco-suite'); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Other sections would be implemented similarly -->
            
            <!-- Placeholder for other sections -->
            <div class="mifeco-section" id="market-size-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Market Size Estimation', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-placeholder-content">
                    <div class="mifeco-placeholder-icon">
                        <span class="dashicons dashicons-chart-pie"></span>
                    </div>
                    <div class="mifeco-placeholder-text">
                        <?php _e('Market Size Estimation tools coming soon', 'mifeco-suite'); ?>
                    </div>
                </div>
            </div>
            
            <div class="mifeco-section" id="reports-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Reports Generator', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-placeholder-content">
                    <div class="mifeco-placeholder-icon">
                        <span class="dashicons dashicons-media-document"></span>
                    </div>
                    <div class="mifeco-placeholder-text">
                        <?php _e('Reports Generator coming soon', 'mifeco-suite'); ?>
                    </div>
                </div>
            </div>
            
            <div class="mifeco-section" id="saved-research-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Saved Research', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-placeholder-content">
                    <div class="mifeco-placeholder-icon">
                        <span class="dashicons dashicons-saved"></span>
                    </div>
                    <div class="mifeco-placeholder-text">
                        <?php _e('Saved Research coming soon', 'mifeco-suite'); ?>
                    </div>
                </div>
            </div>
            
            <div class="mifeco-section" id="settings-section">
                <div class="mifeco-section-header">
                    <h2><?php _e('Settings', 'mifeco-suite'); ?></h2>
                </div>
                
                <div class="mifeco-placeholder-content">
                    <div class="mifeco-placeholder-icon">
                        <span class="dashicons dashicons-admin-settings"></span>
                    </div>
                    <div class="mifeco-placeholder-text">
                        <?php _e('Settings coming soon', 'mifeco-suite'); ?>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    
    <div class="mifeco-product-footer">
        <div class="mifeco-footer-copyright">
            &copy; <?php echo date('Y'); ?> MIFECO. <?php _e('All rights reserved.', 'mifeco-suite'); ?>
        </div>
        
        <div class="mifeco-footer-links">
            <a href="<?php echo esc_url(site_url('/privacy-policy/')); ?>"><?php _e('Privacy Policy', 'mifeco-suite'); ?></a>
            <a href="<?php echo esc_url(site_url('/terms-of-service/')); ?>"><?php _e('Terms of Service', 'mifeco-suite'); ?></a>
            <a href="<?php echo esc_url(site_url('/contact/')); ?>"><?php _e('Support', 'mifeco-suite'); ?></a>
        </div>
    </div>
</div>