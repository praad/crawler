<?php

namespace Crawler\Finders;

use Crawler\BaseFinder;
use Crawler\FinderInterface;

/**
 * Class for finding external links in HTML-documents.
 *
 * @internal
 */
class PHPCrawlerExternalLinkFinder extends BaseFinder implements FinderInterface
{
    /**
     * Implementation of the finder function.
     *
     * @param string $html
     *
     * @return array $data data found on the page
     */
    public static function find(string $html = '')
    {
        return self::findExternalLinks($html);
    }

    /**
     * Implementation of the getFinderName function.
     *
     * @return string the namme of the finder
     */
    public static function getFinderName()
    {
        return 'External links finder';
    }

    /**
     * Find all href attributes in a given $html.
     *
     * @param string $html
     */
    public static function findExternalLinks(string $html)
    {
        $urls = [];

        // Load filter array:
        $filterArray = include 'PHPCrawlerExternalLinkFinderFilter.php';

        // Add baseUrl to filter array:
        $filterArray[] = self::getDomain(self::getBaseUrl());

        $regexp = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';

        if (preg_match_all($regexp, $html, $matches)) {
            foreach ($matches[0] as $url) {
                if ($url = self::getDomain($url)) {
                    // Filtering domains by filter array located in: PHPCrawlerExternalLinkFinderFilter.php
                    if (!in_array($url, $filterArray)) {
                        $urls[] = 'http://'.$url;
                    }
                }
            }
        }

        // Remove duplicate links:
        $urls = array_unique($urls);

        return $urls;
    }

    /**
     * Get the domain name from the url.
     *
     * @param mixed $url
     */
    public static function getDomain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return false;
    }
}
