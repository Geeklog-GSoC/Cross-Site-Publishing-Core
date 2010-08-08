<?php
/**
 * @package PublishingControl
 * @author Tim Patrick
 * @license GPL V3
 * @copyright 2010 Tim Patrick
 * @version 0.2
 *
 */


/**
 * Site wide definitions
 *
 */
define("CMD_GET_FEED_DATA", 109);
define("CMD_LIST_FEEDS", 110);
define("CMD_LIST_GROUPS", 111);
define("CMD_SUBSCRIBE", 112);
define("CMD_PUBREPO", 113);

/**
 * For the methods that interact with the management of the group objects
 */
class PublishingGroupControl
{
    // Private Variables
    private $_LimitLast = 0;
    private $_CurrentLimit = 0;
    
    // Public methods

    /**
     * Creates a group in the publishing database
     *
     * @param GroupObject       $groupobject        A group object filled with the data
     * @param Boolean           $nodisplay=FALSE    If set to true, then the group will be set to no display which means it will not be shown to the user to delete or edit - This is for plugin use
     * @return int                      The insertion id
     * @throws PublishingException on failure to create
     */
    public function createGroup($groupobject, $nodisplay=FALSE)
    {
        // Declare Variables
        $conn = new DAL();

        // Make sure the values are valid
        if($groupobject->verify() === FALSE) {
            throw new PublishingException("Invalid group values in object");
        }

        // Attempt to insert into the database
        $table = DAL::getFormalTableName("pubcontrol_Groups");

        // Clean up the values
        $groupobject->_Title = DAL::applyFilter($groupobject->_Title);
        $groupobject->_Summary = DAL::applyFilter($groupobject->_Summary);
        $groupobject->_Type = DAL::applyFilter($groupobject->_Type, true);

        // Get the NODISPLAY value
        if($nodisplay === FALSE) {
            $nodisplay = 'N';
        }
        else {
            $nodisplay = 'Y';
        }

        // Do a test to make sure the % symbol is only there if nodisplay is Y
        if( (strpos($groupobject->_Title, "%") !== FALSE) && ($nodisplay === 'N')){
            throw new PublishingException("% Special Character can only be used with No Display flag set to TRUE");
        }

        $sql = "INSERT INTO {$table} (title, summary, type, nodisplay) VALUES('{$groupobject->_Title}','{$groupobject->_Summary}','{$groupobject->_Type}', '{$nodisplay}');";
        
        // Send out the query
        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
    }

    /**
     * Gets the id for the no display group title (Only works with no display unique titles)
     *
     * @param   Integer       $title      The title of the group
     * @return  Mixed                     The id of the group (> 0) OR FALSE on failure
     */
    public function getIdForNDTitle($title)
    {
        // Declare Variables
        $conn = new DAL();
        $title = DAL::applyFilter($title);
        $table = DAL::getFormalTableName("pubcontrol_Groups");

        $sql = "SELECT id FROM {$table} WHERE title = '{$title}';";
        $result = $conn->executeQuery($sql);
        $row = $result->fetchAssoc();

        if($row === NULL) {
            return FALSE;
        }
        
        return $row["id"];
    }

