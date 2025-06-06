<?php
/**
 * Admin SEO Dashboard View.
 * Expects $dashboard_data to be passed from a controller.
 * $dashboard_data might include:
 * - page_title (string)
 * - welcome_message (string)
 * - seo_status (array: percentage, color, features_status)
 * - content_analysis_stats (array: analyzed_percentage, excellent_count, good_count, etc.)
 * - checklist_items (array of arrays: status, title, description, link_text, link_url)
 * - quick_tips (array of arrays: icon_class, title, description)
 * - urls (array: settings_url, tools_url, content_analysis_url, etc.)
 */

// Example: Data that would be passed by a controller
$dashboard_data = $dashboard_data ?? [
    'page_title' => 'SEO Dashboard',
    'welcome_message' => 'Optimize your website for search engines and improve your rankings.',
    'seo_status' => [
        'percentage' => 75, // Example
        'color' => '#f0c33c', // Example
        'features_status' => [
            ['label' => 'Meta Tags', 'enabled' => true, 'link' => '/admin/seo/meta-tags-settings'],
            ['label' => 'XML Sitemap', 'enabled' => true, 'link' => '/admin/seo/sitemap-settings'],
            ['label' => 'Schema Markup', 'enabled' => false, 'link' => '/admin/seo/schema-settings'],
        ]
    ],
    'content_analysis_stats' => [
        'analyzed_percentage' => 60,
        'excellent_count' => 10,
        'good_count' => 25,
        'needs_improvement_count' => 15,
        'poor_count' => 5,
        'total_analyzed' => 55,
    ],
    'checklist_items' => [
        ['status' => 'complete', 'title' => 'XML Sitemap', 'description' => 'XML sitemaps help search engines find and index your content.', 'link_text' => 'View Sitemap', 'link_url' => '/sitemap.xml', 'target' => '_blank'],
        ['status' => 'incomplete', 'title' => 'Schema Markup', 'description' => 'Schema markup helps search engines understand your content better.', 'link_text' => 'Configure Schema', 'link_url' => '/admin/seo/schema-settings'],
        ['status' => 'complete', 'title' => 'Webmaster Tools', 'description' => 'Connect your site to search engine webmaster tools for better insights.', 'link_text' => 'Configure Webmaster Tools', 'link_url' => '/admin/seo/settings#webmaster'],
        ['status' => 'complete', 'title' => 'Robots.txt', 'description' => 'Your robots.txt file tells search engines which pages to crawl.', 'link_text' => 'View Robots.txt', 'link_url' => '/robots.txt', 'target' => '_blank'],
    ],
    'quick_tips' => [
        ['icon_class' => 'mifeco-icon-content', 'title' => 'Optimize Your Content', 'description' => 'Always include your focus keyword in the title, meta description, headings, and throughout the content.'],
        ['icon_class' => 'mifeco-icon-links', 'title' => 'Internal Linking', 'description' => 'Link to related content within your site to improve navigation and help search engines discover your content.'],
        ['icon_class' => 'mifeco-icon-mobile', 'title' => 'Mobile-Friendly', 'description' => 'Ensure your site works well on mobile devices, as Google uses mobile-first indexing for ranking.'],
        ['icon_class' => 'mifeco-icon-images', 'title' => 'Optimize Images', 'description' => 'Add descriptive alt text to images and optimize image file sizes for faster loading.'],
    ],
    'urls' => [
        'settings_url' => '/admin/seo/settings',
        'tools_url' => '/admin/seo/tools', // Assuming a future tools page
        'content_analysis_url' => '/admin/seo/content-analysis', // Assuming a future page
    ]
];
?>

