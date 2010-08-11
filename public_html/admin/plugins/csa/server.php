<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | CSA Plugin 1.0                                                            |
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
$G_CSAGroupId = 1;

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
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'main.thtml');
            $temp->set_var('lang_1', $LANG_CSA_UPLUGIN[1]);
            $temp->set_var('lang_2', $LANG_CSA_UPLUGIN[2]);
            $temp->set_var('lang_3', $LANG_CSA_UPLUGIN[3]);
            $temp->set_var('lang_4', $LANG_CSA_UPLUGIN[4]);
            $temp->set_var('lang_5', $LANG_CSA_UPLUGIN[5]);
            $temp->set_var('lang_6', $LANG_CSA_UPLUGIN[6]);
            $temp->set_var('lang_7', $LANG_CSA_UPLUGIN[7]);

            $display .= $temp->parse_output();
            break;
        case 2:
            // Technically this basically takes over a feed and works with it
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'cralert.thtml');
            $temp->set_var('lang_4', $LANG_CSA_UPLUGIN[4]);
            $temp->set_var('lang_27', $LANG_CSA_UPLUGIN[27]);
            $temp->set_var('lang_28', $LANG_CSA_UPLUGIN[28]);
            $temp->set_var('lang_29', $LANG_CSA_UPLUGIN[29]);
            $temp->set_var('lang_30', $LANG_CSA_UPLUGIN[30]);
            $temp->set_var('lang_31', $LANG_CSA_UPLUGIN[31]);
            $temp->set_var('lang_11', $LANG_CSA_UPLUGIN[11]);

            // Grab the feeds that are associated
            $pub = new PublishingFeedControl();
            $objects = $pub->listFeeds($G_CSAGroupId);

            if($objects === NULL) {
                header("Location: server.php?msg=33&cmd=1");
            }

            $var = "";
            foreach($objects as $obj) {
                $var .= "<option value='{$obj->_Id}'>{$obj->_Title}</option>";
            }

            $temp->set_var('value_0', '');
            $temp->set_var('value_1', '');
            $temp->set_var('hidden_0', '0');
            $temp->set_var('value_2', $var);

            $display .= $temp->parse_output();
            
            break;
        case 3:
            // Technically this basically takes over a feed and works with it
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'managealert.thtml');
            $temp->set_var('lang_6', $LANG_CSA_UPLUGIN[6]);
            $temp->set_var('lang_8', $LANG_CSA_UPLUGIN[8]);
            $temp->set_var('lang_9', $LANG_CSA_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_CSA_UPLUGIN[10]);
            $temp->set_var('lang_11', $LANG_CSA_UPLUGIN[11]);
            $temp->set_var('lang_12', $LANG_CSA_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_CSA_UPLUGIN[13]);
            $temp->set_var('lang_14', $LANG_CSA_UPLUGIN[14]);
            $temp->set_var('lang_15', $LANG_CSA_UPLUGIN[15]);

            $temp->set_var('value_0', '');
            $temp->set_var('valang_0', $LANG_CSA_UPLUGIN[12]);
            $temp->set_var('value_1', '');
            $temp->set_var('value_2', '1');
            $temp->set_var('hidden_0', '0');

            $display .= $temp->parse_output();
            break;
        case 4:
            // List all alert galleries
            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_CSA_UPLUGIN[18], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_CSA_UPLUGIN[19], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG_CSA_UPLUGIN[20], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_CSA_UPLUGIN[21], 'field' => 'show', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'server.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'server.php?cmd=2', 'text' => $LANG_CSA_UPLUGIN[4]
                    ),
                array(
                        'url' => 'server.php?cmd=3', 'text' => $LANG_CSA_UPLUGIN[6]
                    ),
                array(
                        'url' => 'server.php?cmd=4', 'text' => $LANG_CSA_UPLUGIN[7]
                    )
                );
            $retval .= COM_startBlock($LANG_CSA_UPLUGIN[7], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[1],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'server.php?cmd=4'
            );

            
            $pub = new PublishingFeedControl();
            $objects = $pub->listFeeds($G_CSAGroupId);
            $data_arr = array();

            if($objects !== NULL) {

                foreach($objects as $obj) {

                    $arr = array(
                        'title' => $obj->_Title,
                        'summary' => $obj->_Summary,
                        'type' => $obj->_Type,
                        'id' => $obj->_Id
                    );

                    $data_arr[] = $arr;
                }
            }

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_simpleList('ADMIN_getListField_CSA_ListGalleries', $header_arr,
                        $text_arr, $data_arr, '', $form_arr);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;
            break;
        case 5:
            // Create / edit alert gallery - simply add a feed with id 1 for GROUP
            // So grab data right now
            $id = DAL::applyFilter(HostInterface::POST("GEEKLOG_EDIT", ""));

            if($id === "") {
                header("Location: server.php?msg=16&cmd=3");
                exit;
            }

            $feedObject = new FeedObject();
            $feedObject->_Id = (int)$id;
            $feedObject->_Title = DAL::applyFilter(HostInterface::POST("GEEKLOG_NAME", ""));
            $feedObject->_Summary = DAL::applyFilter(HostInterface::POST("GEEKLOG_SUMMARY", ""));
            $feedObject->_AccessCode = "ATTG00000C";
            $feedObject->_Type = (int)DAL::applyFilter(HostInterface::POST("GEEKLOG_TYPE", ""));
            $feedObject->_GroupId = $G_CSAGroupId;

            if($feedObject->verify() === false) {
                header("Location: server.php?msg=16&cmd=3");
                exit;
            }

            // What do we do with it - is it a add or edit
            try {
                if($id == 0) {
                    $pub = new PublishingFeedControl();
                    $pub->createFeed($G_CSAGroupId, $feedObject);
                }
                else {
                    $pub = new PublishingFeedControl();
                    $pub->updateFeed($feedObject);
                }

                header("Location: server.php?msg=17&cmd=4");

            }
            catch(Exception $e) {
                header("Location: server.php?msg=16&cmd=3");
                exit;
            }

            break;
        case 6:
            // Delete a gallery
            $id = (int)HostInterface::GET('id', 0);

            if($id === 0) {
                header("Location: server.php?msg=22&cmd=4");
                exit;
            }
            
            // Simply send the delete request
            $pub = new PublishingFeedControl();
            try {
                $pub->deleteFeed($id);
                header("Location: server.php?msg=17&cmd=4");
            }
            catch(Exception $e) {
                header("Location: server.php?msg=25&cmd=4");
            }

            break;
        case 7:
            // Display the data for the selected gallery
            $id = (int)HostInterface::GET('id', 0);

            if($id === 0) {
                header("Location: server.php?msg=22&cmd=4");
                exit;
            }

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_CSA_UPLUGIN[18], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_CSA_UPLUGIN[26], 'field' => 'view', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'server.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'server.php?cmd=2', 'text' => $LANG_CSA_UPLUGIN[4]
                    ),
                array(
                        'url' => 'server.php?cmd=3', 'text' => $LANG_CSA_UPLUGIN[6]
                    ),
                array(
                        'url' => 'server.php?cmd=4', 'text' => $LANG_CSA_UPLUGIN[7]
                    )
                );
            $retval .= COM_startBlock($LANG_CSA_UPLUGIN[7], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[1],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'server.php?cmd=7&id='.$id
            );


            $pub = new PublishingDataControl();
            $objects = $pub->getDataForFeed($id);
            $data_arr = array();

            if($objects !== NULL) {

                foreach($objects as $obj) {

                    $arr = array(
                        'title' => $obj->_Title,
                        'data' => $obj->_Content,
                        'id' => $obj->_Id,
                        'fid' => $obj->_FeedId
                    );

                    $data_arr[] = $arr;
                }
            }

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_simpleList('ADMIN_getListField_CSA_ShowData', $header_arr,
                        $text_arr, $data_arr, '', $form_arr);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;
            break;

        case 8:
            // Edit the current gallery
            // Technically this basically takes over a feed and works with it
            $title = HostInterface::GET('t', '');
            $summary = HostInterface::GET('s', '');
            $type = (int)HostInterface::GET('q', 0);
            $id = (int)HostInterface::GET('id', 0);

            if($id === 0) {
                header("Location: server.php?msg=22&cmd=4");
                exit;
            }

            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'managealert.thtml');
            $temp->set_var('lang_6', $LANG_CSA_UPLUGIN[23]);
            $temp->set_var('lang_8', $LANG_CSA_UPLUGIN[8]);
            $temp->set_var('lang_9', $LANG_CSA_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_CSA_UPLUGIN[10]);
            $temp->set_var('lang_11', $LANG_CSA_UPLUGIN[11]);
            $temp->set_var('lang_12', $LANG_CSA_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_CSA_UPLUGIN[13]);
            $temp->set_var('lang_14', $LANG_CSA_UPLUGIN[14]);
            $temp->set_var('lang_15', $LANG_CSA_UPLUGIN[23]);

            $temp->set_var('value_0', $title);
            $temp->set_var('valang_0', ($type == 1) ? $LANG_CSA_UPLUGIN[TypeObject::getNameForInteger(1)] :  $LANG_CSA_UPLUGIN[TypeObject::getNameForInteger(2)] );
            $temp->set_var('value_1', $summary);
            $temp->set_var('value_2', ($type == 1) ? '1' : '2');
            $temp->set_var('hidden_0', $id);

            $display .= $temp->parse_output();
            break;
         case 9:
             // Edit data entry
             $id = (int)HostInterface::GET('id', 0);

             if($id === 0) {
                 header("Location: server.php?msg=22&cmd=4");
                 exit;
             }

             // Now grab data
             $pub = new PublishingDataControl();
             $object = $pub->getDataForFeed($id, TRUE);

             if($object === NULL) {
                 header("Location: server.php?msg=22&cmd=4");
                 exit;
             }

            // Technically this basically takes over a feed and works with it
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/csa/templates/', 'cralert.thtml');
            $temp->set_var('lang_4', $LANG_CSA_UPLUGIN[34]);
            $temp->set_var('lang_27', $LANG_CSA_UPLUGIN[35]);
            $temp->set_var('lang_28', $LANG_CSA_UPLUGIN[28]);
            $temp->set_var('lang_29', $LANG_CSA_UPLUGIN[29]);
            $temp->set_var('lang_30', $LANG_CSA_UPLUGIN[30]);
            $temp->set_var('lang_31', $LANG_CSA_UPLUGIN[36]);
            $temp->set_var('lang_11', $LANG_CSA_UPLUGIN[11]);

            // Grab the feeds that are associated
            $pub = new PublishingFeedControl();
            $objects = $pub->listFeeds($G_CSAGroupId);

            if($objects === NULL) {
                header("Location: server.php?msg=33&cmd=1");
            }

            $var = "";
            foreach($objects as $obj) {

                if($obj->_Id === $object[0]->_FeedId) {
                    $var .= "<option selected='selected' value='{$obj->_Id}'>{$obj->_Title}</option>";
                }
                else {
                    $var .= "<option value='{$obj->_Id}'>{$obj->_Title}</option>";
                }
            }

            $temp->set_var('value_0', $object[0]->_Title);
            $temp->set_var('value_1', $object[0]->_Content);
            $temp->set_var('hidden_0', $object[0]->_Id);
            $temp->set_var('value_2', $var);

            $display .= $temp->parse_output();

             break;
         case 10:
             // Remove a data entry
             $id = (int)HostInterface::GET('id', 0);
             $fid = (int)HostInterface::GET('fid', 0);

             if( ($id === 0) || ($fid === 0)) {
                 header("Location: server.php?msg=22&cmd=4");
                 exit;
             }

             $pub = new PublishingDataControl();
             $pub->deleteData($id, $fid);

             header("Location: server.php?msg=17&cmd=7&id={$fid}");
             break;
        case 11:
            // Add / Edit CSA alert
            // Grab data
            $id = HostInterface::POST('GEEKLOG_EDIT', '');
            $name = HostInterface::POST('GEEKLOG_NAME', '');
            $summary = DAL::applyFilter(HostInterface::POST('GEEKLOG_SUMMARY', ''));
            $gallery = (int)HostInterface::POST('GEEKLOG_GALLERY', 0);

            if( ($gallery === 0) || ($id === '')){
                header("Location: server.php?cmd=1&msg=16");
                exit;
            }

            $id = (int)$id;

            $DObject = new DataObject();
            $DObject->_Title = $name;
            $DObject->_Content = $summary;
            $DObject->_FeedId = $gallery;
            $DObject->_Id = $id;

            try {

                $pub = new PublishingDataControl();

                if($id === 0) {
                    // Add CSA Alert
                    $pub->addData($DObject);
                }
                else {
                    // Edit CSA Alert
                    $pub->editData($DObject);
                }

                header("Location: server.php?msg=17&cmd=1");
            }
            catch(Exception $e) {
                header("Location: server.php?msg=16&cmd=1");
                exit;
            }

            
            break;
        default:
            header("Location: server.php?cmd=1");
            exit;
    }

}
else {
            header("Location: server.php?cmd=1");
            exit;
}


$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);


