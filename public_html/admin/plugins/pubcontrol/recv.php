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
        // Unsubscribe Private
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
           $ac =  DAL::applyFilter(HostInterface::GET('ac', ''));

            $table = DAL::getFormalTableName("recvcontrol_Feeds");
            $table2 = DAL::getFormalTableName("recvcontrol_Subscriptions");
            
            $ac = $ac === '' ? "" :  " AND {$table}.access_code = '{$ac}'";

            if($id === 0) {
                $qstr = "SELECT {$table}.id, title, summary, url, {$table2}.type, {$table}.subscription_id FROM {$table},{$table2} WHERE {$table}.subscription_id = {$table2}.id {$ac}";
            }
            else {
                $qstr = "SELECT {$table}.id, title, summary, url, {$table2}.type, {$table}.subscription_id FROM {$table},{$table2} WHERE {$table}.subscription_id = {$table2}.id AND {$table}.subscription_id = '{$id}' {$ac}";
            }

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[40], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[101], 'field' => 'url', 'sort' => true),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false)

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
       // List all feeds subscribed
       case 4:
           $id = (int)HostInterface::GET("id", 0);
           $gid = (int)HostInterface::GET("gid", 0);
           
           // Make sure they added it
           if($id === 0) {
               header("Location: recv.php?cmd=3&id={$gid}&msg=15");
               exit;
           }

           // Call the execution method
           $r = new ReceivingControlManagement();
           $r->deleteFeed($id);
           header("Location: recv.php?cmd=3&id={$gid}&msg=110");
           break;
       case 5:
           // Show the user the form data to load the URL
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'subscribe2.thtml');
            $a = HostInterface::GET("trck", '');
            
            $temp->set_var('hidden_a', $a);
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[103] . $LANG_PUBCONTROL_UPLUGIN[112]);
            $temp->set_var('lang_81', $LANG_PUBCONTROL_UPLUGIN[113]);
            $temp->set_var('lang_77', $LANG_PUBCONTROL_UPLUGIN[114]);
            $temp->set_var('lang_8', $LANG_PUBCONTROL_UPLUGIN[115]);
            $temp->set_var('lang_12', $LANG_PUBCONTROL_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_PUBCONTROL_UPLUGIN[13]);
            $temp->set_var('lang_80', $LANG_PUBCONTROL_UPLUGIN[111]);
            $display .= $temp->parse_output();

           break;
           // Return from user subscribe
       case 6:
           // Grab data being sent in
           $url = HostInterface::POST("GEEKLOG_URL", '');
           $type = (int)HostInterface::POST("GEEKLOG_PUBGTYPE", 0);
           $track = HostInterface::POST("GEEKLOG_BACKTRACKADDR");
           $xx = urlencode($url);
           
           $components = parse_url($url);
