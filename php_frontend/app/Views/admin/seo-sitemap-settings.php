<?php
/**
 * Admin SEO Sitemap Settings View.
 * Expects $sitemap_settings_data to be passed from a controller.
 * $sitemap_settings_data would be an array structured by tabs, e.g.:
 * $sitemap_settings_data = [
 *     'general' => ['enable_sitemap' => true, 'ping_search_engines' => true, 'sitemap_url' => '/sitemap.xml'],
 *     'content' => ['include_post_types' => ['post', 'page'], ...],
 *     'priority' => [...],
 *     'advanced' => [...]
 * ];
 * Also expects $page_title (string) and $feedback_message (array: type, text).
 */

// Example data (controller would provide this)
$page_title = $page_title ?? 'XML Sitemap Settings';
$feedback_message = $feedback_message ?? null;

// Default structure for settings data
$sitemap_settings_data = $sitemap_settings_data ?? [
    'general' => [
        'enable_sitemap' => true,
        'ping_search_engines' => true,
    ],
    'content' => [
        'include_post_types' => ['post', 'page'],
        'include_taxonomies' => ['category', 'post_tag'],
        'include_archives' => ['author', 'date'],
        'exclude_items' => "123\npost_type:attachment",
    ],
    'priority' => [
        'post_type_priorities' => ['page' => 0.8, 'post' => 0.7],
        'taxonomy_priorities' => ['category' => 0.6, 'post_tag' => 0.5],
        'change_frequencies' => ['home' => 'daily', 'page' => 'weekly', 'post' => 'monthly', 'taxonomy' => 'weekly', 'archive' => 'monthly'],
    ],
    'advanced' => [
        'max_entries_per_sitemap' => 1000,
        'enable_image_sitemap' => true,
        'enable_news_sitemap' => false,
        'enable_html_sitemap' => true,
    ]
];

// Data that would typically come from the application's configuration or a service
$available_post_types = $available_post_types ?? ['post' => 'Posts', 'page' => 'Pages', 'product' => 'Products'];
$available_taxonomies = $available_taxonomies ?? ['category' => 'Categories', 'post_tag' => 'Tags'];
$available_archive_types = $available_archive_types ?? [
    'author' => 'Author Archives', 'date' => 'Date Archives', /* 'post_type_product' => 'Product Archive' */
];
$frequency_options = $frequency_options ?? [
    'always' => 'Always', 'hourly' => 'Hourly', 'daily' => 'Daily', 'weekly' => 'Weekly',
    'monthly' => 'Monthly', 'yearly' => 'Yearly', 'never' => 'Never',
];
$content_type_freq_labels = $content_type_freq_labels ?? [
    'home' => 'Homepage', 'page' => 'Pages', 'post' => 'Posts',
    'taxonomy' => 'Taxonomies', 'archive' => 'Archives',
];


// Helper for checked attribute
function _sitemap_checked(bool $condition): string { return $condition ? 'checked' : ''; }
// Helper for selected attribute
function _sitemap_selected($current_value, $option_value): string { return ($current_value == $option_value) ? 'selected' : ''; }
?>

