<?php
/**
 * Meta Tags Generator
 *
 * This class handles the generation and optimization of meta tags
 * for better search engine visibility and social sharing.
 *
 * @link       https://mifeco.com
 * @since      1.0.0
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/seo
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Meta Tags Generator Class
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/seo
 * @author     MIFECO <contact@mifeco.com>
 */
class MIFECO_Meta_Tags {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Meta tag settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $settings    Meta tag settings.
     */
    private $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->settings = get_option('mifeco_meta_tags_settings', array());
    }

    /**
     * Register meta tag settings
     *
     * @since    1.0.0
     */
    public function register_meta_tag_settings() {
        register_setting(
            'mifeco_meta_tags_settings_group',
            'mifeco_meta_tags_settings',
            array($this, 'sanitize_meta_tag_settings')
        );
        
        add_settings_section(
            'mifeco_meta_tags_general_section',
            __('General Meta Tag Settings', 'mifeco-suite'),
            array($this, 'render_meta_tags_general_section'),
            'mifeco-meta-tags-settings'
        );
        
        add_settings_field(
            'enable_meta_description',
            __('Enable Meta Description', 'mifeco-suite'),
            array($this, 'render_enable_meta_description_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_general_section'
        );
        
        add_settings_field(
            'enable_canonical_urls',
            __('Enable Canonical URLs', 'mifeco-suite'),
            array($this, 'render_enable_canonical_urls_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_general_section'
        );
        
        add_settings_field(
            'default_title_format',
            __('Default Title Format', 'mifeco-suite'),
            array($this, 'render_default_title_format_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_general_section'
        );
        
        add_settings_field(
            'separator',
            __('Title Separator', 'mifeco-suite'),
            array($this, 'render_separator_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_general_section'
        );
        
        // Social Media Section
        add_settings_section(
            'mifeco_meta_tags_social_section',
            __('Social Media Settings', 'mifeco-suite'),
            array($this, 'render_meta_tags_social_section'),
            'mifeco-meta-tags-settings'
        );
        
        add_settings_field(
            'enable_open_graph',
            __('Enable Open Graph', 'mifeco-suite'),
            array($this, 'render_enable_open_graph_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_social_section'
        );
        
        add_settings_field(
            'enable_twitter_cards',
            __('Enable Twitter Cards', 'mifeco-suite'),
            array($this, 'render_enable_twitter_cards_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_social_section'
        );
        
        add_settings_field(
            'facebook_app_id',
            __('Facebook App ID', 'mifeco-suite'),
            array($this, 'render_facebook_app_id_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_social_section'
        );
        
        add_settings_field(
            'twitter_site',
            __('Twitter @username', 'mifeco-suite'),
            array($this, 'render_twitter_site_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_social_section'
        );
        
        add_settings_field(
            'default_social_image',
            __('Default Social Image', 'mifeco-suite'),
            array($this, 'render_default_social_image_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_social_section'
        );
        
        // Advanced Settings
        add_settings_section(
            'mifeco_meta_tags_advanced_section',
            __('Advanced Settings', 'mifeco-suite'),
            array($this, 'render_meta_tags_advanced_section'),
            'mifeco-meta-tags-settings'
        );
        
        add_settings_field(
            'noindex_settings',
            __('Noindex Settings', 'mifeco-suite'),
            array($this, 'render_noindex_settings_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_advanced_section'
        );
        
        add_settings_field(
            'custom_meta_tags',
            __('Custom Meta Tags', 'mifeco-suite'),
            array($this, 'render_custom_meta_tags_field'),
            'mifeco-meta-tags-settings',
            'mifeco_meta_tags_advanced_section'
        );
    }

    /**
     * Sanitize meta tag settings
     *
     * @since    1.0.0
     * @param    array    $input    The input options.
     * @return   array              The sanitized options.
     */
    public function sanitize_meta_tag_settings($input) {
        $sanitized = array();
        
        // General settings
        $sanitized['enable_meta_description'] = isset($input['enable_meta_description']) ? (bool) $input['enable_meta_description'] : true;
        $sanitized['enable_canonical_urls'] = isset($input['enable_canonical_urls']) ? (bool) $input['enable_canonical_urls'] : true;
        $sanitized['default_title_format'] = isset($input['default_title_format']) ? sanitize_text_field($input['default_title_format']) : '%title% %separator% %sitename%';
        $sanitized['separator'] = isset($input['separator']) ? sanitize_text_field($input['separator']) : '|';
        
        // Social settings
        $sanitized['enable_open_graph'] = isset($input['enable_open_graph']) ? (bool) $input['enable_open_graph'] : true;
        $sanitized['enable_twitter_cards'] = isset($input['enable_twitter_cards']) ? (bool) $input['enable_twitter_cards'] : true;
        $sanitized['facebook_app_id'] = isset($input['facebook_app_id']) ? sanitize_text_field($input['facebook_app_id']) : '';
        $sanitized['twitter_site'] = isset($input['twitter_site']) ? sanitize_text_field($input['twitter_site']) : '';
        $sanitized['default_social_image'] = isset($input['default_social_image']) ? esc_url_raw($input['default_social_image']) : '';
        
        // Advanced settings
        $sanitized['noindex_settings'] = array();
        if (isset($input['noindex_settings']) && is_array($input['noindex_settings'])) {
            foreach ($input['noindex_settings'] as $key => $value) {
                $sanitized['noindex_settings'][$key] = (bool) $value;
            }
        }
        
        $sanitized['custom_meta_tags'] = isset($input['custom_meta_tags']) ? wp_kses_post($input['custom_meta_tags']) : '';
        
        return $sanitized;
    }

    /**
     * Render general section description
     *
     * @since    1.0.0
     */
    public function render_meta_tags_general_section() {
        echo '<p>' . __('Configure general meta tag settings for SEO.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render social section description
     *
     * @since    1.0.0
     */
    public function render_meta_tags_social_section() {
        echo '<p>' . __('Configure social media meta tags for better sharing on platforms like Facebook and Twitter.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render advanced section description
     *
     * @since    1.0.0
     */
    public function render_meta_tags_advanced_section() {
        echo '<p>' . __('Configure advanced meta tag settings for specific SEO needs.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render enable meta description field
     *
     * @since    1.0.0
     */
    public function render_enable_meta_description_field() {
        $enable_meta_description = isset($this->settings['enable_meta_description']) ? $this->settings['enable_meta_description'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_meta_tags_settings[enable_meta_description]" value="1" <?php checked($enable_meta_description, true); ?>>
            <?php _e('Enable meta description tags', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Generate meta description tags for all content.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable canonical URLs field
     *
     * @since    1.0.0
     */
    public function render_enable_canonical_urls_field() {
        $enable_canonical_urls = isset($this->settings['enable_canonical_urls']) ? $this->settings['enable_canonical_urls'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_meta_tags_settings[enable_canonical_urls]" value="1" <?php checked($enable_canonical_urls, true); ?>>
            <?php _e('Enable canonical URL tags', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Generate canonical URL tags to prevent duplicate content issues.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render default title format field
     *
     * @since    1.0.0
     */
    public function render_default_title_format_field() {
        $default_title_format = isset($this->settings['default_title_format']) ? $this->settings['default_title_format'] : '%title% %separator% %sitename%';
        ?>
        <input type="text" name="mifeco_meta_tags_settings[default_title_format]" value="<?php echo esc_attr($default_title_format); ?>" class="regular-text">
        <p class="description">
            <?php _e('Available variables: %title%, %sitename%, %separator%, %tagline%', 'mifeco-suite'); ?>
        </p>
        <?php
    }

    /**
     * Render separator field
     *
     * @since    1.0.0
     */
    public function render_separator_field() {
        $separator = isset($this->settings['separator']) ? $this->settings['separator'] : '|';
        ?>
        <input type="text" name="mifeco_meta_tags_settings[separator]" value="<?php echo esc_attr($separator); ?>" class="small-text">
        <p class="description"><?php _e('The character used to separate title parts.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable Open Graph field
     *
     * @since    1.0.0
     */
    public function render_enable_open_graph_field() {
        $enable_open_graph = isset($this->settings['enable_open_graph']) ? $this->settings['enable_open_graph'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_meta_tags_settings[enable_open_graph]" value="1" <?php checked($enable_open_graph, true); ?>>
            <?php _e('Enable Open Graph meta tags for Facebook and other social networks', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Improves how your content appears when shared on Facebook, LinkedIn, and other platforms.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable Twitter Cards field
     *
     * @since    1.0.0
     */
    public function render_enable_twitter_cards_field() {
        $enable_twitter_cards = isset($this->settings['enable_twitter_cards']) ? $this->settings['enable_twitter_cards'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_meta_tags_settings[enable_twitter_cards]" value="1" <?php checked($enable_twitter_cards, true); ?>>
            <?php _e('Enable Twitter Card meta tags', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Improves how your content appears when shared on Twitter.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render Facebook App ID field
     *
     * @since    1.0.0
     */
    public function render_facebook_app_id_field() {
        $facebook_app_id = isset($this->settings['facebook_app_id']) ? $this->settings['facebook_app_id'] : '';
        ?>
        <input type="text" name="mifeco_meta_tags_settings[facebook_app_id]" value="<?php echo esc_attr($facebook_app_id); ?>" class="regular-text">
        <p class="description"><?php _e('Your Facebook App ID (optional).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render Twitter site field
     *
     * @since    1.0.0
     */
    public function render_twitter_site_field() {
        $twitter_site = isset($this->settings['twitter_site']) ? $this->settings['twitter_site'] : '';
        ?>
        <input type="text" name="mifeco_meta_tags_settings[twitter_site]" value="<?php echo esc_attr($twitter_site); ?>" class="regular-text">
        <p class="description"><?php _e('Your Twitter @username (e.g., @mifeco).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render default social image field
     *
     * @since    1.0.0
     */
    public function render_default_social_image_field() {
        $default_social_image = isset($this->settings['default_social_image']) ? $this->settings['default_social_image'] : '';
        ?>
        <div class="mifeco-media-uploader">
            <input type="text" name="mifeco_meta_tags_settings[default_social_image]" value="<?php echo esc_url($default_social_image); ?>" class="regular-text">
            <button type="button" class="button mifeco-upload-button"><?php _e('Upload', 'mifeco-suite'); ?></button>
            <?php if (!empty($default_social_image)) : ?>
                <div class="mifeco-image-preview">
                    <img src="<?php echo esc_url($default_social_image); ?>" alt="<?php _e('Social Image Preview', 'mifeco-suite'); ?>">
                </div>
            <?php endif; ?>
        </div>
        <p class="description"><?php _e('Default image used when sharing content that doesn\'t have a featured image. Recommended size: 1200x630px.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render noindex settings field
     *
     * @since    1.0.0
     */
    public function render_noindex_settings_field() {
        $noindex_settings = isset($this->settings['noindex_settings']) ? $this->settings['noindex_settings'] : array();
        $default_values = array(
            'search' => true,
            'author' => false,
            'archive' => false,
            'date' => true,
            'tag' => false,
            'category' => false,
        );
        
        // Merge with defaults
        $noindex_settings = wp_parse_args($noindex_settings, $default_values);
        ?>
        <fieldset>
            <legend class="screen-reader-text"><?php _e('Noindex Settings', 'mifeco-suite'); ?></legend>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][search]" value="1" <?php checked($noindex_settings['search'], true); ?>>
                <?php _e('Search Results', 'mifeco-suite'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][author]" value="1" <?php checked($noindex_settings['author'], true); ?>>
                <?php _e('Author Archives', 'mifeco-suite'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][archive]" value="1" <?php checked($noindex_settings['archive'], true); ?>>
                <?php _e('Post Type Archives', 'mifeco-suite'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][date]" value="1" <?php checked($noindex_settings['date'], true); ?>>
                <?php _e('Date Archives', 'mifeco-suite'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][tag]" value="1" <?php checked($noindex_settings['tag'], true); ?>>
                <?php _e('Tag Archives', 'mifeco-suite'); ?>
            </label><br>
            
            <label>
                <input type="checkbox" name="mifeco_meta_tags_settings[noindex_settings][category]" value="1" <?php checked($noindex_settings['category'], true); ?>>
                <?php _e('Category Archives', 'mifeco-suite'); ?>
            </label>
        </fieldset>
        <p class="description"><?php _e('Select pages to add noindex meta tags to (prevents indexing by search engines).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render custom meta tags field
     *
     * @since    1.0.0
     */
    public function render_custom_meta_tags_field() {
        $custom_meta_tags = isset($this->settings['custom_meta_tags']) ? $this->settings['custom_meta_tags'] : '';
        ?>
        <textarea name="mifeco_meta_tags_settings[custom_meta_tags]" rows="5" class="large-text code"><?php echo esc_textarea($custom_meta_tags); ?></textarea>
        <p class="description"><?php _e('Add custom meta tags to be included in the head section of all pages. Enter one tag per line.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Add SEO meta boxes to post/page edit screens
     *
     * @since    1.0.0
     */
    public function add_seo_meta_boxes() {
        // Get post types to add meta boxes to
        $post_types = get_post_types(array('public' => true));
        
        foreach ($post_types as $post_type) {
            add_meta_box(
                'mifeco_seo_meta_box',
                __('MIFECO SEO Settings', 'mifeco-suite'),
                array($this, 'render_seo_meta_box'),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    /**
     * Render SEO meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post    The post object.
     */
    public function render_seo_meta_box($post) {
        // Add nonce field
        wp_nonce_field('mifeco_seo_meta_box', 'mifeco_seo_meta_box_nonce');
        
        // Get post meta
        $meta_title = get_post_meta($post->ID, '_mifeco_meta_title', true);
        $meta_description = get_post_meta($post->ID, '_mifeco_meta_description', true);
        $meta_keywords = get_post_meta($post->ID, '_mifeco_meta_keywords', true);
        $canonical_url = get_post_meta($post->ID, '_mifeco_canonical_url', true);
        $robots_meta = get_post_meta($post->ID, '_mifeco_robots_meta', true);
        $social_title = get_post_meta($post->ID, '_mifeco_social_title', true);
        $social_description = get_post_meta($post->ID, '_mifeco_social_description', true);
        $social_image = get_post_meta($post->ID, '_mifeco_social_image', true);
        
        // If robots meta is empty, set default
        if (empty($robots_meta)) {
            $robots_meta = array(
                'noindex' => false,
                'nofollow' => false,
                'noarchive' => false,
                'noimageindex' => false,
            );
        }
        ?>
        <div class="mifeco-seo-meta-box">
            <div class="mifeco-tabs">
                <div class="mifeco-tab-nav">
                    <button type="button" class="mifeco-tab-button active" data-tab="general"><?php _e('General', 'mifeco-suite'); ?></button>
                    <button type="button" class="mifeco-tab-button" data-tab="social"><?php _e('Social', 'mifeco-suite'); ?></button>
                    <button type="button" class="mifeco-tab-button" data-tab="advanced"><?php _e('Advanced', 'mifeco-suite'); ?></button>
                </div>
                
                <div class="mifeco-tab-content active" data-tab="general">
                    <div class="mifeco-field">
                        <label for="mifeco_meta_title"><?php _e('SEO Title', 'mifeco-suite'); ?></label>
                        <input type="text" id="mifeco_meta_title" name="mifeco_meta_title" value="<?php echo esc_attr($meta_title); ?>" class="widefat">
                        <p class="mifeco-description"><?php _e('Enter a custom SEO title. Leave blank to use the default title format.', 'mifeco-suite'); ?></p>
                        <div class="mifeco-seo-analysis">
                            <div class="mifeco-title-length"><span>0</span> <?php _e('characters (recommended: 50-60)', 'mifeco-suite'); ?></div>
                            <div class="mifeco-title-preview">
                                <div class="mifeco-preview-title"><?php echo esc_html($this->get_preview_title($post, $meta_title)); ?></div>
                                <div class="mifeco-preview-url"><?php echo esc_url(get_permalink($post->ID)); ?></div>
                                <div class="mifeco-preview-description"><?php echo esc_html($this->get_preview_description($post, $meta_description)); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mifeco-field">
                        <label for="mifeco_meta_description"><?php _e('Meta Description', 'mifeco-suite'); ?></label>
                        <textarea id="mifeco_meta_description" name="mifeco_meta_description" rows="3" class="widefat"><?php echo esc_textarea($meta_description); ?></textarea>
                        <p class="mifeco-description"><?php _e('Enter a meta description. Leave blank to use the excerpt or first part of the content.', 'mifeco-suite'); ?></p>
                        <div class="mifeco-description-length"><span>0</span> <?php _e('characters (recommended: 120-160)', 'mifeco-suite'); ?></div>
                    </div>
                    
                    <div class="mifeco-field">
                        <label for="mifeco_meta_keywords"><?php _e('Meta Keywords', 'mifeco-suite'); ?></label>
                        <input type="text" id="mifeco_meta_keywords" name="mifeco_meta_keywords" value="<?php echo esc_attr($meta_keywords); ?>" class="widefat">
                        <p class="mifeco-description"><?php _e('Enter meta keywords separated by commas. Note: Most search engines ignore this tag.', 'mifeco-suite'); ?></p>
                    </div>
                </div>
                
                <div class="mifeco-tab-content" data-tab="social">
                    <div class="mifeco-field">
                        <label for="mifeco_social_title"><?php _e('Social Title', 'mifeco-suite'); ?></label>
                        <input type="text" id="mifeco_social_title" name="mifeco_social_title" value="<?php echo esc_attr($social_title); ?>" class="widefat">
                        <p class="mifeco-description"><?php _e('Enter a custom title for social sharing. Leave blank to use the SEO title or post title.', 'mifeco-suite'); ?></p>
                    </div>
                    
                    <div class="mifeco-field">
                        <label for="mifeco_social_description"><?php _e('Social Description', 'mifeco-suite'); ?></label>
                        <textarea id="mifeco_social_description" name="mifeco_social_description" rows="3" class="widefat"><?php echo esc_textarea($social_description); ?></textarea>
                        <p class="mifeco-description"><?php _e('Enter a custom description for social sharing. Leave blank to use the meta description.', 'mifeco-suite'); ?></p>
                    </div>
                    
                    <div class="mifeco-field">
                        <label for="mifeco_social_image"><?php _e('Social Image', 'mifeco-suite'); ?></label>
                        <div class="mifeco-media-uploader">
                            <input type="text" id="mifeco_social_image" name="mifeco_social_image" value="<?php echo esc_url($social_image); ?>" class="widefat">
                            <button type="button" class="button mifeco-upload-button"><?php _e('Upload/Select Image', 'mifeco-suite'); ?></button>
                            <?php if (!empty($social_image)) : ?>
                                <div class="mifeco-image-preview">
                                    <img src="<?php echo esc_url($social_image); ?>" alt="<?php _e('Social Image Preview', 'mifeco-suite'); ?>">
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="mifeco-description"><?php _e('Select an image for social sharing. Recommended size: 1200x630px. Leave blank to use the featured image.', 'mifeco-suite'); ?></p>
                    </div>
                    
                    <div class="mifeco-field">
                        <div class="mifeco-social-preview">
                            <h4><?php _e('Facebook Preview', 'mifeco-suite'); ?></h4>
                            <div class="mifeco-facebook-preview">
                                <?php if (!empty($social_image) || has_post_thumbnail($post->ID)) : ?>
                                    <div class="mifeco-preview-image">
                                        <img src="<?php echo !empty($social_image) ? esc_url($social_image) : get_the_post_thumbnail_url($post->ID, 'large'); ?>" alt="">
                                    </div>
                                <?php endif; ?>
                                <div class="mifeco-preview-content">
                                    <div class="mifeco-preview-site"><?php echo esc_html(parse_url(home_url(), PHP_URL_HOST)); ?></div>
                                    <div class="mifeco-preview-title"><?php echo esc_html($this->get_preview_social_title($post, $social_title, $meta_title)); ?></div>
                                    <div class="mifeco-preview-description"><?php echo esc_html($this->get_preview_social_description($post, $social_description, $meta_description)); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mifeco-tab-content" data-tab="advanced">
                    <div class="mifeco-field">
                        <label for="mifeco_canonical_url"><?php _e('Canonical URL', 'mifeco-suite'); ?></label>
                        <input type="text" id="mifeco_canonical_url" name="mifeco_canonical_url" value="<?php echo esc_url($canonical_url); ?>" class="widefat">
                        <p class="mifeco-description"><?php _e('Enter a custom canonical URL. Leave blank to use the permalink.', 'mifeco-suite'); ?></p>
                    </div>
                    
                    <div class="mifeco-field">
                        <label><?php _e('Robots Meta', 'mifeco-suite'); ?></label>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="mifeco_robots_meta[noindex]" value="1" <?php checked(isset($robots_meta['noindex']) ? $robots_meta['noindex'] : false); ?>>
                                <?php _e('noindex', 'mifeco-suite'); ?>
                            </label>
                            <p class="mifeco-checkbox-description"><?php _e('Prevent search engines from indexing this page.', 'mifeco-suite'); ?></p>
                            
                            <label>
                                <input type="checkbox" name="mifeco_robots_meta[nofollow]" value="1" <?php checked(isset($robots_meta['nofollow']) ? $robots_meta['nofollow'] : false); ?>>
                                <?php _e('nofollow', 'mifeco-suite'); ?>
                            </label>
                            <p class="mifeco-checkbox-description"><?php _e('Prevent search engines from following links on this page.', 'mifeco-suite'); ?></p>
                            
                            <label>
                                <input type="checkbox" name="mifeco_robots_meta[noarchive]" value="1" <?php checked(isset($robots_meta['noarchive']) ? $robots_meta['noarchive'] : false); ?>>
                                <?php _e('noarchive', 'mifeco-suite'); ?>
                            </label>
                            <p class="mifeco-checkbox-description"><?php _e('Prevent search engines from showing cached copies of this page.', 'mifeco-suite'); ?></p>
                            
                            <label>
                                <input type="checkbox" name="mifeco_robots_meta[noimageindex]" value="1" <?php checked(isset($robots_meta['noimageindex']) ? $robots_meta['noimageindex'] : false); ?>>
                                <?php _e('noimageindex', 'mifeco-suite'); ?>
                            </label>
                            <p class="mifeco-checkbox-description"><?php _e('Prevent search engines from indexing images on this page.', 'mifeco-suite'); ?></p>
                        </fieldset>
                    </div>
                    
                    <div class="mifeco-field mifeco-field-warning">
                        <p><strong><?php _e('Warning:', 'mifeco-suite'); ?></strong> <?php _e('Using noindex will prevent this page from appearing in search results. Use with caution.', 'mifeco-suite'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save SEO meta data
     *
     * @since    1.0.0
     * @param    int    $post_id    The post ID.
     */
    public function save_seo_meta_data($post_id) {
        // Check if nonce is set
        if (!isset($_POST['mifeco_seo_meta_box_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['mifeco_seo_meta_box_nonce'], 'mifeco_seo_meta_box')) {
            return;
        }
        
        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (isset($_POST['post_type'])) {
            if ('page' === $_POST['post_type']) {
                if (!current_user_can('edit_page', $post_id)) {
                    return;
                }
            } else {
                if (!current_user_can('edit_post', $post_id)) {
                    return;
                }
            }
        }
        
        // Save meta title
        if (isset($_POST['mifeco_meta_title'])) {
            update_post_meta(
                $post_id,
                '_mifeco_meta_title',
                sanitize_text_field($_POST['mifeco_meta_title'])
            );
        }
        
        // Save meta description
        if (isset($_POST['mifeco_meta_description'])) {
            update_post_meta(
                $post_id,
                '_mifeco_meta_description',
                sanitize_textarea_field($_POST['mifeco_meta_description'])
            );
        }
        
        // Save meta keywords
        if (isset($_POST['mifeco_meta_keywords'])) {
            update_post_meta(
                $post_id,
                '_mifeco_meta_keywords',
                sanitize_text_field($_POST['mifeco_meta_keywords'])
            );
        }
        
        // Save canonical URL
        if (isset($_POST['mifeco_canonical_url'])) {
            update_post_meta(
                $post_id,
                '_mifeco_canonical_url',
                esc_url_raw($_POST['mifeco_canonical_url'])
            );
        }
        
        // Save robots meta
        $robots_meta = array(
            'noindex' => isset($_POST['mifeco_robots_meta']['noindex']),
            'nofollow' => isset($_POST['mifeco_robots_meta']['nofollow']),
            'noarchive' => isset($_POST['mifeco_robots_meta']['noarchive']),
            'noimageindex' => isset($_POST['mifeco_robots_meta']['noimageindex']),
        );
        update_post_meta($post_id, '_mifeco_robots_meta', $robots_meta);
        
        // Save social title
        if (isset($_POST['mifeco_social_title'])) {
            update_post_meta(
                $post_id,
                '_mifeco_social_title',
                sanitize_text_field($_POST['mifeco_social_title'])
            );
        }
        
        // Save social description
        if (isset($_POST['mifeco_social_description'])) {
            update_post_meta(
                $post_id,
                '_mifeco_social_description',
                sanitize_textarea_field($_POST['mifeco_social_description'])
            );
        }
        
        // Save social image
        if (isset($_POST['mifeco_social_image'])) {
            update_post_meta(
                $post_id,
                '_mifeco_social_image',
                esc_url_raw($_POST['mifeco_social_image'])
            );
        }
    }

    /**
     * Add category SEO fields to the category add form
     *
     * @since    1.0.0
     */
    public function add_category_seo_fields() {
        ?>
        <div class="form-field">
            <label for="mifeco_meta_title"><?php _e('SEO Title', 'mifeco-suite'); ?></label>
            <input type="text" name="mifeco_meta_title" id="mifeco_meta_title" value="">
            <p class="description"><?php _e('Enter a custom SEO title for this category.', 'mifeco-suite'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="mifeco_meta_description"><?php _e('Meta Description', 'mifeco-suite'); ?></label>
            <textarea name="mifeco_meta_description" id="mifeco_meta_description" rows="3"></textarea>
            <p class="description"><?php _e('Enter a meta description for this category.', 'mifeco-suite'); ?></p>
        </div>
        
        <div class="form-field">
            <label for="mifeco_robots_noindex">
                <input type="checkbox" name="mifeco_robots_noindex" id="mifeco_robots_noindex" value="1">
                <?php _e('Noindex', 'mifeco-suite'); ?>
            </label>
            <p class="description"><?php _e('Prevent search engines from indexing this category.', 'mifeco-suite'); ?></p>
        </div>
        <?php
    }

    /**
     * Add category SEO fields to the category edit form
     *
     * @since    1.0.0
     * @param    object    $term    The term object.
     */
    public function edit_category_seo_fields($term) {
        // Get term meta
        $meta_title = get_term_meta($term->term_id, '_mifeco_meta_title', true);
        $meta_description = get_term_meta($term->term_id, '_mifeco_meta_description', true);
        $robots_noindex = get_term_meta($term->term_id, '_mifeco_robots_noindex', true);
        ?>
        <tr class="form-field">
            <th scope="row"><label for="mifeco_meta_title"><?php _e('SEO Title', 'mifeco-suite'); ?></label></th>
            <td>
                <input type="text" name="mifeco_meta_title" id="mifeco_meta_title" value="<?php echo esc_attr($meta_title); ?>">
                <p class="description"><?php _e('Enter a custom SEO title for this category.', 'mifeco-suite'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="mifeco_meta_description"><?php _e('Meta Description', 'mifeco-suite'); ?></label></th>
            <td>
                <textarea name="mifeco_meta_description" id="mifeco_meta_description" rows="3"><?php echo esc_textarea($meta_description); ?></textarea>
                <p class="description"><?php _e('Enter a meta description for this category.', 'mifeco-suite'); ?></p>
            </td>
        </tr>
        
        <tr class="form-field">
            <th scope="row"><label for="mifeco_robots_noindex"><?php _e('Noindex', 'mifeco-suite'); ?></label></th>
            <td>
                <label for="mifeco_robots_noindex">
                    <input type="checkbox" name="mifeco_robots_noindex" id="mifeco_robots_noindex" value="1" <?php checked($robots_noindex, true); ?>>
                    <?php _e('Prevent search engines from indexing this category.', 'mifeco-suite'); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * Save category SEO fields
     *
     * @since    1.0.0
     * @param    int    $term_id    The term ID.
     */
    public function save_category_seo_fields($term_id) {
        // Save meta title
        if (isset($_POST['mifeco_meta_title'])) {
            update_term_meta(
                $term_id,
                '_mifeco_meta_title',
                sanitize_text_field($_POST['mifeco_meta_title'])
            );
        }
        
        // Save meta description
        if (isset($_POST['mifeco_meta_description'])) {
            update_term_meta(
                $term_id,
                '_mifeco_meta_description',
                sanitize_textarea_field($_POST['mifeco_meta_description'])
            );
        }
        
        // Save robots noindex
        $robots_noindex = isset($_POST['mifeco_robots_noindex']) ? true : false;
        update_term_meta($term_id, '_mifeco_robots_noindex', $robots_noindex);
    }

    /**
     * Output meta tags in the head section
     *
     * @since    1.0.0
     */
    public function output_meta_tags() {
        // Check settings
        $meta_tags_enabled = isset($this->settings['enable_meta_description']) ? $this->settings['enable_meta_description'] : true;
        $canonical_urls_enabled = isset($this->settings['enable_canonical_urls']) ? $this->settings['enable_canonical_urls'] : true;
        $open_graph_enabled = isset($this->settings['enable_open_graph']) ? $this->settings['enable_open_graph'] : true;
        $twitter_cards_enabled = isset($this->settings['enable_twitter_cards']) ? $this->settings['enable_twitter_cards'] : true;
        
        // If no settings enabled, return
        if (!$meta_tags_enabled && !$canonical_urls_enabled && !$open_graph_enabled && !$twitter_cards_enabled) {
            return;
        }
        
        echo "\n<!-- MIFECO Suite SEO Meta Tags -->\n";
        
        // Output robots meta tags
        $this->output_robots_meta();
        
        // Output meta description
        if ($meta_tags_enabled) {
            $this->output_meta_description();
            $this->output_meta_keywords();
        }
        
        // Output canonical URL
        if ($canonical_urls_enabled) {
            $this->output_canonical_url();
        }
        
        // Output Open Graph meta tags
        if ($open_graph_enabled) {
            $this->output_open_graph_meta();
        }
        
        // Output Twitter Card meta tags
        if ($twitter_cards_enabled) {
            $this->output_twitter_card_meta();
        }
        
        // Output custom meta tags
        $this->output_custom_meta_tags();
        
        echo "<!-- / MIFECO Suite SEO Meta Tags -->\n";
    }

    /**
     * Output robots meta tags
     *
     * @since    1.0.0
     */
    private function output_robots_meta() {
        $robots_directives = array();
        
        // Global noindex settings
        $noindex_settings = isset($this->settings['noindex_settings']) ? $this->settings['noindex_settings'] : array();
        
        // Check global settings for various page types
        if (is_search() && isset($noindex_settings['search']) && $noindex_settings['search']) {
            $robots_directives[] = 'noindex';
        } elseif (is_author() && isset($noindex_settings['author']) && $noindex_settings['author']) {
            $robots_directives[] = 'noindex';
        } elseif (is_post_type_archive() && isset($noindex_settings['archive']) && $noindex_settings['archive']) {
            $robots_directives[] = 'noindex';
        } elseif (is_date() && isset($noindex_settings['date']) && $noindex_settings['date']) {
            $robots_directives[] = 'noindex';
        } elseif (is_tag() && isset($noindex_settings['tag']) && $noindex_settings['tag']) {
            $robots_directives[] = 'noindex';
        } elseif (is_category() && isset($noindex_settings['category']) && $noindex_settings['category']) {
            $robots_directives[] = 'noindex';
        }
        
        // Check individual post/page settings
        if (is_singular()) {
            global $post;
            $robots_meta = get_post_meta($post->ID, '_mifeco_robots_meta', true);
            
            if (is_array($robots_meta)) {
                if (isset($robots_meta['noindex']) && $robots_meta['noindex']) {
                    $robots_directives[] = 'noindex';
                }
                if (isset($robots_meta['nofollow']) && $robots_meta['nofollow']) {
                    $robots_directives[] = 'nofollow';
                }
                if (isset($robots_meta['noarchive']) && $robots_meta['noarchive']) {
                    $robots_directives[] = 'noarchive';
                }
                if (isset($robots_meta['noimageindex']) && $robots_meta['noimageindex']) {
                    $robots_directives[] = 'noimageindex';
                }
            }
        }
        
        // Check category settings
        if (is_category()) {
            $term_id = get_queried_object_id();
            $robots_noindex = get_term_meta($term_id, '_mifeco_robots_noindex', true);
            
            if ($robots_noindex) {
                $robots_directives[] = 'noindex';
            }
        }
        
        // Output robots meta if directives exist
        if (!empty($robots_directives)) {
            echo '<meta name="robots" content="' . esc_attr(implode(', ', $robots_directives)) . '">' . "\n";
        }
    }

    /**
     * Output meta description
     *
     * @since    1.0.0
     */
    private function output_meta_description() {
        $description = $this->get_meta_description();
        
        if (!empty($description)) {
            echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
        }
    }

    /**
     * Output meta keywords
     *
     * @since    1.0.0
     */
    private function output_meta_keywords() {
        $keywords = $this->get_meta_keywords();
        
        if (!empty($keywords)) {
            echo '<meta name="keywords" content="' . esc_attr($keywords) . '">' . "\n";
        }
    }

    /**
     * Output canonical URL
     *
     * @since    1.0.0
     */
    private function output_canonical_url() {
        $canonical_url = $this->get_canonical_url();
        
        if (!empty($canonical_url)) {
            echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";
        }
    }

    /**
     * Output Open Graph meta tags
     *
     * @since    1.0.0
     */
    private function output_open_graph_meta() {
        // Basic Open Graph meta tags
        echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '">' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
        
        // Facebook App ID
        $facebook_app_id = isset($this->settings['facebook_app_id']) ? $this->settings['facebook_app_id'] : '';
        if (!empty($facebook_app_id)) {
            echo '<meta property="fb:app_id" content="' . esc_attr($facebook_app_id) . '">' . "\n";
        }
        
        // Type
        $og_type = 'website';
        if (is_singular('post')) {
            $og_type = 'article';
        } elseif (is_singular('product') || is_singular('mifeco_product')) {
            $og_type = 'product';
        }
        echo '<meta property="og:type" content="' . esc_attr($og_type) . '">' . "\n";
        
        // URL
        echo '<meta property="og:url" content="' . esc_url($this->get_canonical_url()) . '">' . "\n";
        
        // Title
        $og_title = $this->get_social_title();
        echo '<meta property="og:title" content="' . esc_attr($og_title) . '">' . "\n";
        
        // Description
        $og_description = $this->get_social_description();
        if (!empty($og_description)) {
            echo '<meta property="og:description" content="' . esc_attr($og_description) . '">' . "\n";
        }
        
        // Image
        $og_image = $this->get_social_image();
        if (!empty($og_image)) {
            echo '<meta property="og:image" content="' . esc_url($og_image) . '">' . "\n";
            
            // Image dimensions if available
            $image_id = attachment_url_to_postid($og_image);
            if ($image_id) {
                $image_meta = wp_get_attachment_metadata($image_id);
                if (!empty($image_meta['width'])) {
                    echo '<meta property="og:image:width" content="' . esc_attr($image_meta['width']) . '">' . "\n";
                }
                if (!empty($image_meta['height'])) {
                    echo '<meta property="og:image:height" content="' . esc_attr($image_meta['height']) . '">' . "\n";
                }
            }
        }
        
        // Additional article meta tags
        if ($og_type === 'article' && is_singular('post')) {
            global $post;
            
            // Published time
            echo '<meta property="article:published_time" content="' . esc_attr(get_the_date('c', $post->ID)) . '">' . "\n";
            
            // Modified time
            echo '<meta property="article:modified_time" content="' . esc_attr(get_the_modified_date('c', $post->ID)) . '">' . "\n";
            
            // Author
            $author = get_the_author_meta('display_name', $post->post_author);
            if (!empty($author)) {
                echo '<meta property="article:author" content="' . esc_attr($author) . '">' . "\n";
            }
            
            // Categories as article:section
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $primary_category = $categories[0];
                echo '<meta property="article:section" content="' . esc_attr($primary_category->name) . '">' . "\n";
            }
            
            // Tags as article:tag
            $tags = get_the_tags($post->ID);
            if ($tags) {
                foreach ($tags as $tag) {
                    echo '<meta property="article:tag" content="' . esc_attr($tag->name) . '">' . "\n";
                }
            }
        }
    }

    /**
     * Output Twitter Card meta tags
     *
     * @since    1.0.0
     */
    private function output_twitter_card_meta() {
        // Card type
        $card_type = 'summary_large_image';
        if (!$this->get_social_image()) {
            $card_type = 'summary';
        }
        echo '<meta name="twitter:card" content="' . esc_attr($card_type) . '">' . "\n";
        
        // Site username
        $twitter_site = isset($this->settings['twitter_site']) ? $this->settings['twitter_site'] : '';
        if (!empty($twitter_site)) {
            // Ensure it starts with @
            if (substr($twitter_site, 0, 1) !== '@') {
                $twitter_site = '@' . $twitter_site;
            }
            echo '<meta name="twitter:site" content="' . esc_attr($twitter_site) . '">' . "\n";
        }
        
        // Title
        $twitter_title = $this->get_social_title();
        echo '<meta name="twitter:title" content="' . esc_attr($twitter_title) . '">' . "\n";
        
        // Description
        $twitter_description = $this->get_social_description();
        if (!empty($twitter_description)) {
            echo '<meta name="twitter:description" content="' . esc_attr($twitter_description) . '">' . "\n";
        }
        
        // Image
        $twitter_image = $this->get_social_image();
        if (!empty($twitter_image)) {
            echo '<meta name="twitter:image" content="' . esc_url($twitter_image) . '">' . "\n";
            
            // Alt text for image
            if (is_singular()) {
                global $post;
                $image_id = attachment_url_to_postid($twitter_image);
                if ($image_id) {
                    $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    if (!empty($alt_text)) {
                        echo '<meta name="twitter:image:alt" content="' . esc_attr($alt_text) . '">' . "\n";
                    }
                }
            }
        }
    }

    /**
     * Output custom meta tags
     *
     * @since    1.0.0
     */
    private function output_custom_meta_tags() {
        $custom_meta_tags = isset($this->settings['custom_meta_tags']) ? $this->settings['custom_meta_tags'] : '';
        
        if (!empty($custom_meta_tags)) {
            echo wp_kses_post($custom_meta_tags) . "\n";
        }
    }

    /**
     * Get meta description
     *
     * @since    1.0.0
     * @return   string    Meta description.
     */
    private function get_meta_description() {
        $description = '';
        
        // For singular pages
        if (is_singular()) {
            global $post;
            
            // Check for custom meta description
            $custom_description = get_post_meta($post->ID, '_mifeco_meta_description', true);
            if (!empty($custom_description)) {
                $description = $custom_description;
            }
            // Use excerpt if available
            elseif (has_excerpt($post->ID)) {
                $description = get_the_excerpt($post->ID);
            }
            // Use first part of content
            else {
                $content = get_the_content('', false, $post->ID);
                $content = strip_shortcodes($content);
                $content = wp_strip_all_tags($content);
                $content = str_replace(array("\r", "\n"), ' ', $content);
                $content = preg_replace('/\s+/', ' ', $content);
                $content = trim($content);
                
                if (strlen($content) > 160) {
                    $description = substr($content, 0, 157) . '...';
                } else {
                    $description = $content;
                }
            }
        }
        // For archives
        elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            
            // Check for custom meta description
            $custom_description = get_term_meta($term->term_id, '_mifeco_meta_description', true);
            if (!empty($custom_description)) {
                $description = $custom_description;
            }
            // Use term description
            elseif (!empty($term->description)) {
                $description = wp_strip_all_tags($term->description);
                if (strlen($description) > 160) {
                    $description = substr($description, 0, 157) . '...';
                }
            }
            // Use default format
            else {
                $description = sprintf(__('%s Archives', 'mifeco-suite'), single_term_title('', false));
            }
        }
        // For author archives
        elseif (is_author()) {
            $author = get_queried_object();
            $description = sprintf(__('Archives for %s', 'mifeco-suite'), $author->display_name);
        }
        // For post type archives
        elseif (is_post_type_archive()) {
            $post_type = get_queried_object();
            $description = sprintf(__('Archives for %s', 'mifeco-suite'), $post_type->labels->name);
        }
        // For date archives
        elseif (is_date()) {
            if (is_day()) {
                $description = sprintf(__('Archives for %s', 'mifeco-suite'), get_the_date());
            } elseif (is_month()) {
                $description = sprintf(__('Archives for %s', 'mifeco-suite'), get_the_date('F Y'));
            } elseif (is_year()) {
                $description = sprintf(__('Archives for %s', 'mifeco-suite'), get_the_date('Y'));
            }
        }
        // For search results
        elseif (is_search()) {
            $description = sprintf(__('Search results for "%s"', 'mifeco-suite'), get_search_query());
        }
        // For home page
        elseif (is_front_page()) {
            $description = get_bloginfo('description');
        }
        
        return $description;
    }

    /**
     * Get meta keywords
     *
     * @since    1.0.0
     * @return   string    Meta keywords.
     */
    private function get_meta_keywords() {
        $keywords = '';
        
        if (is_singular()) {
            global $post;
            
            // Check for custom meta keywords
            $custom_keywords = get_post_meta($post->ID, '_mifeco_meta_keywords', true);
            if (!empty($custom_keywords)) {
                $keywords = $custom_keywords;
            }
            // Generate from tags
            elseif (is_singular('post')) {
                $tags = get_the_tags($post->ID);
                if ($tags) {
                    $tag_names = array();
                    foreach ($tags as $tag) {
                        $tag_names[] = $tag->name;
                    }
                    $keywords = implode(', ', $tag_names);
                }
            }
        }
        
        return $keywords;
    }

    /**
     * Get canonical URL
     *
     * @since    1.0.0
     * @return   string    Canonical URL.
     */
    private function get_canonical_url() {
        $canonical_url = '';
        
        // For singular pages
        if (is_singular()) {
            global $post;
            
            // Check for custom canonical URL
            $custom_canonical = get_post_meta($post->ID, '_mifeco_canonical_url', true);
            if (!empty($custom_canonical)) {
                $canonical_url = $custom_canonical;
            }
            // Use permalink
            else {
                $canonical_url = get_permalink($post->ID);
            }
        }
        // For archives
        elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $canonical_url = get_term_link($term);
        }
        // For author archives
        elseif (is_author()) {
            $author = get_queried_object();
            $canonical_url = get_author_posts_url($author->ID);
        }
        // For post type archives
        elseif (is_post_type_archive()) {
            $post_type = get_queried_object();
            $canonical_url = get_post_type_archive_link($post_type->name);
        }
        // For date archives
        elseif (is_date()) {
            if (is_day()) {
                $canonical_url = get_day_link(get_query_var('year'), get_query_var('monthnum'), get_query_var('day'));
            } elseif (is_month()) {
                $canonical_url = get_month_link(get_query_var('year'), get_query_var('monthnum'));
            } elseif (is_year()) {
                $canonical_url = get_year_link(get_query_var('year'));
            }
        }
        // For search results
        elseif (is_search()) {
            $canonical_url = get_search_link();
        }
        // For home page
        elseif (is_front_page()) {
            $canonical_url = home_url('/');
        }
        // For pages
        elseif (is_page()) {
            $canonical_url = get_permalink();
        }
        
        return $canonical_url;
    }

    /**
     * Get social title
     *
     * @since    1.0.0
     * @return   string    Social title.
     */
    private function get_social_title() {
        $title = '';
        
        // For singular pages
        if (is_singular()) {
            global $post;
            
            // Check for custom social title
            $social_title = get_post_meta($post->ID, '_mifeco_social_title', true);
            if (!empty($social_title)) {
                $title = $social_title;
            }
            // Check for custom meta title
            elseif ($meta_title = get_post_meta($post->ID, '_mifeco_meta_title', true)) {
                $title = $meta_title;
            }
            // Use post title
            else {
                $title = get_the_title($post->ID);
            }
        }
        // For archives
        elseif (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            
            // Check for custom meta title
            $meta_title = get_term_meta($term->term_id, '_mifeco_meta_title', true);
            if (!empty($meta_title)) {
                $title = $meta_title;
            }
            // Use default format
            else {
                $title = single_term_title('', false) . ' - ' . get_bloginfo('name');
            }
        }
        // For author archives
        elseif (is_author()) {
            $author = get_queried_object();
            $title = sprintf(__('Posts by %s', 'mifeco-suite'), $author->display_name) . ' - ' . get_bloginfo('name');
        }
        // For post type archives
        elseif (is_post_type_archive()) {
            $post_type = get_queried_object();
            $title = $post_type->labels->name . ' - ' . get_bloginfo('name');
        }
        // For date archives
        elseif (is_date()) {
            if (is_day()) {
                $title = sprintf(__('Daily Archives: %s', 'mifeco-suite'), get_the_date()) . ' - ' . get_bloginfo('name');
            } elseif (is_month()) {
                $title = sprintf(__('Monthly Archives: %s', 'mifeco-suite'), get_the_date('F Y')) . ' - ' . get_bloginfo('name');
            } elseif (is_year()) {
                $title = sprintf(__('Yearly Archives: %s', 'mifeco-suite'), get_the_date('Y')) . ' - ' . get_bloginfo('name');
            }
        }
        // For search results
        elseif (is_search()) {
            $title = sprintf(__('Search Results for "%s"', 'mifeco-suite'), get_search_query()) . ' - ' . get_bloginfo('name');
        }
        // For home page
        elseif (is_front_page()) {
            $title = get_bloginfo('name');
            $tagline = get_bloginfo('description');
            if (!empty($tagline)) {
                $title .= ' - ' . $tagline;
            }
        }
        // For pages
        elseif (is_page()) {
            $title = get_the_title() . ' - ' . get_bloginfo('name');
        }
        
        return $title;
    }

    /**
     * Get social description
     *
     * @since    1.0.0
     * @return   string    Social description.
     */
    private function get_social_description() {
        $description = '';
        
        // For singular pages
        if (is_singular()) {
            global $post;
            
            // Check for custom social description
            $social_description = get_post_meta($post->ID, '_mifeco_social_description', true);
            if (!empty($social_description)) {
                $description = $social_description;
            }
            // Use meta description as fallback
            else {
                $description = $this->get_meta_description();
            }
        } else {
            // Use meta description for all other page types
            $description = $this->get_meta_description();
        }
        
        return $description;
    }

    /**
     * Get social image
     *
     * @since    1.0.0
     * @return   string    Social image URL.
     */
    private function get_social_image() {
        $image = '';
        
        // For singular pages
        if (is_singular()) {
            global $post;
            
            // Check for custom social image
            $social_image = get_post_meta($post->ID, '_mifeco_social_image', true);
            if (!empty($social_image)) {
                $image = $social_image;
            }
            // Use featured image as fallback
            elseif (has_post_thumbnail($post->ID)) {
                $image = get_the_post_thumbnail_url($post->ID, 'large');
            }
        }
        
        // Use default social image if no image is set
        if (empty($image)) {
            $default_social_image = isset($this->settings['default_social_image']) ? $this->settings['default_social_image'] : '';
            if (!empty($default_social_image)) {
                $image = $default_social_image;
            }
        }
        
        return $image;
    }

    /**
     * Get preview title for meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post         The post object.
     * @param    string     $meta_title   The custom meta title.
     * @return   string                   Preview title.
     */
    private function get_preview_title($post, $meta_title) {
        if (!empty($meta_title)) {
            return $meta_title;
        }
        
        // Use title format from settings
        $format = isset($this->settings['default_title_format']) ? $this->settings['default_title_format'] : '%title% %separator% %sitename%';
        $separator = isset($this->settings['separator']) ? $this->settings['separator'] : '|';
        
        $title = get_the_title($post->ID);
        $sitename = get_bloginfo('name');
        $tagline = get_bloginfo('description');
        
        $preview_title = str_replace(
            array('%title%', '%sitename%', '%separator%', '%tagline%'),
            array($title, $sitename, $separator, $tagline),
            $format
        );
        
        return $preview_title;
    }

    /**
     * Get preview description for meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post             The post object.
     * @param    string     $meta_description The custom meta description.
     * @return   string                       Preview description.
     */
    private function get_preview_description($post, $meta_description) {
        if (!empty($meta_description)) {
            return $meta_description;
        }
        
        // Use excerpt if available
        if (has_excerpt($post->ID)) {
            return get_the_excerpt($post->ID);
        }
        
        // Use first part of content
        $content = get_the_content('', false, $post->ID);
        $content = strip_shortcodes($content);
        $content = wp_strip_all_tags($content);
        $content = str_replace(array("\r", "\n"), ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        
        if (strlen($content) > 160) {
            return substr($content, 0, 157) . '...';
        }
        
        return $content;
    }

    /**
     * Get preview social title for meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post          The post object.
     * @param    string     $social_title  The custom social title.
     * @param    string     $meta_title    The custom meta title.
     * @return   string                    Preview social title.
     */
    private function get_preview_social_title($post, $social_title, $meta_title) {
        if (!empty($social_title)) {
            return $social_title;
        }
        
        if (!empty($meta_title)) {
            return $meta_title;
        }
        
        return get_the_title($post->ID);
    }

    /**
     * Get preview social description for meta box
     *
     * @since    1.0.0
     * @param    WP_Post    $post                The post object.
     * @param    string     $social_description  The custom social description.
     * @param    string     $meta_description    The custom meta description.
     * @return   string                          Preview social description.
     */
    private function get_preview_social_description($post, $social_description, $meta_description) {
        if (!empty($social_description)) {
            return $social_description;
        }
        
        if (!empty($meta_description)) {
            return $meta_description;
        }
        
        return $this->get_preview_description($post, '');
    }
}