<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Pubcontrol Plugin 1.0                                                     |
// +---------------------------------------------------------------------------+
// | publ.php                                                                 |
// |                                                                           |
// |  The main page for administrative                                         |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010 by the following author:                               |
// |                                                                           |
// | Authors: Tim Patrick      - timpatrick12 AT gmail DOT com                 |
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
        case 1:
            // Show options of what to do
            // Redirect to the main loading page
            if(isset($_GET['msg'])) {
                header("Location: index.php?msg={$_GET['msg']}");
            }
            else {
               header("Location: index.php");
            }

            exit;

            $dal = new DAL();
            $count_subs = $dal->count("pubcontrol_Subscribers", 'mode', 'A');
            
            $temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'manager.thtml');
            $temp->set_var('lang_22', $LANG_PUBCONTROL_UPLUGIN[22]);
            $temp->set_var('lang_23', $LANG_PUBCONTROL_UPLUGIN[23]);
            $temp->set_var('lang_25', $LANG_PUBCONTROL_UPLUGIN[25]);
            $temp->set_var('lang_26', $LANG_PUBCONTROL_UPLUGIN[26]);
            $temp->set_var('lang_27', $LANG_PUBCONTROL_UPLUGIN[27]);
            $temp->set_var('lang_43', $LANG_PUBCONTROL_UPLUGIN[43]);
            $temp->set_var('lang_32', $LANG_PUBCONTROL_UPLUGIN[32]);
            $temp->set_var('lang_36', $LANG_PUBCONTROL_UPLUGIN[36]);
            $temp->set_var('lang_46', $LANG_PUBCONTROL_UPLUGIN[46]);
            $temp->set_var('lang_49', $LANG_PUBCONTROL_UPLUGIN[49]);
            $temp->set_var('lang_50', $LANG_PUBCONTROL_UPLUGIN[50]);
            $temp->set_var('lang_57', $LANG_PUBCONTROL_UPLUGIN[57]);
            $temp->set_var('lang_58', "({$count_subs}) " . $LANG_PUBCONTROL_UPLUGIN[58] );
            $temp->set_var('lang_59', $LANG_PUBCONTROL_UPLUGIN[59]);
            
            $display .= $temp->parse_output();
            break;
        case 2:
            // Create new group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'group_manage.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[23]);
            $temp->set_var('lang_6', $LANG_PUBCONTROL_UPLUGIN[6]);
            $temp->set_var('lang_7', $LANG_PUBCONTROL_UPLUGIN[7]);
            $temp->set_var('lang_8', $LANG_PUBCONTROL_UPLUGIN[8]);
            $temp->set_var('lang_9', $LANG_PUBCONTROL_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_PUBCONTROL_UPLUGIN[10]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('lang_12', $LANG_PUBCONTROL_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_PUBCONTROL_UPLUGIN[13]);
            $temp->set_var('value_0', '');
            $temp->set_var('value_1', '');
            $temp->set_var('value_2', '1');
            $temp->set_var('hidden_0', '0');
            $temp->set_var('valang_0', $LANG_PUBCONTROL_UPLUGIN[12]);
            // 

            $display .= $temp->parse_output();
            break;
        case 3:
            // Modify Existing Group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'group_manage.thtml');

            // Look for the id GET parameter that states what group id it is
            $id = isset($_GET['id']) ? $_GET['id'] : 0;

            // Grab values
            $group = new PublishingGroupControl();
            $grp = $group->getGroupData($id);

            if($grp === NULL) {
                header("Location: publ.php?msg=15");
            }

            // Set values
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '.  $LANG_PUBCONTROL_UPLUGIN[23]);
            $temp->set_var('lang_6', $LANG_PUBCONTROL_UPLUGIN[6]);
            $temp->set_var('lang_7', $LANG_PUBCONTROL_UPLUGIN[7]);
            $temp->set_var('lang_8', $LANG_PUBCONTROL_UPLUGIN[8]);
            $temp->set_var('lang_9', $LANG_PUBCONTROL_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_PUBCONTROL_UPLUGIN[14]); // Edit Group
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('lang_12', $LANG_PUBCONTROL_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_PUBCONTROL_UPLUGIN[13]);
            $temp->set_var('value_0', $grp->_Title);
            $temp->set_var('value_1', $grp->_Summary);
            $temp->set_var('value_2', $grp->_Type);
            $temp->set_var('hidden_0', $grp->_Id);
            $temp->set_var('valang_0', $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($grp->_Type)]);

            $display .= $temp->parse_output();
            break;
        case 4:
            // List all groups in the database
            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[19], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[39], 'field' => 'load_feeds', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false)
                
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=2', 'text' => $LANG_PUBCONTROL_UPLUGIN[23]
                    ),
                array(
                        'url' => 'publ.php?cmd=4', 'text' => $LANG_PUBCONTROL_UPLUGIN[22]
                    )
                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[22], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=4'
            );

            $table = DAL::getFormalTableName("pubcontrol_Groups");
            $qstr = "SELECT * FROM {$table}";

            $query_arr = array(
                'table' => 'pubcontrol_Groups',
                'sql' => $qstr . " WHERE nodisplay = 'N'",
                'query_fields' => array('title'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Groups', 'ADMIN_getListField_listpubcontrol_groups', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 5:
            // Grab access codes
            $accesscodes = AccessCodeControl::getAccessCodes(); // AccessCodes::getCodes
            $option = "";
            
            foreach($accesscodes as $value)
            {
                $option .= "<option value='{$value->_AccessCode}'>{$value->_AccessCode} - {$value->_Name}</option>";
            }

            $gcontrol = new PublishingGroupControl();
            $groups = $gcontrol->listGroups();
            $option2 = "";

            foreach($groups as $value)
            {
                $option2 .= "<option value='{$value->_Id}'>{$value->_Title}</option>";
            }

            // Make sure there are access codes or groups
            if((count($accesscodes) === 0) || (count($groups) === 0)) {
                header("Location: publ.php?msg=44");
                exit;
            }

            // Create new group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'feed_manage.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[32]);
            $temp->set_var('lang_28', $LANG_PUBCONTROL_UPLUGIN[28]);
            $temp->set_var('lang_30', $LANG_PUBCONTROL_UPLUGIN[30]);
            $temp->set_var('lang_29', $LANG_PUBCONTROL_UPLUGIN[29]);
            $temp->set_var('lang_12', $LANG_PUBCONTROL_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_PUBCONTROL_UPLUGIN[13]);
            $temp->set_var('lang_16', $LANG_PUBCONTROL_UPLUGIN[16]);
            $temp->set_var('lang_33', $LANG_PUBCONTROL_UPLUGIN[33]);
            $temp->set_var('lang_31', $LANG_PUBCONTROL_UPLUGIN[31]);
            $temp->set_var('lang_32', $LANG_PUBCONTROL_UPLUGIN[32]);
            $temp->set_var('lang_34', $LANG_PUBCONTROL_UPLUGIN[34]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('hidden_0', '0');
            $temp->set_var('value_0', '');
            $temp->set_var('value_1', '');
            $temp->set_var('value_2', '1');
            $temp->set_var('value_4', $option);
            $temp->set_var('value_5', $option2);
            $temp->set_var('valang_0', $LANG_PUBCONTROL_UPLUGIN[12]);
            //

            $display .= $temp->parse_output();
            break;
            // Modify Feed
        case 6:
            // Get feed data
            $feed = new PublishingFeedControl();
            // Look for the id GET parameter that states what group id it is
            $id = isset($_GET['id']) ? $_GET['id'] : 0;
            
            $feeds = $feed->listFeeds($id, TRUE);

            if(count($feeds) == 0) {
                header("Location: publ.php?msg=15");
            }

            // Grab access codes
            $accesscodes = AccessCodeControl::getAccessCodes(); // AccessCodes::getCodes
            $option = "";
            
            foreach($accesscodes as $value)
            {
                if($feeds[0]->_AccessCode === $value->_AccessCode) {
                    $option .= "<option selected='selected' value='{$value->_AccessCode}'>{$value->_AccessCode} - {$value->_Name}</option>";
                }
                else {
                    $option .= "<option value='{$value->_AccessCode}'>{$value->_AccessCode} - {$value->_Name}</option>";
                }
            }

            $gcontrol = new PublishingGroupControl();
            $groups = $gcontrol->listGroups();
            $option2 = "";

            foreach($groups as $value)
            {
                if($feeds[0]->_GroupId === $value->_Id) {
                    $option2 .= "<option selected='selected' value='{$value->_Id}'>{$value->_Title}</option>";
                }
                else {
                    $option2 .= "<option value='{$value->_Id}'>{$value->_Title}</option>";
                }
            }

            // Create new group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'feed_manage.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[35]);
            $temp->set_var('lang_28', $LANG_PUBCONTROL_UPLUGIN[28]);
            $temp->set_var('lang_30', $LANG_PUBCONTROL_UPLUGIN[30]);
            $temp->set_var('lang_29', $LANG_PUBCONTROL_UPLUGIN[29]);
            $temp->set_var('lang_12', $LANG_PUBCONTROL_UPLUGIN[12]);
            $temp->set_var('lang_13', $LANG_PUBCONTROL_UPLUGIN[13]);
            $temp->set_var('lang_16', $LANG_PUBCONTROL_UPLUGIN[16]);
            $temp->set_var('lang_33', $LANG_PUBCONTROL_UPLUGIN[33]);
            $temp->set_var('lang_31', $LANG_PUBCONTROL_UPLUGIN[31]);
            $temp->set_var('lang_32', $LANG_PUBCONTROL_UPLUGIN[35]);
            $temp->set_var('lang_34', $LANG_PUBCONTROL_UPLUGIN[34]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('hidden_0', $feeds[0]->_Id);
            $temp->set_var('value_0', $feeds[0]->_Title);
            $temp->set_var('value_1', $feeds[0]->_Summary);
            $temp->set_var('value_2', $feeds[0]->_Type);
            $temp->set_var('value_4', $option);
            $temp->set_var('value_5', $option2);
            $temp->set_var('valang_0', $LANG_PUBCONTROL_UPLUGIN[TypeObject::getNameForInteger($feeds[0]->_Type)]);
            //

            $display .= $temp->parse_output();
            break;
        case 7:
            // List all feeds in the database

            // See if there is an ID after all
            $id = (int)HostInterface::GET('gid', 0);

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[40], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'type', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[37], 'field' => 'access_code', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[38], 'field' => 'load_data', 'sort' => false),
#                array('text' => $LANG_PUBCONTROL_UPLUGIN[21], 'field' => 'group_id', 'sort' => true),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=5', 'text' => $LANG_PUBCONTROL_UPLUGIN[32]
                    ),
                array(
                        'url' => 'publ.php?cmd=7', 'text' => $LANG_PUBCONTROL_UPLUGIN[36]
                    )
                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[36], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => ($id === 0) ? 'publ.php?cmd=7' : 'publ.php?cmd=7&gid='.$id
            );

            $table = DAL::getFormalTableName("pubcontrol_Feeds");

            if($id === 0) {
                $qstr = "SELECT * FROM {$table} WHERE 1=1";
            }
            else {
                $qstr = "SELECT * FROM {$table} WHERE group_id = '{$id}'";
            }

            $query_arr = array(
                'table' => 'pubcontrol_Feeds',
                'sql' => $qstr,
                'query_fields' => array('title'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Feeds', 'ADMIN_getListField_listpubcontrol_feeds', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 8:
            // Edit security group
            // Grab ID data
            // Look for the id GET parameter that states what group id it is
            $group = new PublishingSecurityManagement();
            $id = (int) isset($_GET['id']) ? $_GET['id'] : 0;

            if($id === 0) {
                header("Location: publ.php?msg=15");
                exit;
            }

            $data = $group->getSGroupData($id);
            
            // Make template
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'securityg_manage.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[47]);
            $temp->set_var('lang_45', $LANG_PUBCONTROL_UPLUGIN[45]);
            $temp->set_var('lang_7', $LANG_PUBCONTROL_UPLUGIN[7]);
            $temp->set_var('lang_9', $LANG_PUBCONTROL_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_PUBCONTROL_UPLUGIN[14]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('value_0', $data->_Title);
            $temp->set_var('value_1', $data->_Summary);
            $temp->set_var('hidden_0', $data->_Id);
            //

            $display .= $temp->parse_output();
            break;
        case 9:
            // Create new security group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'securityg_manage.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[46]);
            $temp->set_var('lang_45', $LANG_PUBCONTROL_UPLUGIN[45]);
            $temp->set_var('lang_7', $LANG_PUBCONTROL_UPLUGIN[7]);
            $temp->set_var('lang_9', $LANG_PUBCONTROL_UPLUGIN[9]);
            $temp->set_var('lang_10', $LANG_PUBCONTROL_UPLUGIN[10]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('value_0', '');
            $temp->set_var('value_1', '');
            $temp->set_var('hidden_0', '0');
            //

            $display .= $temp->parse_output();
            break;
        case 10:
            // List all security groups in the database

            // See if there is an ID after all
            $id = (int)HostInterface::GET('gid', 0);

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[19], 'field' => 'title', 'sort' => true),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[20], 'field' => 'summary', 'sort' => false),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[51], 'field' => 'assign', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[56], 'field' => 'assigned', 'sort' => false)


            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=9', 'text' => $LANG_PUBCONTROL_UPLUGIN[46]
                    ),
                array(
                        'url' => 'publ.php?cmd=10', 'text' => $LANG_PUBCONTROL_UPLUGIN[49]
                    )
                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[49], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=10'
            );

            $table = DAL::getFormalTableName("pubcontrol_Security");

