<?php
/*
*	<id>underdog:avatarTransfer</id>
*	<name>Avatar Attachments Transfer Modification</name>
*	<version>1.0</version>
*	<type>modification</type>
*/

/*
 * Avatar Attachments Transfer Modification for SMF forums c/o Underdog @ http://webdevelop.comli.com
 * Copyright 2014 underdog@webdevelop.comli.com
 * This software package is distributed under the terms of its CC BY-ND 4.0 License: http://creativecommons.org/licenses/by-nd/4.0/
*/

/*  This file is for transferring avatar attachments to a common avatar directcry */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

function avatar_transfer_array_insert($array, $insert, $pos, $where='after')
{
	foreach ($array as $arr => $key)
	{
		if ($where === 'after')
			$new[] = $array[$arr];

		if ((!empty($key[1])) && $key[1] === $pos)
		{
			foreach ($insert as $ins)
				$new[] = $ins;
		}

		if ($where !== 'after')
			$new[] = $array[$arr];
	}

	return $new = !empty($new) ? $new : $array;
}

function avatar_add_context($config_vars)
{
	// add settings after the appropriate keys pair
	global $txt, $helptxt, $modSettings, $boarddir, $context;

	// load language files
	loadLanguage('AvatarTransfer');
	loadLanguage('AvatarTransferHelp');

	// subtext message & confirmation for completed task
	$subtext = !empty($modSettings['avatar_transfer']) ? $txt['avatar_transfer_complete'] : '';
	$agree = 'if (document.getElementById(\'avatar_transfer\').checked == 1) {agree = confirm(\'' . $txt['avatar_transfer_confirm'] . '\'); if (!agree){return false;}}';

	// fix the directory path if necessary
	if ((empty($modSettings['avatar_transfer_dir'])) || $modSettings['avatar_transfer_dir'] !== $modSettings['avatar_directory'] . '/Misc_Avatars')
		avatar_transfer_dir();

	$config_vars = avatar_transfer_array_insert($config_vars, array(
		'',
		array('title', 'avatar_transfer_title'),
			array('check', 'avatar_transfer', 0, 'onclick' => $agree, 'subtext' => $subtext),
			array('check', 'avatar_transfer_same', 1),
			array('text', 'avatar_transfer_dir', 40, 'disabled' => true)
	), 'avatar_url');

	// execute task and adjust settings
	if (!empty($modSettings['avatar_transfer']))
		updateSettings(execute_avatar_transfer(), true);

	return $config_vars;
}

function execute_avatar_transfer()
{
	// transfer avatar attachments to a common avatar folder ~ SMF 2.0.X
	global $smcFunc, $boarddir, $modSettings, $sourcedir;

	isAllowedTo('admin_forum');
	require_once($sourcedir . '/ManageAttachments.php');
	@ini_set('memory_limit', '128M');

	$modSettings['avatar_transfer_dir'] = !empty($modSettings['avatar_transfer_dir']) ? $modSettings['avatar_transfer_dir'] : $boarddir . '/avatars/Misc_Avatars';
	$modSettings['avatar_transfer'] = '';

	if (!is_writable($modSettings['avatar_directory'] . '/Misc_Avatars'))
	{
		avatar_transfer_dir();
		@chmod($modSettings['avatar_transfer_dir'], 0755);
	}

	// prepare array for the settings table
	$setArray = array('avatar_transfer' => '', 'avatar_transfer_dir' => $modSettings['avatar_transfer_dir']);

	$request = $smcFunc['db_query']('', "
		SELECT id_attach, id_member, filename, file_hash, fileext
		FROM {db_prefix}attachments
		WHERE attachment_type = {int:type}
		AND id_member > {int:member}",
				array('member' => 0, 'type' => 0)
	);

	$updatedAvatars = array();
	while ($row = $smcFunc['db_fetch_assoc']($request))
	{
		if (empty($row['fileext']))
			$ext = end(explode('.', $row['filename']));
		else
			$ext = $row['fileext'];

		$filename = (string)$row['id_attach'] . '_' . trim($row['file_hash']);
		if (!empty($modSettings['avatar_transfer_same']))
			$newFilename = implode('_', explode('_', $row['filename'], -1)) . '.' . $ext;
		else
			$newFilename = implode('_', explode('_', $row['filename'], -1)) . '_' . (string)$row['id_attach'] . '.' . $ext;

		if (rename($modSettings['attachmentUploadDir'] . '/' . $filename, $modSettings['avatar_transfer_dir'] . '/' . $newFilename))
			$updatedAvatars[] = array('user' => $row['id_member'], 'avatar' => 'Misc_Avatars/' . $newFilename, 'attach' => $row['id_attach'], 'hash' => $filename, 'filename' => $row['filename']);
		elseif (copyAvatarFile($modSettings['attachmentUploadDir'] . '/' . $row['filename'], $modSettings['avatar_transfer_dir'] . '/' . $newFilename))
			$updatedAvatars[] = array('user' => $row['id_member'], 'avatar' => 'Misc_Avatars/' . $newFilename, 'attach' => $row['id_attach'], 'hash' => $filename, 'filename' => $row['filename']);
	}
	$smcFunc['db_free_result']($request);

	foreach ($updatedAvatars as $updateMember)
	{
		$request = $smcFunc['db_query']('', "
				UPDATE {db_prefix}members
				SET avatar = {string:avatar}
				WHERE id_member = {int:user}
				LIMIT 1",
				array('avatar' => $updateMember['avatar'], 'user' => $updateMember['user'])
		);

		removeAttachments(array('id_attach' => array($updateMember['attach'])));
		deleteAvatarFile($modSettings['attachmentUploadDir'] . '/' , $updateMember['hash']);
		deleteAvatarFile($modSettings['attachmentUploadDir'] . '/' , $updateMember['filename']);
	}

	return $setArray;
}

// fix Misc_Avatars directory path
function avatar_transfer_dir()
{
	global $modSettings, $boarddir;

	if (!is_writable($modSettings['avatar_directory']))
		@chmod($modSettings['avatar_directory'], 0755);

	copy_avatarDirectory($boarddir . '/avatars/Misc_Avatars', $modSettings['avatar_directory'] . '/Misc_Avatars');
	$modSettings['avatar_transfer_dir'] = $modSettings['avatar_directory'] . '/Misc_Avatars';
	updateSettings(array('avatar_transfer_dir' => $modSettings['avatar_transfer_dir']), true);
}

/* Copy entire folder/directory  */
function copy_avatarDirectory($source, $destination)
{
	if (@is_dir($source))
	{
		if (@!is_dir($destination))
			@mkdir($destination);

		$directory = @dir($source);
		while (FALSE !== ($readdirectory = $directory->read()))
		{
			if ($readdirectory == '.' || $readdirectory == '..')
				continue;

			$PathDir = $source . '/' . $readdirectory;
			if (@is_dir($PathDir))
			{
				copy_avatarDirectory($PathDir, $destination . '/' . $readdirectory);
				continue;
			}

			copy($PathDir, $destination . '/' . $readdirectory);
		}

		$directory->close();
	}
	elseif (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

/* Copy avatar file */
function copyAvatarFile($source, $destination)
{
	if (@file_exists($source) && !@file_exists($destination))
		@copy($source, $destination);
	else
		return false;

	return true;
}

/* Delete avatar file function */
function deleteAvatarFile($file)
{
	if (@file_exists($file))
		@unlink($file);
	else
		return false;

	return true;
}
?>