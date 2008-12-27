<?php 

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Geeklog 1.5                                                               |
// +---------------------------------------------------------------------------+
// | lib-install.php                                                           |
// |                                                                           |
// | Additional functions for install script.                                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2008 by the following authors:                              |
// |                                                                           |
// | Authors: Matt West - matt.danger.west AT gmail DOT com                    |
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
 * The functionality of many of these functions already exists in other
 * Geeklog libraries. However, during the first few stages of the
 * installation either those libraries cannot be accessed or dependency 
 * libraries cannot be accessed.
 */

if (strpos(strtolower($_SERVER['PHP_SELF']), 'lib-install.php') !== false) {
    die('This file can not be used on its own!');
}

// this should help expose parse errors even when
// display_errors is set to Off in php.ini
if (function_exists('ini_set')) {
    ini_set('display_errors', '1');
}
error_reporting(E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR | E_NOTICE);

if (!defined('LB')) {
    define('LB', "\n");
}
if (!defined('VERSION')) {
    define('VERSION', '1.6.0');
}
if (!defined('XHTML')) {
    define('XHTML', ' /');
}
if (!defined('SUPPORTED_PHP_VER')) {
    define('SUPPORTED_PHP_VER','4.1.0');
}

if (empty($LANG_DIRECTION)) {
    $LANG_DIRECTION = 'ltr';
}
if ($LANG_DIRECTION == 'rtl') {
    $form_label_dir = 'form-label-right';
    $perms_label_dir = 'perms-label-right';
} else {
    $form_label_dir = 'form-label-left';
    $perms_label_dir = 'perms-label-left';
}

$language = 'english';
if (isset($_POST['language'])) {
    $lng = $_POST['language'];
} elseif (isset($_GET['language'])) {
    $lng = $_GET['language'];
} elseif (isset($_COOKIE['language'])) {
    // Okay, so the name of the language cookie is configurable, so it may not
    // be named 'language' after all. Still worth a try ...
    $lng = $_COOKIE['language'];
} else {
    $lng = $language;
}
// sanitize value and check for file
$lng = preg_replace('/[^a-z0-9\-_]/', '', $lng);
if (!empty($lng) && is_file('language/' . $lng . '.php')) {
    $language = $lng;
}
// Include the language file
require_once 'language/' . $language . '.php'; 

// Before we begin, check if an uploaded file exceeds PHP's post_max_size
if (isset($_SERVER['CONTENT_LENGTH'])) {

    // This code is thanks to v3 AT sonic-world DOT ru via PHP.net
    $POST_MAX_SIZE = ini_get('post_max_size');
    $mul = substr($POST_MAX_SIZE, -1);
    $mul = ($mul == 'M' 
            ? 1048576 
            : ( $mul == 'K' 
                ? 1024 
                : ( $mul == 'G' 
                    ? 1073741824 
                    : 1 ) ) );

    if (($_SERVER['CONTENT_LENGTH'] > ($mul*((int)$POST_MAX_SIZE))) && $POST_MAX_SIZE) {

        // If it does, display an error message
        $display = INST_getHeader($LANG_ERROR[8])
            . INST_getAlertMsg($LANG_ERROR[7])
            . INST_getFooter();
        die($display);

    }

}



// +---------------------------------------------------------------------------+
// | Functions                                                                 |
// +---------------------------------------------------------------------------+

/**
 * Written to aid in install script debugging, will be removed later
 *
 * @param   any   $var      The variable you want to print, can be any datatype
 * @param   bool  $stop_exec Either echo $var or die $var 
 * @return  string          The datatype and value of $var
 *
 */
function sanity($var = 'test', $die_after = false)
{
    $retval = '<div style="background-color: #FFF">';
    switch (gettype($var)) {
    case "array":
        $retval .= "Array:<br>";
        foreach ($var as $k=>$i) {
            $retval .= "$k => $i <br>";
        }
        break;
    case "boolean" || "integer" || "double" || "string":
        $retval .= gettype($var) . ": $var <br>";
        break;
    
    default:
        $retval .= gettype($var);
    }
    echo $retval . "</div>";
    if ($die_after) {
        die;
    }
}