    /**
     * Removes the group from the database
     * Note: This will FAIL if there are still feeds assigned to this group.
     * A group with feeds can NOT be deleted. You must reassign the feeds first.
     * 
     * @param int $groupid The id of the group to delete
     * @throws PublishingException on failure to delete
     */
    public function deleteGroup($groupid)
    {
        // Declare Variables
        $conn = new DAL();
        $groupid = DAL::applyFilter($groupid, true);
        $table = DAL::getFormalTableName("pubcontrol_Groups");

        // Make sure that there are no feeds that belong to this group
        $num = $conn->count("pubcontrol_Feeds", "group_id", $groupid);

        if($num > 0)
        {
            throw new PublishingException("Cannot remove group with feeds attached");
        }

        // Send connection
        $sql = "DELETE FROM {$table} WHERE id = '{$groupid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Updates the group in the database
     * Note: If  any fields in the group object left to NULL, then those fields will not be updated. This allows you to choose which fields to update.
     *
     * @param GroupObject $groupobject A group object filled with the data
     * @throws PublishingException on failure to update
     */
    public function updateGroup($groupobject)
    {
        // Declare Variables
        $querystr = "";
        $groupid = DAL::applyFilter($groupobject->_Id, true);
        $conn = new DAL();
        $table = DAL::getFormalTableName("pubcontrol_Groups");
        
        // Do a test to make sure the % symbol is only there if nodisplay is Y
        if( (strpos($groupobject->_Title, "%") !== FALSE)){
            throw new PublishingException("% Special Character can only be used with No Display flag set to TRUE");
        }

        // Check which items are added
        if($groupobject->_Title !== NULL)
        {
            $groupobject->_Title = DAL::applyFilter($groupobject->_Title);
            $querystr .= "title='{$groupobject->_Title}',";
        }

        if($groupobject->_Summary !== NULL)
        {
            $groupobject->_Summary = DAL::applyFilter($groupobject->_Summary);
            $querystr .= "summary='{$groupobject->_Summary}',";
        }

        if($groupobject->_Type !== NULL)
        {
            $groupobject->_Type = DAL::applyFilter($groupobject->_Type, true);
            $querystr .= "type='{$groupobject->_Type}',";
        }

        // Get rid of last comma
        $querystr = rtrim($querystr, ',');

        // Send query
        $sql = "UPDATE {$table} SET {$querystr} WHERE id = '{$groupid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Resets the limit counters
     *
     */
    public function resetLimitCounters()
    {
        $this->_CurrentLimit = 0;
        $this->_LimitLast = 0;
    }

    /**
     * Returns a GroupObject object with the group data in it that matches the specific group id
     * NOTE: NoDisplay groups are NOT returned.
     *
     * @param   int     $groupid        The group id
     * @return                          GroupObject or NULL
     */
    public function getGroupData($groupid)
    {
        // Declare Variables
        $conn = new DAL();
        $grp = NULL;
        $table = DAL::getFormalTableName("pubcontrol_Groups");
        $id = DAL::applyFilter($groupid, TRUE);

        if($id === 0) {
            return NULL;
        }

        $result = $conn->executeQuery("SELECT * FROM {$table} WHERE id = {$id};");
                // Now attempt to loop over and populate objects
        while ( ($row = $result->fetchAssoc()) !== NULL)
        {
            if($row['nodisplay'] == 'Y') {
#                continue;
            }
            
            $grp = new GroupObject();
            $grp->_Id = $row['id'];
            $grp->_Title = $row['title'];
            $grp->_Summary = $row['summary'];
            $grp->_Type = $row['type'];
        }

        return $grp;
    }


    /**
     * Returns an array of all the group items in the database
     *
     * @param  int  $limit=0 Optional limit to use, if set to 0 no limit is used. Otherwise a limit of queries can be specified to be returned. Note that calling this method twice with the same object instance will get the NEXT limit items if a limit was sent first, however TRUE must be specified as this parameter value
     * @return GroupObject[] Array of group objects with all the information about them
     * @throws PublishingException on failure to retreive
     */
    public function listGroups($limit = 0)
    {
        // Declare Variables
        $conn = new DAL();
        $grouparray = array();
        $grp = NULL;
        $table = DAL::getFormalTableName("pubcontrol_Groups");

        // Is there a limit
        if($limit === 0)
        {
            // Create our query
            $result = $conn->executeQuery("SELECT * FROM {$table};");
            $this->_LimitLast = 0;
        }
        else
        {
            // Does it expect a limit is already there
            if($limit === TRUE)
            {
                // What does the limit indicator say, it if it still 0, then there has been a problem
                if($this->_LimitLast === 0)
                {
                    throw new PublishingException("Cannot attempt to use nonexistant limit marker");
                }

                // Create the query
                $result = $conn->executeQuery("SELECT * FROM {$table} LIMIT {$this->_LimitLast},{$this->_CurrentLimit}; ");
                $this->_LimitLast = $this->_LimitLast + $this->_CurrentLimit;
            }
            else
            {
               // Create the limiting factor

               $this->_CurrentLimit = (int)$limit;
               $this->_LimitLast = (int)$limit;
               $result = $conn->executeQuery("SELECT * FROM {$table} LIMIT 0,{$this->_CurrentLimit}; ");
            }
        }

        // Now attempt to loop over and populate objects
        while ( ($row = $result->fetchAssoc()) !== NULL)
        {
            if($row['nodisplay'] == 'Y') {
#                continue;
            }

            $grp = new GroupObject();
            $grp->_Id = $row['id'];
            $grp->_Title = $row['title'];
            $grp->_Summary = $row['summary'];
            $grp->_Type = $row['type'];
            $grouparray[] = $grp;
        }

        return $grouparray;
    }
}

/**
 * Interface for the methods that interact with the management of the feed objects
 */
class PublishingFeedControl
{
    // Private Variables
    private $_Conn = NULL;


    /**
     * Creates an instance of the object
     */
    public function __construct()
    {
        $this->_Conn = new DAL();
    }

    /**
     * Destroys object data
     */
    public function __destruct()
    {
        $this->_Conn->cleanup();
    }


    /**
     * Creates a feed assigned to a specific group
     *
     * @param int $groupid The id of the group to assign to
     * @param FeedObject $feedobject The feed object to load
     * @return  int                     The insertion id
     * @throws PublishingException on failure to create
     */
    public function createFeed($groupid, $feedobject)
    {
        $conn = $this->_Conn;

        // Make sure the values are valid
        if($feedobject->verify() === FALSE)
        {
            throw new PublishingException("Invalid feed values in object");
        }

        // Attempt to insert into the database
        $table = DAL::getFormalTableName("pubcontrol_Feeds");

        // Clean up the values
        $feedobject->_AccessCode = DAL::applyFilter($feedobject->_AccessCode);
        $feedobject->_GroupId = DAL::applyFilter($feedobject->_GroupId, true);
        $feedobject->_Summary = DAL::applyFilter($feedobject->_Summary);
        $feedobject->_Type = DAL::applyFilter($feedobject->_Type, true);
        $feedobject->_Title = DAL::applyFilter($feedobject->_Title);

        $sql = "INSERT INTO {$table} (group_id, title, summary, type, access_code, last_modified)
                VALUES('{$feedobject->_GroupId}', '{$feedobject->_Title}','{$feedobject->_Summary}','{$feedobject->_Type}', '{$feedobject->_AccessCode}', NOW());";

        // Send out the query
        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
    }

    /**
     * Removes the feed from the database
     * Note: A feed must be EMPTY of all data objects before it can be deleted
     * 
     * @param int $feedid The id of the field to delete
     * @throws PublishingException on failure to delete
     */
    public function deleteFeed($feedid)
    {
        // Grab connection and create variables
        $conn = $this->_Conn;
        $feedid = DAL::applyFilter($feedid, true);
        $table = DAL::getFormalTableName("pubcontrol_Feeds");

        // Check the count
        $num = $conn->count("pubcontrol_Data", "feed_id", $feedid);

        if($num > 0)
        {
            throw new PublishingException("Cannot delete feed that has data items assigned to it");
        }

        // Must be ok, send deletion request
        $sql = "DELETE FROM {$table} WHERE id = '{$feedid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Updates the field in the database
     * Note: If any fields in the FeedObject left to NULL, then those fields will not be updated. This allows you to choose which fields to update.
     *
     * @param FeedObject $feedobject The feed object to update
     * @throws PublishingException on failure to update
     */
    public function updateFeed($feedobject)
    {
        // Declare Variables
        $querystr = "";
        $feedid = DAL::applyFilter($feedobject->_Id, true);
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Feeds");

        // Get the items that are not null
        if($feedobject->_Title !== NULL)
        {
            $feedobject->_Title = DAL::applyFilter($feedobject->_Title);
            $querystr .= "title='{$feedobject->_Title}',";
        }

        if($feedobject->_Summary !== NULL)
        {
            $feedobject->_Summary = DAL::applyFilter($feedobject->_Summary);
            $querystr .= "summary='{$feedobject->_Summary}',";
        }

        if($feedobject->_Type !== NULL)
        {
            $feedobject->_Type = DAL::applyFilter($feedobject->_Type, true);
            $querystr .= "type='{$feedobject->_Type}',";
        }

        if($feedobject->_AccessCode !== NULL)
        {
            $feedobject->_AccessCode = DAL::applyFilter($feedobject->_AccessCode);
            $querystr .= "access_code='{$feedobject->_AccessCode}',";
        }

        if($feedobject->_GroupId !== NULL)
        {
            $feedobject->_GroupId = DAL::applyFilter($feedobject->_GroupId, true);
            $querystr .="group_id='{$feedobject->_GroupId}',";
        }

        $querystr = rtrim($querystr, ',');

        // Send query out
        $sql = "UPDATE {$table} SET {$querystr} WHERE id = '{$feedid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Assigns the feed to a new group
     * @param int $feedid The feed to assign
     * @param int $groupid The new group id
     * @throws PublishingException on failure to assign
     */
    public function assignNewGroup($feedid, $groupid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $feedid = DAL::applyFilter($feedid, true);
        $groupid = DAL::applyFilter($groupid, true);
        $table = DAL::getFormalTableName("pubcontrol_Feeds");
        
        // Send query
        $conn->executeNonQuery("UPDATE {$table} SET group_id = '{$groupid}' WHERE id = {$feedid};");
    }

    /**
     * Deletes ALL data that is associated with the feed.
     * Note: This is irreversable, do not call this method unless you are sure of the consequences
     *
     * @param int $feedid       The feedid to delete for
     *
     */
    public function destroyFeedData($feedid)
    {
        // Declare Variables
        $conn = $this->_Conn;

        // Sanitize Data
        $feedid = DAL::applyFilter($feedid, true);
        $table = DAL::getFormalTableName("pubcontrol_Data");

        // Execute query
        $conn->executeNonQuery("DELETE FROM {$table} WHERE feed_id = '{$feedid}';");
    }

    /**
     * Moves all data associated with the old feed id to the new feed
     *
     * @param int $oldfeedid        The old feed to move from
     * @param int $newfeedid        The new feed to move to
     */
    public function massMoveData($oldfeedid, $newfeedid)
    {
        // Declare Variables
        $conn = $this->_Conn;

        // Sanitize Data
        $oldfeedid = DAL::applyFilter($oldfeedid, true);
        $newfeedid = DAL::applyFilter($newfeedid, true);
        $table = DAL::getFormalTableName("pubcontrol_Data");

        // Execute query
        $conn->executeNonQuery("UPDATE {$table} SET feed_id = '{$newfeedid}' WHERE feed_id = '{$oldfeedid}';");
    }

    /**
     * Returns an array of all the feed items in the database
     * @param int $groupid OPTIONAL - If specified, only the feeds that belong to this group will be returned, otherwise all feed objects will be
     * @param Bool $feedsekect OPTIONAL - If specified, the groupid becomes a feed id and only data for the one feed will be returned
     * @return FeedObject[] Array of feed objects with all the information about them
     * @throws PublishingException on failure to retreive
     */
    public function listFeeds($groupid=NULL, $feedselect=FALSE)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $feedarray = array();
        $frp = NULL;
        $table = DAL::getFormalTableName("pubcontrol_Feeds");

        // Make up query
        if($groupid === NULL)
        {
            $sql = "SELECT * FROM {$table};";
        }
        else
        {
            $groupid = DAL::applyFilter($groupid, true);

            if($feedselect === TRUE)
            {
                $sql = "SELECT * FROM {$table} WHERE id = {$groupid};";
            }
            else
            {
                $sql = "SELECT * FROM {$table} WHERE group_id = {$groupid};";
            }
        }

        // Execute Query
        $result = $conn->executeQuery($sql);

        // And now populate data
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $frp = new FeedObject();
            $frp->_Id = $row['id'];
            $frp->_GroupId = $row['group_id'];
            $frp->_AccessCode = $row['access_code'];
            $frp->_Summary = $row['summary'];
            $frp->_Type = $row['type'];
            $frp->_Title = $row['title'];
            $frp->_DateLastUpdated = strtotime($row['last_modified']);
            $feedarray[] = $frp;
        }
        return $feedarray;
    }
}

/**
 * Class for the methods that interact with the management of the data
 */
class PublishingDataControl
{
    // Private Variables
    private $_Conn = NULL;

    /**
     * Updates the feed table entries last modified tag to determine when the last modified date was
     *
     * @param   Integer     $feedid     The feed id
     */
    private function _updateLastModified($feedid)
    {
        // Grab the connection
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Feeds");
        $feedid = (int)DAL::applyFilter($feedid, true);

        // Send query
        $conn->executeNonQuery("UPDATE {$table} SET last_modified = NOW() WHERE id = '{$feedid}';");
    }

    /**
     * Updates the old tables last modified entry if the data has changed to a new feed
     * - Checks to see if the feed id has changed - if it has, then get the new feed and update the old last modified one
     * NOTE: MUST BE CALLED BEFORE THE UPDATE DATA CALL
     *
     * @param   Integer     $feedid     The feed id
     * @param   Integer     $dataid     The data id
     *
     */
    private function _updateLastModifiedOldObject($feedid, $dataid)
    {
        // Get the feed id
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Data");
        $dataid = (int)DAL::applyFilter($dataid, true);

        // Send query
        $result = $conn->executeQuery("SELECT feed_id FROM {$table} WHERE id = '{$dataid}';");
        $row = $result->fetchAssoc();

        if($row['feed_id'] == $feedid) {
            // has not changed
            return;
        }
        else {
            // Has changed
            $this->_updateLastModified($row['feed_id']);
        }
    }

    public function __construct()
    {
        $this->_Conn = new DAL();
    }

    public function __destruct()
    {
        $this->_Conn->cleanup();
    }

    /**
     * Adds data to a certain feed
     *
     * WARNING:
     * The CONTENT IS NOT MADE SAFE AGAINST SQL INJECTIONS, due to the fact it could be binary code.
     * The onus is then on the developer using this API to determine the type and then do the appropriate safe gaurds
     *
     * @param DataObject $DataObject The data information to add
     * @return  int                     The last insertion id
     * @throws PublishingException on serious error
     */
    public function addData($DataObject)
    {
        // Declare Variables
        $conn = $this->_Conn;

        // Make sure it is valid
        if($DataObject->verify() === FALSE)
        {
            throw new PublishingException("Invalid entry data");
        }

        // Create the items
        $table = DAL::getFormalTableName("pubcontrol_Data");
        $DataObject->_Title = DAL::applyFilter($DataObject->_Title);
        $DataObject->_FeedId = DAL::applyFilter($DataObject->_FeedId, true);
        $DataObject->_DateCreated = DAL::applyFilter($DataObject->_DateCreated);
        $DataObject->_DateLastUpdated = DAL::applyFilter($DataObject->_DateLastUpdated);
        $authors = implode(':', $DataObject->_Authors);
        $cont = implode(':', $DataObject->_Contributors);
        $authors = DAL::applyFilter($authors);
        $cont = DAL::applyFilter($cont);

        // And now make the query
        $sql = "INSERT INTO {$table} (feed_id, title, content, date_created, date_lastupdated, authors, contributors)
                VALUES
                ('{$DataObject->_FeedId}', '{$DataObject->_Title}', '{$DataObject->_Content}',
                NOW(), NOW(), '{$authors}', '{$cont}');";
        $conn->executeNonQuery($sql);
        $this->_updateLastModified($DataObject->_FeedId);
        return $conn->getInsertId();
    }

    /**
     * Updates data for a certain feed
     * 
     * @param DataObject $DataObject The data information to update
     * @throws PublishingException on serious error
     */
    public function editData($DataObject)
    {
        // Grab connection and create variables
        $conn = $this->_Conn;
        $dataid = DAL::applyFilter($DataObject->_Id, true);
        $table = DAL::getFormalTableName("pubcontrol_Data");

        // Sanitize all data
        $DataObject->_Title = DAL::applyFilter($DataObject->_Title);
        $DataObject->_FeedId = DAL::applyFilter($DataObject->_FeedId, true);
        $DataObject->_DateCreated = DAL::applyFilter($DataObject->_DateCreated);
        $DataObject->_DateLastUpdated = DAL::applyFilter($DataObject->_DateLastUpdated);
        $authors = implode(':', $DataObject->_Authors);
        $cont = implode(':', $DataObject->_Contributors);
        $authors = DAL::applyFilter($authors);
        $cont = DAL::applyFilter($cont);

        // And now make the query
        $sql = "UPDATE {$table} SET feed_id = '{$DataObject->_FeedId}', title = '{$DataObject->_Title}', content = '{$DataObject->_Content}',
                date_lastupdated = NOW(), authors = '{$authors}', contributors = '{$cont}' WHERE id = '{$dataid}';";
        $this->_updateLastModifiedOldObject($DataObject->_FeedId, $dataid);
        $conn->executeNonQuery($sql);
        $this->_updateLastModified($DataObject->_FeedId);
    }

    /**
     * Removes a data entry from the feed
     * @param  $dataid The data object to delete
     * @param  $feedid The feed id
     * @throws PublishingException on serious error
     */
    public function deleteData($dataid, $feedid)
    {
        // Grab connection and create variables
        $conn = $this->_Conn;
        $dataid = DAL::applyFilter($dataid, true);
        $table = DAL::getFormalTableName("pubcontrol_Data");

        // Must be ok, send deletion request
        $sql = "DELETE FROM {$table} WHERE id = '{$dataid}';";
        $conn->executeNonQuery($sql);
        $this->_updateLastModified($feedid);
    }

    /**
     * Gets all the data objects for a specific feed.
     *
     * @param int       $feedid         The feed id to get data for
     * @param bool      $isdata=FALSE   If this is true, only that data id data will be returned (and the feed id becomes the data id)
     * @param int       $lastmod=0      The last modified value to return data that is newer than that date
     * @return DataObject[]             Array of data objects associated with that feed or NULL
     * @throws PublishingException on serious error
     */
    public function getDataForFeed($feedid, $isdata=FALSE, $lastmod=0)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Data");
        $feedid = DAL::applyFilter($feedid, true);
        $lastmod = (int)$lastmod;
        $dataobjects = array();
        $dsg = NULL;

        // Send SQL query
        if($isdata === FALSE) {
            $sql = "SELECT * FROM {$table} WHERE feed_id = '{$feedid}'";
        }
        else {
            $sql = "SELECT * FROM {$table} WHERE id = '{$feedid}'";
        }

        if($lastmod === 0) {
            $sql .= ';';
        }
        else {
            $sql .= " AND date_lastupdated > FROM_UNIXTIME({$lastmod});";
        }

        $result = $conn->executeQuery($sql);

        // And loop over the result
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
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
        

        return (count($dataobjects) === 0) ? NULL : $dataobjects;
    }
}

/**
 * Holds methods for dealing with security
 */
class PublishingSecurityManagement
{
    // Private Variables
    private $_Conn = NULL;
    private $_DBNAME = "";

