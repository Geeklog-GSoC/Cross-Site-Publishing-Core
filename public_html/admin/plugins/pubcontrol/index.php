<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Pubcontrol Plugin 1.0                                                     |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// |  The main page for administrative                                         |
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
* Geeklog common function library
*/
require_once '../../../lib-common.php';


/**
* Security check to ensure user even belongs on this page
*/
require_once '../../auth.inc.php';

$display = "";

// Ensure user has rights to access this page
##TODO: Add Check

##TODO: Remove this hack to allow includes
require_once $_CONF['path'] . 'plugins/pubcontrol/functions.inc';

$display .= COM_siteHeader('');

// Do we have a message>

if ($_GET['msg']) {
    $display .= COM_showMessageText($LANG_PUBCONTROL_UPLUGIN[(int)$_GET['msg']]);
}

// What command do we have now
if(isset($_GET['cmd'])) {
    
    // Switch on the command
    switch((int)$_GET['cmd']) {
        case 1:
            // Show options of what to do
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'manager.thtml');

            
            $display .= $temp->parse_output();
            break;
        case 2:
            // Create new group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'group_manage.thtml');

            // 

            $display .= $temp->parse_output();
            break;
    }



}
else if(isset($_GET['ret']))
{
    // Switch on the return command
    switch($_GET['ret']) {
        // Modify Group
        case "group_modify":
            // First thing is to grab the data from the POST variables
            $group = new GroupObject();
            $group->_Title = HostInterface::POST('GEEKLOG_PUBGNAME');
            $group->_Summary = HostInterface::POST('GEEKLOG_PUBGSUMMARY');
            $group->_Type = TypeObject::makeTypeFromInteger((int)HostInterface::POST('GEEKLOG_PUBGTYPE'));
            $group->_Id = (int)HostInterface::POST("GEEKLOG_PUBGEDIT");
            
            // Are all the values valid (not null and type 1 or 2
            if( ($group->_Title === NULL) || ($group->_Summary === NULL)) {
                header("Location: index.php?msg=2");
            }
            else if( ($group->_Type === NULL) || (($group->_Type !== TypeObject::O_PRIVATE) && ($group->_Type !== TypeObject::O_PUBLIC))) {
                header("Location: index.php?msg=2");
            }

            // Ok now attempt to add it
            try {
                $pub = new PublishingGroupControl();
                if($group->_Id === 0) {
                    // Add
                    $pub->createGroup($group);
                }
                else {
                    $pub->updateGroup($group);
                }
            }
            catch(PublishingException $pe) {
                header("Location: index.php?msg=3");
            }

            header("Location: index.php?cmd=1&msg=4");

            break;
    }
}









$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);


