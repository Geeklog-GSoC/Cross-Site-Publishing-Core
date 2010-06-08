<?php
/**
 */

/**
 * A wrapper around the system library functions
 */
class HostInterface
{
    // Private Variables

    // Constructor



    // Public Variables

    /**
     * Searches for the $key variable as a GET parameter
     * @param String $key The key to search for
     * @param Type $return=NULL The return value to use
     * @return $return
     */
    public static function GET($key, $return=NULL)
    {
        return (isset($_GET[$key])) ?  $_GET[$key] : $return;
    }

    /**
     * Searches for the $key variable as a POST parameter
     * @param String $key The key to search for
     * @param Type $return=NULL The return value to use
     * @return $return
     */
    public static function POST($key, $return=NULL)
    {
        return (isset($_POST[$key])) ?  $_POST[$key] : $return;
    }


    /**
     * Returns the block code associated with the segment
     * @param String $segment       The segment to return
     *                              FOOTER  The footer
     *                              HEADER  The header
     *
     * @return  String              The code segment associated with that object or NULL
     */
    public static function getSegment($segment)
    {
        // What are we dealing with
        switch($segment)
        {
            case "HEADER":
                return COM_siteHeader('');
                break;
            case "FOOTER":
                return COM_siteFooter();
                break;
            default:
                return NULL;
                break;
        }
    }
}

/**
 * Allows management of formatted dates and times between systems that do not share like time stamps (PHP and the DB)
 */
class DateTimeConversions
{
    // Public static methods


    /**
     * Converts the incoming timestamp to the appropriate DATETIME string for safe database insertion
     * YYYY-MM-DD HH:MM:SS
     * @param   int     $timestamp          The timestamp to convert
     * @return  String                      The converted DATETIME string or FALSE on failure
     */
    public static function timestampToDATETIME($timestamp)
    {
        // Do conversion
        return date("Y-m-d H:i:s", $timestamp);
    }

    /**
     * Converts the DATETIME string to a unix timestamp
     *
     * @param   String  $datetime           The DATETIME string to convert
     * @return  int|bool                    The timestamp or FALSE on failure
     */
    public static function datetimeToTimestamp($datetime)
    {
        return strtotime($datetime);
    }
}

?>
