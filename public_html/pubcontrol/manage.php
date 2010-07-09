<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Pubcontrol Plugin 1.0                                                     |
// +---------------------------------------------------------------------------+
// | manage.php                                                                |
// |                                                                           |
// |  Handles subscribing to the plugin                                        |
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

// REQUIRED FOR THIS SCRIPT TO WORK
ini_set('allow_url_fopen', 1);


/**
* Geeklog common function library
*/
require_once '../lib-common.php';
require_once $_CONF['path'] . 'plugins/pubcontrol/functions.inc';
$display = '';

$display .= COM_siteHeader('');

// Do we have a message>

if ($_GET['msg']) {
    $display .= COM_showMessageText($LANG_PUBCONTROL_UPLUGIN[(int)$_GET['msg']]);
}

// Load up the GET engine
$cmd = HostInterface::GET('cmd', NULL);
switch($cmd) {
    case "step2":
        // Make sure data is valid
        $url = HostInterface::POST('GEEKLOG_URL', '');
        $iden = HostInterface::POST('GEEKLOG_IDENTIFIER', '');
        $pin = HostInterface::POST('GEEKLOG_PIN', '');

        // Check to make sure they are ok


        if( ($url === '') || ($iden === '') || ($pin === '')) {
            header("Location: manage.php?msg=81");
            exit;
        }

        // Now generate token and display instructions
        $token = base64_encode($url . '!' . md5($url . $iden . $pin . rand() . time()));
#        echo $token;
#; 

        $token = urlencode($token);
        $u = urlencode($url);
        $iden = urlencode($iden);
        $pin = urlencode($pin);
        $display .= <<<EOSTROM
<br />

{$LANG_PUBCONTROL_UPLUGIN[84]}
<br /><br />
<i>{$LANG_PUBCONTROL_UPLUGIN[85]}</i>
<br />
<br />
<a href='{$url}/admin/plugins/pubcontrol/recv.php?cmd=50&d={$token}' target='_blank'>{$LANG_PUBCONTROL_UPLUGIN[86]}</a>
    <br />
    <br />
<input class="pubcontrol" type="button" name="sgsg" value="{$LANG_PUBCONTROL_UPLUGIN[80]}" onclick='window.location="manage.php?cmd=step3&t={$token}&u={$u}&e={$iden}&p={$pin}"' />
EOSTROM;

        break;
    case "step3":
        // Grab token
        $token = HostInterface::GET('t', '');
        $url = HostInterface::GET('u', '');
        $components = parse_url($url);
        $iden = HostInterface::GET('e', '');
        $pin = HostInterface::GET('p', '');
        
        // Check if they are invalid
        if( ($iden === '') || ($pin === '') || ($token === '') || ($url === '') || ($components['scheme'] != 'http')) {
            header("Location: manage.php?msg=87");
            exit;
        }

        // Read contents of the file located at that token data
        $contents = file_get_contents($url . "/pubcontrol/tokens/pubcontrol.tok");

        // Do they match
        if(strcmp($contents, $token) !== 0) {
            // They did not match
            header("Location: manage.php?msg=88");
            exit;
        }

        // They matched, subscribe user
        $p = new PublishingSecurityManagement();
        $p->subscribe($url, $iden, $pin);
        
        header("Location: manage.php?msg=89");

        break;
    
    // No command, load the generic load data interface
    default:
            // Create new security group
            $temp = new TemplatingLayer($_CONF['path']. 'plugins/pubcontrol/templates', 'subscribe.thtml');
            $temp->set_var('lang_5', $LANG_PUBCONTROL_UPLUGIN[82]);
            $temp->set_var('lang_77', $LANG_PUBCONTROL_UPLUGIN[77]);
            $temp->set_var('lang_79', $LANG_PUBCONTROL_UPLUGIN[79]);
            $temp->set_var('lang_80', $LANG_PUBCONTROL_UPLUGIN[80]);
            $temp->set_var('lang_78', $LANG_PUBCONTROL_UPLUGIN[78]);
            $temp->set_var('lang_81', $LANG_PUBCONTROL_UPLUGIN[83]);
            //

            $display .= $temp->parse_output();
        break;


}

$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);
?>
