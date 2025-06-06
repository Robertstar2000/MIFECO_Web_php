<?php
/**
 * XML Sitemap Generator
 *
 * This class handles the generation of XML sitemaps.
 * It expects pre-fetched URL data.
 */

// Namespace can be added if the project uses PSR-4 autoloading
// namespace App\Lib\Seo;

class SitemapGenerator {

    private $xsl_stylesheet_url;

    /**
     * Constructor.
     * @param string|null $xsl_stylesheet_url Optional URL to an XSL stylesheet for the sitemap.
     */
    public function __construct(string $xsl_stylesheet_url = null) {
        $this->xsl_stylesheet_url = $xsl_stylesheet_url;
    }

    /**
     * Generates an XML sitemap index string.
     *
     * @param array $sitemaps Array of sitemap data.
     *        Example: [
     *            ['loc' => 'https://example.com/sitemap-posts.xml', 'lastmod' => '2023-01-01T10:00:00+00:00'],
     *            ['loc' => 'https://example.com/sitemap-pages.xml']
     *        ]
     * @return string The XML sitemap index.
     */
    public function generateSitemapIndex(array $sitemaps): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        if ($this->xsl_stylesheet_url) {
            $xml .= '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($this->xsl_stylesheet_url) . '"?>';
        }
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($sitemaps as $sitemap) {
            if (empty($sitemap['loc'])) continue;

            $xml .= '<sitemap>';
            $xml .= '<loc>' . htmlspecialchars($sitemap['loc']) . '</loc>';
            if (!empty($sitemap['lastmod'])) {
                $xml .= '<lastmod>' . htmlspecialchars($this->formatDate($sitemap['lastmod'])) . '</lastmod>';
            }
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';
        return $xml;
    }

    /**
     * Generates an XML URL set sitemap string.
     *
     * @param array $urls Array of URL data.
     *        Example: [
     *            ['loc' => 'https://example.com/page1', 'lastmod' => '2023-01-01', 'changefreq' => 'weekly', 'priority' => '0.8'],
     *            ['loc' => 'https://example.com/page2', 'images' => [['loc' => 'image.jpg', 'title' => 't']]]
     *        ]
     * @param array $namespaces Optional array of additional namespaces like 'image' or 'news'. E.g. ['image' => 'http://www.google.com/schemas/sitemap-image/1.1']
     * @return string The XML sitemap.
     */
    public function generateUrlSet(array $urls, array $namespaces = []): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        if ($this->xsl_stylesheet_url) {
            $xml .= '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($this->xsl_stylesheet_url) . '"?>';
        }
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        foreach($namespaces as $prefix => $uri) {
            $xml .= ' xmlns:' . $prefix . '="' . htmlspecialchars($uri) . '"';
        }
        $xml .= '>';

        foreach ($urls as $url_data) {
            if (empty($url_data['loc'])) continue;

            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($url_data['loc']) . '</loc>';
            if (!empty($url_data['lastmod'])) {
                $xml .= '<lastmod>' . htmlspecialchars($this->formatDate($url_data['lastmod'])) . '</lastmod>';
            }
            if (!empty($url_data['changefreq'])) {
                $xml .= '<changefreq>' . htmlspecialchars($url_data['changefreq']) . '</changefreq>';
            }
            if (!empty($url_data['priority'])) {
                $xml .= '<priority>' . htmlspecialchars(number_format($url_data['priority'], 1)) . '</priority>';
            }

            // Image sitemap extensions
            if (isset($namespaces['image']) && !empty($url_data['images']) && is_array($url_data['images'])) {
                foreach ($url_data['images'] as $image_data) {
                    if (empty($image_data['loc'])) continue;
                    $xml .= '<image:image>';
                    $xml .= '<image:loc>' . htmlspecialchars($image_data['loc']) . '</image:loc>';
                    if (!empty($image_data['title'])) {
                        $xml .= '<image:title>' . htmlspecialchars($image_data['title']) . '</image:title>';
                    }
                    if (!empty($image_data['caption'])) {
                        $xml .= '<image:caption>' . htmlspecialchars($image_data['caption']) . '</image:caption>';
                    }
                    // geo_location, license can be added if needed
                    $xml .= '</image:image>';
                }
            }

            // News sitemap extensions
            if (isset($namespaces['news']) && !empty($url_data['news']) && is_array($url_data['news'])) {
                $news_data = $url_data['news'];
                $xml .= '<news:news>';
                $xml .= '<news:publication>';
                $xml .= '<news:name>' . htmlspecialchars($news_data['publication']['name']) . '</news:name>';
                $xml .= '<news:language>' . htmlspecialchars($news_data['publication']['language']) . '</news:language>';
                $xml .= '</news:publication>';
                $xml .= '<news:publication_date>' . htmlspecialchars($this->formatDate($news_data['publication_date'])) . '</news:publication_date>';
                $xml .= '<news:title>' . htmlspecialchars($news_data['title']) . '</news:title>';
                // genres, keywords etc. can be added
                $xml .= '</news:news>';
            }

            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $xml;
    }

    /**
     * Formats a date string/timestamp into W3C Datetime format.
     */
    private function formatDate($date_input): string {
        if (is_numeric($date_input)) { // Assume timestamp
            return date('c', $date_input);
        }
        // Attempt to parse string dates
        $timestamp = strtotime($date_input);
        if ($timestamp === false) {
            return date('c'); // Fallback to now if parse fails
        }
        return date('c', $timestamp);
    }

    /**
     * Generates an empty sitemap (e.g., if a requested sitemap type has no data).
     */
    public function generateEmptySitemap(): string {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        if ($this->xsl_stylesheet_url) {
            $xml .= '<?xml-stylesheet type="text/xsl" href="' . htmlspecialchars($this->xsl_stylesheet_url) . '"?>';
        }
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>';
        return $xml;
    }
}
