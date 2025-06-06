<?php
/**
 * Research Tool product page view.
 * Expects $view_data to be passed from a controller, containing:
 * - user_name (string)
 * - user_avatar_url (string, optional)
 * - account_url (string)
 * - home_url (string)
 * - logout_url (string)
 * - current_plan_display (string)
 * - upgrade_plan_url (string)
 * - privacy_policy_url (string)
 * - terms_of_service_url (string)
 * - support_url (string)
 * - nav_items (array of arrays: ['id' => 'dashboard', 'label' => 'Dashboard', 'icon_class' => 'dashicons-dashboard', 'active' => true])
 * - product_title (string)
 * - initial_stats (array of arrays: ['value' => 'X', 'label' => 'Label'])
 * - quick_actions (array of arrays: ['id' => 'market-trends', 'label' => 'Analyze Market Trends', 'icon_class' => 'dashicons-chart-line'])
 * - recent_research_items (array of arrays: ['icon_class' => 'dashicons-chart-line', 'title' => 'Item Title', 'meta' => 'X days ago'])
 */

// Default values for placeholder data (controller would override these)
$view_data = $view_data ?? [
    'user_name' => 'Guest User',
    'user_avatar_url' => '', // Path to a default avatar or empty
    'account_url' => '/account',
    'home_url' => '/',
    'logout_url' => '/logout',
    'current_plan_display' => 'Basic Plan',
    'upgrade_plan_url' => '/pricing',
    'privacy_policy_url' => '/privacy',
    'terms_of_service_url' => '/terms',
    'support_url' => '/contact',
    'product_title' => 'Advanced Research Tool',
    'nav_items' => [
        ['id' => 'dashboard', 'label' => 'Dashboard', 'icon_class' => 'dashicons-dashboard mifeco-icon-dashboard', 'active' => true],
        ['id' => 'market-trends', 'label' => 'Market Trends', 'icon_class' => 'dashicons-chart-line mifeco-icon-trends', 'active' => false],
        ['id' => 'competitor-analysis', 'label' => 'Competitor Analysis', 'icon_class' => 'dashicons-groups mifeco-icon-competitors', 'active' => false],
        ['id' => 'market-size', 'label' => 'Market Size', 'icon_class' => 'dashicons-chart-pie mifeco-icon-market-size', 'active' => false],
        ['id' => 'reports', 'label' => 'Reports', 'icon_class' => 'dashicons-media-document mifeco-icon-reports', 'active' => false],
        ['id' => 'saved-research', 'label' => 'Saved Research', 'icon_class' => 'dashicons-saved mifeco-icon-saved', 'active' => false],
        ['id' => 'settings', 'label' => 'Settings', 'icon_class' => 'dashicons-admin-settings mifeco-icon-settings', 'active' => false],
    ],
    'initial_stats' => [
        ['value' => '5', 'label' => 'Saved Reports'],
        ['value' => '23', 'label' => 'Recent Searches'],
        ['value' => '8', 'label' => 'Tracked Industries'],
    ],
    'quick_actions' => [
        ['id' => 'market-trends', 'label' => 'Analyze Market Trends', 'icon_class' => 'dashicons-chart-line mifeco-icon-trends'],
        ['id' => 'competitor-analysis', 'label' => 'Competitor Analysis', 'icon_class' => 'dashicons-groups mifeco-icon-competitors'],
        ['id' => 'market-size', 'label' => 'Estimate Market Size', 'icon_class' => 'dashicons-chart-pie mifeco-icon-market-size'],
        ['id' => 'reports', 'label' => 'Generate a Report', 'icon_class' => 'dashicons-media-document mifeco-icon-reports'],
    ],
    'recent_research_items' => [
        ['icon_class' => 'dashicons-chart-line mifeco-icon-trends', 'title' => 'Global AI Market Trends 2024', 'meta' => '3 days ago'],
        ['icon_class' => 'dashicons-groups mifeco-icon-competitors', 'title' => 'Competitor Deep Dive: SaaS Sector', 'meta' => '1 week ago'],
    ]
];

?>

