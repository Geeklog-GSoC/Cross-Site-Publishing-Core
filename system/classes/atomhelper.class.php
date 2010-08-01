<?php


define("ATOMWRITER_FILE", 0x02);
define("ATOMWRITER_PRINT", 0x04);
define("ATOMWRITER_STRING", 0x08);

/**
 * This class manages writing of ATOM feeds
 *
 * You can choose to write to a file, console, or string buffer. 
 *
 */
class AtomWriter
{
    /*
     * For Developers of the class
     * The output type values are as follows
     * 0x02 = FILE
     * 0x04 = PRINT
     * 0x08 = STRING BUFFER
     *
     */

    // Private Methods
    private $_Handle = NULL;
    private $_OutputType = 0x00;
    private $_Buffer = "";


    /**
     * Writes out data entries to the selected outputs
     */
    private function writeToOutputs($data)
    {
        // Check which outputs
        // String first, print, then file
        if(($this->_OutputType & ATOMWRITER_STRING) === ATOMWRITER_STRING) {
            $this->_Buffer .= $data;
        }
        else if(($this->_OutputType & ATOMWRITER_PRINT) === ATOMWRITER_PRINT) {
            echo $data;
        }
        else if(($this->_OutputType & ATOMWRITER_FILE) === ATOMWRITER_FILE) {
            fwrite($this->_Handle, $data);
        }
    }

    // Public methods

    /**
     * Constructor
     */
    public function __construct()
    {
        
    }

    /**
     * This method flushes the memory buffer so a new ATOM file can be processed. You should always call this between processing atom files, otherwise there may be residue.
     *
     * @param   Boolean     $clearv=false        (Optional) If this is set to TRUE, then the output values option will be cleared
     */
    public function flushData($clearv=FALSE)
    {
        $this->_Buffer = "";
        $this->_Handle = NULL;

        if($clearv === TRUE) {
            $this->_OutputType = 0x00;
        }
        
    }

    /**
     * Set the output types that the atom writer will use
     * Simply OR values togethor to chain more than one.
     * Acceptable values are: ATOMWRITER_FILE, ATOMWRITER_PRINT, ATOMWRITER_STRING
     * for writing to a file (make sure to open the file using openFile), printint to the output console directly, and writing to a string that can be retreived with getOutputString()
     *
     * @param   Integer     $output     The values to set
     */
    public function setOutputValues($output)
    {
        $this->_OutputType = $output;
    }


    /**
     * Opens a file for writing the XML data into. 
     *
     * @param   String      $file       The file to open
     * @return  Boolean                 True on success, False on failure
     */
    public function openFile($file)
    {
        // Open the file
        $this->_Handle = fopen($file, "w");

        if($this->_Handle === NULL) {
            return false;
        }

        $this->_OutputType = $this->_OutputType | ATOMWRITER_FILE;
        return true;
    }

    /**
     * Closes the open stream
     * Always call this method at the end of writing to commit the changes
     * 
     */
    public function closeStream()
    {
        if(($this->_OutputType & ATOMWRITER_FILE) === ATOMWRITER_FILE) {
            fclose($this->_Handle);
        }
    }

    /**
     * If string is an output type, then this function will return a output string of the data
     *
     * @return      String              The output string or NULL if it is not set
     */
    public function getOutputString()
    {
        // If the
        return $this->_Buffer;
    }

    /**
     * Writes the top of the feed out to the output types
     * Note: If one write fail, the others will still work
     * NOTE: Do NOT place entry data inside the AtomFeed object's Entries field. Use instead the writeEntry method. Any data inside the Entries field WILL be ignored. This is to protect memory resources. 
     * 
     * @param   AtomFeed        $AtomFeedObject         The atom feed object that holds information about the feed to write
     */
    public function writeTopData($AtomFeedObject)
    {
        $topdata = "
                    <?xml version='1.0' encoding='utf-8'?>
                    <feed xmlns='http://www.w3.org/2005/Atom'>
                    <title>{$AtomFeedObject->_Title}</title>
                    <id>{$AtomFeedObject->_Id}</id>
                    <updated>{$AtomFeedObject->_Updated}</updated>
                    <link>{$AtomFeedObject->_Link}</link>
                   ";

        // Write out
        $this->writeToOutputs($topdata);

    }

