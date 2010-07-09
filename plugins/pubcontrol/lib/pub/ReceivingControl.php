<?php
/**
 * @package ReceivingControl
 * @author Tim Patrick
 * @license GPL V3
 * @copyright 2010 Tim Patrick
 * @version 0.1
 *
 */


/**
 * Controls the access of data associated with topics, feeds, or access codes, as well as manipulating subscriptions
 */
class ReceivingControlManagement
{

    // Private Methods
    private $_Conn = NULL;
    private $_DBNAME = NULL;
    
    public function __construct()
    {
        $this->_Conn = new DAL();
        $this->_DBNAME = DAL::getFormalTableName("recvcontrol_Data");
    }

    public function __destruct()
    {
        $this->_Conn->cleanup();
    }

    /**
     * Finishes a valid private subscription
     *
     * @param   String      $ssid       The security identifier that has been issued
     * @param   String      $url        The URL that the security identifier is used for
     * @return  Boolean                 On success TRUE, false means that it already exists in the DB
     */
    public function finishPrivateSubscription($ssid, $url)
    {
        // Declare Variables
        $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $ssid = DAL::applyFilter($ssid);
        $url = DAL::applyFilter($url);
        $conn = $this->_Conn;

        // Check if already exists
        $sql = "SELECT type FROM {$table} WHERE ssid = '{$ssid}' AND url = '{$url}';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();
        if($row !== NULL) {
            return FALSE;
        }

        // Create query
        $sql = "INSERT INTO {$table} (ssid, identifier, url, type) VALUES('{$ssid}','','{$url}','1');";
        $conn->executeNonQuery($sql);

        return TRUE;
    }

    /**
     * Unsubscribes a site from the application
     *
     * @param   int     $id     The id of the subscription
     */
    public function unSubscribe($id)
    {
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $id = DAL::applyFilter($id);
        $table2 = DAL::getFormalTableName("recvcontrol_Feeds");
        
        // Delete Feeds
        $sql = "DELETE FROM {$table2} WHERE subscription_id = '{$id}';";
        $conn->executeNonQuery($sql);
        
        // Delete Subscriber
        $sql = "DELETE FROM {$table} WHERE id = '{$id}';";
        $conn->executeNonQuery($sql);
    }


    /**
     * Attempts to subscribe a URL to a specific site and load the feed information into the list
     * 
     * @param
     */
    public static function subscribeURL($url, $identifier)
    {
        echo "ReceivingControll::subscribeURL($url, $identifer) is NOT created yet - please create";
    }

    /**
     * Lists all groups that are available for subscription at the specified URL
     *
     * @param   String  $url        The URL to load for
     * @return  GroupObject[]       An array of groups objects
     */
    public static function listGroups($url)
    {
        echo "ReceivingControll::listGroups($url) is NOT created yet - please create";
    }

    /**
     * Lists all feeds that are available for a specific group and URL
     *
     * @param   String      $url            The url to load for
     * @param   int         $groupid        The group id to load feeds for
     * @return  FeedObject[]                An array of feed objects
     */
    public static function listFeeds($url, $groupid)
    {
        echo "ReceivingControll::listFeeds($url) is NOT created yet - please create";
    }

    

    /**
     * Returns all data in the database that matches a certain access key
     * @param   String      $accesskey          The access key to load data for
     * @param   Int         $lastmod            (Optional, =0)The last id for the last data loaded ( a way to limit the amount of entries to the newest possible ones)
     * @return  DataObject[]                    An array of data objects that match the criteria
     * @throws                                  ReceivingException on error
     */
    public function getDataForAccessKey($accesskey, $lastmod=0)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $dataobjects = array();
        $dsg = NULL;
        $accesskey = DAL::applyFilter($accesskey);
        $lastmod = DAL::applyFilter($lastmod, TRUE);

        // Get a list of all content
        $result = $conn->executeNonQuery("FAIL();");

        // Loop over the result and return the data
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            if((int)$row["id"] < $lastmod) {
                continue;
            }
            
            $dsg = new DataObject();
            $dsg->_Authors = explode(':', $row['authors']);
            $dsg->_Contributors = explode(':', $row['contributors']);
            $dsg->_Content = $row['content'];
            $dsg->_DateCreated = $row['date_created'];
            $dsg->_DateLastUpdated = $row['date_lastupdated'];
            $dsg->_FeedId = $row['feed_id'];
            $dsg->_Id = $row['id'];
            $dsg->_Title = $row['title'];
            $dataobjects[] = $dsg;
        }

        return $dataobjects;
    }

    /**
     * Gets all data associated with a specific feed
     * @param   int    $feedid      The id of the feed to search for
     * @param   Int         $lastmod            (Optional, =0)The last id for the last data loaded ( a way to limit the amount of entries to the newest possible ones)
     * @return  DataObject[]                    An array of data objects that match the criteria
     */
    public function getDataForFeed($feedid, $lastmod=0)
    {
        // Declare variables
        $conn = $this->_Conn;
        $feedid = DAL::applyFilter($feedid, TRUE);
        $lastmod = DAL::applyFilter($lastmod, TRUE);
        
        // Attempt query
        $table = DAL::getFormalTableName("recvcontrol_Data");
        $conn->executeNonQuery("SELECT * FROM {$table} WHERE feed_id = {$feedid};");

        // Loop over the result and return the data
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            if((int)$row["id"] < $lastmod) {
                continue;
            }
            
            $dsg = new DataObject();
            $dsg->_Authors = explode(':', $row['authors']);
            $dsg->_Contributors = explode(':', $row['contributors']);
            $dsg->_Content = $row['content'];
            $dsg->_DateCreated = $row['date_created'];
            $dsg->_DateLastUpdated = $row['date_lastupdated'];
            $dsg->_FeedId = $row['feed_id'];
            $dsg->_Id = $row['id'];
            $dsg->_Title = $row['title'];
            $dataobjects[] = $dsg;
        }

        return $dataobjects;
    }

}



?>