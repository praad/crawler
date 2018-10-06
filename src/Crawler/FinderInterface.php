<?php

namespace Crawler;

interface FinderInterface
{
    /**
     * Get back the given finder name.
     *
     * @param string $html
     *
     * @return string the name of the finder
     */
    public static function getFinderName();

    /**
     * Find in the given $html string.
     *
     * @param string $html
     *
     * @return array data found in the html
     */
    public static function find(string $html = '');

    /**
     * Setter function to store the baee url.
     *
     * @var string
     */
    public static function setBaseUrl(string $baseUrl);
}
