<?php
/*
	<id>napalm:WebDevil</id>
	<name>WebDevil</name>
	<version>1.0</version>
*/
/*
 * WebDevil is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * WebDevil Portal ~ Copyright (c) 2014 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$_GET['debug'] = 'Blue Dream!';
	require_once(dirname(__FILE__) . '/SSI.php');
}
// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $modSettings;

// Remove permission hook from the database
$sp_hook = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'SMF2' : 'SMF2.1';
if ($sp_hook === 'SMF2')
{
	remove_integration_function('integrate_pre_include', '$sourcedir/PortalHooks.php');
	remove_integration_function('integrate_load_permissions', 'sportal_permissions');
}
else
	remove_integration_function('integrate_load_permissions', '$sourcedir/PortalHooks.php|sportal_permissions');
?>