    /**
     * Breaks up the SSID into its parts and returns an associative array with them
     * 
     * @param       String  $ssid            The SSID to
     * @param       Boolean $ignorehash      Ignores the ID requirement on the ssid
     * @return      Mixed                   Associative array [ URL, IDENTIFIER, PIN, ID, SSID ]
     * @throws      PublishingException     on serious error
     */
    private function _getRealSSID($ssid, $ignorehash=False)
    {
        // Decouple the hash (always the last A)
        $mixed = array();

        if($ignorehash === FALSE) {
            $pos = strrpos($ssid, 'A');
        
            if($pos === FALSE)
            {
                throw new PublishingException("Invalid SSID format passed");
            }

            // Split the string on that location
            $ssid2 = substr($ssid, 0, $pos);
            $idl = substr($ssid, $pos);
            $idl = substr($idl, 1);
        }
        else {
            $ssid2 = $ssid;
            $idl = 0;
        }

        $mixed['SSID'] = $ssid2;
        
        // And now decode the data
        // urlencode(base64_encode('%' . $url . '%' . $identifier . '%' . $pin . '#' . md5($url . 'D' . $identifier . 'E' . $pin . 'F')));
        $ssid2 = base64_decode(urldecode($ssid2));

        // Explode the string on the identifers to relate it
        $exp = explode("#", $ssid2);
        $exp = explode('%', $exp[0]);

        // And now set values
        $mixed['URL'] = $exp[1];
        $mixed['IDENTIFIER'] = $exp[2];
        $mixed['PIN'] = $exp[3];
        $mixed['ID'] = $idl;

        return $mixed;
    }