/**
 * Returns the beginning HTML for the installer theme.
 *
 * @param   $mHeading   Heading
 * @return  string      Header HTML code
 *
 */
function INST_getHeader($mHeading)
{
    global $LANG_CHARSET, $LANG_INSTALL, $LANG_DIRECTION;

    return (defined('XHTML') 
            ? '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' 
                . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'
            : '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">' 
                . '<html>' ) 
        . '<head>
<meta http-equiv="Content-Type" content="text/html;charset=' . $LANG_CHARSET . '"' . XHTML . '>
<meta name="robots" content="noindex,nofollow"' . XHTML . '>
<meta http-equiv="Cache-Control" content="no-cache/"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="-1"/>
<link rel="stylesheet" type="text/css" href="layout/style.css"' . XHTML . '>
<script language="javascript" type="text/javascript">
function INST_selectMigrationType() 
{
    var myType = document.migrate.migration_type.value;  
    var migrationSelect = document.getElementById("migration-select");
    var migrationUpload = document.getElementById("migration-upload");
    var migrationUploadWarning = document.getElementById("migration-upload-warning");

    switch (myType) {
        case "select":
          migrationSelect.style.display = "inline";
          migrationUpload.style.display = "none";
          migrationUploadWarning.style.display = "none";
          break;l
        case "upload":
          migrationSelect.style.display = "none";
          migrationUpload.style.display = "inline";
          migrationUploadWarning.style.display = "block";
          break;
        default:
          migrationSelect.style.display = "none";
          migrationUpload.style.display = "none";
          migrationUploadWarning.style.display = "none";
    }
}
</script>
<title>' . $LANG_INSTALL[0] . '</title>
</head>

<body dir="' . $LANG_DIRECTION . '">
    <div class="header-navigation-container">
        <div class="header-navigation-line">
            <a href="' . $LANG_INSTALL[87] . '" class="header-navigation">' . $LANG_INSTALL[1] . '</a>&nbsp;&nbsp;&nbsp;
        </div>
    </div>
    <div class="header-logobg-container-inner">
        <a class="header-logo" href="http://www.geeklog.net/">
            <img src="layout/logo.png"  width="151" height="56" alt="Geeklog"' . XHTML . '>
        </a>
        <div class="header-slogan">' . $LANG_INSTALL[2] . ' <br' . XHTML . '><br' . XHTML . '>
        </div>
    </div>
    <div class="installation-container">
        <div class="installation-body-container">
            <h1 class="heading">' . $mHeading . '</h1>' . LB;

}

/**
 * Returns the ending HTML for the installer theme.
 *
 * @return string Footer HTML code
 *
 */
function INST_getFooter()
{
    return INST_printTab(3) . '<br' . XHTML . '><br' . XHTML . '>' . LB
        . INST_printTab(2) . '</div>' . LB
        . INST_printTab(1) . '</div>' . LB
        . '</body>' . LB 
        . '</html>';
}

/**
 * Returns the PHP version
 *
 * Note: Removes appendices like 'rc1', etc.
 *
 * @return array the 3 separate parts of the PHP version number
 *
 */
function php_v()
{
    $phpv = explode('.', phpversion());
    return array($phpv[0], $phpv[1], (int) $phpv[2]);
}

/**
 * Check if the user's PHP version is supported by Geeklog
 *
 * @return bool True if supported, falsed if not supported
 *
 */
function INST_phpOutOfDate()
{
    $phpv = php_v();
    if (($phpv[0] < 4) || (($phpv[0] == 4) && ($phpv[1] < 1))) {
        return true;    
    } else {
        return false;
    }
}

/**
 * Returns the MySQL version
 *
 * @return  mixed   array[0..2] of the parts of the version number or false
 *
 */
