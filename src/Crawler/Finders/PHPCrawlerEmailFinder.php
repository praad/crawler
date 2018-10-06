<?php

namespace Crawler\Finders;

use Crawler\BaseFinder;
use Crawler\FinderInterface;

/**
 * Class for finding email ids in HTML-documents.
 *
 * @internal
 */
class PHPCrawlerEmailFinder extends BaseFinder implements FinderInterface
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
        return self::findEmails($html);
    }

    /**
     * Implementation of the getFinderName function.
     *
     * @return string the namme of the finder
     */
    public static function getFinderName()
    {
        return 'Emails finder';
    }

    /**
     * Find all email ids in a given $data source v1.0.
     *
     * @param string $data
     */
    public static function findEmails(string $data)
    {
        // don't need to preassign $matches, it's created dynamically

        // this regex handles more email address formats like a+b@google.com.sg, and the i makes it case insensitive
        $pattern = '/[a-z0-9_\-\+]+@[a-z0-9\-]+\.([a-z]{2,3})(?:\.[a-z]{2})?/i';

        // preg_match_all returns an associative array
        preg_match_all($pattern, $data, $matches);

        // delete duplicate email ids:
        $result[] = array_unique($matches[0]);

        // the data you want is in $matches[0], dump it with var_export() to see it
        //var_export($result[0]);

        return $result[0];
    }
}
