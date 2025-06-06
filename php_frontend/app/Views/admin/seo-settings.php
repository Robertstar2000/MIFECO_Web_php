<?php
/**
 * Admin SEO Settings View.
 * Expects $settings_data to be passed from a controller.
 * $settings_data would be an array structured by tabs, e.g.:
 * $settings_data = [
 *     'general' => ['enable_seo_features' => true, 'disable_author_archives' => false, ...],
 *     'social' => ['facebook_url' => '...', ...],
 *     'webmaster' => ['google_verification' => '...', ...],
 *     'analytics' => ['google_analytics_id' => '...', ...],
 * ];
 * Also expects $page_title (string) and $feedback_message (array: type, text).
 */

// Example data (controller would provide this)
$page_title = $page_title ?? 'SEO Settings';
$feedback_message = $feedback_message ?? null;
$settings_data = $settings_data ?? [
    'general' => [
        'enable_seo_features' => true,
        'disable_author_archives' => false,
        'disable_date_archives' => false,
        'remove_category_base' => false,
        'custom_robots_txt' => "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\nSitemap: /sitemap.xml"
    ],
    'social' => [
        'facebook_url' => 'https://facebook.com/example',
        'twitter_url' => 'https://twitter.com/example',
        'linkedin_url' => '',
        'instagram_url' => '',
        'youtube_url' => '',
        'pinterest_url' => '',
        'enable_social_sharing' => true,
        'social_sharing_networks' => ['facebook', 'twitter', 'linkedin'],
        'social_sharing_position' => 'after',
        'social_sharing_post_types' => ['post', 'page']
    ],
    'webmaster' => [
        'google_verification' => '',
        'bing_verification' => '',
        'pinterest_verification' => '',
        'yandex_verification' => '',
        'baidu_verification' => ''
    ],
    'analytics' => [
        'google_analytics_id' => '',
        'google_analytics_script_position' => 'head',
        'disable_analytics_logged_in' => true,
        'custom_analytics_code' => ''
    ]
];

// Helper for checked attribute
function _checked(bool $condition): string { return $condition ? 'checked' : ''; }
// Helper for selected attribute
function _selected($current_value, $option_value): string { return $current_value == $option_value ? 'selected' : ''; }