#           $url = urlencode($url);

           // Make sure the data is valid
           if( ($url === '') || ( ($type !== 1) && ($type !== 2)) || ($components['scheme'] != 'http')) {
               header("Location: recv.php?msg=116&cmd=5");
               exit;
           }

           // Read in the value from the site to make sure it is valid
           $value = @file_get_contents($url . '/pubcontrol/scvalidate.php');
           
           if($value === FALSE) {
               header("Location: recv.php?msg=116&cmd=5");
               exit;
           }
           
           // Convert to an integer to read in the value
           $value = (int)$value;

           if($value === 0) {
               header("Location: recv.php?msg=116&cmd=5");
               exit;
           }

           // Get supported types
           // Check to see if the subscription exists
           $r = new ReceivingControlManagement();
           $sid = $r->subscriptionExists($url, ($type === TypeObject::O_PRIVATE) ? TypeObject::O_PRIVATE : TypeObject::O_PUBLIC);
           
           if($sid !== FALSE) {
               // Already exists
               if($track == '') {
                   header("Location: recv.php?msg=100");
               }
               else {
                   header("Location: {$track}&sid={$sid}&url={$xx}&type={$type}");
               }
               exit;
           }

           // Is it private or public
           if($type === 1) {
               // Private
               // Redirect User to the Appropriate Site ()
               if( ($value & 0x04) !== 0x04) {
                   // Private not supported
                   header("Location: recv.php?msg=117&cmd=5");
                   exit;
               }

               // Display a link to register at
               $display .= <<<OUTPUT
<br />
{$LANG_PUBCONTROL_UPLUGIN[103]} {$LANG_PUBCONTROL_UPLUGIN[112]}
<br /><br />
<a href="{$url}/pubcontrol/manage.php">{$LANG_PUBCONTROL_UPLUGIN[119]}</a> {$LANG_PUBCONTROL_UPLUGIN[120]}
OUTPUT;
           }
           else {
               if( ($value & 0x02) !== 0x02) {
                   // Public not supported
                   header("Location: recv.php?msg=118&cmd=5");
                   exit;
               }

               // Automatically Register them
               $r->finishPublicSubscription($url, $sids);
               // Already subscribed
               if($track == '') {
                   header("Location: recv.php?msg=99");
               }
               else {
                   header("Location: {$track}&sid={$sid}&url={$xx}");
               }
           }
           
           break;
        // Unsubscribe Public
        case 7:
            // Grab incoming
            $url = HostInterface::GET('url', '');
            $id = (int)HostInterface::GET('id', 0);
            
            // Make sure the data is valid
            if(($url === '') || ($id === 0)) {
                header("Location: recv.php?msg=106");
                exit;
            }

            // Remove data
            $re = new ReceivingControlManagement();
            $re->unSubscribe($id);

            // Redirect to clause
            header("Location: recv.php?cmd=1&msg=105");
            break;
       // List all the groups from an address
       case 8:
            // Get group listing
            $host = DAL::applyFilter(HostInterface::GET('q', ''));
            $r = (int)DAL::applyFilter(HostInterface::GET('r', 0), true);
            $s = (int)DAL::applyFilter(HostInterface::GET('s', 0));

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[19], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[56], 'field' => 'feeds', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'recv.php', 'text' => $LANG01[68]
                    )

                );
            $name_r = $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($r)];
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[122] . '<a href="'.$host.'"><i>`' . $host . '`</i></a>' . " ({$name_r})<br />{$LANG_PUBCONTROL_UPLUGIN[127]}", '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[103],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => "recv.php?cmd=8&q={$host}"
            );

            $objects = ReceivingControlManagement::listGroups($host);

            if($objects === NULL) {
                // Error occured, exit out
                header("Location: recv.php?msg=123");
                exit;
            }

            $data_arr = array();

            foreach($objects as $obj) {

                # Is it private
                if( ($obj->_Type !== $r) && ($r === TypeObject::O_PUBLIC)) {
                    continue;
                }

                $arr = array(
                    'title' => $obj->_Title,
                    'summary' => $obj->_Summary,
                    'type' => $obj->_Type,
                    'id' => $obj->_Id,
                    'url' => $host,
                    'sub_id' => $s,
                    'rep_id' => $r
                );

                $data_arr[] = $arr;
            }

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_simpleList('ADMIN_getListField_listrecvcontrol_SubscribersFeeds', $header_arr,
                        $text_arr, $data_arr, '', $form_arr);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

           break;
       case 9:
           // List all subscriptions
            $table = DAL::getFormalTableName("recvcontrol_Subscriptions");

            $qstr = "SELECT url, id, type FROM {$table}";

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[101], 'field' => 'url', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[125], 'field' => 'feed', 'sort' => false)
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'recv.php', 'text' => $LANG01[68]
                    )

                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[126], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[103],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => "recv.php?cmd=9"
            );

            $query_arr = array(
                'table' => DAL::getFormalTableName("recvcontrol_Subscriptions"),
                'sql' => $qstr,
                'query_fields' => array('url'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Groups', 'ADMIN_getListField_listrecvcontrol_SubscribersList', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;


       break;
       // List all the feeds from an address
       case 10:
            // Get group listing
            $host = DAL::applyFilter(HostInterface::GET('q', ''));
            $gid = (int)HostInterface::GET('d', 0);
            $title = DAL::applyFilter(HostInterface::GET('t', ''));
            $r = (int)DAL::applyFilter(HostInterface::GET('r', 0));
            $s = (int)DAL::applyFilter(HostInterface::GET('s', 0));
            $trck = HostInterface::GET('trck', '');

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[19], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[129], 'field' => 'gidselect', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'recv.php', 'text' => $LANG01[68]
                    )

                );
            $name_r = $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($r)];
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[130] . '<a href="'.$host.'"><i>`' . $host . '`</i></a>' . " ({$name_r}) {$LANG_PUBCONTROL_UPLUGIN[131]} `{$title}`", '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[103],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => "recv.php?cmd=10&q={$host}&d={$gid}"
            );

            $objects = ReceivingControlManagement::listFeeds($host, $gid);

            if($objects === NULL) {
                // Error occured, exit out
                header("Location: recv.php?msg=123");
                exit;
            }

            $data_arr = array();
            $receiving = new ReceivingControlManagement();

            foreach($objects as $obj) {
                // First check if that feed is already in the database
                if($receiving->feedExists($obj->_Id) === true) {
                    continue;
                }

                # Is it private
                if( ($obj->_Type !== $r) && ($r === TypeObject::O_PUBLIC)) {
                    continue;
                }

                $arr = array(
                    'title' => $obj->_Title,
                    'summary' => $obj->_Summary,
                    'type' => $obj->_Type,
                    'id' => $obj->_Id,
                    'group_id' => $obj->_GroupId,
                    'access_code' => $obj->_AccessCode,
                    'sub_id' => $s,
                    'trck' => $trck
                );

                $data_arr[] = $arr;
            }

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_simpleList('ADMIN_getListField_listrecvcontrol_SubscribersFeeds', $header_arr,
                        $text_arr, $data_arr, '', $form_arr);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

           break;
       case 11:
           // Get incoming data
           $summary = DAL::applyFilter(HostInterface::GET('sum', ''));
           $gid = (int)DAL::applyFilter(HostInterface::GET('gid', 0));
           $title = DAL::applyFilter(HostInterface::GET('t', ''));
           $type = (int)DAL::applyFilter(HostInterface::GET('r', 0));
           $sid = (int)DAL::applyFilter(HostInterface::GET('s', 0));
           $access = DAL::applyFilter(HostInterface::GET('a', ''));
           $id =  (int)DAL::applyFilter(HostInterface::GET('fid', 0));
           $trck = DAL::applyFilter(HostInterface::GET('trck', ''));

           // Make sure it is valid
           if( ($summary === '') || ($gid === 0) || ($title === '') || ($type === 0) || ($sid === 0) || ($access === '') || ($id === 0)) {
               header("Location: recv.php?msg=15");
               exit;
           }

           // Attempt to add it
           $feed = new FeedObject();
           $feed->_Id = $id;
           $feed->_Title = $title;
           $feed->_Summary = $summary;
           $feed->_Type = $type;
           $feed->_GroupId = $gid;
           $feed->_AccessCode = $access;

           $re = new ReceivingControlManagement();
           $re->addFeed($feed, $sid);

           if($trck === '') {
               header("Location: recv.php?msg=132");
           }
           else {
               $trck = base64_decode($trck);
               header("Location: {$trck}");
           }

           break;

       # This is a funny test
       case 1000:
#           echo "This is a test<br />";

           $r = new ReceivingControlManagement();
           $r->doDataScrape();
#           $t = $r->collectFeedData("http://localhost/gsoc/gsoc-2010-tpatrick/public_html/pubcontrol/", 2);
           #var_dump($t);
           $display .= "TEST -- MANUAL SCRAPE OF DATA (Will be performed by a g-cron job)";
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
    // Redirect to the main loading page
    if(isset($_GET['msg'])) {
        header("Location: index.php?msg={$_GET['msg']}");
    }
    else {
       header("Location: index.php");
    }
    
    exit;
    
     // Load panel
    $temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'manager2.thtml');
    $temp->set_var('lang_94', $LANG_PUBCONTROL_UPLUGIN[94]);
    $temp->set_var('lang_95', $LANG_PUBCONTROL_UPLUGIN[95]);
    $temp->set_var('lang_96', $LANG_PUBCONTROL_UPLUGIN[96]);
    $temp->set_var('lang_102', $LANG_PUBCONTROL_UPLUGIN[102]);
    $temp->set_var('lang_109', $LANG_PUBCONTROL_UPLUGIN[109]);
    $temp->set_var('lang_111', $LANG_PUBCONTROL_UPLUGIN[111]);
    $temp->set_var('lang_124', $LANG_PUBCONTROL_UPLUGIN[124]);

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