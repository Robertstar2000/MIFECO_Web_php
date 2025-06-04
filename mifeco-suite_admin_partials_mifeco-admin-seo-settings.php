<?php
/**
 * Admin SEO Settings Template
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
            <a href="#social" class="nav-tab"><?php _e('Social', 'mifeco-suite'); ?></a>
            <a href="#webmaster" class="nav-tab"><?php _e('Webmaster Tools', 'mifeco-suite'); ?></a>
            <a href="#analytics" class="nav-tab"><?php _e('Analytics', 'mifeco-suite'); ?></a>
        </nav>
    </div>
    
    <!-- General Settings -->
    <div id="general" class="mifeco-tab-content active">
        <form method="post" action="options.php">
            <?php
            settings_fields('mifeco_general_seo_settings_group');
            $options = get_option('mifeco_general_seo_settings', array());
            ?>
            
            <h2><?php _e('General SEO Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure basic settings that affect the SEO of your entire site.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Enable SEO Features', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_general_seo_settings[enable_seo_features]" value="1" <?php checked(isset($options['enable_seo_features']) ? $options['enable_seo_features'] : true); ?>>
                            <?php _e('Enable all SEO features', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Uncheck to disable all SEO features globally.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Disable Author Archives', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_general_seo_settings[disable_author_archives]" value="1" <?php checked(isset($options['disable_author_archives']) ? $options['disable_author_archives'] : false); ?>>
                            <?php _e('Disable author archives', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Redirects author archives to the homepage to prevent duplicate content issues.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Disable Date Archives', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_general_seo_settings[disable_date_archives]" value="1" <?php checked(isset($options['disable_date_archives']) ? $options['disable_date_archives'] : false); ?>>
                            <?php _e('Disable date archives', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Redirects date archives to the homepage to prevent duplicate content issues.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Remove Category Base', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_general_seo_settings[remove_category_base]" value="1" <?php checked(isset($options['remove_category_base']) ? $options['remove_category_base'] : false); ?>>
                            <?php _e('Remove category base (category/) from URLs', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Makes URLs cleaner and more SEO-friendly by removing the /category/ prefix.', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('Note: This may conflict with some permalink structures. After enabling, please check that your category links work properly.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Custom Robots.txt Content', 'mifeco-suite'); ?></th>
                    <td>
                        <textarea name="mifeco_general_seo_settings[custom_robots_txt]" rows="8" class="large-text code"><?php echo esc_textarea(isset($options['custom_robots_txt']) ? $options['custom_robots_txt'] : ''); ?></textarea>
                        <p class="description"><?php _e('Add custom content for robots.txt. Leave blank to use default WordPress robots.txt with sitemap URL.', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('Current robots.txt:', 'mifeco-suite'); ?> <a href="<?php echo esc_url(home_url('robots.txt')); ?>" target="_blank"><?php echo esc_url(home_url('robots.txt')); ?></a></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <!-- Social Settings -->
    <div id="social" class="mifeco-tab-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('mifeco_social_settings_group');
            $options = get_option('mifeco_social_settings', array());
            ?>
            
            <h2><?php _e('Social Media Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure settings for social media integration and sharing.', 'mifeco-suite'); ?></p>
            
            <h3><?php _e('Social Media Profiles', 'mifeco-suite'); ?></h3>
            <p><?php _e('Add your social media profile URLs. These will be used in schema markup to help search engines understand your social presence.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Facebook URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[facebook_url]" value="<?php echo esc_url(isset($options['facebook_url']) ? $options['facebook_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Facebook page or profile URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Twitter URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[twitter_url]" value="<?php echo esc_url(isset($options['twitter_url']) ? $options['twitter_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Twitter profile URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('LinkedIn URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[linkedin_url]" value="<?php echo esc_url(isset($options['linkedin_url']) ? $options['linkedin_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your LinkedIn profile or company URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Instagram URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[instagram_url]" value="<?php echo esc_url(isset($options['instagram_url']) ? $options['instagram_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Instagram profile URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('YouTube URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[youtube_url]" value="<?php echo esc_url(isset($options['youtube_url']) ? $options['youtube_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your YouTube channel URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Pinterest URL', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="url" name="mifeco_social_settings[pinterest_url]" value="<?php echo esc_url(isset($options['pinterest_url']) ? $options['pinterest_url'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Pinterest profile URL.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Social Sharing Settings', 'mifeco-suite'); ?></h3>
            <p><?php _e('Configure social sharing buttons for your content.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Enable Social Sharing', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_social_settings[enable_social_sharing]" value="1" <?php checked(isset($options['enable_social_sharing']) ? $options['enable_social_sharing'] : true); ?>>
                            <?php _e('Enable social sharing buttons', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Display social sharing buttons on your content.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Social Networks', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $social_sharing_networks = isset($options['social_sharing_networks']) ? $options['social_sharing_networks'] : array('facebook', 'twitter', 'linkedin');
                        $networks = array(
                            'facebook' => __('Facebook', 'mifeco-suite'),
                            'twitter' => __('Twitter', 'mifeco-suite'),
                            'linkedin' => __('LinkedIn', 'mifeco-suite'),
                            'pinterest' => __('Pinterest', 'mifeco-suite'),
                            'reddit' => __('Reddit', 'mifeco-suite'),
                            'email' => __('Email', 'mifeco-suite'),
                        );
                        ?>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Social Networks', 'mifeco-suite'); ?></legend>
                            <?php foreach ($networks as $network => $label) : ?>
                                <label>
                                    <input type="checkbox" name="mifeco_social_settings[social_sharing_networks][]" value="<?php echo esc_attr($network); ?>" <?php checked(in_array($network, $social_sharing_networks)); ?>>
                                    <?php echo esc_html($label); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e('Select which social networks to include in the sharing buttons.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Sharing Buttons Position', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $social_sharing_position = isset($options['social_sharing_position']) ? $options['social_sharing_position'] : 'after';
                        $positions = array(
                            'before' => __('Before content', 'mifeco-suite'),
                            'after' => __('After content', 'mifeco-suite'),
                            'both' => __('Before and after content', 'mifeco-suite'),
                        );
                        ?>
                        <select name="mifeco_social_settings[social_sharing_position]">
                            <?php foreach ($positions as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($social_sharing_position, $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Choose where to display the social sharing buttons.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Show On', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $social_sharing_post_types = isset($options['social_sharing_post_types']) ? $options['social_sharing_post_types'] : array('post');
                        $post_types = get_post_types(array('public' => true), 'objects');
                        ?>
                        <fieldset>
                            <legend class="screen-reader-text"><?php _e('Show On', 'mifeco-suite'); ?></legend>
                            <?php foreach ($post_types as $post_type) : ?>
                                <label>
                                    <input type="checkbox" name="mifeco_social_settings[social_sharing_post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $social_sharing_post_types)); ?>>
                                    <?php echo esc_html($post_type->labels->name); ?>
                                </label><br>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description"><?php _e('Select which post types to display sharing buttons on.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <!-- Webmaster Tools Settings -->
    <div id="webmaster" class="mifeco-tab-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('mifeco_webmaster_tools_settings_group');
            $options = get_option('mifeco_webmaster_tools_settings', array());
            ?>
            
            <h2><?php _e('Webmaster Tools Verification', 'mifeco-suite'); ?></h2>
            <p><?php _e('Add verification codes for various webmaster tools services.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Google Search Console', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[google_verification]" value="<?php echo esc_attr(isset($options['google_verification']) ? $options['google_verification'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Google Search Console verification code (e.g., google123456789012345).', 'mifeco-suite'); ?></p>
                        <p class="description"><?php _e('Get this from Google Search Console by selecting the HTML tag verification method and extracting the content of the meta tag.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Bing Webmaster Tools', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[bing_verification]" value="<?php echo esc_attr(isset($options['bing_verification']) ? $options['bing_verification'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Bing Webmaster Tools verification code.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Pinterest', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[pinterest_verification]" value="<?php echo esc_attr(isset($options['pinterest_verification']) ? $options['pinterest_verification'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Pinterest verification code.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Yandex Webmaster', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[yandex_verification]" value="<?php echo esc_attr(isset($options['yandex_verification']) ? $options['yandex_verification'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Yandex Webmaster verification code.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Baidu Webmaster Tools', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[baidu_verification]" value="<?php echo esc_attr(isset($options['baidu_verification']) ? $options['baidu_verification'] : ''); ?>" class="regular-text">
                        <p class="description"><?php _e('Enter your Baidu Webmaster Tools verification code.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    
    <!-- Analytics Settings -->
    <div id="analytics" class="mifeco-tab-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('mifeco_webmaster_tools_settings_group');
            $options = get_option('mifeco_webmaster_tools_settings', array());
            ?>
            
            <h2><?php _e('Analytics Settings', 'mifeco-suite'); ?></h2>
            <p><?php _e('Configure analytics settings for your site.', 'mifeco-suite'); ?></p>
            
            <table class="form-table mifeco-settings-table">
                <tr>
                    <th scope="row"><?php _e('Google Analytics ID', 'mifeco-suite'); ?></th>
                    <td>
                        <input type="text" name="mifeco_webmaster_tools_settings[google_analytics_id]" value="<?php echo esc_attr(isset($options['google_analytics_id']) ? $options['google_analytics_id'] : ''); ?>" class="regular-text" placeholder="G-XXXXXXXXXX or UA-XXXXXXXX-X">
                        <p class="description"><?php _e('Enter your Google Analytics measurement ID (G-XXXXXXXXXX for GA4 or UA-XXXXXXXX-X for Universal Analytics).', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Analytics Script Position', 'mifeco-suite'); ?></th>
                    <td>
                        <?php
                        $script_position = isset($options['google_analytics_script_position']) ? $options['google_analytics_script_position'] : 'head';
                        $positions = array(
                            'head' => __('Header (recommended for GA4)', 'mifeco-suite'),
                            'body' => __('Footer', 'mifeco-suite'),
                        );
                        ?>
                        <select name="mifeco_webmaster_tools_settings[google_analytics_script_position]">
                            <?php foreach ($positions as $value => $label) : ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($script_position, $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Choose where to place the analytics script.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Disable for Logged-in Users', 'mifeco-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="mifeco_webmaster_tools_settings[disable_analytics_logged_in]" value="1" <?php checked(isset($options['disable_analytics_logged_in']) ? $options['disable_analytics_logged_in'] : true); ?>>
                            <?php _e('Do not track logged-in users', 'mifeco-suite'); ?>
                        </label>
                        <p class="description"><?php _e('Prevents analytics from tracking administrators and other logged-in users.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Custom Analytics Code', 'mifeco-suite'); ?></th>
                    <td>
                        <textarea name="mifeco_webmaster_tools_settings[custom_analytics_code]" rows="8" class="large-text code"><?php echo esc_textarea(isset($options['custom_analytics_code']) ? $options['custom_analytics_code'] : ''); ?></textarea>
                        <p class="description"><?php _e('Enter custom analytics code (e.g., Facebook Pixel, Hotjar, etc.). This will be added to the site header.', 'mifeco-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.mifeco-admin-tabs .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href').substring(1);
        
        // Hide all content divs
        $('.mifeco-tab-content').removeClass('active');
        
        // Show target content div
        $('#' + target).addClass('active');
        
        // Update active class on tabs
        $('.mifeco-admin-tabs .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
    });
    
    // Media uploader for image fields
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
});
</script>