#            if($id === 0) {
                $qstr = "SELECT * FROM {$table} WHERE 1=1";
##            }

            $query_arr = array(
                'table' => 'pubcontrol_Security',
                'sql' => $qstr,
                'query_fields' => array('title'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Security', 'ADMIN_getListField_listpubcontrol_sgroup', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 11:
            // Add feed to security group

            $id = (int) isset($_GET['id']) ? $_GET['id'] : 0;

            if($id === 0) {
                header("Location: publ.php?msg=15");
                exit;
            }

            // Grab all feeds
            $feedobj = new PublishingFeedControl();
            $feeds = $feedobj->listFeeds();

            // Grab all feeds in use
            $tp = new PublishingSecurityManagement();
            $sfeeds = $tp->getFeedsAssignedToGroup($id);

            foreach($feeds as $key => $value) {
                foreach($sfeeds as $int) {
                    if($value->_Id === $int) {
                        unset($feeds[$key]);
                        break;
                    }
                }
            }
            
            // Create the data variable
            $sel = '';
            foreach($feeds as $value) {
                $sel .= "<option value='{$value->_Id}'>{$value->_Title}</option>";
            }
                        

            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'assign_sgroupdata.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[53]);
            $temp->set_var('lang_54', $LANG_PUBCONTROL_UPLUGIN[54]);
            $temp->set_var('lang_52', $LANG_PUBCONTROL_UPLUGIN[52]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('lang_53', $LANG_PUBCONTROL_UPLUGIN[53]);
            $temp->set_var('data_0', $sel);
            $temp->set_var('var_a', 'sgroup_addfeed');
            $temp->set_var('id_0', $id);
            
            $display .= $temp->parse_output();
            break;
        case 12:
            // List feeds associated with the feeds

            // See if there is an ID after all
            $id = (int)HostInterface::GET('id', 0);

            if($id === 0) {
                header("Location: publ.php?msg=15");
                exit;
            }

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[40], 'field' => 'title', 'sort' => false),
                array('text' => $LANG_ACCESS['remove'], 'field' => 'delete', 'sort' => false)
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=9', 'text' => $LANG_PUBCONTROL_UPLUGIN[46]
                    ),
                array(
                        'url' => 'publ.php?cmd=10', 'text' => $LANG_PUBCONTROL_UPLUGIN[49]
                    )
                );
            
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[55], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=12&id='.$id
            );

            $table = DAL::getFormalTableName("pubcontrol_SecurityGroupLink");
            $table2 = DAL::getFormalTableName("pubcontrol_Feeds");

