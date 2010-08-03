
<?php

###############################################################################
# english.php
#
# This is the English language file for the Geeklog Calendar plugin
#
# Copyright (C) 2010 Tim Patrick
# timpatrick12 AT gmail DOT com
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
#
###############################################################################

###############################################################################
# Array Format:
# $LANGXX[YY]:  $LANG - variable name
#               XX    - file id number
#               YY    - phrase id number
###############################################################################

$LANG_PUBCONTROL_UPLUGIN = array(
    1 => "Hello All",
    2 => "Invalid data entered",
    3 => "Serious Internal Error",
    4 => "Success!!",
    5 => "Publishing Control Panel",
    6 => 'Groups are standard containers that hold multiple data feeds into one generic category, eg. Blogs',
    7 => 'Group Name<br /><i>The name of the group that everyone sees</i>',
    8 => 'Group Type<br /><i>Public means any site can subscribe to it, private means that only sites that are approved by you can subscribe</i>',
    9 => '>Group Summary<br /><i>A short summary to describe the group</i>',
    10 => 'Add Group',
    11 => 'Reset',
    12 => 'Private',
    13 => 'Public',
    14 => 'Edit Group',
    15 => 'The entry does not exist - Invalid ID specified',
    16 => 'Inherited',
    17 => 'Suspended',
    18 => 'OK',
    19 => 'Group Title',
    20 => 'Summary',
    21 => 'Type',
    22 => 'List and Manage Groups',
    23 => 'Add New Group',
    24 => 'Are you sure you want to delete this entry?',
    25 => 'Groups',
    26 => 'Allows management of groups, feeds, and subscriptions',
    27 => 'Publishing Control Panel',
    28 => 'Feeds are containers that hold common data entries, and are part of a specific group that binds them togethor',
    29 => 'Feed Name<br /><i>The name of the feed that everyone sees</i>',
    30 => 'Feed Type<br /><i>Public means any site can subscribe to it, private means that only sites that are approved by you can subscribe, inherited inherits what the feed\'s group is</i>',
    31 => '>Feed Summary<br /><i>A short summary to describe the feed and what data it contains</i>',
    32 => 'Add New Feed',
    33 => 'Access Code<br /><i>This code determines what type of data is associated with the feed, and how other applications deal with it</i>',
    34 => 'Parent Group<br /><i>This determines what category the feed falls under</i>',
    35 => 'Edit Feed',
    36 => 'List and Manage Feeds',
    37 => 'Access Code',
    38 => 'Load Data',
    39 => 'Load Feeds',
    40 => 'Feed Title',
    41 => 'It is not possible to delete a group with feeds still attached to it',
    42 => 'It is not possible to delete a feed with data still attached to it',
    43 => 'Feeds',
    44 => 'No groups or access codes in the database',
    45 => 'Secrity groups are profiles that give access to various feeds. Subscribers can then be assigned a security group that best fits their needs',
    46 => 'Add New Security Group',
    47 => 'Edit Security Group',
    48 => 'It is not possible to delete a security group with subscribers still attached to it',
    49 => 'List and Manage Security Groups',
    50 => 'Security Groups',
    51 => 'Assign Feeds',
    52 => 'Allows you to assign a security group policy to include a specific feed. Note that one feed can be a part of multiple security group policies',
    53 => 'Attach to feed',
    54 => 'Feed<br /><i>Select a feed to attach this group policy too - only feeds that are not already attached are shown</i>  ',
    55 => 'List of all Feeds associated with the Security Group',
    56 => 'List Assigned Feeds',
    57 => 'Subscribers',
    58 => 'Subscriptions awaiting approval',
    59 => 'List of Subscribers',
    60 => 'Subscriber URL',
    61 => 'Approve',
    62 => 'List all subscribers',
    63 => 'Subscribers awaiting approval',
    64 => 'Are you sure you want to approve this URL',
    65 => 'Suspend',
    66 => 'Are you sure you want to suspend / unsuspend this URL',
    67 => 'Unsuspend',
    68 => 'Assign Security Group',
    69 => 'Assigned Security Groups',
    70 => 'List Assigned Groups',
    71 => 'Attach to Group',
    72 => 'Allows you to add a subscriber to a certain security group. Once they are a member they inherit all the priviledges of that security group.',
    73 => 'Security Group<br /><i>Select a group to assign to this subscriber</i>',
    74 => 'Add to security group',
    75 => 'List of all Security Groups associated with the Subscriber',
    76 => 'Unique Security Identifier (SSID)',
    77 => 'URL<br /><i>The URL of the site that you wish to subscribe for (eg. http://www.myvalidurl.com/) - Note you MUST be the owner of this account to proceed, as you will need to add a keycode to the property manager to generate a response</i>',
    78 => 'Email<br /><i>The administrative email for the site. This must be a valid email address as you will be notified via this email when your account has been approved</i>',
    79 => 'PIN<br /><i>A personal pin that verifies as a password. This pin may contain any characters and is 4 characters in length</i>',
    80 => 'Next -> ',
    81 => 'Missing information - a valid URL, Email, and PIN must be provided',
    82 => 'Subscribe to this site - Part 1: Enter your information',
    83 => 'The private subscription process consists of three parts - Entering information and verifying account ownership, getting approved, and finally adding the approval token to your Receiving control panel to finalize the subscription.',
    84 => 'Subscribe to this site - Part 2: Site Verification',
    85 => 'Now you must verify that you are the admin of the site you wish to subscribe on behalf of.<br />To do this, simply click the token link below, and once the key has been processed, a message will display its status. <br />
           Upon a success message, simply click the Next button on this page and a connection will be attempted between the two severs.',
    86 => 'Load Token',
    87 => 'Error validating account - Please try again',
    88 => 'Verification of your site failed - could your site be down?',
    89 => 'Site Verification was successful - Please await your subscription request to be approved, where you will be sent an email with more instructions',
    90 => 'Click Here',
    91 => 'Failure to register token - the link seems to be corrupted.',
    92 => 'Success - Token was registered - you may now attempt to verify this URL',
    93 => 'Failure to register token - cannot write out token file',
    94 => 'Receiving Control Panel',
    95 => 'Allows management subscription to various data feeds ',
    96 => 'Feed Management',
    97 => 'Subscription failed - no token in URL - try again',
    98 => 'Token is invalid - please try again',
    99 => 'Subscription added - Success',
    100 => 'You have already subscribed to this URL',
    101 => 'URL',
    102 => 'Load all subscriptions',
    103 => 'Receiving Control Panel',
    104 => 'Your SSID is invalid',
    105 => 'Unsubscribed Successfully',
    106 => 'Failure UnSubscribing - Invalid data',
    107 => 'UnSubscribe',
    108 => 'Are you sure you wish to unsubscribe? - This will remove all subscribed feeds',
    109 => 'Subscribed Feeds',
    110 => 'Success - Feed unsubscribed',
    111 => 'Subscribe to Repository',
    112 => ' - Subscribe to a Repository',
    113 => 'Here you are able to subscribe to a feed repository',
    114 => 'URL<br /><i>The URL of the site that you wish to subscribe for (eg. http://www.feedrepository.com/)</i>',
    115 => 'Subscription Type<br /><i>This is the type of subscription - Public or Private. Public subscription involves no sign-up. However Private subscription involves being approved by the repository administrator.<br />Check to determine which subscription type is right for you</i>',
    116 => 'URL is not valid - are you sure it exists',
    117 => 'Unfortunately the repository you requested does not support the type - Private subscriptions are not supported',
    118 => 'Unfortunately the repository you requested does not support the type - Public subscriptions are not supported',
    119 => 'Click here',
    120 => ' to register for a private subscription <br /> - Note that you will be redirected to the repository URL, and as such the proper security concerns should be taken into consideration.',
    121 => 'No SSID - PUBLIC Subscription',
    122 => 'Groups that exist for repository ',
    123 => 'Error loading the repository specified',
    124 => 'Join a new feed',
    125 => 'Get repository group listings',
    126 => 'Listing of all subscribed repositories - Select one to view that repository\'s groups',
    127 => 'Please select a group to proceed',
    128 => 'Select this group',
    129 => 'Join this feed',
    130 => 'Feeds that exist for repository ',
    131 => ' and group ',
    132 => 'You have joined the selected feed'

);


?>