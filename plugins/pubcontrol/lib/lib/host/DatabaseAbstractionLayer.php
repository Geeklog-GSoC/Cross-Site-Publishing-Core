<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog 1.6                                                               |
// +---------------------------------------------------------------------------+
// | DatabaseAbstractionLayer.php                                              |
// |                                                                           |
// | Geeklog database library.                                                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2000-2010 by the following authors:                         |
// |                                                                           |
// | Authors: Tim Patrick timpatrick12@gmail.com                               |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software Foundation,   |
// | Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.           |
// |                                                                           |
// +---------------------------------------------------------------------------+

/**
* A generic wrapper module around all database abstractions to abstract the concept beyond a peculiar system
*
*/


/**
 * Provides an interface around the specific database methods
 */
class DAL
{
    // Private Variables
    private $_IgnoreErrors = 0;

    // Constructor

    /**
     * Creates a new handle to the database, the connection should already be initialized
     */
    public function __construct()
    {
        
    }

    public function cleanup()
    {
        
    }

    /**
     * Returns the qualified table name for the generic name specified
     * @param String $name The name of the table
     * @return The qualified table name or NULL if it does nto exist
     */
    public static function getFormalTableName($name)
    {
        return (isset($_TABLES[$name])) ? $_TABLES[$name] : NULL;
    }

    /**
     * Returns the last auto generated ID from the last query
     * @return int The last ID auto generated
     */
    public function getInsertId()
    {
        return DB_insertId();
    }


    /**
     * Sets the error reporting level - if set to false, errors will be surpressed
     * @param boolean $error_reporting = TRUE Error reporting on
     */
    public function setErrorReporting($error_reporting=TRUE)
    {
        if($error_reporting == FALSE)
        {
            $this->_IgnoreErrors = 1;
        }
        else
        {
            $this->_IgnoreErrors = 0;
        }
    }

    // Public Variables

    /**
     * Escapes the string being passed to it
     * @param $string The string to escape
     * @param $numerical=FALSE Set to true if it is a numeric entity
     * @return The escaped string
     */
    public static function applyFilter($string, $numerical=FALSE)
    {
        return COM_applyFilter($string, $numerical);
    }

    /**
     * Executes a generic non query, like UPDATE, DELETE, or INSERT that do not return a recordset
     * @param $query The query to execute
     */
    public function executeNonQuery($query)
    {
        // Call DB function
        DB_Query($query, $this->_IgnoreErrors);
    }

    /**
     * Execute a generic query that will return a recordset or extra data
     * The data can then be accessed with a subsequent call to the fetch_assoc method. 
     * @param String $query The query to execute
     * @return ResultSet class pointer.
     */
    public function executeQuery($query)
    {
        // Execute Query
        $result = DB_Query($query, $this->_IgnoreErrors);

        // Create a new result set
        $res = new DAL_ResultSet($result);
        return $res;
    }

    /**
     * Counts records in a table
     *
     * This will return the number of records which meet the given criteria in a given table
     *
     * @param string              $table      Table to perform count on - Note: This is the UNQUALIFIED name
     * @param array|string        $id=''         field name(s) to use in where clause
     * @param array|string        $value=''      Value(s) to use in where clause
     * @param boolean             $qualified=FALSE Set to true if you wish to include a qualified name (no lookup will be performed)
     * @return int     Returns row count from generated SQL
     */
    public function count($table, $id='', $value='', $qualified=FALSE)
    {
        $table = ($qualified === FALSE) ? self::getFormalTableName($table) : $table;
        return DB_count($table, $id, $value);
    }
}

/**
 * Holds methods for manipulating a result set
 */
class DAL_ResultSet
{
    // Private Variables
    private $_ResultSet = NULL;
    
    // Constructor

    /**
     * The constructor for the class
     * @param resource $resultset The result set to associate to
     */
    public function __construct($resultset)
    {
        $this->_ResultSet = $resultset;
    }

    // Public Methods

    /**
     * Fetches an associate array from the current result set
     * @return Array[column] = value or NULL on end of data (no more columns)
     */
    public function fetchAssoc()
    {
        $array = DB_fetchArray($this->_ResultSet);
        return ($array === FALSE) ? NULL : $array;
    }
}

?>
