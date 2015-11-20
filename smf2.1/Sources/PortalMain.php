<?php
/*
	<id>ChenZhen:EhPortal</id>
	<name>EhPortal</name>
	<version>1.1</version>
*/
/*
 * EhPortal is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * EhPortal ~ Copyright (c) 2015 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/*
	void sportal_main()
		// !!!

	void sportal_credits()
		// !!!
*/

function sportal_main()
{
	global $smcFunc, $context, $sourcedir;

	$wirelessArray = array(
		'is_iphone',
		'is_android',
		'is_blackberry',
		'is_nokia',
		'is_opera_mobi',
		'is_opera_mini'
	);
	foreach ($wirelessArray as $wirelessType)
		$sp_wireless = !empty($context['browser'][$wirelessType]) ? true : false;

	if ($sp_wireless)
		redirectexit('action=forum');

	$context['page_title'] = $context['forum_name'];

	if (isset($context['page_title_html_safe']))
		$context['page_title_html_safe'] = $smcFunc['htmlspecialchars'](un_htmlspecialchars($context['page_title']));

	if (!empty($context['standalone']))
		setupMenuContext();

	$actions = array(
		'addarticle' => array('PortalArticles.php', 'sportal_add_article'),
		'articles' => array('PortalArticles.php', 'sportal_articles'),
		'credits' => array('', 'sportal_credits'),
		'pages' => array('PortalPages.php', 'sportal_pages'),
		'removearticle' => array('PortalArticles.php', 'sportal_remove_article'),
		'shoutbox' => array('PortalShoutbox.php', 'sportal_shoutbox'),
	);

	if (!isset($_REQUEST['sa']) || !isset($actions[$_REQUEST['sa']]))
		$_REQUEST['sa'] = 'articles';

	if (!empty($actions[$_REQUEST['sa']][0]))
		require_once($sourcedir . '/' . $actions[$_REQUEST['sa']][0]);

	$actions[$_REQUEST['sa']][1]();
}

function sportal_credits()
{
	global $sourcedir, $context, $txt;

	require_once($sourcedir . '/PortalAdminMain.php');
	loadLanguage('SPortalAdmin', sp_languageSelect('SPortalAdmin'));

	sportal_information(false);

	$context['page_title'] = $txt['sp-info_title'];
	$context['sub_template'] = 'information';
}

?>