<div class="wrap mifeco-admin-wrap">
    <h1><?php echo htmlspecialchars($page_title); ?></h1>

    <?php if ($feedback_message): ?>
        <div class="mifeco-admin-notice notice-<?php echo htmlspecialchars($feedback_message['type'] ?? 'info'); ?>">
            <p><?php echo htmlspecialchars($feedback_message['text'] ?? ''); ?></p>
        </div>
    <?php endif; ?>

    <div class="mifeco-admin-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active">General</a>
            <a href="#content" class="nav-tab">Content</a>
            <a href="#priority" class="nav-tab">Priority</a>
            <a href="#advanced" class="nav-tab">Advanced</a>
            <a href="#tools" class="nav-tab">Tools</a>
        </nav>
    </div>

    <form method="post" action="/admin/seo/save-sitemap-settings"> <!-- Custom save URL -->
        <input type="hidden" name="_csrf_token" value="your_csrf_token_here"> <!-- CSRF token -->

        <!-- General Settings -->
        <div id="general" class="mifeco-tab-content active">
            <?php $options = $sitemap_settings_data['general']; ?>
            <h2>General Sitemap Settings</h2>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Enable XML Sitemap</th>
                    <td>
                        <label><input type="checkbox" name="general[enable_sitemap]" value="1" <?php echo _sitemap_checked($options['enable_sitemap'] ?? true); ?>> Enable XML sitemap generation</label>
                        <p class="description">Generate XML sitemaps for better search engine indexing.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Ping Search Engines</th>
                    <td>
                        <label><input type="checkbox" name="general[ping_search_engines]" value="1" <?php echo _sitemap_checked($options['ping_search_engines'] ?? true); ?>> Ping search engines when sitemap is updated</label>
                        <p class="description">Automatically notify Google and Bing when your sitemap changes.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Sitemap URL</th>
                    <td>
                        <?php $sitemap_full_url = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/sitemap.xml'; ?>
                        <input type="text" value="<?php echo htmlspecialchars($sitemap_full_url); ?>" class="regular-text" readonly>
                        <p class="description">The URL of your XML sitemap.</p>
                        <button type="button" class="button mifeco-copy-button" data-clipboard-text="<?php echo htmlspecialchars($sitemap_full_url); ?>">Copy URL</button>
                        <a href="<?php echo htmlspecialchars($sitemap_full_url); ?>" target="_blank" class="button">View Sitemap</a>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Content Settings -->
        <div id="content" class="mifeco-tab-content" style="display:none;">
            <?php $options = $sitemap_settings_data['content']; ?>
            <h2>Content Settings</h2>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Include Post Types</th>
                    <td><fieldset>
                        <?php foreach ($available_post_types as $key => $label): ?>
                        <label><input type="checkbox" name="content[include_post_types][]" value="<?php echo $key; ?>" <?php echo _sitemap_checked(in_array($key, $options['include_post_types'] ?? [])); ?>> <?php echo htmlspecialchars($label); ?></label><br>
                        <?php endforeach; ?>
                    </fieldset><p class="description">If none selected, all public post types might be included by default by the generator.</p></td>
                </tr>
                <tr>
                    <th scope="row">Include Taxonomies</th>
                    <td><fieldset>
                        <?php foreach ($available_taxonomies as $key => $label): ?>
                        <label><input type="checkbox" name="content[include_taxonomies][]" value="<?php echo $key; ?>" <?php echo _sitemap_checked(in_array($key, $options['include_taxonomies'] ?? [])); ?>> <?php echo htmlspecialchars($label); ?></label><br>
                        <?php endforeach; ?>
                    </fieldset><p class="description">If none selected, all public taxonomies might be included by default.</p></td>
                </tr>
                <tr>
                    <th scope="row">Include Archives</th>
                     <td><fieldset>
                        <?php foreach ($available_archive_types as $key => $label): ?>
                        <label><input type="checkbox" name="content[include_archives][]" value="<?php echo $key; ?>" <?php echo _sitemap_checked(in_array($key, $options['include_archives'] ?? [])); ?>> <?php echo htmlspecialchars($label); ?></label><br>
                        <?php endforeach; ?>
                    </fieldset><p class="description">If none selected, relevant archives might be included by default.</p></td>
                </tr>
                <tr>
                    <th scope="row">Exclude Items</th>
                    <td>
                        <textarea name="content[exclude_items]" rows="5" class="large-text code"><?php echo htmlspecialchars($options['exclude_items'] ?? ''); ?></textarea>
                        <p class="description">Enter post/page IDs to exclude, one per line. You can also use type prefixes like "post_type:product_name" or "taxonomy:category_slug".</p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Priority Settings -->
        <div id="priority" class="mifeco-tab-content" style="display:none;">
            <?php $options = $sitemap_settings_data['priority']; ?>
            <h2>Priority Settings</h2>
             <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Post Type Priorities</th>
                    <td><table class="widefat striped" style="width: auto;"><thead><tr><th>Post Type</th><th>Priority (0.0 - 1.0)</th></tr></thead><tbody>
                        <?php foreach ($available_post_types as $key => $label):
                            $priority_val = $options['post_type_priorities'][$key] ?? 0.5; ?>
                        <tr><td><?php echo htmlspecialchars($label); ?></td><td><input type="number" name="priority[post_type_priorities][<?php echo $key; ?>]" value="<?php echo htmlspecialchars($priority_val); ?>" min="0" max="1" step="0.1" class="small-text"></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></td>
                </tr>
                <tr>
                    <th scope="row">Taxonomy Priorities</th>
                    <td><table class="widefat striped" style="width: auto;"><thead><tr><th>Taxonomy</th><th>Priority (0.0 - 1.0)</th></tr></thead><tbody>
                        <?php foreach ($available_taxonomies as $key => $label):
                             $priority_val = $options['taxonomy_priorities'][$key] ?? 0.5; ?>
                        <tr><td><?php echo htmlspecialchars($label); ?></td><td><input type="number" name="priority[taxonomy_priorities][<?php echo $key; ?>]" value="<?php echo htmlspecialchars($priority_val); ?>" min="0" max="1" step="0.1" class="small-text"></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></td>
                </tr>
                <tr>
                    <th scope="row">Change Frequencies</th>
                    <td><table class="widefat striped" style="width: auto;"><thead><tr><th>Content Type</th><th>Change Frequency</th></tr></thead><tbody>
                        <?php foreach ($content_type_freq_labels as $key => $label):
                            $freq_val = $options['change_frequencies'][$key] ?? 'monthly'; ?>
                        <tr><td><?php echo htmlspecialchars($label); ?></td><td><select name="priority[change_frequencies][<?php echo $key; ?>]">
                            <?php foreach ($frequency_options as $f_key => $f_label): ?>
                            <option value="<?php echo $f_key; ?>" <?php echo _sitemap_selected($freq_val, $f_key); ?>><?php echo htmlspecialchars($f_label); ?></option>
                            <?php endforeach; ?>
                        </select></td></tr>
                        <?php endforeach; ?>
                    </tbody></table></td>
                </tr>
            </table>
        </div>

        <!-- Advanced Settings -->
        <div id="advanced" class="mifeco-tab-content" style="display:none;">
            <?php $options = $sitemap_settings_data['advanced']; ?>
            <h2>Advanced Settings</h2>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Max Entries Per Sitemap</th>
                    <td>
                        <input type="number" name="advanced[max_entries_per_sitemap]" value="<?php echo htmlspecialchars($options['max_entries_per_sitemap'] ?? 1000); ?>" min="100" max="50000" step="100" class="small-text">
                        <p class="description">Maximum number of URLs per sitemap file.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Enable Image Sitemap</th>
                    <td><label><input type="checkbox" name="advanced[enable_image_sitemap]" value="1" <?php echo _sitemap_checked($options['enable_image_sitemap'] ?? true); ?>> Include image information in the sitemap</label></td>
                </tr>
                <tr>
                    <th scope="row">Enable News Sitemap</th>
                    <td><label><input type="checkbox" name="advanced[enable_news_sitemap]" value="1" <?php echo _sitemap_checked($options['enable_news_sitemap'] ?? false); ?>> Generate a Google News sitemap</label>
                    <p class="description">Only enable if your site is registered with Google News.</p></td>
                </tr>
                <tr>
                    <th scope="row">Enable HTML Sitemap</th>
                    <td><label><input type="checkbox" name="advanced[enable_html_sitemap]" value="1" <?php echo _sitemap_checked($options['enable_html_sitemap'] ?? true); ?>> Generate an HTML sitemap</label>
                    <p class="description">Create a human-readable HTML sitemap. (Shortcode for display would be handled by app logic)</p></td>
                </tr>
            </table>
        </div>

        <!-- Tools Tab -->
        <div id="tools" class="mifeco-tab-content" style="display:none;">
            <h2>Sitemap Tools</h2>
            <div class="mifeco-card">
                <div class="mifeco-card-header"><h3>Sitemap Validation</h3></div>
                <div class="mifeco-card-body">
                    <p>Verify that your sitemap is valid and accessible.</p>
                    <button type="button" id="mifeco-validate-sitemap-btn" class="button button-primary">Validate Sitemap</button>
                    <div id="mifeco-sitemap-validation-results" style="margin-top: 20px;">
                        <!-- Validation results will be shown here by JavaScript -->
                    </div>
                </div>
            </div>
            <div class="mifeco-card" style="margin-top: 20px;">
                <div class="mifeco-card-header"><h3>Submit to Search Engines</h3></div>
                <div class="mifeco-card-body">
                     <?php $sitemap_full_url = rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . ($_SERVER['HTTP_HOST'] ?? 'localhost'), '/') . '/sitemap.xml'; ?>
                    <input type="text" id="mifeco-sitemap-url-display" value="<?php echo htmlspecialchars($sitemap_full_url); ?>" class="regular-text" readonly>
                    <div style="margin-top:10px;">
                        <a href="https://www.google.com/ping?sitemap=<?php echo urlencode($sitemap_full_url); ?>" target="_blank" class="button">Submit to Google</a>
                        <a href="https://www.bing.com/ping?sitemap=<?php echo urlencode($sitemap_full_url); ?>" target="_blank" class="button">Submit to Bing</a>
                    </div>
                </div>
            </div>
        </div>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>

