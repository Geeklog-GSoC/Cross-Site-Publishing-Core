<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CSA Plugin 1.0                                                            |
// +---------------------------------------------------------------------------+
// | functions.inc                                                             |
// |                                                                           |
// | This file does two things: 1) it implements the necessary Geeklog Plugin  |
// | API method and 2) implements all the common code needed by the CSA        |
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
* Implementation of the Plugin API for the CSA plugin
*
* @package CSA
*/

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.inc') !== false) {
    die('This file can not be used on its own.');
}

$plugin_path = $_CONF['path'] . 'plugins/csa/';
$langfile = $plugin_path . 'language/' . $_CONF['language'] . '.php';

if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $plugin_path . 'language/english.php';
}

$plugin_path = $_CONF['path'] . 'plugins/pubcontrol/';
require_once $plugin_path . 'lib/includes.php';


function ADMIN_getListField_CSA_ListGalleries($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_CSA_UPLUGIN, $LANG28, $LANG_PUBCONTROL_UPLUGIN, $LANG_ACCESS;

    $retval = '';
    switch($fieldname) {
        case 'title':
            $title = urlencode($A['title']);
            $summary = urlencode($A['summary']);
            $type = $A['type'];
            $retval = "<a href='server.php?cmd=8&id={$A['id']}&t={$title}&s={$summary}&q={$type}'>{$A['title']}</a>";
            break;
        case 'summary':
            $retval = $A['summary'];
            break;
        case 'type':
            $retval = $LANG_CSA_UPLUGIN[TypeObject::getNameForInteger($A['type'])];
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"server.php?cmd=6&id={$A['id']}\");'>{$LANG_ACCESS['remove']}</a>";
            break;
        case 'show':
            $retval = "<a href='server.php?cmd=7&id={$A['id']}'>{$LANG_CSA_UPLUGIN[21]}</a>";
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;

}

function ADMIN_getListField_CSA_ShowData($fieldname, $fieldvalue, $A, $icon_arr)
{
    global $_CONF, $LANG_CSA_UPLUGIN, $LANG28, $LANG_PUBCONTROL_UPLUGIN, $LANG_ACCESS;

    $retval = '';
    switch($fieldname) {
        case 'title':
            $retval = "<a href='server.php?cmd=9&id={$A['id']}'>{$A['title']}</a>";
            break;
        case 'view':
            $retval = "<div id='ci{$A['id']}' style='display:none'>{$A['data']}</div><a href='javascript:void();' onclick='containerAlert(\"ci{$A['id']}\");'>{$LANG_CSA_UPLUGIN[26]}</a>";
            break;
        case 'delete':
            $retval = "<a href='javascript:void();' onclick='deleteItem(\"{$LANG_PUBCONTROL_UPLUGIN[24]}\", \"server.php?cmd=10&id={$A['id']}&fid={$A['fid']}\");'>{$LANG_ACCESS['remove']}</a>";
            break;
        default:
            $retval = $fieldvalue;
            break;
    }
    return $retval;

}