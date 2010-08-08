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


##TODO: Remove this hack to allow includes
require_once $_CONF['path'] . 'plugins/pubcontrol/functions.inc';

$display .= COM_siteHeader('');

// Do we have a message>
if ($_GET['msg']) {
    $display .= COM_showMessageText($LANG_PUBCONTROL_UPLUGIN[(int)$_GET['msg']]);
}

// Read the configuration file and see what the repository is set up for
$DONOTECHO = true;
require $_CONF['path_html'] . '/pubcontrol/scvalidate.php';


// Check for a command
if(isset($_GET['cmd'])) {
    switch((int)$_GET['cmd']) {
        case 2:
            // Update site structure time
            // Check what value we have (what we are doing)
            if(!isset($_GET['ref'])) {
                header("Location: index.php?msg=2");
                exit;
            }

            if($_GET['ref'] == "prdis") {
                // Set the private repository disabled
                $value = $ENTVALUE ^ 0x02;
            }
            else if($_GET['ref'] == "pren") {
                // Set the private repository disabled
                $value = $ENTVALUE | 0x02;
            }
            else if($_GET['ref'] == "puen") {
                // Set the private repository disabled
                $value = $ENTVALUE | 0x04;
            }
            else if($_GET['ref'] == "pudis") {
                // Set the private repository disabled
                $value = $ENTVALUE ^ 0x04;
            }


            $pagecontents = '
            <?php
            $ENTVALUE = '.$value.';
            if(isset($DONOTECHO)) {

            }
            else {
                header("Content-Type: text/plain");
                // Will read from the $_CONF files eventually.
                // The conf values are for whether the repository supports public and private
                echo $ENTVALUE;
                // Supports both -> 0x02 = public, 0x04 = private
            }

            ?>

            ';

            file_put_contents($_CONF['path_html'] . '/pubcontrol/scvalidate.php', $pagecontents);
            header("Location: index.php");
            break;

        default:
            // Do nothing
            break;
    }


}

# And now check what we have
if(($ENTVALUE & 0x02) === 0x02) {
    // Private is yes
    $privatemsg = $LANG_PUBCONTROL_UPLUGIN[136];
    $privateurl = "index.php?cmd=2&ref=prdis";
    $lili = '<li><a href="publ.php?cmd=9">'.$LANG_PUBCONTROL_UPLUGIN[46].'</a></li>
            <li><a href="publ.php?cmd=10">'.$LANG_PUBCONTROL_UPLUGIN[49].'</a></li>
            <li><a href="publ.php?cmd=14">'.$LANG_PUBCONTROL_UPLUGIN[149].'</a></li>';
    $dal = new DAL();
    $numawaiting = "<a href='publ.php?cmd=13'>".$dal->count("pubcontrol_Subscribers", 'mode', 'A') . $LANG_PUBCONTROL_UPLUGIN[142] . "</a>";
}
else {
    // Private is no
    $privatemsg = $LANG_PUBCONTROL_UPLUGIN[135];
    $privateurl = "index.php?cmd=2&ref=pren";
    $lili = $LANG_PUBCONTROL_UPLUGIN[148];
    $numawaiting =  $LANG_PUBCONTROL_UPLUGIN[148];

}

if(($ENTVALUE & 0x04) === 0x04) {
    // Public is yes
    $publicmsg = $LANG_PUBCONTROL_UPLUGIN[136];
    $publicurl = "index.php?cmd=2&ref=pudis";
}
else {
    // Public is no
    $publicmsg = $LANG_PUBCONTROL_UPLUGIN[135];
    $publicurl = "index.php?cmd=2&ref=puen";
}


// Display the image
$temp = new TemplatingLayer($_CONF['path'] . 'plugins/pubcontrol/templates/', 'admin-loader.thtml');
$temp->set_var('lang_134', $LANG_PUBCONTROL_UPLUGIN[134]);
$temp->set_var('lang_134', $LANG_PUBCONTROL_UPLUGIN[134]);
$temp->set_var('lang_139', $LANG_PUBCONTROL_UPLUGIN[139]);
$temp->set_var('lang_137', $LANG_PUBCONTROL_UPLUGIN[137]);
$temp->set_var('lang_138', $LANG_PUBCONTROL_UPLUGIN[138]);
$temp->set_var('url_1', $publicurl);
$temp->set_var('url_2', $privateurl);
$temp->set_var('option_1', $publicmsg);
$temp->set_var('option_2', $privatemsg);
$temp->set_var('lang_140', $LANG_PUBCONTROL_UPLUGIN[140]);
$temp->set_var('num_awaiting', $numawaiting);
$temp->set_var('lang_143', $LANG_PUBCONTROL_UPLUGIN[143]);
$temp->set_var('lang_144', $LANG_PUBCONTROL_UPLUGIN[144]);
$temp->set_var('lang_145', $LANG_PUBCONTROL_UPLUGIN[145]);
$temp->set_var('lang_146', $LANG_PUBCONTROL_UPLUGIN[146]);
$temp->set_var('lang_147', $LANG_PUBCONTROL_UPLUGIN[147]);
$temp->set_var('lili1', $lili);
$temp->set_var('lang_22', $LANG_PUBCONTROL_UPLUGIN[22]);
$temp->set_var('lang_23', $LANG_PUBCONTROL_UPLUGIN[23]);
$temp->set_var('lang_32', $LANG_PUBCONTROL_UPLUGIN[32]);
$temp->set_var('lang_36', $LANG_PUBCONTROL_UPLUGIN[36]);
$temp->set_var('lang_59', $LANG_PUBCONTROL_UPLUGIN[59]);
$temp->set_var('lang_102', $LANG_PUBCONTROL_UPLUGIN[102]);
$temp->set_var('lang_109', $LANG_PUBCONTROL_UPLUGIN[109]);
$temp->set_var('lang_111', $LANG_PUBCONTROL_UPLUGIN[111]);
$temp->set_var('lang_124', $LANG_PUBCONTROL_UPLUGIN[124]);

if( (($ENTVALUE & 0x04) != 0x04) && (($ENTVALUE & 0x02) != 0x02)) {
    $temp->set_var('COMMENT_START', '<!-- ');
    $temp->set_var('COMMENT_END', ' -->');
    $temp->set_var('control1', $LANG_PUBCONTROL_UPLUGIN[141]);
}
else {
    $temp->set_var('COMMENT_START', '');
    $temp->set_var('COMMENT_END', '');
    $temp->set_var('control1', '');
}
$display .= $temp->parse_output();

$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);


?>
