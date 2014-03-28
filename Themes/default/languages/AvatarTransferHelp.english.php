<?php
/*
*	<id>underdog:avatarTransfer</id>
*	<name>Avatar Attachments Transfer Tool</name>
*	<version>1.0</version>
*	<type>modification</type>
*/

/*
 * Avatar Attachments Transfer Modification for SMF forums c/o Underdog @ http://webdevelop.comli.com
 * Copyright 2014 underdog@webdevelop.comli.com
 * This software package is distributed under the terms of its CC BY-ND 4.0 License: http://creativecommons.org/licenses/by-nd/4.0/
*/

global $helptxt;

// Help text
$helptxt['avatar_transfer_title'] = 'This option will transfer avatar attachments to the assigned avatar path.<br />All members will then be able to select those avatars within their profile.';
$helptxt['avatar_transfer'] = 'Enabling this checkbox and selecting Save will transfer all member uploaded avatars to the Misc_Avatar directory.<br />This process is permananet and is not reversable.';
$helptxt['avatar_transfer_same'] = 'With this enabled, performing the avatar transfer option will replace each member\'s previous avatar attachment with their current one.<br /><br />Disabling this option will treat each avatar as unique and will not overwrite any previous image file.';
$helptxt['avatar_transfer_dir'] = 'This should show the Misc_Avatar directory location<br />within the <span class="alert">Avatars directory</span> path.<br /><br />This data is only displayed for reference.';
?>