#            if($id === 0) {
                $qstr = "SELECT securitygroup_id, feed_id, title FROM {$table}, {$table2} WHERE securitygroup_id = '{$id}' AND feed_id = id";
##            }

            $query_arr = array(
                'table' => 'pubcontrol_SecurityGroupLink',
                'sql' => $qstr,
                'query_fields' => array('feed_id'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_SecurityGroupLink', 'ADMIN_getListField_listpubcontrol_sgroupfeed', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 13:
            // List all subscribers in the DB awaiting approval

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[60], 'field' => 'url', 'sort' => true),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[61], 'field' => 'approve', 'sort' => false),
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=14', 'text' => $LANG_PUBCONTROL_UPLUGIN[62]
                    )
                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[63], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=13'
            );

            $table = DAL::getFormalTableName("pubcontrol_Subscribers");

#            if($id === 0) {
                $qstr = "SELECT * FROM {$table} WHERE mode = 'A'";
##            }

            $query_arr = array(
                'table' => 'pubcontrol_Subscribers',
                'sql' => $qstr,
                'query_fields' => array('url'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Subscribers', 'ADMIN_getListField_listpubcontrol_submod', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 14:
            // List all subscribers in the DB awaiting approval

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[60], 'field' => 'url', 'sort' => true),
                array('text' => $LANG01[28], 'field' => 'delete', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[65] . ' / ' . $LANG_PUBCONTROL_UPLUGIN[67], 'field' => 'suspend', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[68], 'field' => 'assign', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[69], 'field' => 'assigned', 'sort' => false),
                array('text' => $LANG_PUBCONTROL_UPLUGIN[76], 'field' => 'ssid', 'sort' => false)

            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=13', 'text' => $LANG_PUBCONTROL_UPLUGIN[63]
                    )
                );
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[62], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=14'
            );

            $table = DAL::getFormalTableName("pubcontrol_Subscribers");