    /**
     * Checks to see if the incoming ssid is valid
     *
     * @param   String          The SSID
     * @param   Integer         &Incoming The ID of the subscriber
     * @return  Boolean         True on valid, false on negative
     */
    private function _ssidValid($ssid, &$id)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");
        
        // Grab the data
        $mixed = $this->_getRealSSID($ssid, TRUE);

        $ssid = urlencode(DAL::applyFilter($mixed['SSID']));

        $result = $conn->executeQuery("SELECT mode, id FROM {$table} WHERE ssid = '{$ssid}';");
        $row = $result->fetchAssoc();

        if( ($row === NULL) || ($row['mode'] !== 'Y'))
        {
            return false;
        }

        $id = $row['id'];
        return true;
    }


    public function __construct()
    {
        $this->_Conn = new DAL();
        $this->_DBNAME = DAL::getFormalTableName("pubcontrol_Security");
    }

    public function __destruct()
    {
        $this->_Conn->cleanup();
    }


    /**
     * Creates a security object
     * @param SecurityObject $securityObject The security object to create
     * @throws PublishingException on serious error
     * @return  int     The last insertion id
     */
    public function createSecurityGroup($securityObject)
    {
        // Declare Variables
        $conn = $this->_Conn;
        
        // Make sure its valid
        if (($securityObject->_Title === "") || ($securityObject->_Title === NULL)
                || ($securityObject->_Summary === "") || ($securityObject->_Summary === NULL)
                || ($securityObject->_Color === "") || ($securityObject->_Color === NULL)
                )
        {
            throw new PublishingException("Invalid security values in object");
        }

        // Clean up the values
        $securityObject->_Summary = DAL::applyFilter($securityObject->_Summary);
        $securityObject->_Title = DAL::applyFilter($securityObject->_Title);
        $securityObject->_Color = DAL::applyFilter($securityObject->_Color);

        // Send out the query
        $sql = "INSERT INTO {$this->_DBNAME} (title, summary, color) VALUES('{$securityObject->_Title}', '{$securityObject->_Summary}', '{$securityObject->_Color}');";

        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
    }

    /**
     * Updates the security object
     *
     * @param SecurityObject $securityObject The security object to update
     * @throws PublishingException on serious error
     */
    public function updateSecurityGroup($securityObject)
    {
        // Declare Variables
        $conn = $this->_Conn;

        // Make sure its valid
        if (($securityObject->_Title === "") || ($securityObject->_Title === NULL)
                || ($securityObject->_Summary === "") || ($securityObject->_Summary === NULL)
                || ($securityObject->_Color === "") || ($securityObject->_Color === NULL)
                )
        {
            throw new PublishingException("Invalid security values in object");
        }

        // Clean up the values
        $securityObject->_Summary = DAL::applyFilter($securityObject->_Summary);
        $securityObject->_Title = DAL::applyFilter($securityObject->_Title);
        $securityObject->_Color = DAL::applyFilter($securityObject->_Color);
        $secid = DAL::applyFilter($securityObject->_Id, true);

        // Send out the query
        $sql = "UPDATE {$this->_DBNAME} SET title = '{$securityObject->_Title}', summary = '{$securityObject->_Summary}', color = '{$securityObject->_Color}' WHERE id = '{$secid}';";

        $conn->executeNonQuery($sql);
    }