function mysql_v($_DB_host, $_DB_user, $_DB_pass)
{
    if (@mysql_connect($_DB_host, $_DB_user, $_DB_pass) === false) {
        return false;
    }

    $mysqlv = @mysql_get_server_info();

    if (!empty($mysqlv)) {
        preg_match('/^([0-9]+).([0-9]+).([0-9]+)/', $mysqlv, $match);
        $mysqlmajorv = $match[1];
        $mysqlminorv = $match[2];
        $mysqlrev = $match[3];
    } else {
        $mysqlmajorv = 0;
        $mysqlminorv = 0;
        $mysqlrev = 0;
    }
    @mysql_close();

    return array($mysqlmajorv, $mysqlminorv, $mysqlrev);
}

/**
 * Check if the user's MySQL version is supported by Geeklog
 *
 * @param   array   $db     Database information
 * @return  bool    True if supported, falsed if not supported
 *
 */
function INST_mysqlOutOfDate($db)
{
    if ($db['type'] == 'mysql' || $db['type'] == 'mysql-innodb') {
        $myv = mysql_v($db['host'], $db['user'], $db['pass']);
        if (($myv[0] < 3) || (($myv[0] == 3) && ($myv[1] < 23)) ||
                (($myv[0] == 3) && ($myv[1] == 23) && ($myv[2] < 2))) {
            return true;
        }
        return false;
    }
}

/**
 * Print tabs so the source code looks presentable 
 *
 * @param   string  $num    Number of tabs to return
 * @return  string          The number of tabs in HTML form.
 *
 */
function INST_printTab($num) 
{
    $retval = '';
    for ($i = 0; $i < $num; $i++) {
        $retval .= "    ";
    }
    return $retval;
}

/**
 * Written to aid in install script development 
 *
 * @param   int $size       Filesize
 * @param   int $dec_places Number of decimal places
 * @return  string          Filesize string
 * @note    This code is a modified copy from PHP.net
 */
function INST_formatSize($size, $dec_places = 0) 
{
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; ($size > 1024 && isset($sizes[$i+1])) ; $i++) {
        $size /= 1024;
    }
    return  round($size, $dec_places) . " " . $sizes[$i];
}

/**
 * Returns the HTML form to return the user's inputted data to the
 * previous page.
 *
 * @return  string  HTML form code.
 *
 */
function INST_helpLink($var)
{
    global $language;

    return '(<a href="help.php?language=' . $language . '#' . $var . '" target="_blank">?</a>)';
}

/**
 * Make a nice display name from the language filename
 *
 * @param    string  $file   filename without the extension
 * @return   string          language name to display to the user
 * @note     This code is a straight copy from MBYTE_languageList()
 *
 */
function INST_prettifyLanguageName($filename)
{
    $langfile = str_replace('_utf-8', '', $filename);
    $uscore = strpos($langfile, '_');
    if ($uscore === false) {
        $lngname = ucfirst($langfile);
    } else {
        $lngname = ucfirst(substr($langfile, 0, $uscore));
        $lngadd = substr($langfile, $uscore + 1);
        $lngadd = str_replace('utf-8', '', $lngadd);
        $lngadd = str_replace('_', ', ', $lngadd);
        $word = explode(' ', $lngadd);
        $lngadd = '';
        foreach ($word as $w) {
            if (preg_match('/[0-9]+/', $w)) {
                $lngadd .= strtoupper($w) . ' ';
            } else {
                $lngadd .= ucfirst($w) . ' ';
            }
        }
        $lngname .= ' (' . trim($lngadd) . ')';
    }

    return $lngname;
}

/**
 * Modify db-config.php
 *
 * @param   string  $config_file    Full path to db-config.php
 * @param   array   $db             Database information to save
 * @return  bool    True if successful, false if not
 *
 */