#            if($id === 0) {
                $qstr = "SELECT * FROM {$table} WHERE mode <> 'A'";
##            }

            $query_arr = array(
                'table' => 'pubcontrol_Subscribers',
                'sql' => $qstr,
                'query_fields' => array('url'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_Subscribers', 'ADMIN_getListField_listpubcontrol_subnorm', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;
        case 15:
            // Add feed to security group

            $id = (int) isset($_GET['id']) ? $_GET['id'] : 0;

            if($id === 0) {
                header("Location: publ.php?msg=15");
                exit;
            }

            // Grab all feeds
            $feedobj = new PublishingSecurityManagement();
            $feeds = $feedobj->getSGroupData();

            // Grab all feeds in use
            $tp = new PublishingSecurityManagement();
            $sfeeds = $tp->getGroupsAssignedToSubscriber($id);
            foreach($feeds as $key => $value) {
                foreach($sfeeds as $int) {
                    if($value->_Id === $int) {
                        unset($feeds[$key]);
                        break;
                    }
                }
            }

            // Create the data variable
            $sel = '';
            foreach($feeds as $value) {
                $sel .= "<option value='{$value->_Id}'>{$value->_Title}</option>";
            }


            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'assign_sgroupdata.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[5] . ' - '. $LANG_PUBCONTROL_UPLUGIN[71]);
            $temp->set_var('lang_54', $LANG_PUBCONTROL_UPLUGIN[73]);
            $temp->set_var('lang_52', $LANG_PUBCONTROL_UPLUGIN[72]);
            $temp->set_var('lang_11', $LANG_PUBCONTROL_UPLUGIN[11]);
            $temp->set_var('lang_53', $LANG_PUBCONTROL_UPLUGIN[74]);
            $temp->set_var('data_0', $sel);
            $temp->set_var('id_0', $id);
            $temp->set_var('var_a', 'sub_addgroup');

            $display .= $temp->parse_output();
 
            break;
       case 16:
            // List feeds associated with the feeds

            // See if there is an ID after all
            $id = (int)HostInterface::GET('id', 0);

            if($id === 0) {
                header("Location: publ.php?msg=15");
                exit;
            }

            $retval = '';
            $header_arr = array( # display 'text' and use table field 'field'
                array('text' => $LANG_PUBCONTROL_UPLUGIN[19], 'field' => 'title', 'sort' => false),
                array('text' => $LANG_ACCESS['remove'], 'field' => 'delete', 'sort' => false)
            );

            $defsort_arr = array();

            $menu_arr = array(
                array(
                        'url' => 'publ.php?cmd=1', 'text' => $LANG01[68]
                    ),
                array(
                        'url' => 'publ.php?cmd=14', 'text' => $LANG_PUBCONTROL_UPLUGIN[59]
                    )
                );
            
            $retval .= COM_startBlock($LANG_PUBCONTROL_UPLUGIN[75], '', COM_getBlockTemplate('_admin_block', 'header'));

            $retval .= ADMIN_createMenu(
                $menu_arr,
                $LANG_PUBCONTROL_UPLUGIN[5],
                $_CONF['layout_url'] . '/images/icons/plugins.' . $_IMAGE_TYPE
            );

            $text_arr = array(
                'has_extras'   => true,
                'instructions' => 'I can haz instructions!!',
                'form_url'     => 'publ.php?cmd=16&id='.$id
            );

            $table = DAL::getFormalTableName("pubcontrol_SubscriberGroupLink");
            $table2 = DAL::getFormalTableName("pubcontrol_Security");

#            if($id === 0) {
                $qstr = "SELECT securitygroup_id, subscriber_id, title FROM {$table}, {$table2} WHERE subscriber_id = '{$id}' AND securitygroup_id = id";
##            }

            $query_arr = array(
                'table' => 'pubcontrol_SubscriberGroupLink',
                'sql' => $qstr,
                'query_fields' => array('securitygroup_id'),
                'default_filter' => ''
            );

            // this is a dummy variable so we know the form has been used if all plugins
            // should be disabled in order to disable the last one.
            $form_arr = array('bottom' => '<input type="hidden" name="pluginenabler" value="true"' . XHTML . '>');

            $retval .= ADMIN_list('pubcontrol_SecurityGroupLink', 'ADMIN_getListField_listpubcontrol_sgroupsub', $header_arr,
                        $text_arr, $query_arr, $defsort_arr, '', $token, '', $form_arr, false);

            $retval .= COM_endBlock(COM_getBlockTemplate('_admin_block', 'footer'));

            $display .= $retval;

            break;


            // 100UP
        case CMD_GET_FEED_DATA: // 109
            //// Loads the data for the feed (the XML entry)
            //
            $p = new PublishingControl();
            $p->parseQueryString();
            $display .= nl2br(htmlentities($p->doOperation(FALSE, TRUE), ENT_NOQUOTES));
            break;
      default:
          header("Location: publ.php?cmd=1");
          exit;

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
                header("Location: publ.php?msg=2");
            }
            else if( ($group->_Type === NULL) || (($group->_Type !== TypeObject::O_PRIVATE) && ($group->_Type !== TypeObject::O_PUBLIC))) {
                header("Location: publ.php?msg=2");
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
                header("Location: publ.php?cmd=4&msg=2");
                exit;
            }

            header("Location: publ.php?cmd=4&msg=4");

            break;
        case "group_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=4&msg=15");
                exit;
            }

            // Attempt to delete
            try {
                $group = new PublishingGroupControl();
                $group->deleteGroup($id);
            }
            catch(PublishingException $pe) {
                header("Location: publ.php?cmd=4&msg=41");
                exit;
            }

            header("Location: publ.php?cmd=4&msg=4");
            break;
        // Modify Group
        case "feed_modify":
            // First thing is to grab the data from the POST variables
            $group = new FeedObject();
            $group->_Title = HostInterface::POST('GEEKLOG_PUBFNAME');
            $group->_Summary = HostInterface::POST('GEEKLOG_PUBFSUMMARY');
            $group->_Type = TypeObject::makeTypeFromInteger((int)HostInterface::POST('GEEKLOG_PUBFTYPE'));
            $group->_AccessCode = HostInterface::POST("GEEKLOG_PUBFACCESSCODE");
            $group->_Id = (int)HostInterface::POST("GEEKLOG_PUBFEDIT");
            $group->_GroupId = (int)HostInterface::POST("GEEKLOG_PUBFGROUP");

            if($group->_GroupId === 0)
            {
                header("Location: publ.php?msg=2");
            }

            // Are all the values valid (not null and type 1 or 2
            if( ($group->_Title === NULL) || ($group->_Summary === NULL) || ($group->_AccessCode === NULL)) {
                header("Location: publ.php?msg=2");
            }
            else if( ($group->_Type === NULL) || (($group->_Type !== TypeObject::O_PRIVATE) && ($group->_Type !== TypeObject::O_PUBLIC) && ($group->_Type !== TypeObject::O_INHERITED))) {
                header("Location: publ.php?msg=2");
            }

            // Ok now attempt to add it
            try {
                $pub = new PublishingFeedControl();
                if($group->_Id === 0) {
                    // Add
                    $pub->createFeed($group->_GroupId, $group);
                }
                else {
                    $pub->updateFeed($group);
                }
            }
            catch(PublishingException $pe) {
                header("Location: publ.php?msg=3");
            }

            header("Location: publ.php?cmd=7&msg=4");

            break;
        case "feed_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=7&msg=15");
                exit;
            }

            // Attempt to delete
            try {
                $feed = new PublishingFeedControl();
                $feed->deleteFeed($id);
            }
            catch(PublishingException $pe) {
                header("Location: publ.php?cmd=7&msg=42");
                exit;
            }

            header("Location: publ.php?cmd=7&msg=4");
            break;
        case "sgroup_modify":
            // First thing is to grab the data from the POST variables
            $group = new SecurityObject();
            $group->_Title = HostInterface::POST('GEEKLOG_PUBSNAME');
            $group->_Summary = HostInterface::POST('GEEKLOG_PUBSSUMMARY');
            $group->_Id = (int)HostInterface::POST("GEEKLOG_PUBSEDIT");
            $group->_Color = "FF9900";
            

            // Are all the values valid (not null and type 1 or 2
            if( ($group->_Title === NULL) || ($group->_Summary === NULL)) {
                header("Location: publ.php?msg=2");
            }

            // Ok now attempt to add it
            try {
                $pub = new PublishingSecurityManagement();
                if($group->_Id === 0) {
                    // Add
                    $pub->createSecurityGroup($group);
                }
                else {
                    $pub->updateSecurityGroup($group);
                }
            }
            catch(PublishingException $pe) {
                header("Location: publ.php?msg=3");
            }

            header("Location: publ.php?cmd=10&msg=4");

            break;
        case "sgroup_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=10&msg=15");
                exit;
            }

            // Attempt to delete
            try {
                $se = new PublishingSecurityManagement();
                $se->deleteSecurityGroup($id);
            }
            catch(PublishingException $pe) {
                header("Location: publ.php?cmd=10&msg=48");
                exit;
            }

            header("Location: publ.php?cmd=10&msg=4");
            break;
        case "sgroupfeed_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);
            $fid = DAL::applyFilter(HostInterface::GET('fid', 0), true);

            // Invalid identifier
            if( ($id === 0) || ($fid === 0)) {
                header("Location: publ.php?cmd=10&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->unlinkToGroup($id, $fid);

            header("Location: publ.php?cmd=12&id=$id&msg=4");
            break;
        case "sgroup_addfeed":
            // Add a feed to the group
            $feedid = (int)HostInterface::POST('GEEKLOG_PUBSFEED', 0);
            $securityid = (int)HostInterface::GET('id', 0);

            // Did it work?
            if( ($feedid === 0) || ($securityid === 0)) {
                header("Location: publ.php?cmd=10&msg=15");
                exit;
            }

            // And now attempt to add it
            $p = new PublishingSecurityManagement();
            $p->linkToGroup($securityid, $feedid);
            header("Location: publ.php?cmd=10&msg=4");
            break;
        case "subscriber_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=14&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->deleteSubscriber($id);

            header("Location: publ.php?cmd=14&msg=4");
            break;
        case "subscriber_unsuspend":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=14&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->modifySubscriberState(TypeObject::O_OK, $id, TRUE);

            header("Location: publ.php?cmd=14&msg=4");
            break;
            // approveSubscriber($email, $ssid, $id)
        case "subscriber_approve":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);
            $ssid = HostInterface::GET('ssid', '');
            $email = HostInterface::GET('email', '');
            $url = HostInterface::GET('url', '');

            // Invalid identifier
            if( ($id === 0) || ($ssid == '') || ($email == '') || ($url == '')) {
                header("Location: publ.php?cmd=14&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->approveSubscriber($email, $ssid, $id, $url);
            $se->modifySubscriberState(TypeObject::O_OK, $id, TRUE);
            header("Location: publ.php?cmd=14&msg=4");
            break;
        case "subscriber_suspend":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);

            // Invalid identifier
            if($id === 0) {
                header("Location: publ.php?cmd=14&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->modifySubscriberState(TypeObject::O_SUSPENDED, $id, TRUE);

            header("Location: publ.php?cmd=14&msg=4");
            break;
//sub_addgroup
        case "sub_addgroup":
            // Add a feed to the group
            $feedid = (int)HostInterface::POST('GEEKLOG_PUBSFEED', 0);
            $securityid = (int)HostInterface::GET('id', 0);

            // Did it work?
            if( ($feedid === 0) || ($securityid === 0)) {
                header("Location: publ.php?cmd=10&msg=15");
                exit;
            }

            // And now attempt to add it
            $p = new PublishingSecurityManagement();
            $p->linkToSubscriber($feedid, $securityid);
            header("Location: publ.php?cmd=14&msg=4");
            break;
//sgroupsub_delete
        case "sgroupsub_delete":
            // Deletes a group from the idf
            // Attempt to get the id
            $id = DAL::applyFilter(HostInterface::GET('id', 0), true);
            $fid = DAL::applyFilter(HostInterface::GET('fid', 0), true);

            // Invalid identifier
            if( ($id === 0) || ($fid === 0)) {
                header("Location: publ.php?cmd=10&msg=15");
                exit;
            }

            // Attempt to delete
            $se = new PublishingSecurityManagement();
            $se->unlinkToSubscriber($id, $fid);

            header("Location: publ.php?cmd=16&id=$id&msg=4");
            break;
      default:
          header("Location: publ.php?cmd=1");
          exit;
            
    }
}
else {
    header("Location: publ.php?cmd=1");
    exit;
}









$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);

?>