    /**
     * Deletes a security group from the database
     * Note: The group must NOT have any subscribers in it
     * @param int $securityid   The id of the object to delete
     * @throws PublishingException on serious error
     */
    public function deleteSecurityGroup($securityid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securityid = DAL::applyFilter($securityid, true);

        // Make sure no subscribers are in the group
#        $num = $conn->count("pubcontrol_Subscribers", "security_id", $securityid);
        $tbl = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        $qstr = "SELECT COUNT(securitygroup_id) AS count FROM {$tbl} WHERE securitygroup_id = {$securityid};";
        
        $result = $conn->executeQuery($qstr);
        $row = $result->fetchAssoc();
        $num = (int)$row['count'];

        if($num > 0)
        {
            throw new PublishingException("Cannot remove security group with subscribers attached");
        }

        // Send connection
        $sql = "DELETE FROM {$this->_DBNAME} WHERE id = '{$securityid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Subscribes a URL successfully
     * Note: This simply adds the subscriber to the database.
     * Any actual subscription work must be done before calling this method
     * 
     * @param String $url The url to subscribe for
     * @param String $identifier The identifier to subscribe for
     * @param String $pin The unique pin
     * @return String ssid The unique identifier for the subscription instance
     * @throws PublishingException on serious error
     */
    public function subscribe($url, $identifier, $pin, $t=TypeObject::O_AWAITINGAPPROVAL)
    {
        // Declare Variables
        $conn = $this->_Conn;

        // Create the SSID hash
        // Looks like this:
        // 25f72ff5cfb7c07399a6725cbe1e7b1ad68c0897%http://mysite.com%COMM1%0x940xd#8b024c624948d2f2a33096d127051aa9
        // JWh0dHA6Ly9teXNpdGUuY29tJUNPTU0xJTB4OTQweGQjOGIwMjRjNjI0OTQ4ZDJmMmEzMzA5NmQxMjcwNTFhYTk=
        $ssid = urlencode(base64_encode('%' . $url . '%' . $identifier . '%' . $pin . '#' . md5($url . 'D' . $identifier . 'E' . $pin . 'F')));

        // Santiize Input
        $url = DAL::applyFilter($url);
        $identifier = DAL::applyFilter($identifier);
        $pin = DAL::applyFilter($pin);
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");
        $sql = "INSERT INTO {$table} (url, identifier, pin, ssid, mode) VALUES('{$url}','{$identifier}','{$pin}', '{$ssid}', '{$t}');";
        $conn->executeNonQuery($sql);
        $ssid .= 'A'.$conn->getInsertId();
        return $ssid;
    }

    /**
     * Returns a SecurityObject object with the group data in it that matches the specific group id or an Array of SecurityObjects
     *
     * @param   int     $groupid        (OPTIONAL)The group id - If present, just that SecurityObject is returned, else a list of all entries are
     * @return                          SecurityObject or NULL
     */
    public function getSGroupData($groupid=0)
    {
        // Declare Variables
        $conn = new DAL();
        $grp = NULL;
        $table = $this->_DBNAME;
        $id = (int)DAL::applyFilter($groupid, TRUE);
        $d = array();
        
        if($id === 0) {
            $qstr = "SELECT * FROM {$table};";
        }
        else {
            $qstr = "SELECT * FROM {$table} WHERE id = {$id};";
        }

        $result = $conn->executeQuery($qstr);

        // Now attempt to loop over and populate objects
        while ( ($row = $result->fetchAssoc()) !== NULL)
        {
            $grp = new SecurityObject();
            $grp->_Id = $row['id'];
            $grp->_Title = $row['title'];
            $grp->_Summary = $row['summary'];
            $grp->_Color = $row['color'];
            $d[] = $grp;
        }

        return ($groupid !== 0) ? $d[0] : $d;
    }


    /**
     * Unsubscribes a URL
     *
     * @param String $ssid      The SSID that associates the subscriber with a subscription
     * 
     */
    public function unsubscribe($ssid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");
        $table2 = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        
        // Get the ssid
        $mixed = $this->_getRealSSID($ssid, TRUE);
        $ssid = DAL::applyFilter($mixed['SSID']);
        $id = DAL::applyFilter($mixed['ID'], TRUE);
        // Remove from database
        $conn->executeNonQuery("DELETE FROM {$table} WHERE ssid = '{$ssid}';");
        $conn->executeNonQuery("DELETE FROM {$table2} WHERE subscriber_id = '{$id}';");
    }

    /**
     * Unsubsbribes a user
     * @param <type> $id
     */
    public function deleteSubscriber($id)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");

        // Get the ssid
        $id = DAL::applyFilter($id, TRUE);

        // Remove from database
        $conn->executeNonQuery("DELETE FROM {$table} WHERE id = '{$id}';");
    }
    
    /**
     * Checks to see if the subscriber is allowed to 
     * @param String $ssid The sites SSID
     * @param int $feed_id The id of the feed to see if access for
     * @return boolean True if the subscription is valid, false if not
     * @throws PublishingException on serious error
     */
    public function verify($ssid, $feed_id)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $feed_id = DAL::applyFilter($feed_id, true);
        $table = DAL::getFormalTableName("pubcontrol_Feeds");

        // First check if it is a public feed
        $result = $conn->executeQuery("SELECT type FROM {$table} WHERE id = '{$feed_id}';");
        $type = $result->fetchAssoc();

        if($type['type'] == TypeObject::O_PUBLIC)
        {
            return true;
        }

        $table = DAL::getFormalTableName("pubcontrol_Subscribers");

        // The work of this method is two fold
        // First, it needs to get the id from the ssid
        $mixed = $this->_getRealSSID($ssid, TRUE);
        $id = 0;
        if($this->_ssidValid($ssid, $id) === FALSE)
        {
            return false;
        }

