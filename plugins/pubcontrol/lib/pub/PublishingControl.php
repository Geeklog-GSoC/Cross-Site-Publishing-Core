<?php
/**
 * @package PublishingControl
 * @author Tim Patrick
 * @license GPL V3
 * @copyright 2010 Tim Patrick
 * @version 0.1
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
     * @param GroupObject $groupobject A group object filled with the data
     * @return int                      The insertion id
     * @throws PublishingException on failure to create
     */
    public function createGroup($groupobject)
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

        $sql = "INSERT INTO {$table} (title, summary, type) VALUES('{$groupobject->_Title}','{$groupobject->_Summary}','{$groupobject->_Type}');";
        
        // Send out the query
        $conn->executeNonQuery($sql);
        return $conn->getInsertId();
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
        $sql = "DELETE FROM {$table} WHERE id = '{$groupid};";
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

        $sql = "INSERT INTO {$table} (group_id, title, summary, type, access_code)
                VALUES('{$feedobject->_GroupId}', '{$feedobject->_Title}','{$feedobject->_Summary}','{$feedobject->_Type}', '{$feedobject->_AccessCode}');";

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
                '{$DataObject->_DateCreated}', '{$DataObject->_DateLastUpdated}', '{$authors}', '{$cont}');";
        $conn->executeNonQuery($sql);
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
                date_created = '{$DataObject->_DateCreated}', date_lastupdated = '{$DataObject->_DateLastUpdated}', authors = '{$authors}', contributors = '{$cont}' WHERE id = '{$dataid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Removes a data entry from the feed
     * @param  $dataid The data object to delete
     * @throws PublishingException on serious error
     */
    public function deleteData($dataid)
    {
        // Grab connection and create variables
        $conn = $this->_Conn;
        $dataid = DAL::applyFilter($dataid, true);
        $table = DAL::getFormalTableName("pubcontrol_Data");

        // Must be ok, send deletion request
        $sql = "DELETE FROM {$table} WHERE id = '{$dataid}';";
        $conn->executeNonQuery($sql);
    }

    /**
     * Gets all the data objects for a specific feed.
     *
     * @param int $feedid The feed id to get data for
     * @return DataObject[] Array of data objects associated with that feed
     * @throws PublishingException on serious error
     */
    public function getDataForFeed($feedid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Data");
        $feedid = DAL::applyFilter($feedid, true);
        $dataobjects = array();
        $dsg = NULL;

        // Send SQL query
        $sql = "SELECT * FROM {$table} WHERE feed_id = '{$feedid}';";
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

        return $dataobjects;
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
     * @param       String $ssid            The SSID to
     * @return      Mixed                   Associative array [ URL, IDENTIFIER, PIN, ID, SSID ]
     * @throws      PublishingException     on serious error
     */
    private function _getRealSSID($ssid)
    {
        // Decouple the hash (always the last A)
        $pos = strrpos($ssid, 'A');
        $mixed = array();
        
        if($pos === FALSE)
        {
            throw new PublishingException("Invalid SSID format passed");
        }

        // Split the string on that location
        $ssid2 = substr($ssid, 0, $pos);
        $idl = substr($ssid, $pos);
        
        $mixed['SSID'] = $ssid2;
        
        // And now decode the data
        // urlencode(base64_encode('%' . $url . '%' . $identifier . '%' . $pin . '#' . md5($url . 'D' . $identifier . 'E' . $pin . 'F')));
        $ssid2 = urldecode(base64_decode($ssid2));

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
     * @return  Boolean         True on valid, false on negative
     */
    private function _ssidValid($ssid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");
        
        // Grab the data
        $mixed = $this->_getRealSSID($ssid);
        $ssid = DAL::applyFilter($mixed['SSID']);

        $result = $conn->executeQuery("SELECT state FROM {$table} WHERE ssid = '{$ssid}';");
        $row = $result->fetchAssoc();

        if( ($row === NULL) || ($row['state'] !== 'Y'))
        {
            return false;
        }

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
        $num = $conn->count("pubcontrol_Subscribers", "security_id", $securityid);

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
    public function subscribe($url, $identifier, $pin)
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
        
        $sql = "INSERT INTO {$table} (url, identifier, pin, ssid) VALUES('{$url}','{$identifier}','{$pin}', '{$ssid}');";
        $conn->executeNonQuery($sql);
        $ssid .= 'A'.$conn->getInsertId();
        return $ssid;
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
        
        // Get the ssid
        $mixed = $this->_getRealSSID($ssid);
        $ssid = DAL::applyFilter($mixed['SSID']);

        // Remove from database
        $conn->executeNonQuery("DELETE FROM {$table} WHERE ssid = '{$ssid}';");
        
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
        $mixed = $this->_getRealSSID($ssid);

        if($this->_ssidValid($ssid) === FALSE)
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
        $id = DAL::applyFilter($mixed['ID'], true);
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
    public function linkToSubscriber($securitygroupid, $ssid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $securitygroupid = DAL::applyFilter($securitygroupid);
        $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
        
        // Is ita valid subscriber
        if($this->_ssidValid($ssid) === FALSE)
        {
            throw new PublishingException("Invalid ssid");
        }

        // Valid, get id for data
        $mixed = $this->_getRealSSID($ssid);
        $id = DAL::applyFilter($mixed['ID'], true);

        // Add the subscriber
        $conn->executeNonQuery("INSERT INTO {$table} (securitygroup_id, subscriber_id) VALUES('{$securitygroupid}', '{$id}');");
        return $conn->getInsertId();
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
        $securitygroupid = DAL::applyFilter($securitygroupid);
        $feedid = DAL::applyFilter($feedid);
        $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");

        // Add the feed
        $conn->executeNonQuery("INSERT INTO {$table} (securitygroup_id, feed_id) VALUES('{$securitygroupid}', '{$feedid}');");
        return $conn->getInsertId();
    }

    /**
     * Modifies the subscriber state
     *
     * @param       Int             The state, use one of the constants -> TypeObject.O_SUSPENDED or TypeObject.O_OK
     * @param       String          SSID
     */
    public function modifySubscriberState($state, $ssid)
    {
        // Declare Variables
        $conn = $this->_Conn;
        $table = DAL::getFormalTableName("pubcontrol_Subscribers");

        // Is ita valid subscriber
        if($this->_ssidValid($ssid) === FALSE)
        {
            throw new PublishingException("Invalid ssid");
        }

        // Valid, get id for data
        $mixed = $this->_getRealSSID($ssid);
        $ssid = DAL::applyFilter($mixed['SSID']);
        $state = DAL::applyFilter($state);
        
        // What state is it
        $conn->executeNonQuery("UPDATE {$table} SET state = '{$state}' WHERE ssid='{$ssid}';");
        return $conn->getInsertId();
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
     * @return      String                      The data or ' '
     * @throws      PublishingException         Invalid query string, fatal error
     */
    public function doOperation($print=TRUE)
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

                    // Verify
                    if($sec->verify($ssid, $feedid) === FALSE)
                    {
                        // Verification failed, they do not have the necessary permissions to view this content
                        $buffer->write(":-1:");
                    }

                    // Grab feed data
                    $fdata = $feedobj->listFeeds($feedid, true);

                    // Write out feed data
                    $buffer->write("
                       <?xml version='1.0' encoding='utf-8'?>
                        <feed xmlns='http://www.w3.org/2005/Atom'>
                       <title>{$fdata[0]->_Title}</title>
                       <id>tag:site_url,date:{$fdata[0]->_AccessCode}.{$fdata[0]->_Id}.{$fdata[0]->_GroupId}</id>
                       <updated></updated>
                       <link></link>
                    ");

                   // Store data in a buffer
                    $data = new PublishingDataControl();
                    $result = $data->getDataForFeed($feedid);

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

                    $buffer->write("</feed>");

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
                       <?xml version='1.0' encoding='utf-8'?>
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
                                <id>tag:0,0:{$fobj->_GroupId}.{$fobj->_Id}.{$fobj->_Type}</id>
                                <published></published>
                                <summary>{$fobj->_Summary}</summary>
                            </entry>
                        ");
                    }

                    $buffer->write("</feed>");
                    return $buffer->getOutput();
                case CMD_LIST_GROUPS:
                    // And now list all groups
                    $buffer->write("
                       <?xml version='1.0' encoding='utf-8'?>
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

                    $buffer->write("</feed>");
                    return $buffer->getOutput();
                case CMD_SUBSCRIBE:
                    // Subscribe to the database
                    // Grab url, identifier
                    $url = (isset($_GET['url'])) ?  $_GET['url'] : NULL;
                    $iden = (isset($_GET['iden'])) ? $_GET['iden'] : NULL;

                    if( ($url === NULL) || ($iden === NULL))
                    {
                        $buffer->write(":-3:");
                    }

                    // $var = 10; Foo:()&(%~!!$var)
                    
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
    public $_Authors = NULL;

    /**
     * An array of contributors (full names)
     * @var String[]
     */
    public $_Contributors = NULL;


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
                || ( $this->_DateCreated === NULL) || ( $this->_DateCreated === "")
                || ( $this->_DateLastUpdated === NULL) || ( $this->_DateLastUpdated === "")
                || ( $this->_FeedId === NULL) || ( $this->_FeedId === ""))
        {
            return false;
        }

        return true;
    }

}

?>
