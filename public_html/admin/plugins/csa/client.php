<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CSA Plugin 1.0                                                            |
// +---------------------------------------------------------------------------+
// | client.php                                                                |
// |                                                                           |
// |  The main page for administrative   (client control)                      |
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
require_once $_CONF['path_system'] . 'lib-admin.php';
$display = "";

// Ensure user has rights to access this page
##TODO: Add Check

##TODO: Remove this hack to allow includes
require_once $_CONF['path'] . 'plugins/csa/functions.inc';

$display .= COM_siteHeader('');

// Do we have a message>

if ($_GET['msg']) {
    $display .= COM_showMessageText($LANG_CSA_UPLUGIN[(int)$_GET['msg']]);
}


// What command do we have now
if(isset($_GET['cmd'])) {

    // Switch on the command
    switch((int)$_GET['cmd']) {
        case 1:
            // Show main menu
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'main2.thtml');
            $temp->set_var('lang_37', $LANG_CSA_UPLUGIN[37]);
            $temp->set_var('lang_38', $LANG_CSA_UPLUGIN[38]);
            $temp->set_var('lang_39', $LANG_CSA_UPLUGIN[39]);
            $temp->set_var('lang_40', $LANG_CSA_UPLUGIN[40]);
            $temp->set_var('lang_41', $LANG_CSA_UPLUGIN[41]);
            $temp->set_var('track', urlencode("../csa/client.php?cmd=2"));
            $temp->set_var('ac', urlencode("ATTG00000C"));
            $display .= $temp->parse_output();
            break;
        case 2:
            // Next step in the wizard process now, the subscription is finished
            // Now we need to grab the groups for that ID and find a match with the CSA string or if none found alert the user
            $url = DAL::applyFilter(HostInterface::GET('url', ''));
            $sid = (int)DAL::applyFilter(HostInterface::GET('sid', 0));
            $type = ((int) HostInterface::GET('type', 0) === TypeObject::O_PRIVATE) ? TypeObject::O_PRIVATE : TypeObject::O_PUBLIC;
            $p = new ReceivingControlManagement();
            $groups = $p->listGroups($url);

            // What did we get
            if( ($groups === NULL) || ($sid === 0)) {
                header("Location: client.php?cmd=1&msg=43");
                exit;
            }

            // Check if the CSA string is there
            $found = FALSE;
            $selgroup = NULL;
            foreach($groups as $grp) {
                if(strcmp($grp->_Title, "Cross Site Alert - ATTG00000C - %") === 0) {
                    $found = TRUE;
                    $selgroup = $grp;
                    break;
                }
            }

            // Nothing was found
            if($found === FALSE) {
                header("Location: client.php?cmd=1&msg=42");
                exit;
            }

            // Found a group
            // Create the URL data
            $trck = urlencode(base64_encode("../csa/client.php?cmd=1&msg=44"));
            $url = urlencode($url);
            header("Location: ../pubcontrol/recv.php?cmd=10&trck={$trck}&q={$url}&d={$selgroup->_Id}&r={$type}&s={$sid}");

            break;

        case 4:
            // Feed has been joined
            break;
        default:
           header("Location: client.php?cmd=1");
            exit;
    }

}
else {
            header("Location: client.php?cmd=1");
            exit;
}


$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);


