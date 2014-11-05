<?php
/*
	<id>napalm:EhPortal</id>
	<name>EhPortal</name>
	<version>1.0</version>
*/
/*
 * EhPortal is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * EhPortal Portal ~ Copyright (c) 2014 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_permissions()
		// !!!
*/

function sportal_permissions(&$permissionGroups, &$permissionList, &$leftPermissionGroups, &$hiddenPermissions, &$relabelPermissions)
{
	global $context, $modSettings;
	$version = version_compare((!empty($modSettings['smfVersion']) ? substr($modSettings['smfVersion'], 0, 3) : '2.0'), '2.1', '<') ? 'v2.0' : 'v2.1';

	$permissionList['membergroup'] += array(
			'sp_admin' => array(false, 'sp', 'sp'),
			'sp_manage_settings' => array(false, 'sp', 'sp'),
			'sp_manage_blocks' => array(false, 'sp', 'sp'),
			'sp_manage_articles' => array(false, 'sp', 'sp'),
			'sp_manage_pages' => array(false, 'sp', 'sp'),
			'sp_manage_shoutbox' => array(false, 'sp', 'sp'),
			'sp_add_article' => array(false, 'sp', 'sp'),
			'sp_auto_article_approval' => array(false, 'sp', 'sp'),
			'sp_remove_article' => array(false, 'sp', 'sp'),
	);

	if ($version === 'v2.0')
	{
		$permissionGroups['membergroup']['simple'] += array(
				'sp',
		);

		$permissionGroups['membergroup']['classic'] += array(
				'sp',
		);
	}
	else
		$permissionGroups['membergroup'] += array(
				'sp',
		);

	$context['non_guest_permissions'] += array(
		'sp_admin',
		'sp_manage_settings',
		'sp_manage_blocks',
		'sp_manage_articles',
		'sp_manage_pages',
		'sp_manage_shoutbox',
		'sp_add_article',
		'sp_auto_article_approval',
		'sp_remove_article',
	);
}
?>