$all_post_types = $all_post_types ?? ['post' => 'Posts', 'page' => 'Pages', 'product' => 'Products']; // Example, controller provides
$social_networks_available = $social_networks_available ?? [
    'facebook' => 'Facebook', 'twitter' => 'Twitter', 'linkedin' => 'LinkedIn',
    'pinterest' => 'Pinterest', 'reddit' => 'Reddit', 'email' => 'Email'
];
$sharing_positions_available = $sharing_positions_available ?? [
    'before' => 'Before content', 'after' => 'After content', 'both' => 'Before and after content'
];
$analytics_script_positions = $analytics_script_positions ?? [
    'head' => 'Header (recommended for GA4)', 'body' => 'Footer'
];

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
            <a href="#social" class="nav-tab">Social</a>
            <a href="#webmaster" class="nav-tab">Webmaster Tools</a>
            <a href="#analytics" class="nav-tab">Analytics</a>
        </nav>
    </div>

    <form method="post" action="/admin/seo/save-settings"> <!-- Custom save URL -->
        <input type="hidden" name="_csrf_token" value="your_csrf_token_here"> <!-- CSRF token -->

        <!-- General Settings -->
        <div id="general" class="mifeco-tab-content active">
            <?php $options = $settings_data['general']; ?>
            <h2>General SEO Settings</h2>
            <p>Configure basic settings that affect the SEO of your entire site.</p>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Enable SEO Features</th>
                    <td>
                        <label><input type="checkbox" name="general[enable_seo_features]" value="1" <?php echo _checked($options['enable_seo_features'] ?? true); ?>> Enable all SEO features</label>
                        <p class="description">Uncheck to disable all SEO features globally.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Disable Author Archives</th>
                    <td>
                        <label><input type="checkbox" name="general[disable_author_archives]" value="1" <?php echo _checked($options['disable_author_archives'] ?? false); ?>> Disable author archives</label>
                        <p class="description">Redirects author archives to the homepage to prevent duplicate content issues.</p>
                    </td>
                </tr>
                 <tr>
                    <th scope="row">Disable Date Archives</th>
                    <td>
                        <label><input type="checkbox" name="general[disable_date_archives]" value="1" <?php echo _checked($options['disable_date_archives'] ?? false); ?>> Disable date archives</label>
                        <p class="description">Redirects date archives to the homepage to prevent duplicate content issues.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Remove Category Base</th>
                    <td>
                        <label><input type="checkbox" name="general[remove_category_base]" value="1" <?php echo _checked($options['remove_category_base'] ?? false); ?>> Remove category base (e.g. /category/) from URLs</label>
                        <p class="description">Makes URLs cleaner. Note: This may conflict with some permalink structures.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Custom Robots.txt Content</th>
                    <td>
                        <textarea name="general[custom_robots_txt]" rows="8" class="large-text code"><?php echo htmlspecialchars($options['custom_robots_txt'] ?? ''); ?></textarea>
                        <p class="description">Add custom content for robots.txt. Leave blank for system default.</p>
                        <p class="description">Current robots.txt: <a href="/robots.txt" target="_blank">/robots.txt</a></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Social Settings -->
        <div id="social" class="mifeco-tab-content" style="display:none;">
            <?php $options = $settings_data['social']; ?>
            <h2>Social Media Settings</h2>
            <p>Configure settings for social media integration and sharing.</p>
            <h3>Social Media Profiles</h3>
            <p>Add your social media profile URLs for schema markup.</p>
            <table class="form-table mifeco-settings-table">
                <?php
                $social_profiles = ['facebook_url' => 'Facebook URL', 'twitter_url' => 'Twitter URL', 'linkedin_url' => 'LinkedIn URL', 'instagram_url' => 'Instagram URL', 'youtube_url' => 'YouTube URL', 'pinterest_url' => 'Pinterest URL'];
                foreach ($social_profiles as $key => $label): ?>
                <tr>
                    <th scope="row"><?php echo htmlspecialchars($label); ?></th>
                    <td>
                        <input type="url" name="social[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($options[$key] ?? ''); ?>" class="regular-text">
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h3>Social Sharing Settings</h3>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Enable Social Sharing</th>
                    <td><label><input type="checkbox" name="social[enable_social_sharing]" value="1" <?php echo _checked($options['enable_social_sharing'] ?? true); ?>> Enable social sharing buttons</label></td>
                </tr>
                <tr>
                    <th scope="row">Social Networks</th>
                    <td><fieldset>
                        <?php foreach ($social_networks_available as $network_key => $network_label): ?>
                        <label><input type="checkbox" name="social[social_sharing_networks][]" value="<?php echo $network_key; ?>" <?php echo _checked(in_array($network_key, $options['social_sharing_networks'] ?? [])); ?>> <?php echo htmlspecialchars($network_label); ?></label><br>
                        <?php endforeach; ?>
                    </fieldset></td>
                </tr>
                <tr>
                    <th scope="row">Sharing Buttons Position</th>
                    <td><select name="social[social_sharing_position]">
                        <?php foreach ($sharing_positions_available as $pos_key => $pos_label): ?>
                        <option value="<?php echo $pos_key; ?>" <?php echo _selected($options['social_sharing_position'] ?? 'after', $pos_key); ?>><?php echo htmlspecialchars($pos_label); ?></option>
                        <?php endforeach; ?>
                    </select></td>
                </tr>
                <tr>
                    <th scope="row">Show On</th>
                    <td><fieldset>
                        <?php foreach ($all_post_types as $pt_key => $pt_label): ?>
                        <label><input type="checkbox" name="social[social_sharing_post_types][]" value="<?php echo $pt_key; ?>" <?php echo _checked(in_array($pt_key, $options['social_sharing_post_types'] ?? ['post'])); ?>> <?php echo htmlspecialchars($pt_label); ?></label><br>
                        <?php endforeach; ?>
                    </fieldset></td>
                </tr>
            </table>
        </div>

        <!-- Webmaster Tools Settings -->
        <div id="webmaster" class="mifeco-tab-content" style="display:none;">
            <?php $options = $settings_data['webmaster']; ?>
            <h2>Webmaster Tools Verification</h2>
            <p>Add verification codes for various webmaster tools services.</p>
            <table class="form-table mifeco-settings-table">
                <?php
                $webmaster_fields = [
                    'google_verification' => 'Google Search Console',
                    'bing_verification' => 'Bing Webmaster Tools',
                    'pinterest_verification' => 'Pinterest',
                    'yandex_verification' => 'Yandex Webmaster',
                    'baidu_verification' => 'Baidu Webmaster Tools'
                ];
                foreach ($webmaster_fields as $key => $label):
                ?>
                <tr>
                    <th scope="row"><?php echo htmlspecialchars($label); ?></th>
                    <td><input type="text" name="webmaster[<?php echo $key; ?>]" value="<?php echo htmlspecialchars($options[$key] ?? ''); ?>" class="regular-text">
                    <?php if ($key === 'google_verification'): ?>
                    <p class="description">Enter your Google verification code (content of meta tag).</p>
                    <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <!-- Analytics Settings -->
        <div id="analytics" class="mifeco-tab-content" style="display:none;">
            <?php $options = $settings_data['analytics']; ?>
            <h2>Analytics Settings</h2>
            <p>Configure analytics settings for your site.</p>
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row">Google Analytics ID</th>
                    <td>
                        <input type="text" name="analytics[google_analytics_id]" value="<?php echo htmlspecialchars($options['google_analytics_id'] ?? ''); ?>" class="regular-text" placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
                        <p class="description">Enter your Google Analytics measurement ID.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Analytics Script Position</th>
                    <td><select name="analytics[google_analytics_script_position]">
                        <?php foreach ($analytics_script_positions as $pos_key => $pos_label): ?>
                        <option value="<?php echo $pos_key; ?>" <?php echo _selected($options['google_analytics_script_position'] ?? 'head', $pos_key); ?>><?php echo htmlspecialchars($pos_label); ?></option>
                        <?php endforeach; ?>
                    </select></td>
                </tr>
                <tr>
                    <th scope="row">Disable for Logged-in Users</th>
                    <td><label><input type="checkbox" name="analytics[disable_analytics_logged_in]" value="1" <?php echo _checked($options['disable_analytics_logged_in'] ?? true); ?>> Do not track logged-in users</label></td>
                </tr>
                <tr>
                    <th scope="row">Custom Analytics Code</th>
                    <td>
                        <textarea name="analytics[custom_analytics_code]" rows="8" class="large-text code"><?php echo htmlspecialchars($options['custom_analytics_code'] ?? ''); ?></textarea>
                        <p class="description">Enter custom analytics code (e.g., Facebook Pixel, Hotjar, etc.). This will be added to the site header.</p>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
    </form>