function INST_writeConfig($config_file, $db)
{
    require_once $config_file; // Grab the current DB values

    $db = array('host' => (isset($db['host']) ? $db['host'] : $_DB_host),
                'name' => (isset($db['name']) ? $db['name'] : $_DB_name),
                'user' => (isset($db['user']) ? $db['user'] : $_DB_user),
                'pass' => (isset($db['pass']) ? $db['pass'] : $_DB_pass),
                'table_prefix' => (isset($db['table_prefix']) ? $db['table_prefix'] : $_DB_table_prefix),
                'type' => (isset($db['type']) ? $db['type'] : $_DB_dbms) );

    // Read in db-config.php so we can insert the DB information
    $dbconfig_file = fopen($config_file, 'r');
    $dbconfig_data = fread($dbconfig_file, filesize($config_file));
    fclose($dbconfig_file);

    // Replace the values with the new ones
    $dbconfig_data = str_replace("\$_DB_host = '" . $_DB_host . "';", "\$_DB_host = '" . $db['host'] . "';", $dbconfig_data); // Host
    $dbconfig_data = str_replace("\$_DB_name = '" . $_DB_name . "';", "\$_DB_name = '" . $db['name'] . "';", $dbconfig_data); // Database
    $dbconfig_data = str_replace("\$_DB_user = '" . $_DB_user . "';", "\$_DB_user = '" . $db['user'] . "';", $dbconfig_data); // Username
    $dbconfig_data = str_replace("\$_DB_pass = '" . $_DB_pass . "';", "\$_DB_pass = '" . $db['pass'] . "';", $dbconfig_data); // Password
    $dbconfig_data = str_replace("\$_DB_table_prefix = '" . $_DB_table_prefix . "';", "\$_DB_table_prefix = '" . $db['table_prefix'] . "';", $dbconfig_data); // Table prefix
    $dbconfig_data = str_replace("\$_DB_dbms = '" . $_DB_dbms . "';", "\$_DB_dbms = '" . $db['type'] . "';", $dbconfig_data); // Database type ('mysql' or 'mssql')

    // Write our changes to db-config.php
    $dbconfig_file = fopen($config_file, 'w');
    if (!fwrite($dbconfig_file, $dbconfig_data)) {
        return false;
    }
    fclose($dbconfig_file);
    return true;
}

/**
 * Check if a table exists
 * @see DB_checkTableExists
 *
 *
 * @param   string $table   Table name
 * @return  boolean         True if table exists, false if it does not
 *
 */
function INST_checkTableExists($table)
{
    return DB_checkTableExists($table);
}

/**
 * Import a Geeklog database from a backup file
 *
 * @param   string  $file   Path to backup file on the server
 * @return  bool            True if successful, false if not
 *
 */
/*
function INST_importDBFromBackup($file)
{
    global $_DB, $_DB_name, $display, $_TABLES;

    // Make sure the database is clean
    if (!DB_query('drop database ' . $_DB_name)) 
        die('unable to drop database ' . $_DB_name);
    if (!DB_query('create database ' . $_DB_name)) 
        die('unable to create database ' . $_DB_name);

    return true;
}
*/

/**
 * Can the install script connect to the database?
 *
 * @param   array   $db Database information
 * @return  mixed       Returns the DB handle if true, false if not
 *
 */
function INST_dbConnect($db)
{
    if (empty($db['pass'])) {
        return false;
    }
    $db_handle = false;
    switch ($db['type']) {
    case 'mysql-innodb':
        // deliberate fallthrough - no "break"
    case 'mysql':
        if ($db_handle = @mysql_connect($db['host'], $db['user'], $db['pass'])) {
            return $db_handle;
        }
        break;
    case 'mssql':
        if ($db_handle = @mssql_connect($db['host'], $db['user'], $db['pass'])) {
            return $db_handle;
        }
        break;
    }
    return $db_handle;
}

/**
 * Check if a Geeklog database exists
 *
 * @param   array   $db Array containing connection info
 * @return  bool        True if a database exists, false if not
 *
 */
