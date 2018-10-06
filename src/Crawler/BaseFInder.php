<?php

namespace Crawler;

abstract class BaseFinder
{
    /**
     * Base Url (starting_url).
     *
     * @var string
     *
     * @SuppressWarnings(PHPMD)
     */
    private static $baseUrl = '';

    /**
     * User agent for finder curl accesses.
     *
     * @var string
     */
    private static $userAgent = '';

    /**
     * Setter function to store the $baeeUrl.
     *
     * @var string
     */
    public static function setBaseUrl(string $baseUrl)
    {
        self::$baseUrl = $baseUrl;
    }

    /**
     * Getter function of $baeeUrl.
     *
     * @var string
     */
    public static function getBaseUrl()
    {
        return self::$baseUrl;
    }

    /**
     * Setter function to store the $userAgent.
     *
     * @var string
     */
    public static function setUserAgent(string $userAgent)
    {
        self::$userAgent = $userAgent;
    }

    /**
     * Getter function of $userAgent.
     *
     * @var string
     */
    public static function getUserAgent()
    {
        return self::$userAgent;
    }
}
