<?php

namespace Crawler\Finders;

use Crawler\BaseFinder;
use Crawler\FinderInterface;

/**
 * Class for finding hungarian phone numbers in HTML-documents.
 *
 * @internal
 */
class PHPCrawlerPhoneNumberFinder extends BaseFinder implements FinderInterface
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
        return self::findHuPhoneNumbers($html);
    }

    /**
     * Implementation of the getFinderName function.
     *
     * @return string the namme of the finder
     */
    public static function getFinderName()
    {
        return 'Phone numbers finder';
    }

    /**
     * Hungarian phone number converter v1.1
     * Convert a valid Hungarian phone number into standard (+36) ..... format.
     *
     * @param $phonenumber must be a valid nine-digit number (with optional international prefix)
     *
     * @return string the formatted phone number
     */
    public static function formatHuPhoneNumber(string $phonenumber)
    {
        $phonenumber = trim($phonenumber);

        // Define regular expression
        $regex = "/(\+36|06|0036|36)\D*(\d{2})\D*(\d{3,4})\D*(\d{3,4})/";

        // Get digits of phone number
        preg_match($regex, $phonenumber, $matches);

        // Construct ten-digit phone number
        //var_export($matches); // Debug.

        if (isset($matches[2]) && isset($matches[3]) && isset($matches[4])) {
            // Sometimes the phone number can be too long or too short remove them if the length is not 12
            $phonenumber = '+36'.$matches[2].$matches[3].$matches[4];

            if (strlen($phonenumber) != 12) {
                $phonenumber = '';
            }
        }

        return $phonenumber;
    }

    /**
     * Find all Hungarian phone numbers in a given $data source v2.2.
     *
     * @param $data an array wich contains the phone numbers can be in different formats
     *
     * @return array of founded phone numbers
     */
    public static function findHuPhoneNumbers(string $data)
    {
        // don't need to preassign $matches, it's created dynamically

        /* Regex patterns:
            +36204665495
            06204665495
            +36 (20) 466-5496
            +36(20)466-5497
            +36-20-466-5498
            +36 20 466-5499

            +3620466549
            +36 (20) 466-549
            +36(20)466-549
            +36-20-466-548
            +36 20 466-549

            06204665495
            06 (20) 466-5496
            06(20)466-5497
            06-20-466-5498
            06 20 466-5499

            0620466549
            06 (20) 466-549
            06(20)466-549
            06-20-466-549
            06 20 466-549

            (+36) - 70 / 2705221
        */
        //$regex = "(((\+36|06)).?\(?[0-9]{2}\)?.?[0-9]{3}.?-?.?[0-9]{3,4})";// MATCH ALL ABOVE More complex sanitize  needed! v3.0

        // MATCH ALL ABOVE More complex sanitize  needed! v3.1
        $regex = "(((\(?\+36\)?|06)).?.?.?\(?[0-9]{2}\)?.?.?.?[0-9]{3}.?-?.?[0-9]{3,4})";

        // preg_match_all returns an associative array
        preg_match_all($regex, $data, $matches);
        $i = 0;
        $return = [];

        if (is_array($matches[0])) {
            foreach ($matches[0] as $phone) {
                $phone = self::formatHuPhoneNumber($phone);

                // Remove empty lines:
                if ($phone != '') {
                    $return[$i] = $phone;
                    ++$i;
                }
            }

            // Delete duplicate phone numbers:
            $return = array_unique($return);
        }

        return $return;
    }
}