    /**
     * Writes an entry out to the output type(s)
     *
     * @param   AtomFeedEntry        $AtomFeedEntryObject         The atom feed entry object that holds information about the feed entry to write
     */
    public function writeEntry($AtomFeedEntryObject)
    {
        // Set entry data
        $entrydata = "
                    <entry>
                        <title>{$AtomFeedEntryObject->_Title}</title>
                        <link></link>
                        <id>{$AtomFeedEntryObject->_Id}</id>
                        <published>$AtomFeedEntryObject->_Published}</published>
                        <updated>{$AtomFeedEntryObject->_Updated}</updated>
                    ";
        if($AtomFeedEntryObject->_Content !== NULL) {
            $entrydata .= "
                                 <content>
                            <![CDATA[
                                    {$AtomFeedEntryObject->_Content}
                            ]]>
                            </content>
                          ";
        }
        else {
            $entrydata .= "
                            <summary>{$AtomFeedEntryObject->_Summary}</summary>
                          ";
        }

        #### DEBUG FOR AUTHORS AND CONTRIBUTORS
        

        // Write out
        $this->writeToOutputs($entrydata . "</entry>");
    }

    /**
     * Writes the ending tags and formalizes the structure.
     * This MUST be called to arrange the data structures.
     * 
     */
    public function writeFinish()
    {
        // Write out
        $this->writeToOutputs("</feed>");
    }

    
}



/**
 * This class holds a implementation of the simple XML parser library
 * As such, requires PHP 5.0
 *
 *
 * To use, simply invoke the class:
 * eg:
 * $class = new AtomReader();
 *
 * Check if it has changed
 * if($class->isNewData("URL", "TIME") !== 0)  { // old data or error
 *
 * $class->loadFile("URL");
 * $AtomFeedObject = $class->getFeedTopData();
 * $AtomFeedEntry = $class->getNextEntry();
 * while($AtomFeedEntry !== NULL) {
 *  $AtomFeedEntry = $class->getNextEntry();
 *  
 * }
 *
 * Note: While the AtomFeed object has an entry array, by default this is left as NULL and the default use of looping to get the entry data should be used as this uses less memory.
 * However, calling the getFeedData will load all the data into the object.
 */
class AtomReader
{
    // Private Variables
    private $_Handle = NULL;
    private $_EntryInterator = 0;
    private $_EntryExtractArray = NULL;

    // Makes sure the atom feed is valid
    private function _isValid()
    {
        if($this->_Handle === NULL) {
            throw new Exception("Error: AtomParser::Atom feed not loaded");
        }
    }

    /**
     * Searches through the entry extraction keys, and if it is allowed returns the value, otherwise NULL
     *
     * @param   String      $key        The key to search for
     * @param   String      $value      The value to use if found
     */
    private function searchKeys($key, $value)
    {
        // Set found
        $found = false;
        
        // Search through the keys
        foreach($this->_EntryExtractArray as $nn) {
            if($key === $nn) {
                $found = true;
            }
        }

        return ($found === TRUE) ? $value : NULL;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        
    }

    /**
     * Reads in the data content headers to determine if the data has been modified since
     *
     * @param   String      $url        The URL to check for -> Do not add the header check on it, this will automatically be added
     * @param   String      $olddate    UnixTimestamp of the old date
     * @return  Integer                 0 if there is new data, -1 if there is no last modified header, -2 if the date cannot be parsed, -3 if the URL does not exist, 1 if the time does not match
     */
    public static function isNewData($url, $olddate)
    {
        // Grab the headers from the url
        $headers = get_headers(urlencode($url . '&fmt=headers'), 1);

        if($headers === FALSE) {
            return -3;
        }

        // Check the last modified tag
        if(!($headers['Last-Modified'])) {
            return -1;
        }

        // Convert the time
        $time = strtotime($headers['Last-Modified']);

        if($time === FALSE) {
            return -2;
        }

        // Now match the times
        if($olddate < $time) {
            return 0;
        }
        else {
            return 1;
        }
    }

    /**
     * Loads a valid ATOM XML file into memory (just the handle)
     *
     * @param   String      file        The XML file to load
     * @return  Boolean                 True on success, False on failure
     * 
     */
    public function loadFile($file)
    {
        // Attempt to open the file
        $this->_Handle = @simplexml_load_file($file);

        if($this->_Handle === FALSE) {
            throw new Exception("Cannot load XML FILE {$file}");
        }
    }