function INST_dbExists($db)
{
    $db_handle = INST_dbConnect($db);
    $db_exists = false;
    switch ($db['type']) {
    case 'mysql':
        if (@mysql_select_db($db['name'], $db_handle)) {
            return true;
        }
        break;
    case 'mssql':
        if (@mssql_select_db($db['name'], $db_handle)) {
            return true;
        }
        break;
    }
    return false;
}

/**
 * Check if URL exists
 *
 * @param   string  $url    URL
 * @return  bool            True if URL exists, false if not
 * @note    This code is a modified copy from marufit at gmail dot com
 *
 */
function INST_urlExists($url) 
{
/*
    $handle = curl_init($url);
    if ($handle === false) {
        return false;
    }
    curl_setopt($handle, CURLOPT_HEADER, false);
    curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
    curl_setopt($handle, CURLOPT_NOBODY, true);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
    $response = curl_exec($handle);
    curl_close($handle);
    return $response;
*/
    return true;
}

/**
 * Check if an error occured while uploading a file
 *
 * @param   array   $mFile  $_FILE['uploaded_file']
 * @return  mixed           Returns the error string if an error occured,
 *                          returns false if no error occured
 *
 */
function INST_getUploadError($mFile) 
{
    global $LANG_ERROR;

    $mRetval = '';
    $mErrors = array(
        UPLOAD_ERR_INI_SIZE => $LANG_ERROR[0],
        UPLOAD_ERR_FORM_SIZE => $LANG_ERROR[1],
        UPLOAD_ERR_PARTIAL => $LANG_ERROR[2],
        UPLOAD_ERR_NO_FILE => $LANG_ERROR[3],
        UPLOAD_ERR_NO_TMP_DIR => $LANG_ERROR[4],
        UPLOAD_ERR_CANT_WRITE => $LANG_ERROR[5],
        UPLOAD_ERR_EXTENSION => $LANG_ERROR[6]);

    if ($mFile['error'] !== UPLOAD_ERR_OK) { // If an error occured while uploading the file.

        if ($mFile['error'] > count($mErrors)) { // If the error code isn't listed in $mErrors

            $mRetval = 'An unknown error occured'; // Unknown error

        } else {

            $mRetval = $mErrors[$mFile['error']]; // Print the error

        }

    } else { // If no upload error occurred

        $mRetval = false;

    }
    
    return $mRetval;
}

/**
 * Check which plugins are actually installed and disable them if needed
 *
 * @return   int     number of plugins that were disabled
 *
 */
function INST_checkPlugins()
{
    global $_CONF, $_TABLES;

    $disabled = 0;
    $plugin_path = $_CONF['path'] . 'plugins/';

    $result = DB_query("SELECT pi_name FROM {$_TABLES['plugins']} WHERE pi_enabled = 1");
    $num_plugins = DB_numRows($result);
    for ($i = 0; $i < $num_plugins; $i++) {
        $A = DB_fetchArray($result);
        if (!file_exists($plugin_path . $A['pi_name'] . '/functions.inc')) {
            DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled = 0 WHERE pi_name = '{$A['pi_name']}'");
            $disabled++;
        }
    }

    return $disabled;
}

/**
 * Nicely formats the alert messages
 *
 * @param    $mMessage   Message string
 * @param    $mType      'error', 'warning', 'success', or 'notice'
 * @return   string      HTML formatted dialog message
 *
 */
function INST_getAlertMsg($mMessage, $mType = 'notice')
{
    global $LANG_INSTALL;

    $mStyle = ($mType == 'success') ? 'success' : 'error';

    switch (strtolower($mType)) {
    case 'error':
        $mType = $LANG_INSTALL[38]; break;
    case 'warning':
        $mType = $LANG_INSTALL[20]; break;
    case 'success':
        $mType = $LANG_INSTALL[93]; break;
    default:
        $mType = $LANG_INSTALL[59]; break;
    }

    return '<div class="notice"><span class="' . $mStyle . '">' . $mType .':</span> ' 
        . $mMessage . '</div>' . LB;

}

