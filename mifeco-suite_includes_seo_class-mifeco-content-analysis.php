<?php
/**
 * Content Analysis Class
 *
 * This class handles SEO content analysis and provides optimization suggestions.
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
 * Content Analysis Class
 *
 * @package    MIFECO_Suite
 * @subpackage MIFECO_Suite/includes/seo
 * @author     MIFECO <contact@mifeco.com>
 */
class MIFECO_Content_Analysis {

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
     * Analysis settings
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $settings    Analysis settings.
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
        $this->settings = get_option('mifeco_content_analysis_settings', array());
    }

    /**
     * Register content analysis settings
     *
     * @since    1.0.0
     */
    public function register_content_analysis_settings() {
        register_setting(
            'mifeco_content_analysis_settings_group',
            'mifeco_content_analysis_settings',
            array($this, 'sanitize_content_analysis_settings')
        );
        
        add_settings_section(
            'mifeco_content_analysis_general_section',
            __('Content Analysis Settings', 'mifeco-suite'),
            array($this, 'render_content_analysis_general_section'),
            'mifeco-content-analysis-settings'
        );
        
        add_settings_field(
            'enable_content_analysis',
            __('Enable Content Analysis', 'mifeco-suite'),
            array($this, 'render_enable_content_analysis_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'analysis_post_types',
            __('Apply Analysis To', 'mifeco-suite'),
            array($this, 'render_analysis_post_types_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'min_content_length',
            __('Minimum Content Length', 'mifeco-suite'),
            array($this, 'render_min_content_length_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'min_keyword_density',
            __('Minimum Keyword Density', 'mifeco-suite'),
            array($this, 'render_min_keyword_density_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'max_keyword_density',
            __('Maximum Keyword Density', 'mifeco-suite'),
            array($this, 'render_max_keyword_density_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'min_readability_score',
            __('Minimum Readability Score', 'mifeco-suite'),
            array($this, 'render_min_readability_score_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
        
        add_settings_field(
            'disable_analysis_features',
            __('Disable Analysis Features', 'mifeco-suite'),
            array($this, 'render_disable_analysis_features_field'),
            'mifeco-content-analysis-settings',
            'mifeco_content_analysis_general_section'
        );
    }

    /**
     * Sanitize content analysis settings
     *
     * @since    1.0.0
     * @param    array    $input    The input options.
     * @return   array              The sanitized options.
     */
    public function sanitize_content_analysis_settings($input) {
        $sanitized = array();
        
        $sanitized['enable_content_analysis'] = isset($input['enable_content_analysis']) ? (bool) $input['enable_content_analysis'] : true;
        
        $sanitized['analysis_post_types'] = isset($input['analysis_post_types']) && is_array($input['analysis_post_types']) ? $input['analysis_post_types'] : array('post', 'page');
        
        $sanitized['min_content_length'] = isset($input['min_content_length']) ? intval($input['min_content_length']) : 300;
        
        $sanitized['min_keyword_density'] = isset($input['min_keyword_density']) ? floatval($input['min_keyword_density']) : 1.0;
        
        $sanitized['max_keyword_density'] = isset($input['max_keyword_density']) ? floatval($input['max_keyword_density']) : 3.0;
        
        $sanitized['min_readability_score'] = isset($input['min_readability_score']) ? intval($input['min_readability_score']) : 60;
        
        $sanitized['disable_analysis_features'] = isset($input['disable_analysis_features']) && is_array($input['disable_analysis_features']) ? $input['disable_analysis_features'] : array();
        
        return $sanitized;
    }

    /**
     * Render content analysis general section
     *
     * @since    1.0.0
     */
    public function render_content_analysis_general_section() {
        echo '<p>' . __('Configure content analysis settings to help optimize your content for better search engine rankings.', 'mifeco-suite') . '</p>';
    }

    /**
     * Render enable content analysis field
     *
     * @since    1.0.0
     */
    public function render_enable_content_analysis_field() {
        $enable_content_analysis = isset($this->settings['enable_content_analysis']) ? $this->settings['enable_content_analysis'] : true;
        ?>
        <label>
            <input type="checkbox" name="mifeco_content_analysis_settings[enable_content_analysis]" value="1" <?php checked($enable_content_analysis, true); ?>>
            <?php _e('Enable content analysis features', 'mifeco-suite'); ?>
        </label>
        <p class="description"><?php _e('Provides analysis and recommendations to improve content for SEO.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render analysis post types field
     *
     * @since    1.0.0
     */
    public function render_analysis_post_types_field() {
        $analysis_post_types = isset($this->settings['analysis_post_types']) ? $this->settings['analysis_post_types'] : array('post', 'page');
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <fieldset>
            <legend class="screen-reader-text"><?php _e('Apply Analysis To', 'mifeco-suite'); ?></legend>
            <?php foreach ($post_types as $post_type) : ?>
                <label>
                    <input type="checkbox" name="mifeco_content_analysis_settings[analysis_post_types][]" value="<?php echo esc_attr($post_type->name); ?>" <?php checked(in_array($post_type->name, $analysis_post_types)); ?>>
                    <?php echo esc_html($post_type->labels->name); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <p class="description"><?php _e('Select which post types to apply content analysis to.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render minimum content length field
     *
     * @since    1.0.0
     */
    public function render_min_content_length_field() {
        $min_content_length = isset($this->settings['min_content_length']) ? $this->settings['min_content_length'] : 300;
        ?>
        <input type="number" name="mifeco_content_analysis_settings[min_content_length]" value="<?php echo esc_attr($min_content_length); ?>" class="small-text" min="100" max="3000" step="50">
        <p class="description"><?php _e('Minimum recommended number of words for content (recommended: 300+).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render minimum keyword density field
     *
     * @since    1.0.0
     */
    public function render_min_keyword_density_field() {
        $min_keyword_density = isset($this->settings['min_keyword_density']) ? $this->settings['min_keyword_density'] : 1.0;
        ?>
        <input type="number" name="mifeco_content_analysis_settings[min_keyword_density]" value="<?php echo esc_attr($min_keyword_density); ?>" class="small-text" min="0.1" max="5.0" step="0.1">
        <p class="description"><?php _e('Minimum recommended keyword density percentage.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render maximum keyword density field
     *
     * @since    1.0.0
     */
    public function render_max_keyword_density_field() {
        $max_keyword_density = isset($this->settings['max_keyword_density']) ? $this->settings['max_keyword_density'] : 3.0;
        ?>
        <input type="number" name="mifeco_content_analysis_settings[max_keyword_density]" value="<?php echo esc_attr($max_keyword_density); ?>" class="small-text" min="0.5" max="10.0" step="0.1">
        <p class="description"><?php _e('Maximum recommended keyword density percentage to avoid keyword stuffing.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render minimum readability score field
     *
     * @since    1.0.0
     */
    public function render_min_readability_score_field() {
        $min_readability_score = isset($this->settings['min_readability_score']) ? $this->settings['min_readability_score'] : 60;
        ?>
        <input type="number" name="mifeco_content_analysis_settings[min_readability_score]" value="<?php echo esc_attr($min_readability_score); ?>" class="small-text" min="0" max="100" step="5">
        <p class="description"><?php _e('Minimum recommended readability score (0-100).', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Render disable analysis features field
     *
     * @since    1.0.0
     */
    public function render_disable_analysis_features_field() {
        $disable_analysis_features = isset($this->settings['disable_analysis_features']) ? $this->settings['disable_analysis_features'] : array();
        
        $features = array(
            'keyword_analysis' => __('Keyword Analysis', 'mifeco-suite'),
            'readability_analysis' => __('Readability Analysis', 'mifeco-suite'),
            'content_structure' => __('Content Structure Analysis', 'mifeco-suite'),
            'link_analysis' => __('Link Analysis', 'mifeco-suite'),
            'image_analysis' => __('Image Analysis', 'mifeco-suite'),
        );
        ?>
        <fieldset>
            <legend class="screen-reader-text"><?php _e('Disable Analysis Features', 'mifeco-suite'); ?></legend>
            <?php foreach ($features as $feature => $label) : ?>
                <label>
                    <input type="checkbox" name="mifeco_content_analysis_settings[disable_analysis_features][]" value="<?php echo esc_attr($feature); ?>" <?php checked(in_array($feature, $disable_analysis_features)); ?>>
                    <?php echo esc_html($label); ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <p class="description"><?php _e('Select analysis features to disable.', 'mifeco-suite'); ?></p>
        <?php
    }

    /**
     * Analyze content via AJAX
     *
     * @since    1.0.0
     */
    public function ajax_analyze_content() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'mifeco_seo_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed.', 'mifeco-suite')));
        }
        
        // Check if content analysis is enabled
        $enable_content_analysis = isset($this->settings['enable_content_analysis']) ? $this->settings['enable_content_analysis'] : true;
        if (!$enable_content_analysis) {
            wp_send_json_error(array('message' => __('Content analysis is disabled in settings.', 'mifeco-suite')));
        }
        
        // Get post ID and keyword
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $focus_keyword = isset($_POST['focus_keyword']) ? sanitize_text_field($_POST['focus_keyword']) : '';
        
        if (empty($post_id)) {
            wp_send_json_error(array('message' => __('No post ID provided.', 'mifeco-suite')));
        }
        
        // Get post for analysis
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => __('Post not found.', 'mifeco-suite')));
        }
        
        // Analyze the content
        $analysis_results = $this->analyze_content($post, $focus_keyword);
        
        // Update post meta
        update_post_meta($post_id, '_mifeco_content_analysis', $analysis_results);
        update_post_meta($post_id, '_mifeco_focus_keyword', $focus_keyword);
        
        // Calculate overall score
        $score = $this->calculate_overall_score($analysis_results);
        $score_label = $this->get_score_label($score);
        $score_color = $this->get_score_color($score);
        
        // Send the results
        wp_send_json_success(array(
            'score' => $score,
            'score_label' => $score_label,
            'score_color' => $score_color,
            'results' => $analysis_results,
            'message' => __('Analysis completed successfully.', 'mifeco-suite'),
        ));
    }

    /**
     * Analyze content
     *
     * @since    1.0.0
     * @param    WP_Post    $post            The post object.
     * @param    string     $focus_keyword   The focus keyword.
     * @return   array                       Analysis results.
     */
    public function analyze_content($post, $focus_keyword = '') {
        // Check if any analysis features are disabled
        $disable_analysis_features = isset($this->settings['disable_analysis_features']) ? $this->settings['disable_analysis_features'] : array();
        
        $results = array();
        
        // Get post content
        $content = $post->post_content;
        $title = $post->post_title;
        $excerpt = has_excerpt($post->ID) ? get_the_excerpt($post->ID) : '';
        
        // Content analysis
        $results['content'] = $this->analyze_content_length($content);
        
        // Keyword analysis (if not disabled)
        if (!in_array('keyword_analysis', $disable_analysis_features) && !empty($focus_keyword)) {
            $results['keyword'] = $this->analyze_keyword_usage($content, $title, $excerpt, $focus_keyword);
        }
        
        // Readability analysis (if not disabled)
        if (!in_array('readability_analysis', $disable_analysis_features)) {
            $results['readability'] = $this->analyze_readability($content);
        }
        
        // Content structure analysis (if not disabled)
        if (!in_array('content_structure', $disable_analysis_features)) {
            $results['structure'] = $this->analyze_content_structure($content);
        }
        
        // Link analysis (if not disabled)
        if (!in_array('link_analysis', $disable_analysis_features)) {
            $results['links'] = $this->analyze_links($content);
        }
        
        // Image analysis (if not disabled)
        if (!in_array('image_analysis', $disable_analysis_features)) {
            $results['images'] = $this->analyze_images($content, $focus_keyword);
        }
        
        return $results;
    }

    /**
     * Analyze content length
     *
     * @since    1.0.0
     * @param    string    $content    The content to analyze.
     * @return   array                 Analysis results.
     */
    private function analyze_content_length($content) {
        // Get settings
        $min_content_length = isset($this->settings['min_content_length']) ? $this->settings['min_content_length'] : 300;
        
        // Strip shortcodes and HTML tags
        $clean_content = strip_shortcodes($content);
        $clean_content = wp_strip_all_tags($clean_content);
        
        // Count words
        $word_count = str_word_count($clean_content);
        
        // Determine status
        $status = 'ok';
        $message = sprintf(__('Your content contains %d words, which is sufficient.', 'mifeco-suite'), $word_count);
        
        if ($word_count < $min_content_length) {
            $status = 'warning';
            $message = sprintf(
                __('Your content contains %1$d words, which is below the recommended minimum of %2$d words. Consider adding more content.', 'mifeco-suite'),
                $word_count,
                $min_content_length
            );
        } elseif ($word_count >= $min_content_length && $word_count < $min_content_length * 1.5) {
            $status = 'good';
            $message = sprintf(
                __('Your content contains %1$d words, which is good. The recommended minimum is %2$d words.', 'mifeco-suite'),
                $word_count,
                $min_content_length
            );
        } elseif ($word_count >= $min_content_length * 1.5) {
            $status = 'good';
            $message = sprintf(
                __('Your content contains %1$d words, which is excellent. Long-form content tends to rank better in search results.', 'mifeco-suite'),
                $word_count
            );
        }
        
        return array(
            'word_count' => $word_count,
            'status' => $status,
            'message' => $message,
        );
    }

    /**
     * Analyze keyword usage
     *
     * @since    1.0.0
     * @param    string    $content         The content to analyze.
     * @param    string    $title           The post title.
     * @param    string    $excerpt         The post excerpt.
     * @param    string    $focus_keyword   The focus keyword.
     * @return   array                      Analysis results.
     */
    private function analyze_keyword_usage($content, $title, $excerpt, $focus_keyword) {
        // Get settings
        $min_keyword_density = isset($this->settings['min_keyword_density']) ? $this->settings['min_keyword_density'] : 1.0;
        $max_keyword_density = isset($this->settings['max_keyword_density']) ? $this->settings['max_keyword_density'] : 3.0;
        
        $results = array();
        
        // Clean content
        $clean_content = strip_shortcodes($content);
        $clean_content = wp_strip_all_tags($clean_content);
        
        // Count words
        $word_count = str_word_count($clean_content);
        
        // Check keyword in title
        $keyword_in_title = stripos($title, $focus_keyword) !== false;
        $results['title'] = array(
            'status' => $keyword_in_title ? 'good' : 'warning',
            'message' => $keyword_in_title
                ? __('Focus keyword appears in the title. Good job!', 'mifeco-suite')
                : __('Focus keyword does not appear in the title. Consider adding it.', 'mifeco-suite'),
        );
        
        // Check keyword in first paragraph
        $paragraphs = preg_split('/\r\n|\r|\n/', $clean_content);
        $first_paragraph = '';
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (!empty($paragraph)) {
                $first_paragraph = $paragraph;
                break;
            }
        }
        
        $keyword_in_first_paragraph = stripos($first_paragraph, $focus_keyword) !== false;
        $results['first_paragraph'] = array(
            'status' => $keyword_in_first_paragraph ? 'good' : 'warning',
            'message' => $keyword_in_first_paragraph
                ? __('Focus keyword appears in the first paragraph. Good job!', 'mifeco-suite')
                : __('Focus keyword does not appear in the first paragraph. Consider adding it.', 'mifeco-suite'),
        );
        
        // Check keyword in excerpt
        if (!empty($excerpt)) {
            $keyword_in_excerpt = stripos($excerpt, $focus_keyword) !== false;
            $results['excerpt'] = array(
                'status' => $keyword_in_excerpt ? 'good' : 'warning',
                'message' => $keyword_in_excerpt
                    ? __('Focus keyword appears in the excerpt. Good job!', 'mifeco-suite')
                    : __('Focus keyword does not appear in the excerpt. Consider adding it.', 'mifeco-suite'),
            );
        }
        
        // Check keyword in URL
        global $wp_rewrite;
        $post_name = get_post_field('post_name', get_the_ID());
        $keyword_in_url = stripos($post_name, str_replace(' ', '-', strtolower($focus_keyword))) !== false;
        $results['url'] = array(
            'status' => $keyword_in_url ? 'good' : 'warning',
            'message' => $keyword_in_url
                ? __('Focus keyword appears in the URL. Good job!', 'mifeco-suite')
                : __('Focus keyword does not appear in the URL. Consider updating the permalink.', 'mifeco-suite'),
        );
        
        // Check keyword density
        $keyword_count = substr_count(strtolower($clean_content), strtolower($focus_keyword));
        $keyword_density = ($word_count > 0) ? ($keyword_count * 100) / $word_count : 0;
        
        if ($keyword_density < $min_keyword_density) {
            $status = 'warning';
            $message = sprintf(
                __('Keyword density is %.2f%%, which is below the recommended minimum of %.1f%%. Consider using the focus keyword more often.', 'mifeco-suite'),
                $keyword_density,
                $min_keyword_density
            );
        } elseif ($keyword_density > $max_keyword_density) {
            $status = 'warning';
            $message = sprintf(
                __('Keyword density is %.2f%%, which is above the recommended maximum of %.1f%%. This may be considered keyword stuffing.', 'mifeco-suite'),
                $keyword_density,
                $max_keyword_density
            );
        } else {
            $status = 'good';
            $message = sprintf(
                __('Keyword density is %.2f%%, which is good. The recommended range is between %.1f%% and %.1f%%.', 'mifeco-suite'),
                $keyword_density,
                $min_keyword_density,
                $max_keyword_density
            );
        }
        
        $results['density'] = array(
            'density' => round($keyword_density, 2),
            'count' => $keyword_count,
            'status' => $status,
            'message' => $message,
        );
        
        // Check keyword in headings
        preg_match_all('/<h([1-6]).*?>(.*?)<\/h\1>/i', $content, $headings);
        
        $keyword_in_headings = false;
        $headings_with_keyword = 0;
        
        if (!empty($headings[2])) {
            foreach ($headings[2] as $heading) {
                if (stripos(wp_strip_all_tags($heading), $focus_keyword) !== false) {
                    $keyword_in_headings = true;
                    $headings_with_keyword++;
                }
            }
        }
        
        $results['headings'] = array(
            'status' => $keyword_in_headings ? 'good' : 'warning',
            'count' => $headings_with_keyword,
            'message' => $keyword_in_headings
                ? sprintf(
                    _n(
                        'Focus keyword appears in %d heading. Good job!',
                        'Focus keyword appears in %d headings. Good job!',
                        $headings_with_keyword,
                        'mifeco-suite'
                    ),
                    $headings_with_keyword
                )
                : __('Focus keyword does not appear in any headings. Consider adding it to at least one heading.', 'mifeco-suite'),
        );
        
        return $results;
    }

    /**
     * Analyze readability
     *
     * @since    1.0.0
     * @param    string    $content    The content to analyze.
     * @return   array                 Analysis results.
     */
    private function analyze_readability($content) {
        // Get settings
        $min_readability_score = isset($this->settings['min_readability_score']) ? $this->settings['min_readability_score'] : 60;
        
        // Strip shortcodes and HTML tags
        $clean_content = strip_shortcodes($content);
        $clean_content = wp_strip_all_tags($clean_content);
        
        // Split into paragraphs
        $paragraphs = preg_split('/\r\n|\r|\n/', $clean_content);
        $paragraphs = array_filter($paragraphs, function($paragraph) {
            return !empty(trim($paragraph));
        });
        
        // Count sentences
        $sentence_count = preg_match_all('/[.!?]+(?:\s|$)/', $clean_content, $matches);
        
        // Count words
        $word_count = str_word_count($clean_content);
        
        // Count syllables (rough estimate)
        $syllable_count = $this->count_syllables($clean_content);
        
        // Calculate average words per sentence
        $words_per_sentence = $sentence_count > 0 ? $word_count / $sentence_count : 0;
        
        // Calculate average syllables per word
        $syllables_per_word = $word_count > 0 ? $syllable_count / $word_count : 0;
        
        // Identify complex words (more than 3 syllables)
        $complex_words = 0;
        $words = explode(' ', $clean_content);
        foreach ($words as $word) {
            if ($this->count_syllables($word) > 3) {
                $complex_words++;
            }
        }
        
        // Calculate percentage of complex words
        $complex_percentage = $word_count > 0 ? ($complex_words * 100) / $word_count : 0;
        
        // Calculate Flesch Reading Ease score
        // FRE = 206.835 - (1.015 × ASL) - (84.6 × ASW)
        // ASL = Average Sentence Length (words)
        // ASW = Average Syllables per Word
        $flesch_score = 206.835 - (1.015 * $words_per_sentence) - (84.6 * $syllables_per_word);
        $flesch_score = max(0, min(100, $flesch_score)); // Clamp to 0-100
        
        // Calculate Flesch-Kincaid Grade Level
        // FKGL = (0.39 × ASL) + (11.8 × ASW) - 15.59
        $grade_level = (0.39 * $words_per_sentence) + (11.8 * $syllables_per_word) - 15.59;
        $grade_level = max(0, $grade_level);
        
        // Determine readability status
        $status = 'ok';
        $message = '';
        
        if ($flesch_score < $min_readability_score) {
            $status = 'warning';
            $message = sprintf(
                __('Readability score is %.1f (difficult to read). Consider simplifying your content, using shorter sentences, and simpler words.', 'mifeco-suite'),
                $flesch_score
            );
        } elseif ($flesch_score >= $min_readability_score && $flesch_score < 70) {
            $status = 'ok';
            $message = sprintf(
                __('Readability score is %.1f (moderately easy to read). Your content has a good balance of sentence length and word complexity.', 'mifeco-suite'),
                $flesch_score
            );
        } else {
            $status = 'good';
            $message = sprintf(
                __('Readability score is %.1f (easy to read). Your content is very accessible to a wide audience.', 'mifeco-suite'),
                $flesch_score
            );
        }
        
        // Check paragraph length
        $long_paragraphs = 0;
        foreach ($paragraphs as $paragraph) {
            $paragraph_word_count = str_word_count($paragraph);
            if ($paragraph_word_count > 150) {
                $long_paragraphs++;
            }
        }
        
        $paragraph_status = 'good';
        $paragraph_message = __('Your paragraphs have a good length.', 'mifeco-suite');
        
        if ($long_paragraphs > 0) {
            $paragraph_status = 'warning';
            $paragraph_message = sprintf(
                _n(
                    'You have %d paragraph that is too long (>150 words). Consider breaking it into smaller paragraphs.',
                    'You have %d paragraphs that are too long (>150 words). Consider breaking them into smaller paragraphs.',
                    $long_paragraphs,
                    'mifeco-suite'
                ),
                $long_paragraphs
            );
        }
        
        // Check sentence length
        $sentences = preg_split('/[.!?]+(?:\s|$)/', $clean_content, -1, PREG_SPLIT_NO_EMPTY);
        $long_sentences = 0;
        
        foreach ($sentences as $sentence) {
            $sentence_word_count = str_word_count(trim($sentence));
            if ($sentence_word_count > 25) {
                $long_sentences++;
            }
        }
        
        $sentence_status = 'good';
        $sentence_message = __('Your sentences have a good length.', 'mifeco-suite');
        
        if ($long_sentences > 0) {
            $sentence_status = 'warning';
            $sentence_message = sprintf(
                _n(
                    'You have %d sentence that is too long (>25 words). Consider breaking it into smaller sentences.',
                    'You have %d sentences that are too long (>25 words). Consider breaking them into smaller sentences.',
                    $long_sentences,
                    'mifeco-suite'
                ),
                $long_sentences
            );
        }
        
        return array(
            'flesch_score' => round($flesch_score, 1),
            'grade_level' => round($grade_level, 1),
            'words_per_sentence' => round($words_per_sentence, 1),
            'complex_words_percentage' => round($complex_percentage, 1),
            'status' => $status,
            'message' => $message,
            'paragraphs' => array(
                'count' => count($paragraphs),
                'long_count' => $long_paragraphs,
                'status' => $paragraph_status,
                'message' => $paragraph_message,
            ),
            'sentences' => array(
                'count' => $sentence_count,
                'long_count' => $long_sentences,
                'status' => $sentence_status,
                'message' => $sentence_message,
            ),
        );
    }

    /**
     * Count syllables in text
     *
     * @since    1.0.0
     * @param    string    $text    The text to count syllables in.
     * @return   int                Number of syllables.
     */
    private function count_syllables($text) {
        // This is a very simplified estimation
        $text = strtolower($text);
        $text = preg_replace('/[^a-z]/', ' ', $text);
        $words = explode(' ', $text);
        $words = array_filter($words);
        
        $syllable_count = 0;
        
        foreach ($words as $word) {
            // Count vowel groups
            $word_syllables = 0;
            $word = trim($word);
            
            if (empty($word)) {
                continue;
            }
            
            // Word with 3 or less chars has 1 syllable
            if (strlen($word) <= 3) {
                $syllable_count += 1;
                continue;
            }
            
            // Count vowel groups
            $word_syllables = preg_match_all('/[aeiouy]+/', $word, $matches);
            
            // Remove silent 'e'
            if (substr($word, -1) === 'e') {
                $word_syllables--;
            }
            
            // If word has 'le' ending, add 1 syllable
            if (substr($word, -2) === 'le' && !in_array(substr($word, -3, 1), array('a', 'e', 'i', 'o', 'u', 'y'))) {
                $word_syllables++;
            }
            
            // Handle special cases
            $special_combinations = array('io', 'ia', 'ea');
            foreach ($special_combinations as $combo) {
                $word_syllables -= substr_count($word, $combo);
            }
            
            // Ensure at least 1 syllable
            $syllable_count += max(1, $word_syllables);
        }
        
        return $syllable_count;
    }

    /**
     * Analyze content structure
     *
     * @since    1.0.0
     * @param    string    $content    The content to analyze.
     * @return   array                 Analysis results.
     */
    private function analyze_content_structure($content) {
        $results = array();
        
        // Check headings
        preg_match_all('/<h([1-6]).*?>(.*?)<\/h\1>/i', $content, $headings, PREG_SET_ORDER);
        
        $has_h1 = false;
        $has_h2 = false;
        $has_proper_hierarchy = true;
        $heading_count = count($headings);
        $previous_level = 0;
        
        foreach ($headings as $heading) {
            $level = intval($heading[1]);
            
            if ($level === 1) {
                $has_h1 = true;
            }
            
            if ($level === 2) {
                $has_h2 = true;
            }
            
            // Check heading hierarchy
            if ($previous_level > 0 && $level > $previous_level && $level - $previous_level > 1) {
                $has_proper_hierarchy = false;
            }
            
            $previous_level = $level;
        }
        
        // Headings analysis
        if ($heading_count === 0) {
            $results['headings'] = array(
                'status' => 'warning',
                'message' => __('No headings found in the content. Consider adding headings to structure your content.', 'mifeco-suite'),
            );
        } else {
            $status = 'good';
            $message = sprintf(__('Your content has %d headings. Good job!', 'mifeco-suite'), $heading_count);
            
            if ($has_h1) {
                $status = 'warning';
                $message .= ' ' . __('However, using H1 in the content is not recommended as it should be reserved for the post title.', 'mifeco-suite');
            }
            
            if (!$has_h2) {
                $status = 'warning';
                $message .= ' ' . __('Consider adding H2 headings to structure your main content sections.', 'mifeco-suite');
            }
            
            if (!$has_proper_hierarchy) {
                $status = 'warning';
                $message .= ' ' . __('Ensure proper heading hierarchy (do not skip heading levels).', 'mifeco-suite');
            }
            
            $results['headings'] = array(
                'status' => $status,
                'message' => $message,
                'count' => $heading_count,
                'has_h1' => $has_h1,
                'has_h2' => $has_h2,
                'has_proper_hierarchy' => $has_proper_hierarchy,
            );
        }
        
        // Check paragraphs
        preg_match_all('/<p>.*?<\/p>/is', $content, $paragraphs);
        $paragraph_count = count($paragraphs[0]);
        
        if ($paragraph_count < 3) {
            $results['paragraphs'] = array(
                'status' => 'warning',
                'message' => sprintf(__('Your content only has %d paragraphs. Consider adding more paragraphs for better structure.', 'mifeco-suite'), $paragraph_count),
                'count' => $paragraph_count,
            );
        } else {
            $results['paragraphs'] = array(
                'status' => 'good',
                'message' => sprintf(__('Your content has %d paragraphs. Good structure!', 'mifeco-suite'), $paragraph_count),
                'count' => $paragraph_count,
            );
        }
        
        // Check lists
        preg_match_all('/<(ul|ol)>.*?<\/\1>/is', $content, $lists);
        $list_count = count($lists[0]);
        
        $results['lists'] = array(
            'status' => $list_count > 0 ? 'good' : 'ok',
            'message' => $list_count > 0 
                ? sprintf(__('Your content includes %d list(s). Lists are great for readability and engagement.', 'mifeco-suite'), $list_count)
                : __('Consider adding lists to break up content and improve readability.', 'mifeco-suite'),
            'count' => $list_count,
        );
        
        return $results;
    }

    /**
     * Analyze links
     *
     * @since    1.0.0
     * @param    string    $content    The content to analyze.
     * @return   array                 Analysis results.
     */
    private function analyze_links($content) {
        $results = array();
        
        // Find all links
        preg_match_all('/<a\s[^>]*href=[\'"]([^\'"]+)[\'"][^>]*>(.*?)<\/a>/i', $content, $links, PREG_SET_ORDER);
        
        $internal_links = 0;
        $external_links = 0;
        $nofollow_links = 0;
        $empty_anchor_texts = 0;
        
        $site_url = get_site_url();
        $site_host = parse_url($site_url, PHP_URL_HOST);
        
        foreach ($links as $link) {
            $url = $link[1];
            $anchor_text = strip_tags($link[2]);
            
            // Check if link is internal or external
            $host = parse_url($url, PHP_URL_HOST);
            
            if (empty($host) || $host === $site_host) {
                $internal_links++;
            } else {
                $external_links++;
                
                // Check if external link has nofollow
                if (strpos($link[0], 'rel="nofollow"') === false && strpos($link[0], "rel='nofollow'") === false) {
                    $nofollow_links++;
                }
            }
            
            // Check for empty anchor text
            if (empty(trim($anchor_text))) {
                $empty_anchor_texts++;
            }
        }
        
        $total_links = count($links);
        
        // Links analysis
        if ($total_links === 0) {
            $results['count'] = array(
                'status' => 'warning',
                'message' => __('No links found in the content. Consider adding internal and external links to improve SEO.', 'mifeco-suite'),
                'total' => 0,
                'internal' => 0,
                'external' => 0,
            );
        } else {
            $status = 'good';
            $message = sprintf(
                __('Your content has %1$d links (%2$d internal, %3$d external). Good job!', 'mifeco-suite'),
                $total_links,
                $internal_links,
                $external_links
            );
            
            if ($internal_links === 0) {
                $status = 'warning';
                $message .= ' ' . __('Consider adding internal links to other content on your site.', 'mifeco-suite');
            }
            
            if ($external_links === 0) {
                $status = 'warning';
                $message .= ' ' . __('Consider adding external links to authoritative sources to improve credibility.', 'mifeco-suite');
            }
            
            $results['count'] = array(
                'status' => $status,
                'message' => $message,
                'total' => $total_links,
                'internal' => $internal_links,
                'external' => $external_links,
            );
        }
        
        // Nofollow analysis for external links
        if ($external_links > 0 && $nofollow_links > 0) {
            $results['nofollow'] = array(
                'status' => 'warning',
                'message' => sprintf(
                    __('%d out of %d external links do not have nofollow attribute. Consider adding rel="nofollow" to external links.', 'mifeco-suite'),
                    $nofollow_links,
                    $external_links
                ),
                'count' => $nofollow_links,
            );
        } else {
            $results['nofollow'] = array(
                'status' => 'good',
                'message' => __('All external links have proper nofollow attributes.', 'mifeco-suite'),
                'count' => 0,
            );
        }
        
        // Empty anchor text analysis
        if ($empty_anchor_texts > 0) {
            $results['anchor_text'] = array(
                'status' => 'warning',
                'message' => sprintf(
                    _n(
                        'You have %d link with empty anchor text. Use descriptive anchor text for better SEO and accessibility.',
                        'You have %d links with empty anchor text. Use descriptive anchor text for better SEO and accessibility.',
                        $empty_anchor_texts,
                        'mifeco-suite'
                    ),
                    $empty_anchor_texts
                ),
                'count' => $empty_anchor_texts,
            );
        } else {
            $results['anchor_text'] = array(
                'status' => 'good',
                'message' => __('All links have descriptive anchor text. Good job!', 'mifeco-suite'),
                'count' => 0,
            );
        }
        
        return $results;
    }

    /**
     * Analyze images
     *
     * @since    1.0.0
     * @param    string    $content         The content to analyze.
     * @param    string    $focus_keyword   The focus keyword.
     * @return   array                      Analysis results.
     */
    private function analyze_images($content, $focus_keyword = '') {
        $results = array();
        
        // Find all images
        preg_match_all('/<img[^>]+>/i', $content, $images);
        
        $total_images = count($images[0]);
        $missing_alt = 0;
        $keyword_in_alt = 0;
        $oversized_images = 0;
        
        foreach ($images[0] as $image) {
            // Check if image has alt attribute
            if (strpos($image, 'alt=') === false) {
                $missing_alt++;
            } elseif (!empty($focus_keyword)) {
                // Check if alt contains focus keyword
                preg_match('/alt=[\'"]([^\'"]*)[\'"]/', $image, $alt_match);
                $alt_text = !empty($alt_match[1]) ? $alt_match[1] : '';
                
                if (stripos($alt_text, $focus_keyword) !== false) {
                    $keyword_in_alt++;
                }
            }
            
            // Check image dimensions
            preg_match('/width=[\'"]([0-9]+)[\'"]/', $image, $width_match);
            preg_match('/height=[\'"]([0-9]+)[\'"]/', $image, $height_match);
            
            if (!empty($width_match[1]) && !empty($height_match[1])) {
                $width = intval($width_match[1]);
                $height = intval($height_match[1]);
                
                // Check if image is too large
                if ($width > 1200 || $height > 1200) {
                    $oversized_images++;
                }
            }
        }
        
        // Image count analysis
        if ($total_images === 0) {
            $results['count'] = array(
                'status' => 'warning',
                'message' => __('No images found in the content. Consider adding images to make your content more engaging.', 'mifeco-suite'),
                'total' => 0,
            );
        } else {
            $results['count'] = array(
                'status' => 'good',
                'message' => sprintf(
                    _n(
                        'Your content includes %d image. Good job!',
                        'Your content includes %d images. Good job!',
                        $total_images,
                        'mifeco-suite'
                    ),
                    $total_images
                ),
                'total' => $total_images,
            );
        }
        
        // Alt text analysis
        if ($total_images > 0) {
            if ($missing_alt > 0) {
                $results['alt'] = array(
                    'status' => 'warning',
                    'message' => sprintf(
                        _n(
                            '%d out of %d image is missing alt text. Add descriptive alt text for better SEO and accessibility.',
                            '%d out of %d images are missing alt text. Add descriptive alt text for better SEO and accessibility.',
                            $missing_alt,
                            'mifeco-suite'
                        ),
                        $missing_alt,
                        $total_images
                    ),
                    'missing' => $missing_alt,
                );
            } else {
                $results['alt'] = array(
                    'status' => 'good',
                    'message' => __('All images have alt text. Good job!', 'mifeco-suite'),
                    'missing' => 0,
                );
            }
            
            // Keyword in alt text analysis
            if (!empty($focus_keyword)) {
                if ($keyword_in_alt === 0) {
                    $results['keyword_alt'] = array(
                        'status' => 'warning',
                        'message' => __('None of your images have the focus keyword in their alt text. Consider adding the focus keyword to at least one image alt text.', 'mifeco-suite'),
                        'count' => 0,
                    );
                } else {
                    $results['keyword_alt'] = array(
                        'status' => 'good',
                        'message' => sprintf(
                            _n(
                                '%d image has the focus keyword in its alt text. Good job!',
                                '%d images have the focus keyword in their alt text. Good job!',
                                $keyword_in_alt,
                                'mifeco-suite'
                            ),
                            $keyword_in_alt
                        ),
                        'count' => $keyword_in_alt,
                    );
                }
            }
            
            // Oversized images analysis
            if ($oversized_images > 0) {
                $results['size'] = array(
                    'status' => 'warning',
                    'message' => sprintf(
                        _n(
                            '%d image is too large (>1200px). Resize and optimize your images for better performance.',
                            '%d images are too large (>1200px). Resize and optimize your images for better performance.',
                            $oversized_images,
                            'mifeco-suite'
                        ),
                        $oversized_images
                    ),
                    'count' => $oversized_images,
                );
            } else {
                $results['size'] = array(
                    'status' => 'good',
                    'message' => __('All images have appropriate dimensions.', 'mifeco-suite'),
                    'count' => 0,
                );
            }
        }
        
        return $results;
    }

    /**
     * Calculate overall score
     *
     * @since    1.0.0
     * @param    array     $results    Analysis results.
     * @return   int                   Overall score (0-100).
     */
    public function calculate_overall_score($results) {
        $total_points = 0;
        $max_points = 0;
        
        // Define point values for each status
        $status_points = array(
            'good' => 3,
            'ok' => 2,
            'warning' => 1,
        );
        
        // Calculate points for each section
        foreach ($results as $section => $section_results) {
            foreach ($section_results as $key => $item) {
                if (is_array($item) && isset($item['status'])) {
                    $status = $item['status'];
                    $points = isset($status_points[$status]) ? $status_points[$status] : 0;
                    $total_points += $points;
                    $max_points += 3;
                }
            }
        }
        
        // Calculate percentage score
        $score = $max_points > 0 ? ($total_points * 100) / $max_points : 0;
        
        return round($score);
    }

    /**
     * Get score label
     *
     * @since    1.0.0
     * @param    int        $score    Score (0-100).
     * @return   string               Score label.
     */
    public function get_score_label($score) {
        if ($score >= 80) {
            return __('Excellent', 'mifeco-suite');
        } elseif ($score >= 60) {
            return __('Good', 'mifeco-suite');
        } elseif ($score >= 40) {
            return __('Needs Improvement', 'mifeco-suite');
        } else {
            return __('Poor', 'mifeco-suite');
        }
    }

    /**
     * Get score color
     *
     * @since    1.0.0
     * @param    int        $score    Score (0-100).
     * @return   string               Score color.
     */
    public function get_score_color($score) {
        if ($score >= 80) {
            return '#27ae60'; // Green
        } elseif ($score >= 60) {
            return '#2ecc71'; // Light green
        } elseif ($score >= 40) {
            return '#f39c12'; // Orange
        } else {
            return '#e74c3c'; // Red
        }
    }
}