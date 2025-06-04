<?php
/**
 * Admin SEO Dashboard Template
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap mifeco-admin-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="mifeco-admin-notices"></div>
    
    <div class="mifeco-admin-dashboard">
        <div class="mifeco-dashboard-header">
            <div class="mifeco-dashboard-welcome">
                <h2><?php _e('Welcome to MIFECO SEO', 'mifeco-suite'); ?></h2>
                <p><?php _e('Optimize your website for search engines and improve your rankings.', 'mifeco-suite'); ?></p>
            </div>
            
            <div class="mifeco-dashboard-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-settings')); ?>" class="button button-primary"><?php _e('SEO Settings', 'mifeco-suite'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-tools')); ?>" class="button"><?php _e('SEO Tools', 'mifeco-suite'); ?></a>
            </div>
        </div>
        
        <div class="mifeco-dashboard-widgets">
            <div class="mifeco-dashboard-widget-row">
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header">
                        <h3><?php _e('SEO Status', 'mifeco-suite'); ?></h3>
                    </div>
                    <div class="mifeco-dashboard-widget-content">
                        <?php
                        // Check SEO settings
                        $meta_tags_settings = get_option('mifeco_meta_tags_settings', array());
                        $sitemap_settings = get_option('mifeco_sitemap_settings', array());
                        $schema_settings = get_option('mifeco_schema_settings', array());
                        
                        $meta_tags_enabled = isset($meta_tags_settings['enable_meta_description']) ? $meta_tags_settings['enable_meta_description'] : true;
                        $sitemap_enabled = isset($sitemap_settings['enable_sitemap']) ? $sitemap_settings['enable_sitemap'] : true;
                        $schema_enabled = isset($schema_settings['enable_schema']) ? $schema_settings['enable_schema'] : true;
                        
                        $total_features = 3;
                        $enabled_features = ($meta_tags_enabled ? 1 : 0) + ($sitemap_enabled ? 1 : 0) + ($schema_enabled ? 1 : 0);
                        $percentage = round(($enabled_features / $total_features) * 100);
                        
                        // Status bar color
                        $status_color = '#007cba';
                        if ($percentage < 50) {
                            $status_color = '#ca4a1f';
                        } elseif ($percentage < 100) {
                            $status_color = '#f0c33c';
                        }
                        ?>
                        <div class="mifeco-status-bar">
                            <div class="mifeco-status-progress" style="width: <?php echo esc_attr($percentage); ?>%; background-color: <?php echo esc_attr($status_color); ?>;"></div>
                            <span class="mifeco-status-percentage"><?php echo esc_html($percentage); ?>%</span>
                        </div>
                        
                        <ul class="mifeco-feature-list">
                            <li class="<?php echo $meta_tags_enabled ? 'enabled' : 'disabled'; ?>">
                                <span class="dashicons dashicons-<?php echo $meta_tags_enabled ? 'yes' : 'no'; ?>"></span>
                                <?php _e('Meta Tags', 'mifeco-suite'); ?>
                                <?php if (!$meta_tags_enabled) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-meta-tags')); ?>" class="mifeco-action-link"><?php _e('Enable', 'mifeco-suite'); ?></a>
                                <?php endif; ?>
                            </li>
                            <li class="<?php echo $sitemap_enabled ? 'enabled' : 'disabled'; ?>">
                                <span class="dashicons dashicons-<?php echo $sitemap_enabled ? 'yes' : 'no'; ?>"></span>
                                <?php _e('XML Sitemap', 'mifeco-suite'); ?>
                                <?php if (!$sitemap_enabled) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-sitemap')); ?>" class="mifeco-action-link"><?php _e('Enable', 'mifeco-suite'); ?></a>
                                <?php endif; ?>
                            </li>
                            <li class="<?php echo $schema_enabled ? 'enabled' : 'disabled'; ?>">
                                <span class="dashicons dashicons-<?php echo $schema_enabled ? 'yes' : 'no'; ?>"></span>
                                <?php _e('Schema Markup', 'mifeco-suite'); ?>
                                <?php if (!$schema_enabled) : ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-schema')); ?>" class="mifeco-action-link"><?php _e('Enable', 'mifeco-suite'); ?></a>
                                <?php endif; ?>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header">
                        <h3><?php _e('Content Analysis', 'mifeco-suite'); ?></h3>
                    </div>
                    <div class="mifeco-dashboard-widget-content">
                        <?php
                        // Get content analysis data
                        $posts = get_posts(array(
                            'posts_per_page' => -1,
                            'post_type' => 'post',
                            'post_status' => 'publish',
                            'meta_query' => array(
                                array(
                                    'key' => '_mifeco_content_analysis',
                                    'compare' => 'EXISTS'
                                )
                            )
                        ));
                        
                        $total_posts = count(get_posts(array('posts_per_page' => -1, 'post_type' => 'post', 'post_status' => 'publish')));
                        $analyzed_posts = count($posts);
                        $analyzed_percentage = $total_posts > 0 ? round(($analyzed_posts / $total_posts) * 100) : 0;
                        
                        // Score calculations
                        $excellent_count = 0;
                        $good_count = 0;
                        $needs_improvement_count = 0;
                        $poor_count = 0;
                        
                        foreach ($posts as $post) {
                            $analysis = get_post_meta($post->ID, '_mifeco_content_analysis', true);
                            $score = 0;
                            
                            // Calculate score based on analysis results
                            if (is_array($analysis)) {
                                $score = MIFECO_Content_Analysis::calculate_overall_score($analysis);
                            }
                            
                            if ($score >= 80) {
                                $excellent_count++;
                            } elseif ($score >= 60) {
                                $good_count++;
                            } elseif ($score >= 40) {
                                $needs_improvement_count++;
                            } else {
                                $poor_count++;
                            }
                        }
                        ?>
                        
                        <div class="mifeco-analysis-summary">
                            <div class="mifeco-analysis-progress">
                                <div class="mifeco-progress-circle" style="--percentage: <?php echo esc_attr($analyzed_percentage); ?>">
                                    <span class="mifeco-progress-value"><?php echo esc_html($analyzed_percentage); ?>%</span>
                                    <span class="mifeco-progress-label"><?php _e('Analyzed', 'mifeco-suite'); ?></span>
                                </div>
                            </div>
                            
                            <div class="mifeco-analysis-stats">
                                <div class="mifeco-stat">
                                    <span class="mifeco-stat-value excellent"><?php echo esc_html($excellent_count); ?></span>
                                    <span class="mifeco-stat-label"><?php _e('Excellent', 'mifeco-suite'); ?></span>
                                </div>
                                <div class="mifeco-stat">
                                    <span class="mifeco-stat-value good"><?php echo esc_html($good_count); ?></span>
                                    <span class="mifeco-stat-label"><?php _e('Good', 'mifeco-suite'); ?></span>
                                </div>
                                <div class="mifeco-stat">
                                    <span class="mifeco-stat-value needs-improvement"><?php echo esc_html($needs_improvement_count); ?></span>
                                    <span class="mifeco-stat-label"><?php _e('Needs Improvement', 'mifeco-suite'); ?></span>
                                </div>
                                <div class="mifeco-stat">
                                    <span class="mifeco-stat-value poor"><?php echo esc_html($poor_count); ?></span>
                                    <span class="mifeco-stat-label"><?php _e('Poor', 'mifeco-suite'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($analyzed_posts > 0) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-content-analysis')); ?>" class="button"><?php _e('Content Analysis Details', 'mifeco-suite'); ?></a>
                        <?php else : ?>
                            <p><?php _e('No content has been analyzed yet. Content analysis helps you optimize your content for search engines.', 'mifeco-suite'); ?></p>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-content-analysis')); ?>" class="button"><?php _e('Start Content Analysis', 'mifeco-suite'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="mifeco-dashboard-widget-row">
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header">
                        <h3><?php _e('SEO Setup Checklist', 'mifeco-suite'); ?></h3>
                    </div>
                    <div class="mifeco-dashboard-widget-content">
                        <ul class="mifeco-checklist">
                            <?php
                            // Check sitemap status
                            $sitemap_status = $sitemap_enabled ? 'complete' : 'incomplete';
                            $sitemap_url = home_url('sitemap.xml');
                            ?>
                            <li class="<?php echo esc_attr($sitemap_status); ?>">
                                <span class="dashicons dashicons-<?php echo $sitemap_status === 'complete' ? 'yes' : 'no-alt'; ?>"></span>
                                <div class="mifeco-checklist-item">
                                    <h4><?php _e('XML Sitemap', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('XML sitemaps help search engines find and index your content.', 'mifeco-suite'); ?></p>
                                    <?php if ($sitemap_status === 'complete') : ?>
                                        <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank"><?php _e('View Sitemap', 'mifeco-suite'); ?></a>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-sitemap')); ?>"><?php _e('Configure Sitemap', 'mifeco-suite'); ?></a>
                                    <?php endif; ?>
                                </div>
                            </li>
                            
                            <?php
                            // Check schema status
                            $schema_status = $schema_enabled ? 'complete' : 'incomplete';
                            ?>
                            <li class="<?php echo esc_attr($schema_status); ?>">
                                <span class="dashicons dashicons-<?php echo $schema_status === 'complete' ? 'yes' : 'no-alt'; ?>"></span>
                                <div class="mifeco-checklist-item">
                                    <h4><?php _e('Schema Markup', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Schema markup helps search engines understand your content better.', 'mifeco-suite'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-schema')); ?>"><?php _e('Configure Schema', 'mifeco-suite'); ?></a>
                                </div>
                            </li>
                            
                            <?php
                            // Check webmaster tools status
                            $webmaster_tools_settings = get_option('mifeco_webmaster_tools_settings', array());
                            $has_google = !empty($webmaster_tools_settings['google_verification']);
                            $has_bing = !empty($webmaster_tools_settings['bing_verification']);
                            $webmaster_tools_status = ($has_google || $has_bing) ? 'complete' : 'incomplete';
                            ?>
                            <li class="<?php echo esc_attr($webmaster_tools_status); ?>">
                                <span class="dashicons dashicons-<?php echo $webmaster_tools_status === 'complete' ? 'yes' : 'no-alt'; ?>"></span>
                                <div class="mifeco-checklist-item">
                                    <h4><?php _e('Webmaster Tools', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Connect your site to search engine webmaster tools for better insights.', 'mifeco-suite'); ?></p>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-settings')); ?>"><?php _e('Configure Webmaster Tools', 'mifeco-suite'); ?></a>
                                </div>
                            </li>
                            
                            <?php
                            // Check robots.txt status
                            $robots_url = home_url('robots.txt');
                            ?>
                            <li class="complete">
                                <span class="dashicons dashicons-yes"></span>
                                <div class="mifeco-checklist-item">
                                    <h4><?php _e('Robots.txt', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Your robots.txt file tells search engines which pages to crawl.', 'mifeco-suite'); ?></p>
                                    <a href="<?php echo esc_url($robots_url); ?>" target="_blank"><?php _e('View Robots.txt', 'mifeco-suite'); ?></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header">
                        <h3><?php _e('SEO Quick Tips', 'mifeco-suite'); ?></h3>
                    </div>
                    <div class="mifeco-dashboard-widget-content">
                        <div class="mifeco-tips-list">
                            <div class="mifeco-tip">
                                <div class="mifeco-tip-icon">
                                    <span class="dashicons dashicons-format-aside"></span>
                                </div>
                                <div class="mifeco-tip-content">
                                    <h4><?php _e('Optimize Your Content', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Always include your focus keyword in the title, meta description, headings, and throughout the content.', 'mifeco-suite'); ?></p>
                                </div>
                            </div>
                            
                            <div class="mifeco-tip">
                                <div class="mifeco-tip-icon">
                                    <span class="dashicons dashicons-admin-links"></span>
                                </div>
                                <div class="mifeco-tip-content">
                                    <h4><?php _e('Internal Linking', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Link to related content within your site to improve navigation and help search engines discover your content.', 'mifeco-suite'); ?></p>
                                </div>
                            </div>
                            
                            <div class="mifeco-tip">
                                <div class="mifeco-tip-icon">
                                    <span class="dashicons dashicons-performance"></span>
                                </div>
                                <div class="mifeco-tip-content">
                                    <h4><?php _e('Mobile-Friendly', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Ensure your site works well on mobile devices, as Google uses mobile-first indexing for ranking.', 'mifeco-suite'); ?></p>
                                </div>
                            </div>
                            
                            <div class="mifeco-tip">
                                <div class="mifeco-tip-icon">
                                    <span class="dashicons dashicons-images-alt2"></span>
                                </div>
                                <div class="mifeco-tip-content">
                                    <h4><?php _e('Optimize Images', 'mifeco-suite'); ?></h4>
                                    <p><?php _e('Add descriptive alt text to images and optimize image file sizes for faster loading.', 'mifeco-suite'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mifeco-learn-more">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=mifeco-seo-tools')); ?>" class="button"><?php _e('SEO Tools & Resources', 'mifeco-suite'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .mifeco-admin-dashboard {
        max-width: 1200px;
        margin-top: 20px;
    }
    
    .mifeco-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        background: #fff;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }
    
    .mifeco-dashboard-welcome h2 {
        margin-top: 0;
        margin-bottom: 10px;
    }
    
    .mifeco-dashboard-welcome p {
        margin-top: 0;
        color: #666;
    }
    
    .mifeco-dashboard-actions {
        display: flex;
        gap: 10px;
    }
    
    .mifeco-dashboard-widget-row {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .mifeco-dashboard-widget {
        flex: 1;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }
    
    .mifeco-dashboard-widget-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
    }
    
    .mifeco-dashboard-widget-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .mifeco-dashboard-widget-content {
        padding: 20px;
    }
    
    .mifeco-status-bar {
        height: 20px;
        background-color: #f0f0f1;
        border-radius: 10px;
        position: relative;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .mifeco-status-progress {
        height: 100%;
        border-radius: 10px;
    }
    
    .mifeco-status-percentage {
        position: absolute;
        top: 0;
        right: 10px;
        line-height: 20px;
        font-size: 12px;
        color: #fff;
        text-shadow: 0 0 2px rgba(0, 0, 0, 0.5);
    }
    
    .mifeco-feature-list {
        margin: 0;
        padding: 0;
        list-style-type: none;
    }
    
    .mifeco-feature-list li {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f1;
    }
    
    .mifeco-feature-list li:last-child {
        border-bottom: none;
    }
    
    .mifeco-feature-list li.enabled {
        color: #2c3338;
    }
    
    .mifeco-feature-list li.disabled {
        color: #a7aaad;
    }
    
    .mifeco-feature-list .dashicons-yes {
        color: #00a32a;
        margin-right: 10px;
    }
    
    .mifeco-feature-list .dashicons-no {
        color: #cc1818;
        margin-right: 10px;
    }
    
    .mifeco-action-link {
        margin-left: auto;
        color: #2271b1;
        text-decoration: none;
    }
    
    .mifeco-action-link:hover {
        color: #135e96;
        text-decoration: underline;
    }
    
    .mifeco-analysis-summary {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
    }
    
    .mifeco-analysis-progress {
        flex: 0 0 120px;
    }
    
    .mifeco-progress-circle {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: conic-gradient(#2271b1 calc(var(--percentage) * 1%), #f0f0f1 0);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .mifeco-progress-circle::before {
        content: '';
        position: absolute;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #fff;
    }
    
    .mifeco-progress-value {
        position: relative;
        font-size: 24px;
        font-weight: 600;
        color: #2271b1;
    }
    
    .mifeco-progress-label {
        position: relative;
        font-size: 12px;
        color: #50575e;
    }
    
    .mifeco-analysis-stats {
        flex: 1;
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .mifeco-stat {
        flex: 0 0 calc(50% - 10px);
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        background: #f0f0f1;
        border-radius: 4px;
    }
    
    .mifeco-stat-value {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .mifeco-stat-value.excellent {
        color: #00a32a;
    }
    
    .mifeco-stat-value.good {
        color: #5cb85c;
    }
    
    .mifeco-stat-value.needs-improvement {
        color: #f0ad4e;
    }
    
    .mifeco-stat-value.poor {
        color: #d9534f;
    }
    
    .mifeco-stat-label {
        font-size: 12px;
        color: #50575e;
    }
    
    .mifeco-checklist {
        margin: 0;
        padding: 0;
        list-style-type: none;
    }
    
    .mifeco-checklist li {
        display: flex;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f0f1;
    }
    
    .mifeco-checklist li:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    
    .mifeco-checklist .dashicons {
        flex: 0 0 24px;
        margin-right: 10px;
        margin-top: 3px;
    }
    
    .mifeco-checklist .dashicons-yes {
        color: #00a32a;
    }
    
    .mifeco-checklist .dashicons-no-alt {
        color: #cc1818;
    }
    
    .mifeco-checklist-item {
        flex: 1;
    }
    
    .mifeco-checklist-item h4 {
        margin-top: 0;
        margin-bottom: 5px;
    }
    
    .mifeco-checklist-item p {
        margin-top: 0;
        margin-bottom: 10px;
        color: #666;
    }
    
    .mifeco-tips-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    .mifeco-tip {
        display: flex;
        gap: 10px;
    }
    
    .mifeco-tip-icon {
        flex: 0 0 35px;
        height: 35px;
        background: #f0f0f1;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    
    .mifeco-tip-icon .dashicons {
        color: #2271b1;
    }
    
    .mifeco-tip-content {
        flex: 1;
    }
    
    .mifeco-tip-content h4 {
        margin-top: 0;
        margin-bottom: 5px;
        font-size: 14px;
    }
    
    .mifeco-tip-content p {
        margin-top: 0;
        margin-bottom: 0;
        color: #666;
        font-size: 12px;
    }
    
    .mifeco-learn-more {
        margin-top: 20px;
        text-align: center;
    }
    
    @media (max-width: 768px) {
        .mifeco-dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .mifeco-dashboard-actions {
            margin-top: 15px;
        }
        
        .mifeco-dashboard-widget-row {
            flex-direction: column;
        }
        
        .mifeco-tips-list {
            grid-template-columns: 1fr;
        }
    }
</style>