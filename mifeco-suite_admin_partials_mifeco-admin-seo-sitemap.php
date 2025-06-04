<?php
/**
 * Admin SEO Sitemap Template
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
    
    <?php settings_errors(); ?>
    
    <div class="mifeco-admin-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'mifeco-suite'); ?></a>
            <a href="#content" class="nav-tab"><?php _e('Content', 'mifeco-suite'); ?></a>
            <a href="#priority" class="nav-tab"><?php _e('Priority', 'mifeco-suite'); ?></a>
            <a href="#advanced" class="nav-tab"><?php _e('Advanced', 'mifeco-suite'); ?></a>
            <a href="#tools" class="nav-tab"><?php _e('Tools', 'mifeco-suite'); ?></a>
        </nav>
    </div>
    
    <form method="post" action="options.php">
        <?php
        settings_fields('mifeco_sitemap_settings_group');
        $options = get_option('mifeco_sitemap_settings', array());
        ?>
        
        <!-- General Settings -->
        <div id="general" class="mifeco-tab-content active">
            <h2><?php _e('General Sitemap Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure basic settings for your XML sitemap.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Enable XML Sitemap', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_sitemap_settings[enable_sitemap]" value="1" <?php checked(isset($options['enable_sitemap']) ? $options['enable_sitemap'] : true); ?>>
                            <?php _e('Enable XML sitemap generation', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Generate XML sitemaps for better search engine indexing.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Ping Search Engines', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_sitemap_settings[ping_search_engines]" value="1" <?php checked(isset($options['ping_search_engines']) ? $options['ping_search_engines'] : true); ?>>
                            <?php _e('Ping search engines when sitemap is updated', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically notify Google and Bing when your sitemap changes.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Sitemap URL', 'mifeco-suite'); ?></th>
                    <td>
                        <?php $sitemap_url = home_url('sitemap.xml'); ?>
                        <input type="text" value="<?php echo esc_url($sitemap_url); ?>" class="regular-text" readonly>
                        <p class="description"><?php _e('The URL of your XML sitemap.', 'mifeco-suite'); ?></p>
                        <button type="button" class="button mifeco-copy-button" data-clipboard-text="<?php echo esc_url($sitemap_url); ?>"><?php _e('Copy URL', 'mifeco-suite'); ?></button>
                        <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" class="button"><?php _e('View Sitemap', 'mifeco-suite'); ?></a>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('What is a Sitemap?', 'mifeco-suite'); ?></th>
                    <td>
                        <div class="mifeco-card">
                            <div class="mifeco-card-body">
                                <p><?php _e('A sitemap is a file where you can list the web pages of your site to tell search engines about the organization of your site content. Search engines like Google read this file to more intelligently crawl your site.', 'mifeco-suite'); ?></p>
                                <p><?php _e('Benefits of XML sitemaps include:', 'mifeco-suite'); ?></p>
                                <ul>
                                    <li><?php _e('Better crawling: Search engines can discover new content quicker.', 'mifeco-suite'); ?></li>
                                    <li><?php _e('Improved indexing: More complete coverage of your website in search results.', 'mifeco-suite'); ?></li>
                                    <li><?php _e('Faster discovery: New content is found and indexed more quickly.', 'mifeco-suite'); ?></li>
                                    <li><?php _e('Better ranking signals: Helps search engines understand your site structure.', 'mifeco-suite'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Content Settings -->
        <div id="content" class="mifeco-tab-content">
            <h2><?php _e('Content Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure what content to include in your sitemap.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Include Post Types', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $include_post_types = isset($options['include_post_types']) ? $options['include_post_types'] : array();
                        $post_types = get_post_types(array('public' => true), 'objects');
                        ?>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Include Post Types', 'mifeco-suite'); ?></legend>
                            <?php foreach ($post_types as $post_type) : ?>
                                <label>
                                    <input type="checkbox" name="mifeco_sitemap_settings[include_post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $include_post_types) || empty($include_post_types)); ?>>
                                    <?php echo esc_html($post_type->labels->name); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e('Select which post types to include in the sitemap.', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('If none selected, all public post types will be included.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Include Taxonomies', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $include_taxonomies = isset($options['include_taxonomies']) ? $options['include_taxonomies'] : array();
                        $taxonomies = get_taxonomies(array('public' => true), 'objects');
                        ?>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Include Taxonomies', 'mifeco-suite'); ?></legend>
                            <?php foreach ($taxonomies as $taxonomy) : ?>
                                <label>
                                    <input type="checkbox" name="mifeco_sitemap_settings[include_taxonomies][]" value="<?php echo esc_attr($taxonomy->name); ?>" <?php checked(in_array($taxonomy->name, $include_taxonomies) || empty($include_taxonomies)); ?>>
                                    <?php echo esc_html($taxonomy->labels->name); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e('Select which taxonomies to include in the sitemap.', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('If none selected, all public taxonomies will be included.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Include Archives', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $include_archives = isset($options['include_archives']) ? $options['include_archives'] : array();
                        $archive_types = array(
                            'author' => __('Author Archives', 'mifeco-suite'),
                            'date' => __('Date Archives', 'mifeco-suite'),
                        );
                        
                        // Add post type archives
                        $archive_post_types = get_post_types(array('public' => true, 'has_archive' => true), 'objects');
                        foreach ($archive_post_types as $post_type) {
                            $archive_types['post_type_' . $post_type->name] = sprintf(__('%s Archive', 'mifeco-suite'), $post_type->labels->name);
                        }
                        ?>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Include Archives', 'mifeco-suite'); ?></legend>
                            <?php foreach ($archive_types as $type => $label) : ?>
                                <label>
                                    <input type="checkbox" name="mifeco_sitemap_settings[include_archives][]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $include_archives) || empty($include_archives)); ?>>
                                    <?php echo esc_html($label); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e('Select which archives to include in the sitemap.', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('If none selected, all archives will be included.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Exclude Items', 'mifeco-suite'); ?></th>
                    <td>
                        <textarea name="mifeco_sitemap_settings[exclude_items]" rows="5" class="large-text code"><?php echo esc_textarea(isset($options['exclude_items']) ? $options['exclude_items'] : ''); ?></textarea>
                        <p class="description"><?php _e('Enter post/page IDs to exclude from the sitemap, one per line. You can also use post types or taxonomies with "post_type:name" or "taxonomy:name".', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('Examples: "123" (exclude post ID 123), "post_type:revision" (exclude all revisions), "taxonomy:category:3" (exclude category ID 3).', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Priority Settings -->
        <div id="priority" class="mifeco-tab-content">
            <h2><?php _e('Priority Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure priorities and change frequencies for different content types.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Post Type Priorities', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $post_type_priorities = isset($options['post_type_priorities']) ? $options['post_type_priorities'] : array();
                        $post_types = get_post_types(array('public' => true), 'objects');
                        
                        // Default priorities
                        $default_priorities = array(
                            'page' => 0.8,
                            'post' => 0.7,
                            'product' => 0.8,
                            'mifeco_product' => 0.8,
                            'attachment' => 0.4,
                        );
                        ?>
                        <table class="widefat striped" style="width: auto;">
                            <thead>
                                <tr>
                                    <th><?php _e('Post Type', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Priority (0.0 - 1.0)', 'mifeco-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($post_types as $post_type) : 
                                    $priority = isset($post_type_priorities[$post_type->name]) ? $post_type_priorities[$post_type->name] : (isset($default_priorities[$post_type->name]) ? $default_priorities[$post_type->name] : 0.5);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($post_type->labels->name); ?></td>
                                        <td>
                                            <input type="number" name="mifeco_sitemap_settings[post_type_priorities][<?php echo esc_attr($post_type->name); ?>]" value="<?php echo esc_attr($priority); ?>" min="0" max="1" step="0.1" class="small-text">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="description"><?php _e('Set the priority for each post type (0.0 - 1.0, higher values mean higher priority).', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Taxonomy Priorities', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $taxonomy_priorities = isset($options['taxonomy_priorities']) ? $options['taxonomy_priorities'] : array();
                        $taxonomies = get_taxonomies(array('public' => true), 'objects');
                        
                        // Default priorities
                        $default_priorities = array(
                            'category' => 0.6,
                            'post_tag' => 0.5,
                            'product_cat' => 0.6,
                            'product_tag' => 0.5,
                        );
                        ?>
                        <table class="widefat striped" style="width: auto;">
                            <thead>
                                <tr>
                                    <th><?php _e('Taxonomy', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Priority (0.0 - 1.0)', 'mifeco-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($taxonomies as $taxonomy) : 
                                    $priority = isset($taxonomy_priorities[$taxonomy->name]) ? $taxonomy_priorities[$taxonomy->name] : (isset($default_priorities[$taxonomy->name]) ? $default_priorities[$taxonomy->name] : 0.5);
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($taxonomy->labels->name); ?></td>
                                        <td>
                                            <input type="number" name="mifeco_sitemap_settings[taxonomy_priorities][<?php echo esc_attr($taxonomy->name); ?>]" value="<?php echo esc_attr($priority); ?>" min="0" max="1" step="0.1" class="small-text">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="description"><?php _e('Set the priority for each taxonomy (0.0 - 1.0, higher values mean higher priority).', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Change Frequencies', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $change_frequencies = isset($options['change_frequencies']) ? $options['change_frequencies'] : array();
                        
                        // Default frequencies
                        $default_frequencies = array(
                            'home' => 'daily',
                            'page' => 'weekly',
                            'post' => 'monthly',
                            'taxonomy' => 'weekly',
                            'archive' => 'monthly',
                        );
                        
                        // Content types
                        $content_types = array(
                            'home' => __('Homepage', 'mifeco-suite'),
                            'page' => __('Pages', 'mifeco-suite'),
                            'post' => __('Posts', 'mifeco-suite'),
                            'taxonomy' => __('Taxonomies', 'mifeco-suite'),
                            'archive' => __('Archives', 'mifeco-suite'),
                        );
                        
                        // Frequency options
                        $frequency_options = array(
                            'always' => __('Always', 'mifeco-suite'),
                            'hourly' => __('Hourly', 'mifeco-suite'),
                            'daily' => __('Daily', 'mifeco-suite'),
                            'weekly' => __('Weekly', 'mifeco-suite'),
                            'monthly' => __('Monthly', 'mifeco-suite'),
                            'yearly' => __('Yearly', 'mifeco-suite'),
                            'never' => __('Never', 'mifeco-suite'),
                        );
                        ?>
                        <table class="widefat striped" style="width: auto;">
                            <thead>
                                <tr>
                                    <th><?php _e('Content Type', 'mifeco-suite'); ?></th>
                                    <th><?php _e('Change Frequency', 'mifeco-suite'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($content_types as $type => $label) : 
                                    $frequency = isset($change_frequencies[$type]) ? $change_frequencies[$type] : (isset($default_frequencies[$type]) ? $default_frequencies[$type] : 'monthly');
                                ?>
                                    <tr>
                                        <td><?php echo esc_html($label); ?></td>
                                        <td>
                                            <select name="mifeco_sitemap_settings[change_frequencies][<?php echo esc_attr($type); ?>]">
                                                <?php foreach ($frequency_options as $value => $option) : ?>
                                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($frequency, $value); ?>><?php echo esc_html($option); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <p class="description"><?php _e('Set how frequently the content is likely to change.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('About Priorities', 'mifeco-suite'); ?></th>
                    <td>
                        <div class="mifeco-card">
                            <div class="mifeco-card-body">
                                <p><?php _e('The priority value for a URL is a hint for search engines regarding the importance of that URL relative to other URLs on your site. Valid values range from 0.0 to 1.0.', 'mifeco-suite'); ?></p>
                                <p><?php _e('The change frequency indicates how frequently the content at a URL is likely to change. This allows search engines to optimize their crawl frequency.', 'mifeco-suite'); ?></p>
                                <p><strong><?php _e('Note:', 'mifeco-suite'); ?></strong> <?php _e('While these settings can help guide search engines, they ultimately decide how often to crawl your site based on many factors.', 'mifeco-suite'); ?></p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Advanced Settings -->
        <div id="advanced" class="mifeco-tab-content">
            <h2><?php _e('Advanced Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure advanced sitemap settings.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Max Entries Per Sitemap', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="number" name="mifeco_sitemap_settings[max_entries_per_sitemap]" value="<?php echo esc_attr(isset($options['max_entries_per_sitemap']) ? $options['max_entries_per_sitemap'] : 1000); ?>" min="100" max="50000" step="100" class="small-text">
                        <p class="description"><?php _e('Maximum number of URLs per sitemap file. If you have more URLs, multiple sitemap files will be created.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Enable Image Sitemap', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_sitemap_settings[enable_image_sitemap]" value="1" <?php checked(isset($options['enable_image_sitemap']) ? $options['enable_image_sitemap'] : true); ?>>
                            <?php _e('Include image information in the sitemap', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Include images from your content in the sitemap for better image indexing.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Enable News Sitemap', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_sitemap_settings[enable_news_sitemap]" value="1" <?php checked(isset($options['enable_news_sitemap']) ? $options['enable_news_sitemap'] : false); ?>>
                            <?php _e('Generate a Google News sitemap', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Create a separate sitemap for Google News. Only enable if your site is registered with Google News.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Enable HTML Sitemap', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_sitemap_settings[enable_html_sitemap]" value="1" <?php checked(isset($options['enable_html_sitemap']) ? $options['enable_html_sitemap'] : true); ?>>
                            <?php _e('Generate an HTML sitemap', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Create a human-readable HTML sitemap. You can add it to your site using the [mifeco_html_sitemap] shortcode.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Tools Tab -->
        <div id="tools" class="mifeco-tab-content">
            <h2><?php _e('Sitemap Tools', 'mifeco-suite'); ?></h2>
            <p><?php _e('Test your sitemap and submit it to search engines.', 'mifeco-suite'); ?></p>
            
            <div class="mifeco-card">
                <div class="mifeco-card-header">
                    <h3><?php _e('Sitemap Validation', 'mifeco-suite'); ?></h3>
                </div>
                <div class="mifeco-card-body">
                    <p><?php _e('Verify that your sitemap is valid and accessible.', 'mifeco-suite'); ?></p>
                    <button type="button" id="mifeco-validate-sitemap" class="button button-primary"><?php _e('Validate Sitemap', 'mifeco-suite'); ?></button>
                    <div id="mifeco-sitemap-validation-results" style="margin-top: 20px;"></div>
                </div>
            </div>
            
            <div class="mifeco-card" style="margin-top: 20px;">
                <div class="mifeco-card-header">
                    <h3><?php _e('Submit to Search Engines', 'mifeco-suite'); ?></h3>
                </div>
                <div class="mifeco-card-body">
                    <p><?php _e('Manually submit your sitemap to search engines.', 'mifeco-suite'); ?></p>
                    
                    <div class="mifeco-form-field">
                        <input type="text" id="mifeco-sitemap-url" value="<?php echo esc_url(home_url('sitemap.xml')); ?>" class="regular-text" readonly>
                    </div>
                    
                    <div class="mifeco-form-field">
                        <a href="https://www.google.com/ping?sitemap=<?php echo urlencode(home_url('sitemap.xml')); ?>" target="_blank" class="button"><?php _e('Submit to Google', 'mifeco-suite'); ?></a>
                        <a href="https://www.bing.com/ping?sitemap=<?php echo urlencode(home_url('sitemap.xml')); ?>" target="_blank" class="button"><?php _e('Submit to Bing', 'mifeco-suite'); ?></a>
                    </div>
                    
                    <p class="description"><?php _e('You can also add your sitemap URL directly in search engine webmaster tools:', 'mifeco-suite'); ?></p>
                    <ul>
                        <li><a href="https://search.google.com/search-console" target="_blank"><?php _e('Google Search Console', 'mifeco-suite'); ?></a></li>
                        <li><a href="https://www.bing.com/webmasters/home" target="_blank"><?php _e('Bing Webmaster Tools', 'mifeco-suite'); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="mifeco-card" style="margin-top: 20px;">
                <div class="mifeco-card-header">
                    <h3><?php _e('Additional Information', 'mifeco-suite'); ?></h3>
                </div>
                <div class="mifeco-card-body">
                    <p><?php _e('Your sitemap will be automatically regenerated when:', 'mifeco-suite'); ?></p>
                    <ul>
                        <li><?php _e('You publish, update or delete content', 'mifeco-suite'); ?></li>
                        <li><?php _e('You change sitemap settings', 'mifeco-suite'); ?></li>
                        <li><?php _e('You manually trigger a regeneration using the "Regenerate Sitemap" button', 'mifeco-suite'); ?></li>
                    </ul>
                    
                    <p><?php _e('If you\'ve enabled the "Ping Search Engines" option, search engines will be automatically notified about these changes.', 'mifeco-suite'); ?></p>
                </div>
            </div>
        </div>
        
        <?php submit_button(); ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize sitemap validation functionality
    $('#mifeco-validate-sitemap').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $results = $('#mifeco-sitemap-validation-results');
        
        // Show loading state
        $button.attr('disabled', true).text('Validating...');
        $results.html('<div class="mifeco-analyzing"><span class="mifeco-analyzing-spinner"></span> Validating sitemap...</div>');
        
        // Make AJAX request
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'mifeco_validate_sitemap',
                nonce: '<?php echo wp_create_nonce('mifeco_seo_nonce'); ?>'
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
});
</script>