<script>
// Basic tab functionality (same as seo-settings.php)
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        $('.mifeco-admin-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            var targetId = $(this).attr('href');
            $(this).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
            $('.mifeco-tab-content').removeClass('active').hide();
            $(targetId).addClass('active').show();
        });
        $('.mifeco-tab-content.active').show();

        // Clipboard copy (basic, can be improved)
        $('.mifeco-copy-button').on('click', function() {
            var textToCopy = $(this).data('clipboard-text');
            navigator.clipboard.writeText(textToCopy).then(function() {
                alert('Sitemap URL copied to clipboard!');
            }, function(err) {
                alert('Failed to copy URL.');
            });
        });

        // Placeholder for sitemap validation - would call a custom API endpoint
        $('#mifeco-validate-sitemap-btn').on('click', function() {
            var $resultsDiv = $('#mifeco-sitemap-validation-results');
            $resultsDiv.html('Validation logic would call a custom backend API endpoint. (Not implemented in this static view)');
            // Example:
            // $.post('/admin/seo/validate-sitemap', { _csrf_token: 'token' }, function(response) {
            //    $resultsDiv.html(response.message);
            // });
        });
    });
} else {
    // Vanilla JS version for tabs
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.mifeco-admin-tabs .nav-tab');
        const tabContents = document.querySelectorAll('.mifeco-tab-content');
        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                tabs.forEach(t => t.classList.remove('nav-tab-active'));
                this.classList.add('nav-tab-active');
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                    if ('#' + content.id === targetId) {
                        content.classList.add('active');
                        content.style.display = 'block';
                    }
                });
            });
        });
        const activeContent = document.querySelector('.mifeco-tab-content.active');
        if(activeContent) activeContent.style.display = 'block';

        document.querySelectorAll('.mifeco-copy-button').forEach(button => {
            button.addEventListener('click', function() {
                const textToCopy = this.dataset.clipboardText;
                navigator.clipboard.writeText(textToCopy).then(function() {
                    alert('Sitemap URL copied to clipboard!');
                }, function(err) {
                    alert('Failed to copy URL: ' + err);
                });
            });
        });

        const validateBtn = document.getElementById('mifeco-validate-sitemap-btn');
        if(validateBtn) {
            validateBtn.addEventListener('click', function() {
                 document.getElementById('mifeco-sitemap-validation-results').textContent = 'Validation logic would call a custom backend API endpoint. (Not implemented in this static view)';
            });
        }
    });
}
</script>
<!-- Styles are assumed to be similar to seo-settings.php or loaded globally -->
<style>
    /* Basic Admin Styles - can be moved to a separate CSS file */
    .mifeco-admin-wrap { margin: 20px; }
    .mifeco-admin-tabs .nav-tab-wrapper { border-bottom: 1px solid #ccc; margin-bottom: 20px; }
    .mifeco-admin-tabs .nav-tab { background: #f1f1f1; border: 1px solid #ccc; border-bottom: none; padding: 10px 15px; text-decoration: none; color: #555; margin-right: 5px; display: inline-block;}
    .mifeco-admin-tabs .nav-tab-active { background: #fff; border-bottom: 1px solid #fff; position: relative; top: 1px; }
    .mifeco-tab-content { display: none; }
    .mifeco-tab-content.active { display: block; }
    .form-table { width: 100%; border-collapse: collapse; }
    .form-table th, .form-table td { padding: 15px; border-bottom: 1px solid #f0f0f1; vertical-align: top; text-align: left; }
    .form-table th { width: 200px; font-weight: normal; }
    .form-table p.description { font-size: 0.9em; color: #666; }
    .regular-text { width: 100%; max-width: 400px; }
    .small-text { width: 100px; }
    .large-text { width: 100%; }
    .button { padding: 8px 15px; text-decoration: none; border-radius: 3px; cursor: pointer; margin-right:5px; }
    .button-primary { background-color: #0073aa; color: white; border: 1px solid #0073aa; }
    .mifeco-card { border: 1px solid #e5e5e5; margin-bottom:15px; box-shadow: 0 1px 1px rgba(0,0,0,.04); background: #fff; }
    .mifeco-card-header h3 { margin:0; padding:10px 15px; font-size:1em; border-bottom:1px solid #e5e5e5;}
    .mifeco-card-body { padding:15px; }
    .widefat { width:auto; } /* Simplified for example */
</style>
