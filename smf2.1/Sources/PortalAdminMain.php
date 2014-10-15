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

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_admin_config_main()
		// !!!

	void sportal_admin_general_settings()
		// !!!

	void sportal_admin_block_settings()
		// !!!

	void sportal_admin_article_settings()
		// !!!

	void sportal_information()
		// !!!

	void sportal_array_flatten()
		// !!!
*/

function sportal_admin_config_main()
{
	global $sourcedir, $context, $txt;

	if (!allowedTo('sp_admin'))
		isAllowedTo('sp_manage_settings');

	require_once($sourcedir . '/Subs-PortalAdmin.php');
	require_once($sourcedir . '/ManageServer.php');
	sp_smf_version();

	loadTemplate('PortalAdmin');

	$subActions = array(
		'information' => 'sportal_information',
		'generalsettings' => 'sportal_admin_general_settings',
		'blocksettings' => 'sportal_admin_block_settings',
		'articlesettings' => 'sportal_admin_article_settings',
	);

	$_REQUEST['sa'] = isset($_REQUEST['sa']) && isset($subActions[$_REQUEST['sa']]) ? $_REQUEST['sa'] : 'information';

	$context[$context['admin_menu_name']]['tab_data'] = array(
		'title' => $txt['sp-adminConfiguration'],
		'help' => 'sp_ConfigurationArea',
		'description' => $txt['sp-adminConfigurationDesc'],
	);

	$subActions[$_REQUEST['sa']]();
}

