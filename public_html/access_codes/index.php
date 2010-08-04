<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Access Code Management                                                    |
// +---------------------------------------------------------------------------+
// | index.php                                                                 |
// |                                                                           |
// | Geeklog Access Code Management                                            |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2010 by the following authors:                              |
// |                                                                           |
// | Authors: Tim Patrick    - timpatrick12 AT gmail DOT com                   |
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

require_once '../lib-common.php';

$display = '';

if (COM_isAnonUser() &&
    (($_CONF['loginrequired'] == 1))) {
    $display .= COM_siteHeader('menu', $LANG_CAL_1[41]);
    $display .= SEC_loginRequiredForm();
    $display .= COM_siteFooter();
    COM_output($display);
    exit;
}

$display .= COM_siteHeader('');

// Do we have a message>

if ($_GET['msg']) {
    $display .= COM_showMessageText($LANG_PUBCONTROL_UPLUGIN[(int)$_GET['msg']]);
}

// Now display a command prompt to the user
if(isset($_GET['cmd'])) {
    switch((int)$_GET['cmd']) {
        case 1:

            // Attempt to add access code 
            if(isset($_GET['s'])) {
                
            }

            // Display add access code file
            $display .= <<<EOO
<br />
<script type="text/javascript">

function GenerateAccessCode
{
        alert("D");

// Grab data
/*
    try {
        var data = document.getElementById("DataType").value;
        var ac = document.getElementById("ACType").value;
        var e = document.getElementById("EType").value;
        var d = document.getElementById("DType").value;

        // Generate the code
        document.getElementById("GEEKLOG_AC").value = "ATT" + data + ac + e + d;
        return true;
    }
    catch(e) {
        alert("Cannot process data");
        return false;
    }
    */
};


</script>
Welcome to the Access Code registration point
<br />
<i>Note: It is important NOT to use an existing access code, and to follow the guidelines for creating one located at<br /><a href="http://site">http://site</a></i>
<br /><br />
<form name="PubControl_AddGroup" method="post" action="index.php?cmd=1&s=100" enctype="multipart/form-data" onsubmit="GenerateAccessCode();">

<div class="smalltxt" id="GEEKLOG_PUPLOAD_ERRFORM" style="color:red"></div>

<div class="smalltxt">Type of Data</div>
<select class="pubcontrol" name="DataType" id="DataType">
    <option value="T" selected="selected">Plain Text</option>
    <option value="B">Binary Data</option>
    <option value="X">XML Data</option>
    <option value="S">S9</option>
    <option value="I">Command Instructions (Language)</option>
    <option value="E">Encrypted Data</option>
</select>
<br /><br />
<div class="smalltxt">Type of Access Code</div>
<select class="pubcontrol" name="ACType" id="ACType">
    <option value="G" selected="selected">Geeklog Plugin</option>
    <option value="P">Plugin (Other)</option>
    <option value="C">Custom</option>
</select>

<br /><br />
<div class="smalltxt">Extra Type Identifier</div>
<select class="pubcontrol" name="EType" id="EType">
    <option value="0">0</option>
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
    <option value="9">9</option>
</select>

<br /><br />
<div class="smalltxt">Developer Identifier</div>
<select class="pubcontrol" name="DType" id="DType">
    <option value="0">0</option>
    <option value="1">1</option>
    <option value="2">2</option>
    <option value="3">3</option>
    <option value="4">4</option>
    <option value="5">5</option>
    <option value="6">6</option>
    <option value="7">7</option>
    <option value="8">8</option>
    <option value="9">9</option>
</select>

<br /><br />
<div class="smalltxt">Access Code</div>
<input class="long" maxlength="100" disabled="disabled" type="text" name="GEEKLOG_AC" id="GEEKLOG_AC" value="AT" />
<br /><br />
<div class="smalltxt"></div>
<input class="pubcontrol" type="submit" name="submit_modify_sgroup" value="Generate and Register Access Code" />


</form>            
EOO;

            break;

        default:
            header("Location: index.php?cmd=1");
            break;

    }
}
else {
    header("Location: index.php?cmd=1");
}

$display .= COM_endBlock (COM_getBlockTemplate ('_msg_block', 'footer'));
$display .= COM_siteFooter();
COM_output($display);