<div class="mifeco-product-interface mifeco-research-tool">
    <div class="mifeco-product-header">
        <div class="mifeco-product-logo">
            <span class="mifeco-logo-icon">🔍</span> <!-- Placeholder, could be an img tag -->
            <h1><?php echo htmlspecialchars($view_data['product_title']); ?></h1>
        </div>

        <div class="mifeco-user-controls">
            <div class="mifeco-user-info">
                <?php if (!empty($view_data['user_avatar_url'])): ?>
                    <img src="<?php echo htmlspecialchars($view_data['user_avatar_url']); ?>" alt="User Avatar" class="mifeco-avatar" width="32" height="32">
                <?php else: ?>
                    <span class="mifeco-avatar-placeholder mifeco-icon-user"></span> <!-- Placeholder icon -->
                <?php endif; ?>
                <span class="mifeco-username"><?php echo htmlspecialchars($view_data['user_name']); ?></span>
            </div>

            <div class="mifeco-controls">
                <a href="<?php echo htmlspecialchars($view_data['account_url']); ?>" class="mifeco-control-link" title="Account">
                    <span class="dashicons dashicons-admin-users mifeco-icon-user"></span>
                </a>
                <a href="<?php echo htmlspecialchars($view_data['home_url']); ?>" class="mifeco-control-link" title="Back to Site">
                    <span class="dashicons dashicons-admin-home mifeco-icon-home"></span>
                </a>
                <a href="<?php echo htmlspecialchars($view_data['logout_url']); ?>" class="mifeco-control-link" title="Logout">
                    <span class="dashicons dashicons-exit mifeco-icon-logout"></span>
                </a>
            </div>
        </div>
    </div>

    <div class="mifeco-product-content">
        <div class="mifeco-sidebar">
            <div class="mifeco-navigation">
                <ul class="mifeco-nav-menu">
                    <?php foreach ($view_data['nav_items'] as $nav_item): ?>
                    <li class="mifeco-nav-item <?php echo $nav_item['active'] ? 'active' : ''; ?>">
                        <a href="#<?php echo htmlspecialchars($nav_item['id']); ?>" class="mifeco-nav-link" data-section="<?php echo htmlspecialchars($nav_item['id']); ?>">
                            <span class="dashicons <?php echo htmlspecialchars($nav_item['icon_class']); ?>"></span>
                            <?php echo htmlspecialchars($nav_item['label']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="mifeco-subscription-info">
                <div class="mifeco-plan-badge">
                    <?php echo htmlspecialchars($view_data['current_plan_display']); ?>
                </div>
                <a href="<?php echo htmlspecialchars($view_data['upgrade_plan_url']); ?>" class="mifeco-upgrade-link">
                    Upgrade ↗
                </a>
            </div>
        </div>

        <div class="mifeco-main-content">
            <!-- Dashboard Section -->
            <div class="mifeco-section active" id="dashboard-section" data-section-content="dashboard">
                <div class="mifeco-section-header"><h2>Research Dashboard</h2></div>
                <div class="mifeco-dashboard-overview">
                    <?php foreach($view_data['initial_stats'] as $stat): ?>
                    <div class="mifeco-overview-stat">
                        <div class="mifeco-stat-value"><?php echo htmlspecialchars($stat['value']); ?></div>
                        <div class="mifeco-stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="mifeco-dashboard-actions">
                    <h3>Quick Actions</h3>
                    <div class="mifeco-action-cards">
                        <?php foreach($view_data['quick_actions'] as $action): ?>
                        <a href="#<?php echo htmlspecialchars($action['id']); ?>" class="mifeco-action-card mifeco-nav-link" data-section="<?php echo htmlspecialchars($action['id']); ?>">
                            <div class="mifeco-action-icon"><span class="dashicons <?php echo htmlspecialchars($action['icon_class']); ?>"></span></div>
                            <div class="mifeco-action-label"><?php echo htmlspecialchars($action['label']); ?></div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mifeco-dashboard-recent">
                    <h3>Recent Research</h3>
                    <div class="mifeco-recent-items">
                        <?php foreach($view_data['recent_research_items'] as $item): ?>
                        <div class="mifeco-recent-item">
                            <div class="mifeco-recent-icon"><span class="dashicons <?php echo htmlspecialchars($item['icon_class']); ?>"></span></div>
                            <div class="mifeco-recent-content">
                                <div class="mifeco-recent-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                <div class="mifeco-recent-meta"><?php echo htmlspecialchars($item['meta']); ?></div>
                            </div>
                            <div class="mifeco-recent-actions">
                                <button class="mifeco-btn mifeco-btn-text" title="View"><span class="dashicons dashicons-visibility mifeco-icon-view"></span></button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                         <?php if (empty($view_data['recent_research_items'])): ?>
                            <p>No recent research items.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Market Trends Section -->
            <div class="mifeco-section" id="market-trends-section" data-section-content="market-trends" style="display:none;">
                <div class="mifeco-section-header"><h2>Market Trends Analysis</h2></div>
                <div class="mifeco-search-form">
                    <form id="market-trends-form">
                        <div class="mifeco-form-row">
                            <div class="mifeco-form-field"><label for="trends-industry">Industry</label><select id="trends-industry" name="industry"><option value="">Select Industry</option><option value="technology">Technology</option><!-- more options --></select></div>
                            <div class="mifeco-form-field"><label for="trends-query">Search Term</label><input type="text" id="trends-query" name="query" placeholder="e.g., Artificial Intelligence"></div>
                            <div class="mifeco-form-field"><label for="trends-date-range">Date Range</label><select id="trends-date-range" name="date_range"><option value="all">All Time</option><!-- more options --></select></div>
                        </div>
                        <div class="mifeco-form-actions"><button type="submit" class="mifeco-btn mifeco-btn-primary"><span class="dashicons dashicons-search mifeco-icon-search"></span> Search</button><button type="reset" class="mifeco-btn mifeco-btn-text">Reset</button></div>
                    </form>
                </div>
                <div class="mifeco-research-results" id="trends-results"><div class="mifeco-results-placeholder"><div class="mifeco-placeholder-icon"><span class="dashicons dashicons-chart-line mifeco-icon-trends"></span></div><div class="mifeco-placeholder-text">Enter search parameters above to analyze market trends</div></div></div>
            </div>

            <!-- Competitor Analysis Section -->
            <div class="mifeco-section" id="competitor-analysis-section" data-section-content="competitor-analysis" style="display:none;">
                <div class="mifeco-section-header"><h2>Competitor Analysis</h2></div>
                <div class="mifeco-search-form">
                    <form id="competitor-form">
                        <div class="mifeco-form-row">
                            <div class="mifeco-form-field"><label for="competitor-industry">Industry</label><select id="competitor-industry" name="industry"><option value="">Select Industry</option><!-- Options --></select></div>
                            <div class="mifeco-form-field"><label for="competitor-company">Primary Company</label><input type="text" id="competitor-company" name="company" placeholder="e.g., Acme Corporation"></div>
                        </div>
                        <div class="mifeco-form-field"><label>Competitors to Include</label><div class="mifeco-competitor-inputs"><div class="mifeco-competitor-input"><input type="text" name="competitors[]" placeholder="Competitor 1"></div></div><button type="button" class="mifeco-btn mifeco-btn-text mifeco-add-competitor"><span class="dashicons dashicons-plus mifeco-icon-add"></span> Add Competitor</button></div>
                        <div class="mifeco-form-actions"><button type="submit" class="mifeco-btn mifeco-btn-primary"><span class="dashicons dashicons-search mifeco-icon-search"></span> Analyze</button><button type="reset" class="mifeco-btn mifeco-btn-text">Reset</button></div>
                    </form>
                </div>
                <div class="mifeco-research-results" id="competitor-results"><div class="mifeco-results-placeholder"><div class="mifeco-placeholder-icon"><span class="dashicons dashicons-groups mifeco-icon-competitors"></span></div><div class="mifeco-placeholder-text">Enter search parameters to analyze competitors</div></div></div>
            </div>

            <!-- Other sections (simplified placeholders) -->
            <div class="mifeco-section" id="market-size-section" data-section-content="market-size" style="display:none;"><div class="mifeco-section-header"><h2>Market Size Estimation</h2></div><div class="mifeco-placeholder-content">Content for Market Size...</div></div>
            <div class="mifeco-section" id="reports-section" data-section-content="reports" style="display:none;"><div class="mifeco-section-header"><h2>Reports Generator</h2></div><div class="mifeco-placeholder-content">Content for Reports...</div></div>
            <div class="mifeco-section" id="saved-research-section" data-section-content="saved-research" style="display:none;"><div class="mifeco-section-header"><h2>Saved Research</h2></div><div class="mifeco-placeholder-content">Content for Saved Research...</div></div>
            <div class="mifeco-section" id="settings-section" data-section-content="settings" style="display:none;"><div class="mifeco-section-header"><h2>Settings</h2></div><div class="mifeco-placeholder-content">Content for Settings...</div></div>
        </div>
    </div>

    <div class="mifeco-product-footer">
        <div class="mifeco-footer-copyright">&copy; <?php echo date('Y'); ?> MIFECO. All rights reserved.</div>
        <div class="mifeco-footer-links">
            <a href="<?php echo htmlspecialchars($view_data['privacy_policy_url']); ?>">Privacy Policy</a>
            <a href="<?php echo htmlspecialchars($view_data['terms_of_service_url']); ?>">Terms of Service</a>
            <a href="<?php echo htmlspecialchars($view_data['support_url']); ?>">Support</a>
        </div>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.mifeco-nav-link');
    const contentSections = document.querySelectorAll('.mifeco-main-content .mifeco-section');
    const actionCards = document.querySelectorAll('.mifeco-action-card');

    function setActiveSection(sectionId) {
        navLinks.forEach(link => {
            link.parentElement.classList.remove('active');
            if (link.dataset.section === sectionId) {
                link.parentElement.classList.add('active');
            }
        });
        contentSections.forEach(section => {
            if (section.dataset.sectionContent === sectionId) {
                section.style.display = 'block';
                section.classList.add('active');
            } else {
                section.style.display = 'none';
                section.classList.remove('active');
            }
        });
    }

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;
            setActiveSection(sectionId);
            // Update URL hash without page jump for better UX, if desired
            // history.pushState(null, null, '#' + sectionId);
        });
    });

    actionCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.dataset.section;
            setActiveSection(sectionId);
            // history.pushState(null, null, '#' + sectionId);
        });
    });

    // Optional: Handle initial section based on URL hash
    // if (window.location.hash) {
    //    const initialSectionId = window.location.hash.substring(1);
    //    setActiveSection(initialSectionId);
    // }

    // JS for competitor adding (example, can be enhanced)
    const addCompetitorBtn = document.querySelector('.mifeco-add-competitor');
    if(addCompetitorBtn) {
        addCompetitorBtn.addEventListener('click', function() {
            const competitorInputs = document.querySelector('.mifeco-competitor-inputs');
            if(competitorInputs) {
                const newDiv = document.createElement('div');
                newDiv.classList.add('mifeco-competitor-input');
                const newInput = document.createElement('input');
                newInput.type = 'text';
                newInput.name = 'competitors[]';
                newInput.placeholder = 'Competitor ' + (competitorInputs.children.length + 1);
                newDiv.appendChild(newInput);
                competitorInputs.appendChild(newDiv);
            }
        });
    }
});
</script>