    /**
     * Gets all the atom data (including entries) from the feed and loads them into an AtomFeed object.
     * Note: This method is MORE memory intensive than the others, and the getNextEntry method should be used instead.
     *
     * @return  AtomFeed        An AtomFeed object with all the data
     */
    public function getFeedData()
    {
        // Get the top data
        $top = $this->getFeedTopData();
        $top->_Entries = array();
        
        // Loop over entries
        $AtomFeeds = $this->getNextEntry();
        while($AtomFeeds !== NULL) {
            $top->_Entries[] = $AtomFeeds;
            $AtomFeeds = $this->getNextEntry();
        }

        return $top;
    }

    /**
     * Gets the next ATOM entry, or NULL if no more exists
     *
     * @return  AtomFeedEntry       OR NULL on end of entries
     */
    public function getNextEntry()
    {
        // Is it at its limit
        if(count($this->_Handle->feed->entry) === $this->_EntryInterator) {
            return NULL;
        }

        $obj = new AtomFeedEntry();
        $bj = $this->_Handle->feed->entry[$this->_EntryInterator];

        // Are we doing a direct entry process
        if($this->_EntryExtractArray === NULL) {
            $obj->_Title = $bj->title;
            $obj->_Link = $bj->link;
            $obj->_Id = $bj->id;
            $obj->_Updated = $bj->updated;
            $obj->_Summary = $bj->summary;
            $obj->_Content = $bj->content;
            $obj->_Published = $bj->published;
        }
        else {
            $obj->_Title = $this->searchKeys('title', $bj->title);
            $obj->_Link = $this->searchKeys('link', $bj->link);
            $obj->_Id = $this->searchKeys('id', $bj->id);
            $obj->_Updated = $this->searchKeys('updated', $bj->updated);
            $obj->_Summary = $this->searchKeys('summary', $bj->summary);
            $obj->_Content = $this->searchKeys('content', $bj->content);
            $obj->_Published = $this->searchKeys('published', $bj->published);
        }

        // Loop over the author data
        // PAUSED, due to issues with validation

        $this->_EntryInterator = $this->_EntryInterator + 1;
        return $obj;
    }

    /**
     * It is possible to determine which data fields are read for each entry. This means that you could limit it to only reading the TITLE for example.
     * Use this method to set an array of values that can be read. If this is not set, it will be determined to be all.
     * Invalid fields are ignored
     * NOTE: Not using this selection process is faster
     * Important NOTE: If you only put title in the array, only the title will ever be returned
     *
     * Supported values are:
     * title, link, id, updated, summary, content, published
     *
     * @param   String[]    $array      An array of the values to read in from the entry
     *
     */
    public function setExtractionObjects($array)
    {
       // Grab data
       $this->_EntryExtractArray = $array;
    }

    /**
     * Returns a feed object with the main feed data populated in it
     * Note: This returns the first top of the feed, getNextEntry must be called after this to get each entry
     *
     * @return      AtomFeed
     */
    public function getFeedTopData()
    {
        // Is it valid
        $this->_isValid();

        // Read in the top data into an object instance
        $obj = new AtomFeed();
        echo $this->_Handle->feed->title;
        $obj->_Title = $this->_Handle->feed->title;
        $obj->_SubTitle = $this->_Handle->feed->subtitle;
        $obj->_Link = $this->_Handle->feed->link;
        $obj->_Id = $this->_Handle->feed->id;
        $obj->_Updated = $this->_Handle->feed->updated;

        return $obj;
    }
    

}


/**
 * An object that holds Atom Feed data
 */
class AtomFeed
{
    // Public Members
    public $_Title = NULL;
    public $_SubTitle = NULL;
    public $_Link = NULL;
    public $_Id = NULL;
    public $_Updated = NULL;
    public $_AuthorArray = NULL;
    public $_ConstributorArray = NULL;
    public $_Entries = NULL;
    public $_Rights = NULL;
}

/**
 * An object that holds Atom Feed Entry details
 */
class AtomFeedEntry
{
    // Public Members
    public $_Title = NULL;
    public $_Link = NULL;
    public $_Id = NULL;
    public $_Updated = NULL;
    public $_Summary = NULL;
    public $_Content = NULL;
    public $_AuthorArray = NULL;
    public $_ContributorArray = NULL;
    public $_Published = NULL;
}

?>