<div class="wrap mifeco-admin-wrap">
    <h1><?php echo htmlspecialchars($dashboard_data['page_title']); ?></h1>

    <div class="mifeco-admin-notices"></div> <!-- For dynamic notices -->

    <div class="mifeco-admin-dashboard">
        <div class="mifeco-dashboard-header">
            <div class="mifeco-dashboard-welcome">
                <h2>Welcome to MIFECO SEO</h2>
                <p><?php echo htmlspecialchars($dashboard_data['welcome_message']); ?></p>
            </div>

            <div class="mifeco-dashboard-actions">
                <a href="<?php echo htmlspecialchars($dashboard_data['urls']['settings_url']); ?>" class="button button-primary">SEO Settings</a>
                <a href="<?php echo htmlspecialchars($dashboard_data['urls']['tools_url']); ?>" class="button">SEO Tools</a>
            </div>
        </div>

        <div class="mifeco-dashboard-widgets">
            <div class="mifeco-dashboard-widget-row">
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header"><h3>SEO Status</h3></div>
                    <div class="mifeco-dashboard-widget-content">
                        <div class="mifeco-status-bar">
                            <div class="mifeco-status-progress" style="width: <?php echo htmlspecialchars($dashboard_data['seo_status']['percentage']); ?>%; background-color: <?php echo htmlspecialchars($dashboard_data['seo_status']['color']); ?>;"></div>
                            <span class="mifeco-status-percentage"><?php echo htmlspecialchars($dashboard_data['seo_status']['percentage']); ?>%</span>
                        </div>
                        <ul class="mifeco-feature-list">
                            <?php foreach ($dashboard_data['seo_status']['features_status'] as $feature): ?>
                            <li class="<?php echo $feature['enabled'] ? 'enabled' : 'disabled'; ?>">
                                <span class="dashicons mifeco-icon-<?php echo $feature['enabled'] ? 'yes' : 'no'; ?>"></span>
                                <?php echo htmlspecialchars($feature['label']); ?>
                                <?php if (!$feature['enabled']): ?>
                                    <a href="<?php echo htmlspecialchars($feature['link']); ?>" class="mifeco-action-link">Enable</a>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header"><h3>Content Analysis</h3></div>
                    <div class="mifeco-dashboard-widget-content">
                        <?php $stats = $dashboard_data['content_analysis_stats']; ?>
                        <div class="mifeco-analysis-summary">
                            <div class="mifeco-analysis-progress">
                                <div class="mifeco-progress-circle" style="--percentage: <?php echo htmlspecialchars($stats['analyzed_percentage']); ?>">
                                    <span class="mifeco-progress-value"><?php echo htmlspecialchars($stats['analyzed_percentage']); ?>%</span>
                                    <span class="mifeco-progress-label">Analyzed</span>
                                </div>
                            </div>
                            <div class="mifeco-analysis-stats">
                                <div class="mifeco-stat"><span class="mifeco-stat-value excellent"><?php echo htmlspecialchars($stats['excellent_count']); ?></span><span class="mifeco-stat-label">Excellent</span></div>
                                <div class="mifeco-stat"><span class="mifeco-stat-value good"><?php echo htmlspecialchars($stats['good_count']); ?></span><span class="mifeco-stat-label">Good</span></div>
                                <div class="mifeco-stat"><span class="mifeco-stat-value needs-improvement"><?php echo htmlspecialchars($stats['needs_improvement_count']); ?></span><span class="mifeco-stat-label">Needs Improvement</span></div>
                                <div class="mifeco-stat"><span class="mifeco-stat-value poor"><?php echo htmlspecialchars($stats['poor_count']); ?></span><span class="mifeco-stat-label">Poor</span></div>
                            </div>
                        </div>
                        <?php if (($stats['total_analyzed'] ?? 0) > 0): ?>
                            <a href="<?php echo htmlspecialchars($dashboard_data['urls']['content_analysis_url']); ?>" class="button">Content Analysis Details</a>
                        <?php else: ?>
                            <p>No content has been analyzed yet. Content analysis helps you optimize your content for search engines.</p>
                            <a href="<?php echo htmlspecialchars($dashboard_data['urls']['content_analysis_url']); ?>" class="button">Start Content Analysis</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="mifeco-dashboard-widget-row">
                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header"><h3>SEO Setup Checklist</h3></div>
                    <div class="mifeco-dashboard-widget-content">
                        <ul class="mifeco-checklist">
                            <?php foreach ($dashboard_data['checklist_items'] as $item): ?>
                            <li class="<?php echo htmlspecialchars($item['status']); ?>">
                                <span class="dashicons mifeco-icon-<?php echo $item['status'] === 'complete' ? 'yes' : 'no-alt'; ?>"></span>
                                <div class="mifeco-checklist-item">
                                    <h4><?php echo htmlspecialchars($item['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($item['description']); ?></p>
                                    <a href="<?php echo htmlspecialchars($item['link_url']); ?>" <?php if(!empty($item['target'])) echo 'target="'.htmlspecialchars($item['target']).'"';?>>
                                        <?php echo htmlspecialchars($item['link_text']); ?>
                                    </a>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="mifeco-dashboard-widget">
                    <div class="mifeco-dashboard-widget-header"><h3>SEO Quick Tips</h3></div>
                    <div class="mifeco-dashboard-widget-content">
                        <div class="mifeco-tips-list">
                            <?php foreach ($dashboard_data['quick_tips'] as $tip): ?>
                            <div class="mifeco-tip">
                                <div class="mifeco-tip-icon"><span class="dashicons <?php echo htmlspecialchars($tip['icon_class']); ?>"></span></div>
                                <div class="mifeco-tip-content">
                                    <h4><?php echo htmlspecialchars($tip['title']); ?></h4>
                                    <p><?php echo htmlspecialchars($tip['description']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mifeco-learn-more">
                            <a href="<?php echo htmlspecialchars($dashboard_data['urls']['tools_url']); ?>" class="button">SEO Tools & Resources</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Minimal styles, assuming a base admin stylesheet exists. Dashicons might need to be replaced with custom icons. */
    /* Placeholder for dashicons classes if not globally available */
    .dashicons { display: inline-block; width: 20px; height: 20px; background-repeat: no-repeat; background-position: center; /* ควรมี icon font หรือ SVG ที่นี่ */ }
    .mifeco-icon-yes::before { content: '✔'; color: green; } /* Example */
    .mifeco-icon-no::before { content: '✖'; color: red; } /* Example */
    .mifeco-icon-no-alt::before { content: '✖'; color: red; } /* Example */
    .mifeco-icon-content::before { content: '📄'; }
    .mifeco-icon-links::before { content: '🔗'; }
    .mifeco-icon-mobile::before { content: '📱'; }
    .mifeco-icon-images::before { content: '🖼️'; }

    .mifeco-admin-dashboard { max-width: 1200px; margin-top: 20px; }
    .mifeco-dashboard-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
    .mifeco-dashboard-welcome h2 { margin-top: 0; margin-bottom: 10px; }
    .mifeco-dashboard-welcome p { margin-top: 0; color: #666; }
    .mifeco-dashboard-actions { display: flex; gap: 10px; }
    .mifeco-dashboard-widget-row { display: flex; gap: 20px; margin-bottom: 20px; }
    .mifeco-dashboard-widget { flex: 1; background: #fff; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); overflow: hidden; }
    .mifeco-dashboard-widget-header { padding: 15px 20px; border-bottom: 1px solid #eee; }
    .mifeco-dashboard-widget-header h3 { margin: 0; font-size: 16px; font-weight: 600; }
    .mifeco-dashboard-widget-content { padding: 20px; }
    .mifeco-status-bar { height: 20px; background-color: #f0f0f1; border-radius: 10px; position: relative; margin-bottom: 20px; overflow: hidden; }
    .mifeco-status-progress { height: 100%; border-radius: 10px; }
    .mifeco-status-percentage { position: absolute; top: 0; right: 10px; line-height: 20px; font-size: 12px; color: #fff; text-shadow: 0 0 2px rgba(0,0,0,0.5); }
    .mifeco-feature-list { margin: 0; padding: 0; list-style-type: none; }
    .mifeco-feature-list li { display: flex; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f1; }
    .mifeco-feature-list li:last-child { border-bottom: none; }
    .mifeco-feature-list .dashicons { margin-right: 10px; }
    .mifeco-action-link { margin-left: auto; color: #2271b1; text-decoration: none; }
    .mifeco-action-link:hover { text-decoration: underline; }
    .mifeco-analysis-summary { display: flex; gap: 20px; margin-bottom: 20px; align-items: center; }
    .mifeco-analysis-progress { flex: 0 0 120px; }
    .mifeco-progress-circle { position: relative; width: 100px; height: 100px; border-radius: 50%; background: conic-gradient(var(--status-color, #007cba) calc(var(--percentage, 0) * 1%), #f0f0f1 0); display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .mifeco-progress-circle::before { content: ''; position: absolute; width: 70px; height: 70px; border-radius: 50%; background: #fff; }
    .mifeco-progress-value { position: relative; font-size: 20px; font-weight: 600; color: var(--status-color, #007cba); }
    .mifeco-progress-label { position: relative; font-size: 11px; color: #50575e; }
    .mifeco-analysis-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; flex: 1; }
    .mifeco-stat { text-align: center; padding: 5px; background: #f9f9f9; border-radius: 3px; }
    .mifeco-stat-value { font-size: 18px; font-weight: 600; display: block; }
    .mifeco-stat-value.excellent { color: #00a32a; } .mifeco-stat-value.good { color: #5cb85c; }
    .mifeco-stat-value.needs-improvement { color: #f0ad4e; } .mifeco-stat-value.poor { color: #d9534f; }
    .mifeco-stat-label { font-size: 11px; color: #50575e; }
    .mifeco-checklist { margin:0; padding:0; list-style-type:none; }
    .mifeco-checklist li { display:flex; margin-bottom:15px; padding-bottom:15px; border-bottom:1px solid #f0f0f1; }
    .mifeco-checklist li:last-child { margin-bottom:0; padding-bottom:0; border-bottom:none; }
    .mifeco-checklist .dashicons { flex:0 0 24px; margin-right:10px; margin-top:3px; }
    .mifeco-checklist-item h4 { margin-top:0; margin-bottom:5px; }
    .mifeco-checklist-item p { margin-top:0; margin-bottom:10px; color:#666; font-size:0.9em; }
    .mifeco-tips-list { display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:20px; }
    .mifeco-tip { display:flex; gap:10px; align-items:flex-start; }
    .mifeco-tip-icon { flex:0 0 30px; height:30px; background:#f0f0f1; border-radius:50%; display:flex; justify-content:center; align-items:center; }
    .mifeco-tip-icon .dashicons { color:#2271b1; font-size:16px; }
    .mifeco-tip-content h4 { margin-top:0; margin-bottom:5px; font-size:1em; }
    .mifeco-tip-content p { margin:0; color:#666; font-size:0.9em; }
    .mifeco-learn-more { margin-top:20px; text-align:center; }
    .button { background-color: #f0f0f1; border: 1px solid #ccc; padding: 8px 15px; text-decoration: none; border-radius: 3px; color: #333; }
    .button-primary { background-color: #0073aa; color: white; border-color: #0073aa; }
    @media (max-width: 768px) {
        .mifeco-dashboard-header, .mifeco-dashboard-widget-row, .mifeco-analysis-summary { flex-direction: column; align-items: stretch; }
        .mifeco-dashboard-actions { margin-top: 15px; }
        .mifeco-analysis-progress { margin-bottom: 15px; }
    }
</style>