</div>

<script>
// Basic tab functionality - assumes jQuery for simplicity, can be vanilla JS
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        $('.mifeco-admin-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            var targetId = $(this).attr('href');

            $(this).addClass('nav-tab-active').siblings().removeClass('nav-tab-active');
            $('.mifeco-tab-content').removeClass('active').hide();
            $(targetId).addClass('active').show();
        });
        // Ensure the initially active tab's content is shown
        $('.mifeco-tab-content.active').show();
    });
} else {
    // Vanilla JS version
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
                    content.style.display = 'none'; // Hide all
                    if ('#' + content.id === targetId) {
                        content.classList.add('active');
                        content.style.display = 'block'; // Show target
                    }
                });
            });
        });
         // Ensure the initially active tab's content is shown
        const activeContent = document.querySelector('.mifeco-tab-content.active');
        if(activeContent) activeContent.style.display = 'block';
    });
}
</script>
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
    .large-text { width: 100%; }
    .button-primary { background-color: #0073aa; color: white; border-color: #0073aa; padding: 8px 15px; text-decoration: none; border-radius: 3px; }
    .mifeco-admin-notice { padding: 10px; margin-bottom: 15px; border-left-width: 4px; border-left-style: solid; }
    .notice-info { border-left-color: #0073aa; background: #f0f6fc; }
</style>