<style>
/* Basic styling for product interface. Assumes dashicons might be loaded or replaced with other icon fonts/SVGs. */
/* Add mifeco-icon-* classes for custom icons if Dashicons are not available */
.mifeco-product-interface { display: flex; flex-direction: column; height: 100vh; font-family: Arial, sans-serif; }
.mifeco-product-header { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; }
.mifeco-product-logo { display: flex; align-items: center; }
.mifeco-product-logo .mifeco-logo-icon { font-size: 1.5em; margin-right: 10px; }
.mifeco-product-logo h1 { font-size: 1.2em; margin: 0; }
.mifeco-user-controls { display: flex; align-items: center; }
.mifeco-user-info { display: flex; align-items: center; margin-right: 15px; }
.mifeco-avatar, .mifeco-avatar-placeholder { width: 32px; height: 32px; border-radius: 50%; margin-right: 8px; background-color: #ccc; text-align: center; line-height: 32px; }
.mifeco-controls .mifeco-control-link { margin-left: 10px; color: #555; text-decoration: none; font-size: 1.2em; }
.mifeco-product-content { display: flex; flex-grow: 1; overflow: hidden; }
.mifeco-sidebar { width: 240px; background-color: #343a40; color: #fff; padding: 15px; display: flex; flex-direction: column; }
.mifeco-nav-menu { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
.mifeco-nav-item a { display: flex; align-items: center; padding: 10px; color: #adb5bd; text-decoration: none; border-radius: 4px; margin-bottom: 5px; }
.mifeco-nav-item.active a, .mifeco-nav-item a:hover { background-color: #495057; color: #fff; }
.mifeco-nav-link .dashicons { margin-right: 8px; }
.mifeco-subscription-info { padding: 10px; border-top: 1px solid #495057; text-align: center; }
.mifeco-plan-badge { font-size: 0.9em; padding: 5px; background-color: #007bff; color: white; border-radius: 3px; margin-bottom: 5px; }
.mifeco-upgrade-link { font-size: 0.9em; color: #adb5bd; text-decoration: none; }
.mifeco-main-content { flex-grow: 1; padding: 20px; overflow-y: auto; background-color: #fff; }
.mifeco-section-header h2 { margin-top: 0; margin-bottom: 20px; }
.mifeco-dashboard-overview { display: flex; gap: 20px; margin-bottom: 30px; }
.mifeco-overview-stat { background-color: #e9ecef; padding: 15px; border-radius: 5px; text-align: center; flex-grow: 1; }
.mifeco-stat-value { font-size: 2em; font-weight: bold; }
.mifeco-stat-label { font-size: 0.9em; color: #6c757d; }
.mifeco-dashboard-actions h3, .mifeco-dashboard-recent h3 { margin-bottom: 15px; }
.mifeco-action-cards { display: flex; flex-wrap: wrap; gap: 15px; }
.mifeco-action-card { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 5px; padding: 20px; text-align: center; text-decoration: none; color: #343a40; width: calc(50% - 10px); /* Adjust for gap */ cursor:pointer; }
.mifeco-action-card:hover { background-color: #e9ecef; }
.mifeco-action-icon .dashicons { font-size: 2em; margin-bottom: 10px; }
.mifeco-recent-item { display: flex; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
.mifeco-recent-item:last-child { border-bottom: none; }
.mifeco-recent-icon .dashicons { font-size: 1.5em; margin-right: 15px; color: #6c757d; }
.mifeco-recent-content { flex-grow: 1; }
.mifeco-recent-title { font-weight: bold; }
.mifeco-recent-meta { font-size: 0.85em; color: #6c757d; }
.mifeco-btn-text { background: none; border: none; color: #007bff; cursor: pointer; padding: 5px; }
.mifeco-form-row { display: flex; gap: 15px; margin-bottom: 15px; }
.mifeco-form-field { flex-grow: 1; }
.mifeco-form-field label { display: block; margin-bottom: 5px; font-weight: 500; }
.mifeco-form-field input[type="text"], .mifeco-form-field select { width: 100%; padding: 8px; border: 1px solid #ced4da; border-radius: 4px; }
.mifeco-form-actions { margin-top: 20px; }
.mifeco-btn { padding: 8px 15px; border-radius: 4px; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
.mifeco-btn-primary { background-color: #007bff; color: white; border-color: #007bff; }
.mifeco-results-placeholder, .mifeco-placeholder-content { text-align: center; padding: 40px; color: #6c757d; }
.mifeco-placeholder-icon .dashicons { font-size: 3em; margin-bottom: 15px; }
.mifeco-product-footer { padding: 15px 20px; background-color: #f8f9fa; border-top: 1px solid #dee2e6; display: flex; justify-content: space-between; font-size: 0.9em; color: #6c757d; }
.mifeco-footer-links a { margin-left: 15px; color: #6c757d; text-decoration: none; }
.mifeco-footer-links a:hover { text-decoration: underline; }
</style>
