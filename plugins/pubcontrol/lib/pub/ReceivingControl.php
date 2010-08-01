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
        $sql = "SELECT type FROM {$table} WHERE ssid = '{$ssid}' AND url = '{$url}' AND type = '1';";
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
     * Finishes a valid public subscription
     *
     * @param   String      $url        The URL that the public repository is listed at
     * @return  Boolean                 On success TRUE, false means that it already exists in the DB
     */
    public function finishPublicSubscription($url)
    {        
        // Declare Variables
        $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $url = DAL::applyFilter($url);
        $conn = $this->_Conn;

        // Check if already exists
        $sql = "SELECT type FROM {$table} WHERE url = '{$url}' AND type = '2';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();
        if($row !== NULL) {
            return FALSE;
        }

        // Create query
        $sql = "INSERT INTO {$table} (ssid, identifier, url, type) VALUES('','','{$url}','2');";
        $conn->executeNonQuery($sql);

        return TRUE;
    }

    /**
     * Removes the feed from the database
     *
     * @param int   $id     The feed id to remove
     */
    public function deleteFeed($id)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("recvcontrol_Feeds");
        $id = DAL::applyFilter($id, TRUE);

        // Delete feed
        $sql = "DELETE FROM {$table} WHERE id = '{$id}';";
        $conn->executeNonQuery($sql);
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
        echo "ReceivingControll::subscribeURL($url, $identifer) is NOT created yet - please create @@DEPRECATED->DO NOT USE THIS METHOD";
    }

    /**
     * Lists all groups that are available for subscription at the specified URL
     *
     * @param   String  $url        The URL to load for
     * @return  GroupObject[]       An array of groups objects
     */
    public static function listGroups($url)
    {
        // Read in the GROUP data from the URL
        // Eg. Read the atom object
        $atom = new AtomReader();
        try {
            $atom->loadFile($url . "/pubcontrol/index.php?cmd=111");
        }
        catch (Exception $d) {
            // Error
            return NULL;
        }

        // Discard top data we do not want it
        $atom->getFeedTopData();

        // Get entries'
        $groupObjectArray = array();
        $atomentry = $atom->getNextEntry();

        while($atomentry !== NULL) {

            $group = new GroupObject();
            $group->_Title = $atomentry->_Title;
            $p = explode(".", $atomentry->_Id);
            $group->_Id = $p[1];
            $group->_Type = TypeObject::makeTypeFromInteger((int)$p[2]);
            $group->_Summary = $atomentry->_Summary;
            $groupObjectArray[] = $group;
            $atomentry = $atom->getNextEntry();
        }

        return $groupObjectArray;
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
        // Read in the GROUP data from the URL
        // Eg. Read the atom object
        $atom = new AtomReader();
        try {
            $atom->loadFile($url . "/pubcontrol/index.php?cmd=110&gid={$groupid}");
        }
        catch (Exception $d) {
            // Error
            return NULL;
        }
        
        // Discard top data we do not want it
        $atom->getFeedTopData();

        // Get entries'
        $feedObjectArray = array();
        $atomentry = $atom->getNextEntry();

        while($atomentry !== NULL) {

            $feed = new FeedObject();
            $feed->_Title = $atomentry->_Title;
            $p = explode(".", $atomentry->_Id);
            $feed->_Id = $p[1];
            $feed->_Type = TypeObject::makeTypeFromInteger((int)$p[2]);
            $feed->_AccessCode = $p[3];
            $feed->_Summary = $atomentry->_Summary;
            $feed->_GroupId = $groupid;
            $feedObjectArray[] = $feed;
            $atomentry = $atom->getNextEntry();
        }

        return $feedObjectArray;
        
    }

    /**
     * Retreives the data for a given feed and extracts it into data objects
     *
     * @param   String       $url       The url to extract from
     * @param   Integer      $feedid    The feed id
     * @param   String       $ssid=""   The SSID
     * @return  Array[2]                An array of data -> [0] -> FeedObject with title, access_code, group_id, feed_id set, [1] -> DataObject[] Array of data objects
     *
     */
    public static function collectFeedData($url, $feedid, $ssid="")
    {
        // Read in the DATA data from the URL
        // Eg. Read the atom object
        $atom = new AtomReader();
        try {
            $atom->loadFile($url . "/pubcontrol/index.php?cmd=109&id={$feedid}&ssid={$ssid}");
        }
        catch (Exception $d) {
            // Error
            return NULL;
        }

        $returndata = array();

        // We need some of the top data, eg (the group id, the access code, and the
        $top = $atom->getFeedTopData();
        
        // Grab the code
        $parts = explode(":", $top->_Id);
        $parts = explode(".", $parts[2]);
        $feed = new FeedObject();
        $feed->_Title = $top->_Title;
        $feed->_Id = $parts[1];
        $feed->_GroupId = $parts[2];
        $feed->_AccessCode = $parts[0];
        $returndata[0] = $feed;

        // Get entries'
        $dataObjectArray = array();
        $atomentry = $atom->getNextEntry();

        while($atomentry !== NULL) {

            $data = new DataObject();
            $data->_Title = $atomentry->_Title;
            $p = explode(":", $atomentry->_Id);
            $data->_Id = $p[2];
            $data->_Content = $atomentry->_Content;
            $data->_FeedId = $feedid;
            $data->_DateCreated = $atomentry->_Published;
            $data->_DateLastUpdated = $atomentry->_Updated;
            $dataObjectArray[] = $data;
            $atomentry = $atom->getNextEntry();
        }

        $returndata[1] = $dataObjectArray;
        return $returndata;
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
        $result = $conn->executeQuery("FAIL();");

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
        $conn->executeQuery("SELECT * FROM {$table} WHERE feed_id = {$feedid};");

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
     * Does a scrape of all the feeds in the FEED table if their last modified has been updated
     *
     */
    public function doDataScrape()
    {
        // Get all feeds listed
        $table = DAL::getFormalTableName("recvcontrol_Feeds");
        $table2 = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $conn = $this->_Conn;
        $conn->executeQuery("SELECT {$table}.id AS feed_id, url, last_modified, ssid, type FROM {$table}, {$table2} WHERE {$table}.subscription_id = {$table2}.id;");
        $array = Array();

        // Loop over the results, and for each one record the URL and the last_modifed date
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $array[] = Array(
              "url" => $row['url'],
              "last_modified" => strtotime($row['last_modified']),
              "ssid" => $row['ssid'],
              "type" => $row['type'],
              "feed_id" => $row['feed_id']
            );
        }

        // Now we have the data, lets do a full scrape for each URL
        // Has it modified yet
        foreach($array as $value)
        {
            // Is it new data
            if(AtomReader::isNewData($value['url']) === TRUE) {
                $DataObjects = $this->collectFeedData($value['url'], $value['feed_id'], $value['ssid']);
                $DataObjects = $DataObjects[1];
                
                // Now loop through and only get ones with last_modified > than the original
                foreach($DataObjects as $obj)
                {
                    // Older?
                    if($strtotime($obj->_DateLastUpdated) < $value['last_modified']) {
                        // Since it is always oldest last, then once one last-modified is reached, they all are old
                        break;
                    }

                    // Insert the data into the database
                    $title = DAL::applyFilter($obj->_Title);
                    $content = $obj->_Content;
                    $date_created = strtotime($obj->_DateCreated);
                    $date_updated = strtotime($obj->_DateLastUpdated);
                    $table = DAL::getFormalTableName("recvcontrol_Data");
                    $query = "INSERT INTO {$table} (feed_id, title, content, date_created, date_lastupdated, authors, contributors) VALUES
                        ('{$value['feed_id']}', '{$title}', '{$content}', FROM_UNIXTIME({$date_created}), FROM_UNIXTIME({$date_updated}), '', '');
                    ";
                    $conn = $this->_Conn;
                    $conn->executeNonQuery($query);
                }
            }
        }
        
    }

}



?>