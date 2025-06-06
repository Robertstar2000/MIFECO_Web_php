<?php
/**
 * Meta Tags Generator
 *
 * This class handles the generation of meta tags for SEO and social sharing.
 * It expects pre-processed data to generate tags.
 */

// Namespace can be added if the project uses PSR-4 autoloading
// namespace App\Lib\Seo;

class MetaTagsGenerator {

    private $default_settings;

    /**
     * Constructor.
     *
     * @param array $default_settings Default settings for the site.
     *        Example: [
     *            'site_name' => 'My Awesome Site',
     *            'title_format' => '%title% - %site_name%',
     *            'title_separator' => '-',
     *            'default_description' => 'Default site description.',
     *            'default_keywords' => 'site, keywords',
     *            'default_social_image' => 'https://example.com/default-social.jpg',
     *            'twitter_site_username' => '@username', // Optional
     *            'facebook_app_id' => '1234567890' // Optional
     *        ]
     */
    public function __construct(array $default_settings = []) {
        $this->default_settings = array_merge([
            'site_name' => 'MIFECO',
            'title_format' => '%title% | %site_name%',
            'title_separator' => '|',
            'default_description' => '',
            'default_keywords' => '',
            'default_social_image' => '',
            'twitter_site_username' => '',
            'facebook_app_id' => ''
        ], $default_settings);
    }

    /**
     * Generate all relevant meta tags for a given page/content.
     *
     * @param array $page_data Data for the current page.
     *        Example: [
     *            'title' => 'Page Title',
     *            'description' => 'Page specific description.',
     *            'keywords' => 'page, specific, keywords', // Comma-separated string or array
     *            'canonical_url' => 'https://example.com/current-page',
     *            'image_url' => 'https://example.com/page-image.jpg', // For OG/Twitter cards
     *            'type' => 'article', // 'website', 'article', 'product' etc. for OG type
     *            'author' => 'John Doe', // For article type
     *            'published_time' => '2023-01-01T10:00:00+00:00', // For article type
     *            'modified_time' => '2023-01-02T12:00:00+00:00', // For article type
     *            'robots' => ['noindex', 'nofollow'] // Optional array of robots directives
     *        ]
     * @return array An array of HTML meta tag strings.
     */
    public function generateAllTags(array $page_data): array {
        $tags = [];

        // Basic Meta Tags
        $tags[] = '<meta charset="UTF-8">';
        $tags[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0">';

        $title = $this->generateTitle($page_data['title'] ?? '');
        $tags[] = '<title>' . htmlspecialchars($title) . '</title>';

        $description = $page_data['description'] ?? $this->default_settings['default_description'];
        if (!empty($description)) {
            $tags[] = '<meta name="description" content="' . htmlspecialchars(strip_tags($description)) . '">';
        }

        $keywords = $page_data['keywords'] ?? $this->default_settings['default_keywords'];
        if (!empty($keywords)) {
            $keywords_string = is_array($keywords) ? implode(', ', $keywords) : $keywords;
            $tags[] = '<meta name="keywords" content="' . htmlspecialchars(strip_tags($keywords_string)) . '">';
        }

        // Canonical URL
        if (!empty($page_data['canonical_url'])) {
            $tags[] = '<link rel="canonical" href="' . htmlspecialchars($page_data['canonical_url']) . '">';
        }

        // Robots meta
        if (!empty($page_data['robots']) && is_array($page_data['robots'])) {
            $tags[] = '<meta name="robots" content="' . htmlspecialchars(implode(', ', $page_data['robots'])) . '">';
        }

        // Open Graph Tags
        $og_tags = $this->generateOpenGraphTags($page_data, $title, $description);
        $tags = array_merge($tags, $og_tags);

        // Twitter Card Tags
        $twitter_tags = $this->generateTwitterCardTags($page_data, $title, $description);
        $tags = array_merge($tags, $twitter_tags);

        // Facebook App ID
        if (!empty($this->default_settings['facebook_app_id'])) {
            $tags[] = '<meta property="fb:app_id" content="' . htmlspecialchars($this->default_settings['facebook_app_id']) . '">';
        }

        return $tags;
    }

    /**
     * Generates the title string based on format.
     */
    public function generateTitle(string $page_title): string {
        $format = $this->default_settings['title_format'];
        $separator = $this->default_settings['title_separator'];
        $site_name = $this->default_settings['site_name'];

        $title = str_replace(
            ['%title%', '%separator%', '%sitename%'],
            [$page_title, $separator, $site_name],
            $format
        );
        // Ensure title isn't just the separator if page_title is empty
        if (trim(str_replace($site_name, '', $title)) === $separator || trim($title) === $separator) {
            $title = $page_title ? $page_title . " $separator " . $site_name : $site_name;
        }
        return trim($title);
    }

    /**
     * Generates Open Graph (OG) meta tags.
     */
    public function generateOpenGraphTags(array $page_data, string $processed_title, string $processed_description): array {
        $tags = [];
        $site_name = $this->default_settings['site_name'];
        $og_type = $page_data['type'] ?? 'website';
        $og_image = $page_data['image_url'] ?? $this->default_settings['default_social_image'];

        $tags[] = '<meta property="og:title" content="' . htmlspecialchars($processed_title) . '">';
        $tags[] = '<meta property="og:site_name" content="' . htmlspecialchars($site_name) . '">';
        $tags[] = '<meta property="og:type" content="' . htmlspecialchars($og_type) . '">';

        if (!empty($page_data['canonical_url'])) {
            $tags[] = '<meta property="og:url" content="' . htmlspecialchars($page_data['canonical_url']) . '">';
        }
        if (!empty($processed_description)) {
            $tags[] = '<meta property="og:description" content="' . htmlspecialchars(strip_tags($processed_description)) . '">';
        }
        if (!empty($og_image)) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($og_image) . '">';
            // You might add og:image:width and og:image:height if you have that info
        }

        if ($og_type === 'article') {
            if (!empty($page_data['author'])) {
                $tags[] = '<meta property="article:author" content="' . htmlspecialchars($page_data['author']) . '">';
            }
            if (!empty($page_data['published_time'])) {
                $tags[] = '<meta property="article:published_time" content="' . htmlspecialchars($page_data['published_time']) . '">';
            }
            if (!empty($page_data['modified_time'])) {
                $tags[] = '<meta property="article:modified_time" content="' . htmlspecialchars($page_data['modified_time']) . '">';
            }
            // article:section and article:tag could be added if relevant data is passed
        }

        return $tags;
    }

    /**
     * Generates Twitter Card meta tags.
     */
    public function generateTwitterCardTags(array $page_data, string $processed_title, string $processed_description): array {
        $tags = [];
        $twitter_site = $this->default_settings['twitter_site_username'] ?? '';
        $twitter_image = $page_data['image_url'] ?? $this->default_settings['default_social_image'];
        $card_type = !empty($twitter_image) ? 'summary_large_image' : 'summary';

        $tags[] = '<meta name="twitter:card" content="' . htmlspecialchars($card_type) . '">';
        if (!empty($twitter_site)) {
            $tags[] = '<meta name="twitter:site" content="' . htmlspecialchars($twitter_site) . '">';
        }
        $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($processed_title) . '">';

        if (!empty($processed_description)) {
            $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars(strip_tags($processed_description)) . '">';
        }
        if (!empty($twitter_image)) {
            $tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($twitter_image) . '">';
            // twitter:image:alt could be added if alt text is available in $page_data
        }

        return $tags;
    }
}