        // Get the groups associated with the feed
        $groups = array();
        $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");
        $result = $conn->executeQuery("SELECT securitygroup_id FROM {$table} WHERE feed_id = '{$feed_id}';");

        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $groups[] = $row['securitygroup_id'];
        }

        // Get all groups associated with subscriber id
        $subscriberg = array();
        $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        $result = $conn->executeQuery("SELECT securitygroup_id FROM {$table} WHERE subscriber_id = '{$id}';");

        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $subscriberg[] = $row['securitygroup_id'];
        }

        // And now match them
        foreach($groups as $gid)
        {
            foreach($subscriberg as $sid)
            {
                if($sid === $gid)
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Links a security group with a subscriber
     * @param int $securitygroupid
     * @param String $ssid The security id
     * @throws PublishingException on serious error
     */
    public function linkToSubscriber($securitygroupid, $subid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid);
        $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        
        // Valid, get id for data
        $id = DAL::applyFilter($subid, true);

        // Add the subscriber
        $conn->executeNonQuery("INSERT INTO {$table} (securitygroup_id, subscriber_id) VALUES('{$securitygroupid}', '{$id}');");
        return $conn->getInsertId();
    }

    /**
     * Removes a Link to security group with a subscriber
     * @param int $securitygroupid
     * @param String $subid The subscriber id
     * @throws PublishingException on serious error
     */
    public function unlinkToSubscriber($securitygroupid, $subid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid, TRUE);
        $subid = DAL::applyFilter($subid, TRUE);
        $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");

        // Add the feed
        $conn->executeNonQuery("DELETE FROM {$table} WHERE securitygroup_id = '{$securitygroupid}' AND subscriber_id = '{$subid}';");
    }

    /**
     * Links a security group with a feed
     * @param int $securitygroupid
     * @param String $feedid The feed id
     * @throws PublishingException on serious error
     */
    public function linkToGroup($securitygroupid, $feedid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid, TRUE);
        $feedid = DAL::applyFilter($feedid);
        $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");

        // Add the feed
        $conn->executeNonQuery("INSERT INTO {$table} (securitygroup_id, feed_id) VALUES('{$securitygroupid}', '{$feedid}');");
        return $conn->getInsertId();
    }

    /**
     * Removes a Link to security group with a feed
     * @param int $securitygroupid
     * @param String $feedid The feed id
     * @throws PublishingException on serious error
     */
    public function unlinkToGroup($securitygroupid, $feedid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid, TRUE);
        $feedid = DAL::applyFilter($feedid);
        $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");

        // Add the feed
        $conn->executeNonQuery("DELETE FROM {$table} WHERE securitygroup_id = '{$securitygroupid}' AND feed_id = '{$feedid}';");
    }

    /**
     * Returns an array of feed ids that are associated with the securitygroupid currently
     * @param int $securitygroupid  The security group id to load for
     * @return  Array(INT)  feed ids that are associated
     */
    public function getFeedsAssignedToGroup($securitygroupid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid, TRUE);
        $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");
        $array = array();
        
        // Create query
        $result = $conn->executeQuery("SELECT feed_id FROM {$table} WHERE securitygroup_id = '{$securitygroupid}';");

        while( ($row = $result->fetchAssoc()) !== NULL) {
            $array[] = $row['feed_id'];
        }

        return $array;
    }

    /**
     * Simple wrapper around private method _getRealSSID($ssid)
     * @param <type> $ssid The SSID
     */
    public function getSSIDParts($ssid)
    {
        return $this->_getRealSSID($ssid, TRUE);
    }

    /**
     * Returns an array of security group ids that are associated with the securitygroupid currently
     * @param int $id  The group subscriber id to load for
     * @return  Array(INT)  int of ids
     */
    public function getGroupsAssignedToSubscriber($id)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $id = DAL::applyFilter($id, TRUE);
        $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        $array = array();

        // Create query
        $result = $conn->executeQuery("SELECT securitygroup_id FROM {$table} WHERE subscriber_id = '{$id}';");

        while( ($row = $result->fetchAssoc()) !== NULL) {
            $array[] = $row['securitygroup_id'];
        }

        return $array;
    }


    /**
     * Modifies the subscriber state
     *
     * @param       Int             The state, use one of the constants -> TypeObject.O_SUSPENDED or TypeObject.O_OK or O_AWAITINGAPPROVAL
     * @param       String          SSID
     */
    public function modifySubscriberState($state, $ssid, $nid=FALSE)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");
        $state = DAL::applyFilter($state);
        
        if($nid === FALSE) {
            // Is ita valid subscriber
            if($this->_ssidValid($ssid) === FALSE)
            {
                throw new PublishingException("Invalid ssid");
            }

            // Valid, get id for data
            $mixed = $this->_getRealSSID($ssid);
            $ssid = DAL::applyFilter($mixed['SSID']);
            $sql = "UPDATE {$table} SET mode = '{$state}' WHERE ssid='{$ssid}';";
        }
        else {
            $id = DAL::applyFilter($ssid, TRUE);
            $sql = "UPDATE {$table} SET mode = '{$state}' WHERE id='{$id}';";
        }
  
        // What state is it
        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
    }

    /**
     * Mails an attachment
     * @param <type> $name  The name of the file
     * @param <type> $data Data
     * @param <type> $mailto
     * @param <type> $from_mail
     * @param <type> $from_name
     * @param <type> $replyto
     * @param <type> $subject
     * @param <type> $message
     */
    private function mail_attachment($name, $data, $mailto, $from_mail, $from_name, $replyto, $subject, $message)
    {
        // Create the content
        $content = chunk_split(base64_encode($data));
        $uid = md5(uniqid(time()));
        $header = "From: ".$from_name." <".$from_mail.">\r\n";
        $header .= "Reply-To: ".$replyto."\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $message."\r\n\r\n";
        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/octet-stream; name=\"".$name."\"\r\n"; // use different content types here
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$name."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
        $header .= "--".$uid."--";
        mail($mailto, $subject, "", $header);
#        if (mail($mailto, $subject, "", $header)) {
    }

    /**
     * Returns the public subscription bitcode
     *
     * @return String       The encoded link string
     */
    public function getPublicSubscriptionLink()
    {
        global $_CONF;
        
        return base64_encode("{$_CONF['site_url']}");
    }

    /**
     * Approves a subscriber awaiting approval
     * - Emails the user with the SSID
     * - Sets the mode to approved
     *
     * @param   String      email       The email to send to
     * @param   String      ssid        The SSID
     * @param   Integer     id          The id of the subscriber
     * @param   String      url         The URL to access
     */
    public function approveSubscriber($email, $ssid, $id, $url)
    {
        // Declare Variables
        global $_CONF;
        
        $email = DAL::applyFilter($email);
        $ssid = DAL::applyFilter($ssid);
        $id = (int)DAL::applyFilter($id, TRUE);
        $headers = "From: {$_CONF['site_mail']}\r\nReply-To: {$_CONF['site_mail']}";
        $headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
        $date = date('r', time());
        $file = base64_encode("{$ssid}?{$_CONF['site_url']}?{$date}?");
        // Read attachment daa into chunks
        // 
        // Send out the email
        $message = <<<NESS
<b>{$_CONF['site_name']} Publishing Control Subscription Approval</b>
<br /><br />
Your application to apply to subscription site `{$_CONF['site_url']}` has been approved!
<br />
<br />
Click below to finish the subscription process
<br />
<br />
<a href="{$url}/admin/plugins/pubcontrol/recv.php?cmd=49&t={$file}">Subscribe</a>
<br />
<br />
Thanks - {$_CONF['site_name']} admin
NESS;
    
    COM_mail($email, "{$_CONF['site_name']} Publishing Control Subscription Activated", $message, $_CONF['site_mail'], TRUE);
    #$this->mail_attachment("ssid.pcb", $file, $email, $_CONF['site_mail'], $from_name, $_CONF['site_mail'], "{$_CONF['site_name']} Publishing Control Subscription Activated", $message);
    #mail($email, "{$_CONF['site_name']} Publishing Control Subscription Activated", $message, $headers);

    
    }
}

