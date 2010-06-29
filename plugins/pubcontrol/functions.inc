<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Pubcontrol Plugin 1.0                                                     |
// +---------------------------------------------------------------------------+
// | functions.inc                                                             |
// |                                                                           |
// | This file does two things: 1) it implements the necessary Geeklog Plugin  |
// | API method and 2) implements all the common code needed by the Pubcontrol |
// | plugin's PHP files.                                                       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010 by the following author:                               |
// |                                                                           |
// | Authors: Tim Patrick      - timpatrick12 AT gmail DOT com                 |
// |                                                                           |
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
* Implementation of the Plugin API for the Pubcontrol plugin
*
* @package Pubcontrol
*/

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

// Create table variable array
$_TABLES['pubcontrol_Groups'] = $_DB_table_prefix . 'pubcontrol_Groups';
$_TABLES['pubcontrol_Feeds'] = $_DB_table_prefix . 'pubcontrol_Feeds';
$_TABLES['pubcontrol_Data'] = $_DB_table_prefix . 'pubcontrol_Data';
$_TABLES['pubcontrol_Subscribers'] = $_DB_table_prefix . 'pubcontrol_Subscribers';
$_TABLES['pubcontrol_Security'] = $_DB_table_prefix . 'pubcontrol_Security';
$_TABLES['pubcontrol_SecurityGroupLink'] = $_DB_table_prefix . 'pubcontrol_SecurityGroupLink';
$_TABLES['pubcontrol_SubscriberGroupLink'] = $_DB_table_prefix . 'pubcontrol_SubscriberGroupLink';
$_TABLES['recvcontrol_Feeds'] = $_DB_table_prefix . 'recvcontrol_Feeds';
$_TABLES['recvcontrol_Data'] = $_DB_table_prefix . 'recvcontrol_Data';
$_TABLES['recvcontrol_Subscriptions'] = $_DB_table_prefix . 'recvcontrol_Subscriptions';
$_TABLES['pubcontrol_Accesscodes'] = $_DB_table_prefix . 'pubcontrol_Accesscodes';


$plugin_path = $_CONF['path'] . 'plugins/pubcontrol/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

// Include library files
require_once $plugin_path . 'lib/includes.php';


// Name says all
function plugin_getheadercode_repository()
{
    global $_CONF;

    $str = null;
    $str = '<link rel="stylesheet" type="text/css" href="'
        . $_CONF['site_url'] . '/pubcontrol/style.css"' . XHTML . '>';

#    $str .= '<script type="text/javascript" src="'
#        . $_CONF['site_url'] . '/pubcontrol/main.js"></script>';

    return $str;
}



// Lists all groups
function ADMIN_getListField_listpubcontrol_groups($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_PUBCONTROL_UPLUGIN, $_TABLES, $LANG01;

    $retval = '';
    switch($fieldname) {
        case 'title':
            // Create edit tag
            $retval = "<a href='index.php?cmd=3&id={$A['id']}'>{$A['title']}</a>";
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"index.php?ret=group_delete&id={$A['id']}\");'>{$LANG01[28]}</a>";
            break;
        case 'load_feeds':
            $retval = "<a href='index.php?cmd=7&gid={$A['id']}'>{$LANG_PUBCONTROL_UPLUGIN[39]}</a>";
            break;
        case 'summary':
            // Check to see if the repository is ok
            #$ps = explode('\n', wordwrap($A['summary'], 200, '\n'));
            #$retval = (count($ps) > 1) ? $ps[0] . "..." : $ps[0];
            $retval = $A['summary'];
            break;
        case 'type':
            $retval = $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($A['type'])];
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}

// Lists all feeds
function ADMIN_getListField_listpubcontrol_feeds($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_PUBCONTROL_UPLUGIN, $_TABLES, $LANG01;

    $retval = '';
    switch($fieldname) {
        case 'title':
            // Create edit tag
            $retval = "<a href='index.php?cmd=6&id={$A['id']}'>{$A['title']}</a>";
            break;
        case 'load_data':
            $retval = "<a href='index.php?cmd=109&id={$A['id']}'>{$LANG_PUBCONTROL_UPLUGIN[38]}</a>";
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"index.php?ret=feed_delete&id={$A['id']}\");'>{$LANG01[28]}</a>";
            break;
        case 'summary':
            // Check to see if the repository is ok
            #$ps = explode('\n', wordwrap($A['summary'], 200, '\n'));
            #$retval = (count($ps) > 1) ? $ps[0] . "..." : $ps[0];
            $retval = $A['summary'];
            break;
        case 'type':
            $retval = $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($A['type'])];
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
}

function ADMIN_getListField_listpubcontrol_sgroup($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_PUBCONTROL_UPLUGIN, $_TABLES, $LANG01;

    $retval = '';
    switch($fieldname) {
        case 'title':
            // Create edit tag
            $retval = "<a href='index.php?cmd=8&id={$A['id']}'>{$A['title']}</a>";
            break;
        case 'assign':
            // Create edit tag
            $retval = "<a href='index.php?cmd=11&id={$A['id']}'>{$LANG_PUBCONTROL_UPLUGIN[51]}</a>";
            break;
        case 'assigned':
            // Create edit tag
            $retval = "<a href='index.php?cmd=12&id={$A['id']}'>{$LANG_PUBCONTROL_UPLUGIN[56]}</a>";
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"index.php?ret=sgroup_delete&id={$A['id']}\");'>{$LANG01[28]}</a>";
            break;
        case 'summary':
            // Check to see if the repository is ok
            #$ps = explode('\n', wordwrap($A['summary'], 200, '\n'));
            #$retval = (count($ps) > 1) ? $ps[0] . "..." : $ps[0];
            $retval = $A['summary'];
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;
    
}

function ADMIN_getListField_listpubcontrol_sgroupfeed($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_PUBCONTROL_UPLUGIN, $_TABLES, $LANG_ACCESS;

    $retval = '';
    switch($fieldname) {
        case 'title':
            // Create edit tag
            #$p = new PublishingFeedControl();
            #$d = $p->listFeeds($A['feed_id'], TRUE);
            #$retval = $d[0]->_Title;
            $retval = $A['title'];
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"index.php?ret=sgroupfeed_delete&id={$A['securitygroup_id']}&fid={$A['feed_id']}\");'>{$LANG_ACCESS['remove']}</a>";
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;

}

?>