function sportal_admin_general_settings($return_config = '')
{
	global $smcFunc, $context, $scripturl, $txt;

	$request = $smcFunc['db_query']('','
		SELECT id_theme, value AS name
		FROM {db_prefix}themes
		WHERE variable = {string:name}
			AND id_member = {int:member}
		ORDER BY id_theme',
		array(
			'member' => 0,
			'name' => 'name',
		)
	);
	$context['SPortal']['themes'] = array('0' => &$txt['portalthemedefault']);
	while ($row = $smcFunc['db_fetch_assoc']($request))
		$context['SPortal']['themes'][$row['id_theme']] = $row['name'];
	$smcFunc['db_free_result']($request);

	$config_vars = array(
			array('select', 'sp_portal_mode', explode('|', $txt['sp_portal_mode_options'])),
			array('check', 'sp_maintenance'),
			array('text', 'sp_standalone_url'),
		'',
			array('select', 'portaltheme', $context['SPortal']['themes']),
			array('check', 'sp_disableColor'),
			array('check', 'sp_disableForumRedirect'),
			array('check', 'sp_disable_random_bullets'),
			array('check', 'sp_disable_php_validation'),
			array('check', 'sp_disable_side_collapse'),
			array('check', 'sp_resize_images'),
	);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession();

		$newConfigVariables = array();
		foreach ($config_vars as $key => $value)
		{
			$setting = !empty($config_vars[$key][1]) ? $config_vars[$key][1] : '';
			if (!empty($setting) && isset($_POST[$setting]))
				$newConfigVariables[] = array($setting => $_POST[$setting]);
			elseif (!empty($setting))
				$newConfigVariables[] = array($setting => '');

		}

		updateSettings(sportal_array_flatten($newConfigVariables));
		$context['saved_successful'] = true;
		redirectexit('action=admin;area=portalconfig;sa=generalsettings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=generalsettings;save;';
	$context['settings_title'] = $txt['sp-adminGeneralSettingsName'];
	$context['page_title'] = $txt['sp-adminGeneralSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_admin_block_settings($return_config = '')
{
	global $context, $scripturl, $txt;

	$config_vars = array(
			array('check', 'showleft'),
			array('check', 'showright'),
			array('text', 'leftwidth'),
			array('text', 'rightwidth'),
		'',
			array('check', 'sp_enableIntegration'),
			array('multicheck', 'sp_IntegrationHide', 'subsettings' => array('sp_adminIntegrationHide' => $txt['admin'], 'sp_profileIntegrationHide' => $txt['profile'], 'sp_pmIntegrationHide' => $txt['personal_messages'], 'sp_mlistIntegrationHide' => $txt['members_title'], 'sp_searchIntegrationHide' => $txt['search'], 'sp_calendarIntegrationHide' => $txt['calendar'], 'sp_moderateIntegrationHide' => $txt['moderate'])),
		'',
			array('text', 'sp_delay_time'),
			array('text', 'sp_fade_time'),
			array('check', 'sp_bullets'),
	);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession();

		$width_checkup = array('left', 'right');
		foreach ($width_checkup as $pos)
		{
			if (!empty($_POST[$pos . 'width']))
			{
				if (stripos($_POST[$pos . 'width'], 'px') !== false)
					$suffix = 'px';
				elseif (strpos($_POST[$pos . 'width'], '%') !== false)
					$suffix = '%';
				else
					$suffix = '';

				preg_match_all('/(?:([0-9]+)|.)/i', $_POST[$pos . 'width'], $matches);

				$number = (int) implode('', $matches[1]);
				if (!empty($number) && $number > 0)
					$_POST[$pos . 'width'] = $number . $suffix;
				else
					$_POST[$pos . 'width'] = '';
			}
			else
				$_POST[$pos . 'width'] = '';
		}

		unset($config_vars[11]);
		$config_vars = array_merge(
			$config_vars,
			array(
				array('check', 'sp_adminIntegrationHide'),
				array('check', 'sp_profileIntegrationHide'),
				array('check', 'sp_pmIntegrationHide'),
				array('check', 'sp_mlistIntegrationHide'),
				array('check', 'sp_searchIntegrationHide'),
				array('check', 'sp_calendarIntegrationHide'),
				array('check', 'sp_moderateIntegrationHide'),
			)
		);

		$newConfigVariables = array();
		foreach ($config_vars as $key => $value)
		{
			$setting = !empty($config_vars[$key][1]) ? $config_vars[$key][1] : '';
			if (!empty($setting) && isset($_POST[$setting]))
			{
				if ($setting === 'sp_delay_time' || $setting === 'sp_fade_time')
					$_POST[$setting] = (int)abs($_POST[$setting]);

				$newConfigVariables[] = array($setting => $_POST[$setting]);
			}
			elseif (!empty($setting))
				$newConfigVariables[] = array($setting => '');
		}

		updateSettings(sportal_array_flatten($newConfigVariables));
		$context['saved_successful'] = true;

		redirectexit('action=admin;area=portalconfig;sa=blocksettings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=blocksettings;save;';
	$context['settings_title'] = $txt['sp-adminBlockSettingsName'];
	$context['page_title'] = $txt['sp-adminBlockSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_admin_article_settings($return_config = '')
{
	global $context, $scripturl, $txt;

	$config_vars = array(
			array('check', 'articleactive'),
			array('int', 'articleperpage'),
			array('int', 'articlelength'),
			array('check', 'articleavatar'),
	);

	if ($return_config)
		return $config_vars;

	if (isset($_GET['save']))
	{
		checkSession();

		$newConfigVariables = array();
		foreach ($config_vars as $key => $value)
		{
			$setting = !empty($config_vars[$key][1]) ? $config_vars[$key][1] : '';
			if (!empty($setting) && isset($_POST[$setting]))
				$newConfigVariables[] = array($setting => $_POST[$setting]);
			elseif (!empty($setting))
				$newConfigVariables[] = array($setting => '');
		}

		updateSettings(sportal_array_flatten($newConfigVariables));
		$context['saved_successful'] = true;
		redirectexit('action=admin;area=portalconfig;sa=articlesettings');
	}

	$context['post_url'] = $scripturl . '?action=admin;area=portalconfig;sa=articlesettings;save';
	$context['settings_title'] = $txt['sp-adminArticleSettingsName'];
	$context['page_title'] = $txt['sp-adminArticleSettingsName'];
	$context['sub_template'] = 'general_settings';

	prepareDBSettingContext($config_vars);
}

function sportal_information($in_admin = true)
{
	global $context, $scripturl, $txt, $sourcedir, $sportal_version, $user_profile;

	$context['sp_credits'] = array(
		array(
			'pretext' => $txt['sp-info_intro'],
			'title' => $txt['sp-info_team'],
			'groups' => array(
				array(
					'title' => $txt['sp-info_groups_pm'],
					'members' => array(
						'Napalm',
						'',
					),
				),
				array(
					'title' => $txt['sp-info_groups_webdev'],
					'members' => array(
						'Napalm',						
					),
				),
				array(
					'title' => $txt['sp-info_groups_dev'],
					'members' => array(
						'<span onclick="if (getInnerHTML(this).indexOf(\'Sinan\') == -1) setInnerHTML(this, \'Sinan &quot;[SiNaN]&quot; &Ccedil;evik\'); return false;">Selman &quot;[SiNaN]&quot; Eser</span>',
						'&#12487;&#12451;&#12531;1031',
						'Nathaniel Baxter',
					),
				),
				array(
					'title' => $txt['sp-info_groups_support'],
					'members' => array(
						'',
						'',
						'',
						'',
					),
				),
				array(
					'title' => $txt['sp-info_groups_customize'],
					'members' => array(
						'',
						'',
						'',
					),
				),
				array(
					'title' => $txt['sp-info_groups_language'],
					'members' => array(
						'',
						'',
						'',
					),
				),
				array(
					'title' => $txt['sp-info_groups_marketing'],
					'members' => array(
						'',
					),
				),
				array(
					'title' => $txt['sp-info_groups_beta'],
					'members' => array(
						'',
						'',
						'',
						'',
						'',
						'',
						'',
						'',
					),
				),
			),
		),
		array(
			'title' => $txt['sp-info_special'],
			'posttext' => $txt['sp-info_anyone'],
			'groups' => array(
				array(
					'title' => $txt['sp-info_groups_translators'],
					'members' => array(
						$txt['sp-info_translators_message'],
					),
				),
				array(
					'title' => $txt['sp-info_groups_founder'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['sp-info_groups_orignal_pm'],
					'members' => array(
					),
				),
				array(
					'title' => $txt['sp-info_fam_fam'],
					'members' => array(
						$txt['sp-info_fam_fam_message'],
					),
				),
			),
		),
	);

	if (!$in_admin)
	{
		loadTemplate('PortalAdmin');

		$context['robot_no_index'] = true;
		$context['in_admin'] = false;
	}
	else
	{
		$context['in_admin'] = true;
		$context['sp_version'] = $sportal_version;
		$context['sp_managers'] = array();

		require_once($sourcedir . '/Subs-Members.php');
		$manager_ids = loadMemberData(membersAllowedTo('sp_admin'), false, 'minimal');

		if ($manager_ids)
			foreach ($manager_ids as $member)
				$context['sp_managers'][] = '<a href="' . $scripturl . '?action=profile;u=' . $user_profile[$member]['id_member'] . '">' . $user_profile[$member]['real_name'] . '</a>';
	}

	$context['sub_template'] = 'information';
	$context['page_title'] = $txt['sp-info_title'];
}

function sportal_array_flatten($array)
{
	if (!is_array($array))
		return false;

	$result = array();
	foreach ($array as $key => $value)
	{
		if (is_array($value))
			$result = array_merge($result, sportal_array_flatten($value));
		else
			$result[$key] = $value;

	}

	return $result;
}
?>