/**
 * This class holds the methods that deal with requests to obtain data or send commands
 */
class PublishingControl
{
    // Private Variables
    private $_QueryBits = array();

    
    // Public Methods

    /**
     * Parses the query string that is passed as part of the URL response (the GET and POST headers).
     *
     * @throws      PublishingException         Invalid query string, fatal error
     */
    public function parseQueryString()
    {
        // Declare Variables

        // Try to grab the data
        $this->_QueryBits['cmd'] = (isset($_GET['cmd'])) ? (int) $_GET['cmd'] : 0;

        if($this->_QueryBits['cmd'] === 0)
        {
            throw new PublishingException("Invalid query string");
        }
    }

    /**
     * As long as the query string was parsed properly, the command is then executed
     * Note: If it is a request command to return data, the data is returned as a string. If nothing is returned, an empty string is returned.
     * This could be the actual XML response or raw data response depending on what the command expects. Simply print the output of this method
     *
     * @param       Boolean                     If set to true, the contents will not be returned and instead printed. otherwise it will be returned as a string.
     * @param       Boolean                     (Optional) If true, then all security checks are not performed for the feed data
     * @return      String                      The data or ' '
     * @throws      PublishingException         Invalid query string, fatal error
     */
    public function doOperation($print=TRUE, $bypasssecurity=FALSE)
    {
        // Declare Variables
        $sec = new PublishingSecurityManagement();
        $buffer = new BufferedCharacterWriter($print);
        $feedobj = new PublishingFeedControl();
        $group_obj = new PublishingGroupControl();
        $sec_obj = new PublishingSecurityManagement();

       try
        {
            // Get the command, and depending on what it is branch of
            switch($this->_QueryBits['cmd'])
            {
                // GET FEED DATA
                case CMD_GET_FEED_DATA:
                    // Grab feed data, required is: Feed Id, and Possible SSID
                    $feedid = (isset($_GET['id'])) ? $_GET['id'] : 0;
                    $ssid = (isset($_GET['ssid'])) ? $_GET['ssid'] : NULL;
                    $lmod = (int)(isset($_GET['lmod'])) ? $_GET['lmod'] : 0;

                    if($bypasssecurity === FALSE)
                    {
                        // Verify
                        if($sec->verify($ssid, $feedid) === FALSE)
                        {
                            // Verification failed, they do not have the necessary permissions to view this content
                            $buffer->write(":-1:");
                            return;
                        }
                    }

                    // Grab feed data
                    $fdata = $feedobj->listFeeds($feedid, true);

                    if(isset($_GET['fmt']) && ($_GET['fmt'] === "headers")) {
                        $date = date ("F d Y H:i:s.", $fdata[0]->_DateLastUpdated);
                        header("Last-Modified: {$date}");
                        exit;
                    }

                    // Write out feed data
                    $buffer->write("
                       <xml version='1.0' encoding='utf-8'>
                        <feed xmlns='http://www.w3.org/2005/Atom'>
                       <title>{$fdata[0]->_Title}</title>
                       <id>tag:site_url,date:{$fdata[0]->_AccessCode}.{$fdata[0]->_Id}.{$fdata[0]->_GroupId}</id>
                       <updated></updated>
                       <link></link>
                    ");

                   // Store data in a buffer
                    $data = new PublishingDataControl();
                    $result = $data->getDataForFeed($feedid, FALSE, $lmod);
                    if($result === NULL) {
                        $buffer->write("</feed></xml>");

                        return $buffer->getOutput();
                    }
                    
                    foreach($result as $obj)
                    {
                        $buffer->write("
                        <entry>
                            <title>{$obj->_Title}</title>
                            <link></link>
                            <id>tag:0,0:{$obj->_Id}</id>
                            <published>{$obj->_DateCreated}</published>
                            <updated>{$obj->_DateLastUpdated}</updated>
                            <content>
                        <![CDATA[
                                {$obj->_Content}
                        ]]>
                        </content>
                        <author>
                        "
                        );

                        // And now print out the authors
                        foreach($obj->_Authors as $author)
                        {
                            $buffer->write("<name>{$author}</name>");
                        }

                        $buffer->write("</author><contributor>");
                        // And now print out the cont
                        foreach($obj->_Contributors as $author)
                        {
                            $buffer->write("<name>{$author}</name>");
                        }
                        $buffer->write("</contributor></entry>");

                    }

                    $buffer->write("</feed></xml>");

                    return $buffer->getOutput();
                case CMD_LIST_FEEDS:
                    // Grab a list of all feeds (either by group or all feeds)
                    $gid = (isset($_GET['gid'])) ?  (int)$_GET['gid'] : 0;
                    $fid = (isset($_GET['fid'])) ? (int)$_GET['fid'] : 0;

                    if($gid === 0)
                    {
                        if($fid === 0)
                        {
                            // Group id not specified
                            $result = $feedobj->listFeeds();
                        }
                        else
                        {
                            $result = $feedobj->listFeeds($fid, TRUE);
                        }
                    }
                    else
                    {
                        $result = $feedobj->listFeeds($gid);
                    }

                    // And now list all feeds
                    $buffer->write("
                       <xml version='1.0' encoding='utf-8'>
                        <feed xmlns='http://www.w3.org/2005/Atom'>
                       <title>List of feeds</title>
                       <id>tag:0,0:1</id>
                       <updated></updated>
                       <link></link>
                    ");

                    // Loop over the data
                    foreach($result as $fobj)
                    {
                        $buffer->write("
                            <entry>
                                <title>{$fobj->_Title}</title>
                                <link></link>
                                <id>tag:0,0:{$fobj->_GroupId}.{$fobj->_Id}.{$fobj->_Type}.{$fobj->_AccessCode}</id>
                                <published></published>
                                <summary>{$fobj->_Summary}</summary>
                            </entry>
                        ");
                    }

                    $buffer->write("</feed></xml>");
                    return $buffer->getOutput();
                case CMD_LIST_GROUPS:
                    // And now list all groups
                    $buffer->write("
                       <xml version='1.0' encoding='utf-8'>
                        <feed xmlns='http://www.w3.org/2005/Atom'>
                       <title>List of groups</title>
                       <id>tag:0,0:1</id>
                       <updated></updated>
                       <link></link>
                    ");

                    $result = $group_obj->listGroups();
                    
                    // Loop over the data
                    foreach($result as $fobj)
                    {
                        $buffer->write("
                            <entry>
                                <title>{$fobj->_Title}</title>
                                <link></link>
                                <id>tag:0,0:1.{$fobj->_Id}.{$fobj->_Type}</id>
                                <published></published>
                                <summary>{$fobj->_Summary}</summary>
                            </entry>
                        ");
                    }

                    $buffer->write("</feed></xml>");
                    return $buffer->getOutput();
                case CMD_SUBSCRIBE:
                    // UnSubscribe to the database
                    // Grab url, identifier
                    $ssid = (isset($_GET['ssid'])) ? $_GET['ssid'] : NULL;
                    $redir = (isset($_GET['redir'])) ? $_GET['redir'] : NULL;;
                    
                    if($ssid === NULL)
                    {
                        header("Location: {$redir}?msg=104");
                        exit;
                    }

                    // $var = 10; Foo:()&(%~!!$var)
                    $f = new PublishingSecurityManagement();
                    $f->unsubscribe($ssid);
                    header("Location: {$redir}?msg=105");
                    break;
            }
        }
        catch(PublishingException $e)
        {
            $buffer->write(":-2:");
        }
    }
}



/**
 * A static class that holds type object constants
 */
class TypeObject
{
     const O_PRIVATE = 1;
     const O_PUBLIC = 2;
     const O_INHERITED = 3;
     const O_SUSPENDED = 'S';
     const O_OK = 'Y';
     const O_AWAITINGAPPROVAL = 'A';

     /**
      * Returns a type object or NULL on error
      * @param  Int     $int        The integer to convert
      * @return                     The Integer TypeObject
      */
     public static function makeTypeFromInteger($int)
     {
         switch($int) {
             case 1:
                 return TypeObject::O_PRIVATE;
             case 2:
                 return TypeObject::O_PUBLIC;
             case 3:
                 return TypeObject::O_INHERITED;
             case 'S':
                 return TypeObject::O_SUSPENDED;
             case 'Y':
                 return TypeObject::O_OK;
             default:
                return NULL;
                 
         }
     }

     /**
      * Returns the string name equivalent for an integer representing the type object
      * @param <type> $int
      * @return <type>
      */
     public static function getNameForInteger($int)
     {
          switch($int) {
             case 1:
                 return 12;
             case 2:
                 return 13;
             case 3:
                 return 16;
             case 'S':
                 return 17;
             case 'Y':
                 return 18;
             default:
                return 1;

         }
     }
}


/**
 * Holds information about a security group
 */
class SecurityObject
{
    /**
     * The id of the group
     * @var int
     */
    public $_Id = NULL;

    /**
     * The title of the group
     * @var string
     */
    public $_Title = NULL;

    /**
     * A short summary of the group
     * @var String
     */
    public $_Summary = NULL;

    /**
     * The color of the group, in HEX format
     * @var String
     */
    public $_Color = NULL;
}

/**
 * Holds information about a specific feed
 */
class FeedObject
{
    /**
     * The id of the feed
     * @var int
     */
    public $_Id = NULL;

    /**
     * Its parent group id
     * @var int
     */
    public $_GroupId = NULL;

    /**
     * The title of the feed
     * @var string
     */
    public $_Title = NULL;

    /**
     * A short summary of the feed
     * @var String
     */
    public $_Summary = NULL;

    /**
     * The type of the feed
     * @var TypeObject
     */
    public $_Type = NULL;

    /**
     * The access code of the feed
     * @var AccessCode
     */
    public $_AccessCode = NULL;

     /**
     * The time it was last updated
     * @var TimeStamp as an int
     */
    public $_DateLastUpdated = NULL;

    /**
     * Verifies to see if the data is valid
     *
     * This method will search over each type and make sure it is not NULL, and that are valid values
     * @return TRUE on success, FALSE on error
     */
    public function verify()
    {
        if( ($this->_GroupId === NULL) || ((int)$this->_GroupId === 0) || ($this->_Summary === NULL) || ($this->_Summary === "")
                || ($this->_Title === NULL) || ($this->_Title === "")
                || ( ( (int) $this->_Type) === 0) || ($this->_AccessCode === NULL) || ($this->_AccessCode === ""))
        {
            return false;
        }

        return true;
    }

}


/**
 * Holds information about a specific group
 */
class GroupObject
{
    /**
     * The id of the group
     * @var int
     */
    public $_Id = NULL;

    /**
     * The title of the group
     * @var string
     */
    public $_Title = NULL;

    /**
     * A short summary of the group
     * @var String
     */
    public $_Summary = NULL;

    /**
     * The type of the group
     * @var TypeObject
     */
    public $_Type = NULL;

    /**
     * Verifies to see if the data is valid
     *
     * This method will search over each type and make sure it is not NULL, and that are valid values
     * @return TRUE on success, FALSE on error
     */
    public function verify()
    {
        if( ($this->_Summary === NULL) || ($this->_Summary === "")
                || ($this->_Title === NULL) || ($this->_Title === "")
                || ( ( (int) $this->_Type) === 0))
        {
            return false;
        }

        return true;
    }

}

/**
 * Holds access code information
 */
class AccessCode
{
    /**
     * The name of the access code (what it deals with)
     * @var String
     */
    public $_Name = "";

    /**
     * The access code
     * @var String
     */
    public $_AccessCode = "";
}

/**
 * Provides methods for dealing with access codes
 */
class AccessCodeControl
{
    /**
     * Returns a list of access codes that are currently available
     */
    public static function getAccessCodes()
    {
        // Declare Variables
        $conn = new DAL();
        $accesscodearray = array();
        $arp = NULL;
        $table = DAL::getFormalTableName("pubcontrol_Accesscodes");

        // Make up query
        $sql = "SELECT * FROM {$table};";

        // Execute Query
        $result = $conn->executeQuery($sql);

        // And now populate data
        while( ($row = $result->fetchAssoc()) !== NULL)
        {
            $arp = new AccessCode();
            $arp->_Name = $row['name'];
            $arp->_AccessCode = $row['access_code'];
            $accesscodearray[] = $arp;
        }

        return $accesscodearray;
    }
}


/**
 * Holds information about a data entry for a feed
 */
class DataObject
{
    /**
     * The id of the data object
     * @var int
     */
    public $_Id = NULL;

    /**
     * The feed id that the data object is associated with
     * @var int
     */
    public $_FeedId = NULL;

    /**
     * The title of the data object
     * @var String
     */
    public $_Title = NULL;

    /**
     * The content of the data object
     * @var char[] Array of characters to support binary data
     */
    public $_Content = NULL;

    /**
     * The time it was created
     * @var TimeStamp as an int
     */
    public $_DateCreated = NULL;

    /**
     * The time it was last updated
     * @var TimeStamp as an int
     */
    public $_DateLastUpdated = NULL;

    /**
     * An array of authors (full names)
     * @var String[]
     */
    public $_Authors = Array();

    /**
     * An array of contributors (full names)
     * @var String[]
     */
    public $_Contributors = Array();


    /**
     * Verifies to see if the data is valid
     *
     * This method will search over each type and make sure it is not NULL, and that are valid values
     * @return TRUE on success, FALSE on error
     */
    public function verify()
    {
        if( ($this->_Content === NULL) || ($this->_Content === "")
                || ($this->_Title === NULL) || ($this->_Title === "")
                || ( $this->_FeedId === NULL) || ( $this->_FeedId === ""))
        {
            return false;
        }

        return true;
    }

}



/****
 * Receiving Control Panel -> Provides
 *
 *
 */

?>
