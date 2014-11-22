<?php
/*
*	<id>underdog:avatarTransfer</id>
*	<name>Avatar Attachments Transfer Modification</name>
*	<version>1.2</version>
*	<type>modification</type>
*/

/*
 * Avatar Attachments Transfer Modification for SMF forums c/o Underdog @ http://webdevelop.comli.com
 * Copyright 2014 underdog@webdevelop.comli.com
 * This software package is distributed under the terms of its CC BY-ND 4.0 License: http://creativecommons.org/licenses/by-nd/4.0/
*/

// Use this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
    require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
    die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

/*  This file is for mysql setup */
/*
*	<id>underdog:avatarTransfer</id>
*	<name>Avatar Attachments Transfer Modification</name>
*	<version>1.2</version>
*	<type>modification</type>
*/

/*
 * Avatar Attachments Transfer Modification for SMF forums c/o Underdog @ http://webdevelop.comli.com
 * Copyright 2014 underdog@webdevelop.comli.com
 * This software package is distributed under the terms of its CC BY-ND 4.0 License: http://creativecommons.org/licenses/by-nd/4.0/
*/

/* Remove integration hooks */
remove_integration_function('integrate_modify_avatar_settings', '$sourcedir/AvatarTransfer.php|avatar_transfer_avatar');
remove_integration_function('integrate_helpadmin', '$sourcedir/AvatarTransfer.php|avatar_transfer_help');
?>