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
     * Add an access code handling class
     * NOTE: It MUST implement the ReceivingDataInterface to be accepted
     *
     * @param   String          The class name [ Should be in format: PluginName_Publishing_AccessCode ]
     * @param   String          Access Code to register for
     * @param   String          The plugins pl name
     * @return  Boolean         True on success, False on failure
     * 
     */
    public function addAccessCodeHandler($class, $accessCode, $plname)
    {
        // Check to see if it implemented the interface
        if((class_exists($class) === FALSE) || ($accessCode === '')){
            return false;
        }

        // Make sure it is an instance of
        $p = new $class();
        if(!($p instanceof  ReceivingDataInterface)) {
            return false;
        }

        // Add the handler
        $table = DAL::getFormalTableName("recvcontrol_Handlers");
        $accessCode = DAL::applyFilter($accessCode);
        $plname = DAL::applyFilter($plname);
        $class = DAL::applyFilter($class);
        $conn = $this->_Conn;

        $conn->executeNonQuery("INSERT INTO {$table}(cname, access_code, plugin_name) VALUES('{$class}', '{$accessCode}', '{$plname}');");

        return true;
    }


    /**
     * Checks to see if the subscription exists (URL, private, public)
     *
     * @param   String      $url        The URL of the subscription
     * @param   Integer     $type       The TypeObject value for private or public
     * @return  Boolean                 Subscription ID if it exists, FALSE if otherwise. Use === to check
     */
    public function subscriptionExists($url, $type)
    {
        // Declare Variables
        $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $type = (int)DAL::applyFilter($type, true);
        $url = DAL::applyFilter($url);
        $conn = $this->_Conn;
        
        // Check if already exists
        $sql = "SELECT id FROM {$table} WHERE url = '{$url}' AND type = '{$type}';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();
        if($row !== NULL) {
            return $row['id'];
        }
        else {
            return FALSE;
        }

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
        /*$sql = "SELECT type FROM {$table} WHERE ssid = '{$ssid}' AND url = '{$url}' AND type = '1';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();
        if($row !== NULL) {
            return FALSE;
        }*/

        // Create query
        $sql = "INSERT INTO {$table} (ssid, identifier, url, type) VALUES('{$ssid}','','{$url}','1');";
        $conn->executeNonQuery($sql);

        return TRUE;
    }


    /**
     * Finishes a valid public subscription
     *
     * @param   String      $url        The URL that the public repository is listed at
     * @param   Integer     &$sid       Will hold the security id on insert
     * @return  Boolean                 On success TRUE, false means that it already exists in the DB
     */
    public function finishPublicSubscription($url, &$sid)
    {        
        // Declare Variables
        $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
        $url = DAL::applyFilter($url);
        $conn = $this->_Conn;

        // Check if already exists
        /* $sql = "SELECT id FROM {$table} WHERE url = '{$url}' AND type = '2';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();
        if($row !== NULL) {
            $sid = $row['id'];
            return FALSE;
        }*/

        // Create query
        $sql = "INSERT INTO {$table} (ssid, identifier, url, type) VALUES('','','{$url}','2');";
        $conn->executeNonQuery($sql);
        $sid = $conn->getInsertId();
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
     * Checks to see if a feed by that ID exists in the database
     *
     * @param   Integer $feed_id          The real feed id from the repository, not the local id
     * @return  Boolean                   True if it exists, false if not
     */
    public function feedExists($feed_id)
    {
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("recvcontrol_Feeds");
        $feed_id = (int)DAL::applyFilter($feed_id, TRUE);
        $found = FALSE;
        $result = $conn->executeQuery("SELECT feed_id FROM {$table} WHERE feed_id = '{$feed_id}';");
        if($result->fetchAssoc() === NULL) {

            return false;
        }
        
        return true;

        
    }

    /**
     * Adds a feed to the database from a valid SUI
     * NOTE: CONTENT IS NOT FILTERED
     *
     * @param FeedObject $FeedObject        The feed object
     * @param Integer    $sid               The subscription id
     */
    public function addFeed($feedobject, $sid)
    {
        // Declare variables
        $conn = $this->_Conn;

        // Attempt to insert into the database
        $table = DAL::getFormalTableName("recvcontrol_Feeds");

        $sql = "INSERT INTO {$table} (feed_id, group_id, title, summary, type, access_code, last_modified, subscription_id)
                VALUES('{$feedobject->_Id}', '{$feedobject->_GroupId}', '{$feedobject->_Title}','{$feedobject->_Summary}','{$feedobject->_Type}', '{$feedobject->_AccessCode}', FROM_UNIXTIME(0), '{$sid}');";

        // Send out the query
        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
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
     * @param   Integer      $lastmod=0 The last modified date (as a UNIXTIME stamp), to only load data that is later than that date
     * @return  Array[2]                An array of data -> [0] -> FeedObject with title, access_code, group_id, feed_id set, [1] -> DataObject[] Array of data objects
     *
     */
    public static function collectFeedData($url, $feedid, $ssid="", $lastmod=0)
    {
        // Read in the DATA data from the URL
        // Eg. Read the atom object
        $atom = new AtomReader();
        try {
            $atom->loadFile($url . "/pubcontrol/index.php?cmd=109&id={$feedid}&ssid={$ssid}&lmod={$lastmod}");
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
        $table = DAL::getFormalTableName("recvcontrol_Feeds");
        $table2 = DAL::getFormalTableName("recvcontrol_Data");
        $lastmod = DAL::applyFilter($lastmod, TRUE);

        // Get a list of all content
        $qstr = "SELECT {$table2}.id, {$table2}.feed_id, {$table2}.title, {$table2}.content, {$table2}.date_created, {$table2}.date_lastupdated, {$table2}.authors, {$table2}.contributors FROM {$table}, {$table2} WHERE {$table}.access_code = '{$accesskey}' AND {$table2}.feed_id = {$table}.feed_id ORDER BY {$table2}.date_lastupdated DESC;";
        $result = $conn->executeQuery($qstr);
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
        $result = $conn->executeQuery("SELECT {$table}.id AS qfeed_id, feed_id, url, last_modified, ssid, {$table2}.type FROM {$table}, {$table2} WHERE {$table}.subscription_id = {$table2}.id;");
        $array = Array();

        // Loop over the results, and for each one record the URL and the last_modifed date
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $array[] = Array(
              "url" => $row['url'],
              "last_modified" => strtotime($row['last_modified']),
              "ssid" => $row['ssid'],
              "type" => $row['type'],
              "feed_id" => $row['feed_id'],
              "id" => $row['qfeed_id']
            );

        }

        // Grab the handlers
        $handler_array = Array();
        $table = DAL::getFormalTableName("recvcontrol_Handlers");
        $result = $conn->executeQuery("SELECT * FROM {$table};");

        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            if(class_exists($row['cname'])) {
                $handler_array[] = Array($row['access_code'], $row['cname']);
            }
        }

        // Call the before functions
        foreach($handler_array as $handle) {
            @$handle[1]::beforeScrape();
        }

        // Now we have the data, lets do a full scrape for each URL
        // Has it modified yet
        foreach($array as $value)
        {
            // Is it new data
            if(AtomReader::isNewData($value['url'] . "/pubcontrol/index.php?cmd=109&id={$value['feed_id']}&ssid={$value['ssid']}", $value['last_modified']) === 0) {
                $DataObjects = $this->collectFeedData($value['url'], $value['feed_id'], $value['ssid'], $value['last_modified']);
                $feedData1 = $DataObjects[0];
                $DataObjects = $DataObjects[1];
                $oldest = 0;

                $arrayOfNormalHandles = Array();
                foreach($handler_array as $handle) {
                    if($handle[0] == $feedData1->_AccessCode) {
                        $arrayOfNormalHandles[] = $handle[1];
                    }
                }

                // Now loop through and only get ones with last_modified > than the original
                $bbupdate = false;
                foreach($DataObjects as $obj)
                {
                    // Older?
                    $b = strtotime($obj->_DateLastUpdated);
                    if($b <= $value['last_modified']) {
                        // Since it is always oldest last, then once one last-modified is reached, they all are old
                        continue;
                    }

                    if($b > $oldest) {
                        $oldest = $b;
                    }

                    // Send to the function
                    foreach($arrayOfNormalHandles as $drr) {
                        @$drr::processData($obj);
                    }

                    $bbupdate = true;

                    // Insert the data into the database
                    $title = DAL::applyFilter($obj->_Title);
                    $content = $obj->_Content;
                    $date_created = strtotime($obj->_DateCreated);
                    $date_updated = $b;
                    $table = DAL::getFormalTableName("recvcontrol_Data");
                    $query = "INSERT INTO {$table} (feed_id, title, content, date_created, date_lastupdated, authors, contributors) VALUES
                        ('{$value['feed_id']}', '{$title}', '{$content}', FROM_UNIXTIME({$date_created}), FROM_UNIXTIME({$date_updated}), '', '');
                    ";
                    $conn->executeNonQuery($query);
                }

                // Update feed
                if($bbupdate == TRUE) {
                    $dt = date("Y-m-d H:i:s", $oldest);
                    $table2 = DAL::getFormalTableName("recvcontrol_Feeds");
                    $conn->executeNonQuery("UPDATE {$table2} SET last_modified = '{$dt}';");
                }
                
            }
        }

        // Call the before functions
        foreach($handler_array as $handle) {
            @$handle[1]::afterScrape();
        }

        
    }

}

/**
 * This interface is to be implemented by the plugins that wish to add handlers for incoming data
 */
interface ReceivingDataInterface
{
    /**
     * This method is called before a scrape is performed
     */
    public static function beforeScrape();

    /**
     * This method is called after a scrape is performed
     */
    public static function afterScrape();

    /**
     * This method is called when a read in data object matches a given access code
     * The data object is also copied to the data table
     *
     * @param DataObject    $DataObject     The data object read in
     */
    public static function processData($DataObject);

}

?>