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
require_once $_CONF['path_system'] . 'lib-admin.php';
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
        // Show all repositories
        case 1:
            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[101], 'field' => 'url', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[76], 'field' => 'ssid', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[109], 'field' => 'list', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'unsubscribe', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'recv.php', 'text' => $LANG01[68]
                    )

                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[102], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[103],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'recv.php?cmd=1'
            );

            $table = DAL::getFormalTableName("recvcontrol_Subscriptions");
            $qstr = "SELECT * FROM {$table}";

            $query_arr = array(
                'table' => 'recvcontrol_Subscriptions',
                'sql' => $qstr . " WHERE 1=1",
                'query_fields' => array('url'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Groups', 'ADMIN_getListField_listrecvcontrol_Subscribers', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;
            break;
        // Unsubscribe
        case 2:
            // Grab incoming
            $ssid = HostInterface::GET('ssid', '');
            $url = HostInterface::GET('url', '');
            $id = (int)HostInterface::GET('id', 0);

            // Make sure the data is valid
            if( ($ssid === '') || ($url === '') || ($id === 0)) {
                header("Location: recv.php?msg=106");
                exit;
            }

            // Remove data
            $re = new ReceivingControlManagement();
            $re->unSubscribe($id);
            
            // Redirect to clause
            $redir = $_CONF['site_admin_url'] . '/plugins/pubcontrol/recv.php';
            $redir = urlencode($redir);
            $ssid  = urlencode($ssid);
            header("Location: {$url}/pubcontrol/index.php?cmd=112&ssid={$ssid}&redir={$redir}");
            break;
       // List all feeds subscribed
       case 3:
           // Grab id data
           $id = (int)HostInterface::GET('id', 0);

            $table = DAL::getFormalTableName("recvcontrol_Feeds");
            $table2 = DAL::getFormalTableName("recvcontrol_Subscriptions");
            
            if($id === 0) {
                $qstr = "SELECT {$table}.id, title, summary, url, type FROM {$table},{$table2} WHERE {$table}.subscription_id = {$table2}.id";
            }
            else {
                $qstr = "SELECT {$table}.id, title, summary, url, type FROM {$table},{$table2} WHERE {$table}.subscription_id = {$table2}.id AND {$table}.subscription_id = '{$id}'";
            }

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[101], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[76], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[109], 'field' => 'url', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'unsubscribe', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'recv.php', 'text' => $LANG01[68]
                    )

                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[56], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[103],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => "recv.php?cmd=3&id={$id}"
            );

            $query_arr = array(
                'table' => 'recvcontrol_Feeds',
                'sql' => $qstr . "",
                'query_fields' => array('title'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Groups', 'ADMIN_getListField_listrecvcontrol_SubscribersFeeds', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;
           break;


        // Finish Private Subscription Process
        case 49:
            // Grab token
            $token = HostInterface::GET('t', '');

            // Is it ok
            if($token === '') {
                header("Location: recv.php?msg=97");
                exit;
            }

            // Read in token, and break it into parts
            $token = base64_decode($token);

            if($token === FALSE) {
                header("Location: recv.php?msg=98");
                exit;
            }

            // Extract SSID from the token, and verify
            $parts = explode("?", $token);
            $p = new PublishingSecurityManagement();

            try {
                $mixed = $p->getSSIDParts($parts[0]);
            }
            catch(Exception $e) {
                header("Location: recv.php?msg=98");
                exit;
            }

            // Make sure we got a logical statement
            if($mixed["SSID"] !== $parts[0]) {
                header("Location: recv.php?msg=98");
                exit;
            }

            // Add the data to the panel
            $re = new ReceivingControlManagement();
            if($re->finishPrivateSubscription($mixed["SSID"], $parts[1]) === FALSE) {
                header("Location: recv.php?msg=100");
                exit;
            }
            else {
                header("Location: recv.php?msg=99");
            }
            
            break;


        // Create user specified file data
        case 50:
            // Get data and make sure it is valid
            $token = HostInterface::GET('d', '');

            // Is it ok
            if($token === '') {
                header("Location: recv.php?msg=91");
                exit;
            }

            // It is, let us write into the file
            file_put_contents($_CONF['path_html'] . '/pubcontrol/tokens/pubcontrol.tok', $token);

            // Make sure the file contents match
            if(file_get_contents($_CONF['path_html'] . '/pubcontrol/tokens/pubcontrol.tok') !== $token) {
                header("Location: recv.php?msg=93");
                exit;
            }

            header("Location: recv.php?msg=92");
            break;

    }
}
else {
     // Load panel
    $temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'manager2.thtml');
    $temp->set_var('lang_94', $LANG_PUBCONTROL_UPLUGIN[94]);
    $temp->set_var('lang_95', $LANG_PUBCONTROL_UPLUGIN[95]);
    $temp->set_var('lang_96', $LANG_PUBCONTROL_UPLUGIN[96]);

    $display .= $temp->parse_output();
    
}


$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);

/*
require_once '../../../lib-common.php';
if($_GET['cmd'] == '50') {
    file_put_contents($_CONF['path_html'] . '/pubcontrol/tokens/pubcontrol.tok', $_GET['d']);
    echo 'd';
}
 *
 */

?>