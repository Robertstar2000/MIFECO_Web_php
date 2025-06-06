<?php
/**
 * XML Sitemap Generator
 *
 * This class handles the generation of XML sitemaps for improved search engine indexing.
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
 * XML Sitemap Generator Class
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/seo
 * @author     MIFECO <contact@mifeco.com>
 */
class MIFECO_Sitemap {

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
     * Sitemap settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $settings    Sitemap settings.
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
        $this->settings = get_option('mifeco_sitemap_settings', array());
    }

    /**
     * Register sitemap settings
     *
     * @since    1.0.0
     */
    public function register_sitemap_settings() {
        register_setting(
            'mifeco_sitemap_settings_group',
            'mifeco_sitemap_settings',
            array($this, 'sanitize_sitemap_settings')
        );
        
        add_settings_section(
            'mifeco_sitemap_general_section',
            __('General Sitemap Settings', 'mifeco-suite'),
            array($this, 'render_sitemap_general_section'),
            'mifeco-sitemap-settings'
        );
        
        add_settings_field(
            'enable_sitemap',
            __('Enable XML Sitemap', 'mifeco-suite'),
            array($this, 'render_enable_sitemap_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_general_section'
        );
        
        add_settings_field(
            'ping_search_engines',
            __('Ping Search Engines', 'mifeco-suite'),
            array($this, 'render_ping_search_engines_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_general_section'
        );
        
        add_settings_field(
            'sitemap_url',
            __('Sitemap URL', 'mifeco-suite'),
            array($this, 'render_sitemap_url_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_general_section'
        );
        
        // Content Settings
        add_settings_section(
            'mifeco_sitemap_content_section',
            __('Content Settings', 'mifeco-suite'),
            array($this, 'render_sitemap_content_section'),
            'mifeco-sitemap-settings'
        );
        
        add_settings_field(
            'include_post_types',
            __('Include Post Types', 'mifeco-suite'),
            array($this, 'render_include_post_types_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_content_section'
        );
        
        add_settings_field(
            'include_taxonomies',
            __('Include Taxonomies', 'mifeco-suite'),
            array($this, 'render_include_taxonomies_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_content_section'
        );
        
        add_settings_field(
            'include_archives',
            __('Include Archives', 'mifeco-suite'),
            array($this, 'render_include_archives_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_content_section'
        );
        
        add_settings_field(
            'exclude_items',
            __('Exclude Items', 'mifeco-suite'),
            array($this, 'render_exclude_items_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_content_section'
        );
        
        // Priority Settings
        add_settings_section(
            'mifeco_sitemap_priority_section',
            __('Priority Settings', 'mifeco-suite'),
            array($this, 'render_sitemap_priority_section'),
            'mifeco-sitemap-settings'
        );
        
        add_settings_field(
            'post_type_priorities',
            __('Post Type Priorities', 'mifeco-suite'),
            array($this, 'render_post_type_priorities_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_priority_section'
        );
        
        add_settings_field(
            'taxonomy_priorities',
            __('Taxonomy Priorities', 'mifeco-suite'),
            array($this, 'render_taxonomy_priorities_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_priority_section'
        );
        
        add_settings_field(
            'change_frequencies',
            __('Change Frequencies', 'mifeco-suite'),
            array($this, 'render_change_frequencies_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_priority_section'
        );
        
        // Advanced Settings
        add_settings_section(
            'mifeco_sitemap_advanced_section',
            __('Advanced Settings', 'mifeco-suite'),
            array($this, 'render_sitemap_advanced_section'),
            'mifeco-sitemap-settings'
        );
        
        add_settings_field(
            'max_entries_per_sitemap',
            __('Max Entries Per Sitemap', 'mifeco-suite'),
            array($this, 'render_max_entries_per_sitemap_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_advanced_section'
        );
        
        add_settings_field(
            'enable_image_sitemap',
            __('Enable Image Sitemap', 'mifeco-suite'),
            array($this, 'render_enable_image_sitemap_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_advanced_section'
        );
        
        add_settings_field(
            'enable_news_sitemap',
            __('Enable News Sitemap', 'mifeco-suite'),
            array($this, 'render_enable_news_sitemap_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_advanced_section'
        );
        
        add_settings_field(
            'enable_html_sitemap',
            __('Enable HTML Sitemap', 'mifeco-suite'),
            array($this, 'render_enable_html_sitemap_field'),
            'mifeco-sitemap-settings',
            'mifeco_sitemap_advanced_section'
        );
    }

    /**
     * Sanitize sitemap settings
     *
     * @since    1.0.0
     * @param    array    $input    The input options.
     * @return   array              The sanitized options.
     */
    public function sanitize_sitemap_settings($input) {
        $sanitized = array();
        
        // General settings
        $sanitized['enable_sitemap'] = isset($input['enable_sitemap']) ? (bool) $input['enable_sitemap'] : true;
        $sanitized['ping_search_engines'] = isset($input['ping_search_engines']) ? (bool) $input['ping_search_engines'] : true;
        
        // Content settings
        $sanitized['include_post_types'] = isset($input['include_post_types']) && is_array($input['include_post_types']) ? $input['include_post_types'] : array();
        $sanitized['include_taxonomies'] = isset($input['include_taxonomies']) && is_array($input['include_taxonomies']) ? $input['include_taxonomies'] : array();
        $sanitized['include_archives'] = isset($input['include_archives']) && is_array($input['include_archives']) ? $input['include_archives'] : array();
        $sanitized['exclude_items'] = isset($input['exclude_items']) ? sanitize_textarea_field($input['exclude_items']) : '';
        
        // Priority settings
        $sanitized['post_type_priorities'] = array();
        if (isset($input['post_type_priorities']) && is_array($input['post_type_priorities'])) {
            foreach ($input['post_type_priorities'] as $post_type => $priority) {
                $sanitized['post_type_priorities'][$post_type] = min(max(0.0, floatval($priority)), 1.0);
            }
        }
        
        $sanitized['taxonomy_priorities'] = array();
        if (isset($input['taxonomy_priorities']) && is_array($input['taxonomy_priorities'])) {
            foreach ($input['taxonomy_priorities'] as $taxonomy => $priority) {
                $sanitized['taxonomy_priorities'][$taxonomy] = min(max(0.0, floatval($priority)), 1.0);
            }
        }
        
        $sanitized['change_frequencies'] = array();
        if (isset($input['change_frequencies']) && is_array($input['change_frequencies'])) {
            foreach ($input['change_frequencies'] as $type => $frequency) {
                $sanitized['change_frequencies'][$type] = sanitize_text_field($frequency);
            }
        }
        
        // Advanced settings
        $sanitized['max_entries_per_sitemap'] = isset($input['max_entries_per_sitemap']) ? intval($input['max_entries_per_sitemap']) : 1000;
        $sanitized['enable_image_sitemap'] = isset($input['enable_image_sitemap']) ? (bool) $input['enable_image_sitemap'] : true;
        $sanitized['enable_news_sitemap'] = isset($input['enable_news_sitemap']) ? (bool) $input['enable_news_sitemap'] : false;
        $sanitized['enable_html_sitemap'] = isset($input['enable_html_sitemap']) ? (bool) $input['enable_html_sitemap'] : true;
        
        return $sanitized;
    }

    /**
     * Render general section description
     *
     * @since    1.0.0
     */
    public function render_sitemap_general_section() {
        echo '<p>' . __('Configure general sitemap settings.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render content section description
     *
     * @since    1.0.0
     */
    public function render_sitemap_content_section() {
        echo '<p>' . __('Configure what content to include in your sitemap.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render priority section description
     *
     * @since    1.0.0
     */
    public function render_sitemap_priority_section() {
        echo '<p>' . __('Configure priorities and change frequencies for different content types.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render advanced section description
     *
     * @since    1.0.0
     */
    public function render_sitemap_advanced_section() {
        echo '<p>' . __('Configure advanced sitemap settings.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render enable sitemap field
     *
     * @since    1.0.0
     */
    public function render_enable_sitemap_field() {
        $enable_sitemap = isset($this->settings['enable_sitemap']) ? $this->settings['enable_sitemap'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_sitemap_settings[enable_sitemap]" value="1" <?php checked($enable_sitemap, true); ?>>
            <?php _e('Enable XML sitemap generation', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Generate XML sitemaps for better search engine indexing.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render ping search engines field
     *
     * @since    1.0.0
     */
    public function render_ping_search_engines_field() {
        $ping_search_engines = isset($this->settings['ping_search_engines']) ? $this->settings['ping_search_engines'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_sitemap_settings[ping_search_engines]" value="1" <?php checked($ping_search_engines, true); ?>>
            <?php _e('Ping search engines when sitemap is updated', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Automatically notify Google and Bing when your sitemap changes.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render sitemap URL field
     *
     * @since    1.0.0
     */
    public function render_sitemap_url_field() {
        $sitemap_url = home_url('sitemap.xml');
        ?>
        <input type="text" value="<?php echo esc_url($sitemap_url); ?>" class="regular-text" readonly>
        <p class="description"><?php _e('The URL of your XML sitemap.', 'mifeco-suite'); ?></p>
        <button type="button" class="button" id="mifeco-copy-sitemap-url" data-clipboard-text="<?php echo esc_url($sitemap_url); ?>"><?php _e('Copy URL', 'mifeco-suite'); ?></button>
        <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" class="button"><?php _e('View Sitemap', 'mifeco-suite'); ?></a>
        <?php
    }

    /**
     * Render include post types field
     *
     * @since    1.0.0
     */
    public function render_include_post_types_field() {
        $include_post_types = isset($this->settings['include_post_types']) ? $this->settings['include_post_types'] : array();
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
        <?php
    }

    /**
     * Render include taxonomies field
     *
     * @since    1.0.0
     */
    public function render_include_taxonomies_field() {
        $include_taxonomies = isset($this->settings['include_taxonomies']) ? $this->settings['include_taxonomies'] : array();
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
        <?php
    }

    /**
     * Render include archives field
     *
     * @since    1.0.0
     */
    public function render_include_archives_field() {
        $include_archives = isset($this->settings['include_archives']) ? $this->settings['include_archives'] : array();
        $archive_types = array(
            'author' => __('Author Archives', 'mifeco-suite'),
            'date' => __('Date Archives', 'mifeco-suite'),
        );
        
        // Add post type archives
        $post_types = get_post_types(array('public' => true, 'has_archive' => true), 'objects');
        foreach ($post_types as $post_type) {
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
        <?php
    }

    /**
     * Render exclude items field
     *
     * @since    1.0.0
     */
    public function render_exclude_items_field() {
        $exclude_items = isset($this->settings['exclude_items']) ? $this->settings['exclude_items'] : '';
        ?>
        <textarea name="mifeco_sitemap_settings[exclude_items]" rows="5" class="large-text code"><?php echo esc_textarea($exclude_items); ?></textarea>
        <p class="description"><?php _e('Enter post/page IDs to exclude from the sitemap, one per line. You can also use post types or taxonomies with "post_type:name" or "taxonomy:name".', 'mifeco-suite'); ?></p>
        <p class="description"><?php _e('Examples: "123" (exclude post ID 123), "post_type:revision" (exclude all revisions), "taxonomy:category:3" (exclude category ID 3).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render post type priorities field
     *
     * @since    1.0.0
     */
    public function render_post_type_priorities_field() {
        $post_type_priorities = isset($this->settings['post_type_priorities']) ? $this->settings['post_type_priorities'] : array();
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
        <?php
    }

    /**
     * Render taxonomy priorities field
     *
     * @since    1.0.0
     */
    public function render_taxonomy_priorities_field() {
        $taxonomy_priorities = isset($this->settings['taxonomy_priorities']) ? $this->settings['taxonomy_priorities'] : array();
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
        <?php
    }

    /**
     * Render change frequencies field
     *
     * @since    1.0.0
     */
    public function render_change_frequencies_field() {
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        
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
        <?php
    }

    /**
     * Render max entries per sitemap field
     *
     * @since    1.0.0
     */
    public function render_max_entries_per_sitemap_field() {
        $max_entries = isset($this->settings['max_entries_per_sitemap']) ? $this->settings['max_entries_per_sitemap'] : 1000;
        ?>
        <input type="number" name="mifeco_sitemap_settings[max_entries_per_sitemap]" value="<?php echo esc_attr($max_entries); ?>" min="100" max="50000" step="100" class="small-text">
        <p class="description"><?php _e('Maximum number of URLs per sitemap file. If you have more URLs, multiple sitemap files will be created.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable image sitemap field
     *
     * @since    1.0.0
     */
    public function render_enable_image_sitemap_field() {
        $enable_image_sitemap = isset($this->settings['enable_image_sitemap']) ? $this->settings['enable_image_sitemap'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_sitemap_settings[enable_image_sitemap]" value="1" <?php checked($enable_image_sitemap, true); ?>>
            <?php _e('Include image information in the sitemap', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Include images from your content in the sitemap for better image indexing.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable news sitemap field
     *
     * @since    1.0.0
     */
    public function render_enable_news_sitemap_field() {
        $enable_news_sitemap = isset($this->settings['enable_news_sitemap']) ? $this->settings['enable_news_sitemap'] : false;
        ?>
        <label>
            <input type="checkbox" name="mifeco_sitemap_settings[enable_news_sitemap]" value="1" <?php checked($enable_news_sitemap, true); ?>>
            <?php _e('Generate a Google News sitemap', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Create a separate sitemap for Google News. Only enable if your site is registered with Google News.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render enable HTML sitemap field
     *
     * @since    1.0.0
     */
    public function render_enable_html_sitemap_field() {
        $enable_html_sitemap = isset($this->settings['enable_html_sitemap']) ? $this->settings['enable_html_sitemap'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_sitemap_settings[enable_html_sitemap]" value="1" <?php checked($enable_html_sitemap, true); ?>>
            <?php _e('Generate an HTML sitemap', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Create a human-readable HTML sitemap. You can add it to your site using the [mifeco_html_sitemap] shortcode.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Register sitemap endpoint
     *
     * @since    1.0.0
     */
    public function register_sitemap_endpoint() {
        // Only register if sitemaps are enabled
        $enable_sitemap = isset($this->settings['enable_sitemap']) ? $this->settings['enable_sitemap'] : true;
        
        if (!$enable_sitemap) {
            return;
        }
        
        // Add rewrite rules for sitemaps
        add_rewrite_rule('^sitemap\.xml$', 'index.php?mifeco_sitemap=index', 'top');
        add_rewrite_rule('^sitemap-([^/]+)\.xml$', 'index.php?mifeco_sitemap=$matches[1]', 'top');
        
        // Register query vars
        add_filter('query_vars', function($vars) {
            $vars[] = 'mifeco_sitemap';
            return $vars;
        });
        
        // Check if we need to flush rewrite rules
        if (get_option('mifeco_sitemap_flush_rewrite') != 'no') {
            flush_rewrite_rules();
            update_option('mifeco_sitemap_flush_rewrite', 'no');
        }
    }

    /**
     * Generate sitemap
     *
     * @since    1.0.0
     */
    public function generate_sitemap() {
        $sitemap_type = get_query_var('mifeco_sitemap');
        
        if (empty($sitemap_type)) {
            return;
        }
        
        // Only generate if sitemaps are enabled
        $enable_sitemap = isset($this->settings['enable_sitemap']) ? $this->settings['enable_sitemap'] : true;
        
        if (!$enable_sitemap) {
            return;
        }
        
        // Set content type header
        header('Content-Type: application/xml; charset=UTF-8');
        
        // Disable caching by plugins
        if (!defined('DONOTCACHEPAGE')) {
            define('DONOTCACHEPAGE', true);
        }
        
        // Get last modified timestamp for cache control
        $last_modified = $this->get_sitemap_last_modified();
        if ($last_modified) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
        }
        
        // Generate the appropriate sitemap
        if ($sitemap_type === 'index') {
            echo $this->generate_sitemap_index();
        } elseif (strpos($sitemap_type, 'post-') === 0) {
            $post_type = substr($sitemap_type, 5);
            echo $this->generate_post_type_sitemap($post_type);
        } elseif (strpos($sitemap_type, 'tax-') === 0) {
            $taxonomy = substr($sitemap_type, 4);
            echo $this->generate_taxonomy_sitemap($taxonomy);
        } elseif ($sitemap_type === 'author') {
            echo $this->generate_author_sitemap();
        } elseif ($sitemap_type === 'date') {
            echo $this->generate_date_sitemap();
        } elseif ($sitemap_type === 'image') {
            echo $this->generate_image_sitemap();
        } elseif ($sitemap_type === 'news') {
            echo $this->generate_news_sitemap();
        } elseif (strpos($sitemap_type, 'pt-archive-') === 0) {
            $post_type = substr($sitemap_type, 11);
            echo $this->generate_post_type_archive_sitemap($post_type);
        }
        
        // Terminate execution to prevent WordPress from loading the template
        exit;
    }

    /**
     * Generate sitemap index
     *
     * @since    1.0.0
     * @return   string    XML sitemap index.
     */
    private function generate_sitemap_index() {
        $sitemaps = array();
        
        // Get post types to include
        $include_post_types = isset($this->settings['include_post_types']) ? $this->settings['include_post_types'] : array();
        if (empty($include_post_types)) {
            $include_post_types = array_keys(get_post_types(array('public' => true)));
        }
        
        // Exclude attachment post type by default
        if (($key = array_search('attachment', $include_post_types)) !== false) {
            unset($include_post_types[$key]);
        }
        
        // Check excluded items
        $exclude_items = $this->parse_exclude_items();
        $excluded_post_types = isset($exclude_items['post_types']) ? $exclude_items['post_types'] : array();
        
        // Add post type sitemaps
        foreach ($include_post_types as $post_type) {
            if (in_array($post_type, $excluded_post_types)) {
                continue;
            }
            
            $count = $this->get_post_type_count($post_type);
            $max_entries = isset($this->settings['max_entries_per_sitemap']) ? $this->settings['max_entries_per_sitemap'] : 1000;
            $num_sitemaps = max(1, ceil($count / $max_entries));
            
            for ($i = 1; $i <= $num_sitemaps; $i++) {
                $sitemaps[] = array(
                    'loc' => home_url("sitemap-post-$post_type-$i.xml"),
                    'lastmod' => $this->get_post_type_lastmod($post_type),
                );
            }
        }
        
        // Get taxonomies to include
        $include_taxonomies = isset($this->settings['include_taxonomies']) ? $this->settings['include_taxonomies'] : array();
        if (empty($include_taxonomies)) {
            $include_taxonomies = array_keys(get_taxonomies(array('public' => true)));
        }
        
        // Check excluded items
        $excluded_taxonomies = isset($exclude_items['taxonomies']) ? $exclude_items['taxonomies'] : array();
        
        // Add taxonomy sitemaps
        foreach ($include_taxonomies as $taxonomy) {
            if (in_array($taxonomy, $excluded_taxonomies)) {
                continue;
            }
            
            $count = $this->get_taxonomy_count($taxonomy);
            if ($count > 0) {
                $sitemaps[] = array(
                    'loc' => home_url("sitemap-tax-$taxonomy.xml"),
                    'lastmod' => $this->get_taxonomy_lastmod($taxonomy),
                );
            }
        }
        
        // Add archive sitemaps
        $include_archives = isset($this->settings['include_archives']) ? $this->settings['include_archives'] : array();
        
        if (empty($include_archives) || in_array('author', $include_archives)) {
            $sitemaps[] = array(
                'loc' => home_url('sitemap-author.xml'),
                'lastmod' => $this->get_author_lastmod(),
            );
        }
        
        if (empty($include_archives) || in_array('date', $include_archives)) {
            $sitemaps[] = array(
                'loc' => home_url('sitemap-date.xml'),
                'lastmod' => $this->get_date_lastmod(),
            );
        }
        
        // Add post type archive sitemaps
        foreach ($include_post_types as $post_type) {
            $post_type_obj = get_post_type_object($post_type);
            if ($post_type_obj && $post_type_obj->has_archive) {
                $archive_type = 'post_type_' . $post_type;
                if (empty($include_archives) || in_array($archive_type, $include_archives)) {
                    $sitemaps[] = array(
                        'loc' => home_url("sitemap-pt-archive-$post_type.xml"),
                        'lastmod' => $this->get_post_type_lastmod($post_type),
                    );
                }
            }
        }
        
        // Add image sitemap if enabled
        $enable_image_sitemap = isset($this->settings['enable_image_sitemap']) ? $this->settings['enable_image_sitemap'] : true;
        if ($enable_image_sitemap) {
            $sitemaps[] = array(
                'loc' => home_url('sitemap-image.xml'),
                'lastmod' => $this->get_image_lastmod(),
            );
        }
        
        // Add news sitemap if enabled
        $enable_news_sitemap = isset($this->settings['enable_news_sitemap']) ? $this->settings['enable_news_sitemap'] : false;
        if ($enable_news_sitemap) {
            $sitemaps[] = array(
                'loc' => home_url('sitemap-news.xml'),
                'lastmod' => $this->get_news_lastmod(),
            );
        }
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($sitemaps as $sitemap) {
            $xml .= '<sitemap>';
            $xml .= '<loc>' . esc_url($sitemap['loc']) . '</loc>';
            if (!empty($sitemap['lastmod'])) {
                $xml .= '<lastmod>' . esc_html($sitemap['lastmod']) . '</lastmod>';
            }
            $xml .= '</sitemap>';
        }
        
        $xml .= '</sitemapindex>';
        
        return $xml;
    }

    /**
     * Generate post type sitemap
     *
     * @since    1.0.0
     * @param    string    $post_type    Post type.
     * @return   string                  XML sitemap.
     */
    private function generate_post_type_sitemap($post_type) {
        global $wpdb;
        
        // Extract page number if present
        $page = 1;
        if (strpos($post_type, '-') !== false) {
            $parts = explode('-', $post_type);
            $post_type = $parts[0];
            $page = intval($parts[1]);
        }
        
        // Check if post type exists
        $post_type_obj = get_post_type_object($post_type);
        if (!$post_type_obj || !$post_type_obj->public) {
            return $this->generate_empty_sitemap();
        }
        
        // Get excluded posts
        $exclude_items = $this->parse_exclude_items();
        $excluded_posts = isset($exclude_items['posts']) ? $exclude_items['posts'] : array();
        
        // Build query
        $max_entries = isset($this->settings['max_entries_per_sitemap']) ? $this->settings['max_entries_per_sitemap'] : 1000;
        $offset = ($page - 1) * $max_entries;
        
        $query = $wpdb->prepare(
            "SELECT ID, post_modified, post_date, post_name 
            FROM $wpdb->posts 
            WHERE post_type = %s 
            AND post_status = 'publish'
            AND ID NOT IN (" . implode(',', array_map('intval', $excluded_posts)) . ")
            ORDER BY post_modified DESC
            LIMIT %d, %d",
            $post_type,
            $offset,
            $max_entries
        );
        
        // Fix query if no excluded posts
        if (empty($excluded_posts)) {
            $query = str_replace("AND ID NOT IN ()", "", $query);
        }
        
        $posts = $wpdb->get_results($query);
        
        // Get priorities and change frequencies
        $post_type_priorities = isset($this->settings['post_type_priorities']) ? $this->settings['post_type_priorities'] : array();
        $default_priority = isset($post_type_priorities[$post_type]) ? $post_type_priorities[$post_type] : 0.5;
        
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        $default_frequency = isset($change_frequencies[$post_type]) ? $change_frequencies[$post_type] : 'monthly';
        
        // Enable image sitemap?
        $enable_image_sitemap = isset($this->settings['enable_image_sitemap']) ? $this->settings['enable_image_sitemap'] : true;
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        
        if ($enable_image_sitemap) {
            $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"';
        }
        
        $xml .= '>';
        
        foreach ($posts as $post) {
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url(get_permalink($post->ID)) . '</loc>';
            $xml .= '<lastmod>' . esc_html(date('c', strtotime($post->post_modified))) . '</lastmod>';
            $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
            $xml .= '<priority>' . esc_html($default_priority) . '</priority>';
            
            // Add images if enabled
            if ($enable_image_sitemap) {
                $images = $this->get_post_images($post->ID);
                foreach ($images as $image) {
                    $xml .= '<image:image>';
                    $xml .= '<image:loc>' . esc_url($image['src']) . '</image:loc>';
                    if (!empty($image['title'])) {
                        $xml .= '<image:title>' . esc_html($image['title']) . '</image:title>';
                    }
                    if (!empty($image['alt'])) {
                        $xml .= '<image:caption>' . esc_html($image['alt']) . '</image:caption>';
                    }
                    $xml .= '</image:image>';
                }
            }
            
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate taxonomy sitemap
     *
     * @since    1.0.0
     * @param    string    $taxonomy    Taxonomy name.
     * @return   string                 XML sitemap.
     */
    private function generate_taxonomy_sitemap($taxonomy) {
        // Check if taxonomy exists
        $taxonomy_obj = get_taxonomy($taxonomy);
        if (!$taxonomy_obj || !$taxonomy_obj->public) {
            return $this->generate_empty_sitemap();
        }
        
        // Get excluded terms
        $exclude_items = $this->parse_exclude_items();
        $excluded_terms = isset($exclude_items['terms'][$taxonomy]) ? $exclude_items['terms'][$taxonomy] : array();
        
        // Get terms
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'exclude' => $excluded_terms,
            'hide_empty' => true,
            'number' => 0,
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            return $this->generate_empty_sitemap();
        }
        
        // Get priorities and change frequencies
        $taxonomy_priorities = isset($this->settings['taxonomy_priorities']) ? $this->settings['taxonomy_priorities'] : array();
        $default_priority = isset($taxonomy_priorities[$taxonomy]) ? $taxonomy_priorities[$taxonomy] : 0.5;
        
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        $default_frequency = isset($change_frequencies['taxonomy']) ? $change_frequencies['taxonomy'] : 'weekly';
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($terms as $term) {
            $term_link = get_term_link($term);
            if (is_wp_error($term_link)) {
                continue;
            }
            
            // Check for term specific noindex
            $robots_noindex = get_term_meta($term->term_id, '_mifeco_robots_noindex', true);
            if ($robots_noindex) {
                continue;
            }
            
            // Get term last modified date
            $lastmod = $this->get_term_lastmod($term);
            
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($term_link) . '</loc>';
            
            if ($lastmod) {
                $xml .= '<lastmod>' . esc_html($lastmod) . '</lastmod>';
            }
            
            $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
            $xml .= '<priority>' . esc_html($default_priority) . '</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate author sitemap
     *
     * @since    1.0.0
     * @return   string    XML sitemap.
     */
    private function generate_author_sitemap() {
        // Check if author archives are enabled
        $include_archives = isset($this->settings['include_archives']) ? $this->settings['include_archives'] : array();
        if (!empty($include_archives) && !in_array('author', $include_archives)) {
            return $this->generate_empty_sitemap();
        }
        
        // Get authors with published posts
        $authors = get_users(array(
            'who' => 'authors',
            'has_published_posts' => true,
        ));
        
        if (empty($authors)) {
            return $this->generate_empty_sitemap();
        }
        
        // Get change frequency and priority
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        $default_frequency = isset($change_frequencies['archive']) ? $change_frequencies['archive'] : 'monthly';
        $default_priority = 0.3;
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($authors as $author) {
            $author_url = get_author_posts_url($author->ID);
            $last_post = $this->get_user_last_post($author->ID);
            
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($author_url) . '</loc>';
            
            if ($last_post) {
                $xml .= '<lastmod>' . esc_html(date('c', strtotime($last_post->post_modified))) . '</lastmod>';
            }
            
            $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
            $xml .= '<priority>' . esc_html($default_priority) . '</priority>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate date sitemap
     *
     * @since    1.0.0
     * @return   string    XML sitemap.
     */
    private function generate_date_sitemap() {
        // Check if date archives are enabled
        $include_archives = isset($this->settings['include_archives']) ? $this->settings['include_archives'] : array();
        if (!empty($include_archives) && !in_array('date', $include_archives)) {
            return $this->generate_empty_sitemap();
        }
        
        global $wpdb;
        
        // Get years with posts
        $years = $wpdb->get_col("
            SELECT DISTINCT YEAR(post_date) as year
            FROM $wpdb->posts
            WHERE post_status = 'publish' AND post_type = 'post'
            ORDER BY year DESC
        ");
        
        if (empty($years)) {
            return $this->generate_empty_sitemap();
        }
        
        // Get change frequency and priority
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        $default_frequency = isset($change_frequencies['archive']) ? $change_frequencies['archive'] : 'monthly';
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        // Add year archives
        foreach ($years as $year) {
            $year_url = get_year_link($year);
            
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($year_url) . '</loc>';
            $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
            $xml .= '<priority>0.3</priority>';
            $xml .= '</url>';
            
            // Get months with posts for this year
            $months = $wpdb->get_col($wpdb->prepare("
                SELECT DISTINCT MONTH(post_date) as month
                FROM $wpdb->posts
                WHERE post_status = 'publish' AND post_type = 'post'
                AND YEAR(post_date) = %d
                ORDER BY month DESC
            ", $year));
            
            // Add month archives
            foreach ($months as $month) {
                $month_url = get_month_link($year, $month);
                
                $xml .= '<url>';
                $xml .= '<loc>' . esc_url($month_url) . '</loc>';
                $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
                $xml .= '<priority>0.2</priority>';
                $xml .= '</url>';
            }
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate post type archive sitemap
     *
     * @since    1.0.0
     * @param    string    $post_type    Post type.
     * @return   string                  XML sitemap.
     */
    private function generate_post_type_archive_sitemap($post_type) {
        // Check if post type exists and has archive
        $post_type_obj = get_post_type_object($post_type);
        if (!$post_type_obj || !$post_type_obj->has_archive) {
            return $this->generate_empty_sitemap();
        }
        
        // Check if post type archives are enabled
        $include_archives = isset($this->settings['include_archives']) ? $this->settings['include_archives'] : array();
        $archive_type = 'post_type_' . $post_type;
        if (!empty($include_archives) && !in_array($archive_type, $include_archives)) {
            return $this->generate_empty_sitemap();
        }
        
        // Get change frequency and priority
        $change_frequencies = isset($this->settings['change_frequencies']) ? $this->settings['change_frequencies'] : array();
        $default_frequency = isset($change_frequencies['archive']) ? $change_frequencies['archive'] : 'monthly';
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        $archive_url = get_post_type_archive_link($post_type);
        
        $xml .= '<url>';
        $xml .= '<loc>' . esc_url($archive_url) . '</loc>';
        $xml .= '<lastmod>' . esc_html($this->get_post_type_lastmod($post_type)) . '</lastmod>';
        $xml .= '<changefreq>' . esc_html($default_frequency) . '</changefreq>';
        $xml .= '<priority>0.6</priority>';
        $xml .= '</url>';
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate image sitemap
     *
     * @since    1.0.0
     * @return   string    XML sitemap.
     */
    private function generate_image_sitemap() {
        // Check if image sitemap is enabled
        $enable_image_sitemap = isset($this->settings['enable_image_sitemap']) ? $this->settings['enable_image_sitemap'] : true;
        if (!$enable_image_sitemap) {
            return $this->generate_empty_sitemap();
        }
        
        global $wpdb;
        
        // Get image attachments
        $images = $wpdb->get_results("
            SELECT ID, post_title, post_excerpt, post_content, post_parent, guid, post_modified
            FROM $wpdb->posts
            WHERE post_type = 'attachment'
            AND post_mime_type LIKE 'image/%'
            AND post_status = 'inherit'
            ORDER BY post_modified DESC
            LIMIT 1000
        ");
        
        if (empty($images)) {
            return $this->generate_empty_sitemap();
        }
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';
        
        // Group images by parent post
        $image_groups = array();
        
        foreach ($images as $image) {
            $parent_id = $image->post_parent;
            
            // Skip images without parent
            if (empty($parent_id)) {
                continue;
            }
            
            // Check if parent post is published
            $parent_status = get_post_status($parent_id);
            if ($parent_status !== 'publish') {
                continue;
            }
            
            // Add image to group
            if (!isset($image_groups[$parent_id])) {
                $image_groups[$parent_id] = array();
            }
            
            $image_groups[$parent_id][] = array(
                'src' => wp_get_attachment_url($image->ID),
                'title' => $image->post_title,
                'alt' => get_post_meta($image->ID, '_wp_attachment_image_alt', true),
            );
        }
        
        // Add URLs for each parent post
        foreach ($image_groups as $parent_id => $images) {
            $parent_url = get_permalink($parent_id);
            
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($parent_url) . '</loc>';
            
            foreach ($images as $image) {
                $xml .= '<image:image>';
                $xml .= '<image:loc>' . esc_url($image['src']) . '</image:loc>';
                if (!empty($image['title'])) {
                    $xml .= '<image:title>' . esc_html($image['title']) . '</image:title>';
                }
                if (!empty($image['alt'])) {
                    $xml .= '<image:caption>' . esc_html($image['alt']) . '</image:caption>';
                }
                $xml .= '</image:image>';
            }
            
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate news sitemap
     *
     * @since    1.0.0
     * @return   string    XML sitemap.
     */
    private function generate_news_sitemap() {
        // Check if news sitemap is enabled
        $enable_news_sitemap = isset($this->settings['enable_news_sitemap']) ? $this->settings['enable_news_sitemap'] : false;
        if (!$enable_news_sitemap) {
            return $this->generate_empty_sitemap();
        }
        
        global $wpdb;
        
        // Get recent posts (within 2 days)
        $recent_posts = $wpdb->get_results("
            SELECT ID, post_title, post_date, post_content
            FROM $wpdb->posts
            WHERE post_type = 'post'
            AND post_status = 'publish'
            AND post_date > '" . date('Y-m-d H:i:s', strtotime('-2 days')) . "'
            ORDER BY post_date DESC
            LIMIT 1000
        ");
        
        if (empty($recent_posts)) {
            return $this->generate_empty_sitemap();
        }
        
        // Build XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9">';
        
        $site_name = get_bloginfo('name');
        
        foreach ($recent_posts as $post) {
            $permalink = get_permalink($post->ID);
            $post_date = date('Y-m-d\TH:i:sP', strtotime($post->post_date));
            
            $xml .= '<url>';
            $xml .= '<loc>' . esc_url($permalink) . '</loc>';
            $xml .= '<news:news>';
            $xml .= '<news:publication>';
            $xml .= '<news:name>' . esc_html($site_name) . '</news:name>';
            $xml .= '<news:language>' . esc_html(get_locale()) . '</news:language>';
            $xml .= '</news:publication>';
            $xml .= '<news:publication_date>' . esc_html($post_date) . '</news:publication_date>';
            $xml .= '<news:title>' . esc_html($post->post_title) . '</news:title>';
            $xml .= '</news:news>';
            $xml .= '</url>';
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate empty sitemap
     *
     * @since    1.0.0
     * @return   string    Empty XML sitemap.
     */
    private function generate_empty_sitemap() {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<?xml-stylesheet type="text/xsl" href="' . esc_url(MIFECO_PLUGIN_URL . 'assets/css/sitemap.xsl') . '"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        
        return $xml;
    }

    /**
     * Parse exclude items
     *
     * @since    1.0.0
     * @return   array    Excluded items.
     */
    private function parse_exclude_items() {
        $exclude_items_text = isset($this->settings['exclude_items']) ? $this->settings['exclude_items'] : '';
        $excluded = array(
            'posts' => array(),
            'post_types' => array(),
            'taxonomies' => array(),
            'terms' => array(),
        );
        
        if (empty($exclude_items_text)) {
            return $excluded;
        }
        
        $items = explode("\n", $exclude_items_text);
        
        foreach ($items as $item) {
            $item = trim($item);
            
            if (empty($item)) {
                continue;
            }
            
            // Check if item is a post type
            if (strpos($item, 'post_type:') === 0) {
                $post_type = substr($item, 10);
                $excluded['post_types'][] = $post_type;
                continue;
            }
            
            // Check if item is a taxonomy
            if (strpos($item, 'taxonomy:') === 0) {
                $parts = explode(':', substr($item, 9));
                
                if (count($parts) === 1) {
                    // Exclude entire taxonomy
                    $excluded['taxonomies'][] = $parts[0];
                } elseif (count($parts) === 2) {
                    // Exclude specific term
                    $taxonomy = $parts[0];
                    $term_id = intval($parts[1]);
                    
                    if (!isset($excluded['terms'][$taxonomy])) {
                        $excluded['terms'][$taxonomy] = array();
                    }
                    
                    $excluded['terms'][$taxonomy][] = $term_id;
                }
                
                continue;
            }
            
            // Assume item is a post ID
            $post_id = intval($item);
            if ($post_id > 0) {
                $excluded['posts'][] = $post_id;
            }
        }
        
        return $excluded;
    }

    /**
     * Get post type count
     *
     * @since    1.0.0
     * @param    string    $post_type    Post type.
     * @return   int                     Number of posts.
     */
    private function get_post_type_count($post_type) {
        global $wpdb;
        
        return $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM $wpdb->posts
            WHERE post_type = %s
            AND post_status = 'publish'
        ", $post_type));
    }

    /**
     * Get taxonomy count
     *
     * @since    1.0.0
     * @param    string    $taxonomy    Taxonomy name.
     * @return   int                    Number of terms.
     */
    private function get_taxonomy_count($taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'fields' => 'count',
        ));
        
        return is_wp_error($terms) ? 0 : $terms;
    }

    /**
     * Get post type last modified date
     *
     * @since    1.0.0
     * @param    string    $post_type    Post type.
     * @return   string                  Last modified date in ISO 8601 format.
     */
    private function get_post_type_lastmod($post_type) {
        global $wpdb;
        
        $last_modified = $wpdb->get_var($wpdb->prepare("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_type = %s
            AND post_status = 'publish'
        ", $post_type));
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get taxonomy last modified date
     *
     * @since    1.0.0
     * @param    string    $taxonomy    Taxonomy name.
     * @return   string                 Last modified date in ISO 8601 format.
     */
    private function get_taxonomy_lastmod($taxonomy) {
        global $wpdb;
        
        // Get the most recent post in any term of this taxonomy
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'fields' => 'ids',
        ));
        
        if (is_wp_error($terms) || empty($terms)) {
            return date('c');
        }
        
        $term_ids = implode(',', array_map('intval', $terms));
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(p.post_modified)
            FROM $wpdb->posts p
            JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
            JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.term_id IN ($term_ids)
            AND p.post_status = 'publish'
        ");
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get term last modified date
     *
     * @since    1.0.0
     * @param    object    $term    Term object.
     * @return   string             Last modified date in ISO 8601 format.
     */
    private function get_term_lastmod($term) {
        global $wpdb;
        
        $last_modified = $wpdb->get_var($wpdb->prepare("
            SELECT MAX(p.post_modified)
            FROM $wpdb->posts p
            JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
            JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.term_id = %d
            AND p.post_status = 'publish'
        ", $term->term_id));
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get author last modified date
     *
     * @since    1.0.0
     * @return   string    Last modified date in ISO 8601 format.
     */
    private function get_author_lastmod() {
        global $wpdb;
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_type = 'post'
            AND post_status = 'publish'
        ");
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get date last modified date
     *
     * @since    1.0.0
     * @return   string    Last modified date in ISO 8601 format.
     */
    private function get_date_lastmod() {
        global $wpdb;
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_type = 'post'
            AND post_status = 'publish'
        ");
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get image last modified date
     *
     * @since    1.0.0
     * @return   string    Last modified date in ISO 8601 format.
     */
    private function get_image_lastmod() {
        global $wpdb;
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_type = 'attachment'
            AND post_mime_type LIKE 'image/%'
            AND post_status = 'inherit'
        ");
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get news last modified date
     *
     * @since    1.0.0
     * @return   string    Last modified date in ISO 8601 format.
     */
    private function get_news_lastmod() {
        global $wpdb;
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_type = 'post'
            AND post_status = 'publish'
            AND post_date > '" . date('Y-m-d H:i:s', strtotime('-2 days')) . "'
        ");
        
        return $last_modified ? date('c', strtotime($last_modified)) : date('c');
    }

    /**
     * Get sitemap last modified timestamp
     *
     * @since    1.0.0
     * @return   int    Last modified timestamp.
     */
    private function get_sitemap_last_modified() {
        global $wpdb;
        
        $last_modified = $wpdb->get_var("
            SELECT MAX(post_modified)
            FROM $wpdb->posts
            WHERE post_status = 'publish'
        ");
        
        return $last_modified ? strtotime($last_modified) : time();
    }

    /**
     * Get user last post
     *
     * @since    1.0.0
     * @param    int       $user_id    User ID.
     * @return   WP_Post               Last post object or null.
     */
    private function get_user_last_post($user_id) {
        $posts = get_posts(array(
            'author' => $user_id,
            'orderby' => 'modified',
            'order' => 'DESC',
            'posts_per_page' => 1,
            'post_type' => 'post',
            'post_status' => 'publish',
        ));
        
        return !empty($posts) ? $posts[0] : null;
    }

    /**
     * Get post images
     *
     * @since    1.0.0
     * @param    int       $post_id    Post ID.
     * @return   array                 Array of image data.
     */
    private function get_post_images($post_id) {
        $images = array();
        
        // Featured image
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail = wp_get_attachment_image_src($thumbnail_id, 'full');
            
            if ($thumbnail) {
                $images[] = array(
                    'src' => $thumbnail[0],
                    'title' => get_the_title($thumbnail_id),
                    'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
                );
            }
        }
        
        // Images in content
        $post = get_post($post_id);
        if ($post) {
            $content = $post->post_content;
            
            // Find all img tags
            preg_match_all('/<img [^>]+>/', $content, $matches);
            
            if (!empty($matches[0])) {
                foreach ($matches[0] as $img) {
                    // Get src attribute
                    preg_match('/src=[\'"]([^\'"]+)[\'"]/', $img, $src_match);
                    
                    if (empty($src_match[1])) {
                        continue;
                    }
                    
                    $src = $src_match[1];
                    
                    // Get alt attribute
                    preg_match('/alt=[\'"]([^\'"]*)[\'"]/', $img, $alt_match);
                    $alt = !empty($alt_match[1]) ? $alt_match[1] : '';
                    
                    // Get title attribute
                    preg_match('/title=[\'"]([^\'"]*)[\'"]/', $img, $title_match);
                    $title = !empty($title_match[1]) ? $title_match[1] : '';
                    
                    // Add image
                    $images[] = array(
                        'src' => $src,
                        'title' => $title,
                        'alt' => $alt,
                    );
                }
            }
        }
        
        // Attached images
        $attachments = get_children(array(
            'post_parent' => $post_id,
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'numberposts' => -1,
        ));
        
        if ($attachments) {
            foreach ($attachments as $attachment_id => $attachment) {
                $image_url = wp_get_attachment_url($attachment_id);
                
                if ($image_url) {
                    $images[] = array(
                        'src' => $image_url,
                        'title' => $attachment->post_title,
                        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                    );
                }
            }
        }
        
        // Remove duplicates
        $unique_images = array();
        $urls = array();
        
        foreach ($images as $image) {
            if (!in_array($image['src'], $urls)) {
                $urls[] = $image['src'];
                $unique_images[] = $image;
            }
        }
        
        return $unique_images;
    }

    /**
     * Test sitemap validation using AJAX
     *
     * @since    1.0.0
     */
    public function ajax_validate_sitemap() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_seo_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'mifeco-suite')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'mifeco-suite')));
        }
        
        $sitemap_url = home_url('sitemap.xml');
        
        // Check if sitemap is accessible
        $request = wp_remote_get($sitemap_url);
        
        if (is_wp_error($request)) {
            wp_send_json_error(array(
                'message' => __('Error accessing sitemap: ', 'mifeco-suite') . $request->get_error_message(),
            ));
        }
        
        $response_code = wp_remote_retrieve_response_code($request);
        
        if ($response_code !== 200) {
            wp_send_json_error(array(
                'message' => sprintf(__('Sitemap returned HTTP error code: %d', 'mifeco-suite'), $response_code),
            ));
        }
        
        $body = wp_remote_retrieve_body($request);
        
        if (empty($body)) {
            wp_send_json_error(array(
                'message' => __('Sitemap is empty.', 'mifeco-suite'),
            ));
        }
        
        // Simple XML validation
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body);
        
        if ($xml === false) {
            $errors = libxml_get_errors();
            $error_msg = __('XML validation errors:', 'mifeco-suite') . '<br>';
            
            foreach ($errors as $error) {
                $error_msg .= sprintf(__('Line %d: %s', 'mifeco-suite'), $error->line, $error->message) . '<br>';
            }
            
            libxml_clear_errors();
            
            wp_send_json_error(array(
                'message' => $error_msg,
            ));
        }
        
        // Check for sitemapindex or urlset
        $is_index = $xml->getName() === 'sitemapindex';
        $is_urlset = $xml->getName() === 'urlset';
        
        if (!$is_index && !$is_urlset) {
            wp_send_json_error(array(
                'message' => __('Invalid sitemap format. Root element should be either sitemapindex or urlset.', 'mifeco-suite'),
            ));
        }
        
        // Count sitemaps or URLs
        $count = 0;
        
        if ($is_index) {
            $count = count($xml->sitemap);
            $message = sprintf(__('Sitemap index contains %d sitemaps.', 'mifeco-suite'), $count);
        } else {
            $count = count($xml->url);
            $message = sprintf(__('Sitemap contains %d URLs.', 'mifeco-suite'), $count);
        }
        
        // Send success response
        wp_send_json_success(array(
            'message' => $message,
            'is_index' => $is_index,
            'count' => $count,
            'url' => $sitemap_url,
        ));
    }
}