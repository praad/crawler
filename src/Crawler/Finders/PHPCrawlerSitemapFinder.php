<?php

namespace Crawler\Finders;

use Alc\SitemapCrawler;
use Crawler\BaseFinder;
use Crawler\FinderInterface;

/**
 * Class for finding sitemap links in HTML-documents.
 *
 * @internal
 */
class PHPCrawlerSitemapFinder extends BaseFinder implements FinderInterface
{
    public static $sitemapPattern = '/^sitemap: (.*)$/mi';
    public static $sitemapLinkPattern = '@<loc>(.+?)<\/loc>@';

    /**
     * Implementation of the finder function.
     *
     * @param string $html
     *
     * @return array $data data found on the page
     *
     * @SuppressWarnings(PHPMD)
     */
    public static function find(string $html = '')
    {
        // Avoid to test the sitemap multiple times
        if (!self::$tested) {
            self::$tested = true;

            return self::findSitemap();
        }

        return false;
    }

    /**
     * Implementation of the getFinderName function.
     *
     * @return string the namme of the finder
     */
    public static function getFinderName()
    {
        return 'Sitemap finder';
    }

    public static $tested = false;

    /**
     * Find sitemap and get all links from that.
     *
     * @return array with the links found
     */
    public static function findSitemap()
    {
        $sitemapUrl = self::getBaseUrl().'/sitemap.xml';

        // Test simple sitemap:
        $links = self::getLinksFromSitemap($sitemapUrl);

        // Find sitemap based on robots.txt
        if (empty($links)) {
            $sitemapUrl = self::parseRobotsTxt(self::getBaseUrl().'/robots.txt');
            $links = self::getLinksFromSitemap($sitemapUrl);
        }

        // If the sitemap is more complex run a crawler on it:
        /* TODO: what happened when the second link is an another sitemap file.*/
        if (isset($links[0])) {
            if (count(self::getLinksFromSitemap($links[0])) > 0) {
                // Crawling the sitemap:
                $links = self::crawlingLinksFromSitemap($sitemapUrl);
            }
        }

        return $links;
    }

    /**
     * Get all links from a sitemap url working only with simple sitemap.xml.
     *
     * @param mixed $url
     *
     * @return array with the links found
     */
    public static function getLinksFromSitemap($url)
    {
        $links = [];
        $data = self::getUrl($url);
        $count = preg_match_all(self::$sitemapLinkPattern, $data, $matches);

        for ($i = 0; $i < $count; ++$i) {
            $links[] = $matches[1][$i];
        }

        return $links;
    }

    /**
     * Crawling the complex sitemap with SitemapCrawler.
     *
     * @param mixed $url
     *
     * @return array with the links found
     */
    public static function crawlingLinksFromSitemap($url)
    {
        $crawler = new SitemapCrawler();

        $links = $crawler->crawl($url);

        return $links;
    }

    /**
     * Open the url and get data.
     *
     * @param mixed $url
     *
     * @return mixed the data found on url or error message
     */
    public static function getUrl($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, self::getUserAgent());
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return $error;
        }

        return $data;
    }

    /**
     * Get a sitemap link from robots.txt.
     *
     * @param mixed $url
     *
     * @return string link to sitemap or empty string
     */
    private static function parseRobotsTxt($url)
    {
        $data = self::getUrl($url);
        $pattern = self::$sitemapPattern;
        $pattern = '(Sitemap: |sitemap: )(.*)$';

        preg_match_all(self::$sitemapPattern, $data, $result);

        if (isset($result[1][0])) {
            return $result[1][0];
        }

        return '';
    }
}