/**
 * Check if we can skip upgrade steps (post-1.5.0)
 *
 * If we're doing an upgrade from 1.5.0 or later and we have the necessary
 * DB credentials, skip the forms and upgrade directly.
 *
 * @param   string  $dbconfig_path      path to db-config.php
 * @param   string  $siteconfig_path    path to siteconfig.php
 * @return  string                      database version, if possible
 * @note    Will not return if upgrading from 1.5.0 or later.
 *
 */
function INST_checkPost150Upgrade($dbconfig_path, $siteconfig_path)
{
    global $_CONF, $_TABLES, $_DB, $_DB_dbms, $_DB_host, $_DB_user, $_DB_pass;

    require $dbconfig_path;
    require $siteconfig_path;

    $connected = false;
    $version = '';

    switch ($_DB_dbms) {
    case 'mysql':
        $db_handle = @mysql_connect($_DB_host, $_DB_user, $_DB_pass);
        if ($db_handle) {
            $connected = @mysql_select_db($_DB_name, $db_handle);
        }
        break;

    case 'mssql':    
        $db_handle = @mssql_connect($_DB_host, $_DB_user, $_DB_pass);
        if ($db_handle) {
            $connected = @mssql_select_db($_DB_name, $db_handle);
        }
        break;

    default:
        $connected = false;
        break;
    }

    if ($connected) {
        require $_CONF['path_system'] . 'lib-database.php';

        $version = INST_identifyGeeklogVersion();

        switch ($_DB_dbms) {
        case 'mysql':
            @mysql_close($db_handle);
            break;

        case 'mssql':
            @mssql_close($db_handle);
            break;
        }

        if (!empty($version) && ($version != VERSION) &&
                (version_compare($version, '1.5.0') >= 0)) {

            // current version is at least 1.5.0, so upgrade directly
            $req_string = 'index.php?mode=upgrade&step=3'
                        . '&dbconfig_path=' . $dbconfig_path
                        . '&version=' . $version;

            header('Location: ' . $req_string);
            exit;
        }
    }

    return $version;
}


/**
 * Set VERSION constant in siteconfig.php after successful upgrade
 *
 * @param   string  $siteconfig_path    path to siteconfig.php
 * @return  void
 *
 */
function INST_setVersion($siteconfig_path)
{
    global $LANG_INSTALL;

    $siteconfig_file = fopen($siteconfig_path, 'r');
    $siteconfig_data = fread($siteconfig_file, filesize($siteconfig_path));
    fclose($siteconfig_file);

    $siteconfig_data = preg_replace
            (
             '/define\s*\(\'VERSION\',[^;]*;/',
             "define('VERSION', '" . VERSION . "');",
             $siteconfig_data
            );

    $siteconfig_file = fopen($siteconfig_path, 'w');
    if (!fwrite($siteconfig_file, $siteconfig_data)) {
        exit($LANG_INSTALL[26] . ' ' . $LANG_INSTALL[28]);
    }
    fclose($siteconfig_file);
}

function INST_getPluginInfo($plugin)
{
    global $_CONF;

    $info = false;

    $autoinstall = $_CONF['path'] . 'plugins/' . $plugin . '/autoinstall.php';
    if (! file_exists($autoinstall)) {
        return false;
    }

    include $autoinstall;

    $fn = 'plugin_autoinstall_' . $plugin;
    if (function_exists($fn)) {
        $inst_info = $fn($plugin);
        if (isset($inst_info['info']) &&
                !empty($inst_info['info']['pi_name'])) {
            $info = $inst_info['info'];
        }
    }

    return $info;
}

/**
* Do the actual plugin auto install
*
* @param    string  $plugin     Plugin name
* @param    array   $inst_parm  Installation parameters for the plugin
* @param    boolean $verbose    true: enable verbose logging
* @return   boolean             true on success, false otherwise
*
*/
function INST_pluginAutoinstall($plugin, $inst_parms, $verbose = true)
{
    global $_CONF, $_TABLES, $_USER, $_DB_dbms, $_DB_table_prefix;

    $fake_uid = false;
    if (!isset($_USER['uid'])) {
        $_USER['uid'] = 1;
        $fake_uid = false;
    }

    $base_path = $_CONF['path'] . 'plugins/' . $plugin . '/';

    if ($verbose) {
        COM_errorLog("Attempting to install the '$plugin' plugin", 1);
    }

    // sanity checks in $inst_parms
    if (isset($inst_parms['info'])) {
        $pi_name       = $inst_parms['info']['pi_name'];
        $pi_version    = $inst_parms['info']['pi_version'];
        $pi_gl_version = $inst_parms['info']['pi_gl_version'];
        $pi_homepage   = $inst_parms['info']['pi_homepage'];
    }
    if (empty($pi_name) || ($pi_name != $plugin) || empty($pi_version) ||
            empty($pi_gl_version) || empty($pi_homepage)) {
        COM_errorLog('Incomplete plugin info', 1);

        return false;
    }

    // add plugin tables, if any
    if (! empty($inst_parms['tables'])) {
        $tables = $inst_parms['tables'];                                       
        foreach ($tables as $table) {
            $_TABLES[$table] = $_DB_table_prefix . $table;
        }
    }

    // Create the plugin's group(s), if any
    $groups = array();
    $admin_group_id = 0;
    if (! empty($inst_parms['groups'])) {
        $groups = $inst_parms['groups'];
        foreach ($groups as $name => $desc) {
            if ($verbose) {
                COM_errorLog("Attempting to create '$name' group", 1);
            }

            $grp_name = addslashes($name);
            $grp_desc = addslashes($desc);
            DB_query("INSERT INTO {$_TABLES['groups']} (grp_name, grp_descr) VALUES ('$grp_name', '$grp_desc')", 1);
            if (DB_error()) {
                COM_errorLog('Error creating plugin group', 1);
                PLG_uninstall($plugin);

                return false;
            }

            // keep the new group's ID for use in the mappings section (below)
            $groups[$name] = DB_insertId();

            // assume that the first group is the plugin's Admin group
            if ($admin_group_id == 0) {
                $admin_group_id = $groups[$name];
            }
        }
    }

    // Create the plugin's table(s)
    $_SQL = array();
    $DEFVALUES = array();
    if (file_exists($base_path . 'sql/' . $_DB_dbms . '_install.php')) {
        require_once $base_path . 'sql/' . $_DB_dbms . '_install.php';
    }

    if (count($_SQL) > 0) {
        $use_innodb = false;
        if (($_DB_dbms == 'mysql') &&
            (DB_getItem($_TABLES['vars'], 'value', "name = 'database_engine'")
                == 'InnoDB')) {
            $use_innodb = true;
        }

        foreach ($_SQL as $sql) {
            $sql = str_replace('#group#', $admin_group_id, $sql);
            if ($use_innodb) {
                $sql = str_replace('MyISAM', 'InnoDB', $sql);
            }
            DB_query($sql);
            if (DB_error()) {
                COM_errorLog('Error creating plugin table', 1);
                PLG_uninstall($plugin);

                return false;
            }
        }
    }

    // Add the plugin's features
    if ($verbose) {
        COM_errorLog("Attempting to add '$plugin' features", 1);
    }

    $features = array();
    $mappings = array();
    if (!empty($inst_parms['features'])) {
        $features = $inst_parms['features'];
        if (!empty($inst_parms['mappings'])) {
            $mappings = $inst_parms['mappings'];
        }

        foreach ($features as $feature => $desc) {
            $ft_name = addslashes($feature);
            $ft_desc = addslashes($desc);
            DB_query("INSERT INTO {$_TABLES['features']} (ft_name, ft_descr) "
                     . "VALUES ('$ft_name', '$ft_desc')", 1);
            if (DB_error()) {
                COM_errorLog('Error adding plugin feature', 1);
                PLG_uninstall($plugin);

                return false;
            }

            $feat_id = DB_insertId();

            if (isset($mappings[$feature])) {
                foreach ($mappings[$feature] as $group) {
                    if ($verbose) {
                        COM_errorLog("Adding '$feature' feature to the '$group' group", 1);
                    }

                    DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id, acc_grp_id) VALUES ($feat_id, {$groups[$group]})");
                    if (DB_error()) {
                        COM_errorLog('Error mapping plugin feature', 1);
                        PLG_uninstall($plugin);

                        return false;
                    }
                }
            }
        }
    }

    // Add plugin's Admin group to the Root user group 
    // (assumes that the Root group's ID is always 1)
    if ($admin_group_id > 1) {
        if ($verbose) {
            COM_errorLog("Attempting to give all users in the Root group access to the '$plugin' Admin group", 1);
        }

        DB_query("INSERT INTO {$_TABLES['group_assignments']} VALUES "
                 . "($admin_group_id, NULL, 1)");
        if (DB_error()) {
            COM_errorLog('Error adding plugin admin group to Root group', 1);
            PLG_uninstall($plugin);

            return false;
        }
    }

    // Pre-populate tables or run any other SQL queries
    if (count($DEFVALUES) > 0) {
        if ($verbose) {
            COM_errorLog('Inserting default data', 1);
        }
        foreach ($DEFVALUES as $sql) {
            $sql = str_replace('#group#', $admin_group_id, $sql);
            DB_query($sql, 1);
            if (DB_error()) {
                COM_errorLog('Error adding plugin default data', 1);
                PLG_uninstall($plugin);
            
                return false;
            }
        }
    }

    // Load the online configuration records
    $load_config = 'plugin_load_configuration_' . $plugin;
    if (function_exists($load_config)) {
        if (! $load_config($plugin)) {
            COM_errorLog('Error loading plugin configuration', 1);
            PLG_uninstall($plugin);

            return false;
        }
    }

    // Finally, register the plugin with Geeklog
    if ($verbose) {
        COM_errorLog("Registering '$plugin' plugin", 1);
    }

    // silently delete an existing entry
    DB_delete($_TABLES['plugins'], 'pi_name', $plugin);

    DB_query("INSERT INTO {$_TABLES['plugins']} (pi_name, pi_version, pi_gl_version, pi_homepage, pi_enabled) VALUES "
        . "('$plugin', '$pi_version', '$pi_gl_version', '$pi_homepage', 1)");

    if (DB_error()) {
        COM_errorLog('Failed to register plugin', 1);
        PLG_uninstall($plugin);

        return false;
    }

    // give the plugin a chance to perform any post-install operations
    $post_install = 'plugin_postinstall_' . $plugin;
    if (function_exists($post_install)) {
        if (! $post_install($plugin)) {
            COM_errorLog('Plugin postinstall failed', 1);
            PLG_uninstall($plugin);

            return false;
        }   
    }

    if ($verbose) {
        COM_errorLog("Successfully installed the '$plugin' plugin!", 1);
    }

    if ($fake_uid) {
        unset($_USER['uid']);
    }

    return true;
}

/**
* Upgrade any enabled plugins
*
* @note Needs a fully working Geeklog, so can only be done late in the upgrade
*       process!
*
*/
function INST_pluginUpgrades()
{
    global $_CONF, $_TABLES;

    $result = DB_query("SELECT pi_name, pi_version FROM {$_TABLES['plugins']} WHERE pi_enabled = 1");
    $numPlugins = DB_numRows($result);

    for ($i = 0; $i < $numPlugins; $i++) {
        list($pi_name, $pi_version) = DB_fetchArray($result);

        $code_version = PLG_chkVersion($pi_name);
        if (! empty($code_version) && ($code_version != $pi_version)) {
            if (PLG_upgrade($pi_name) !== true) {
                // upgrade failed - disable plugin
                DB_query("UPDATE {$_TABLES['plugins']} SET pi_enabled = 0 WHERE pi_name = '$pi_name'");
                COM_errorLog("Upgrade for '$pi_name' plugin failed - plugin disabled");
            }
        }
    }
}

?>