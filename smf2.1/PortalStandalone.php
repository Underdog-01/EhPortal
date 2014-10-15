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

	This file here, unbelievably, has your portal within.

	In order to use WebDevil in standalone mode:
		+ Go to "SPortal Admin" >> "Configuration" >> "General Settings"
		+ Select "Standalone" mode as "Portal Mode"
		+ Set "Standalone URL" as the full url of this file.
		+ Edit path to the forum ($forum_dir) in this file.

	See? It's just magic!

*/

global $sp_standalone;

// Should be the full path!
$forum_dir = 'full/path/to/forum';

// Let them know the mode.
$sp_standalone = true;

// Hmm, wrong forum dir?
if (!file_exists($forum_dir . '/index.php'))
	die('Wrong $forum_dir value. Please make sure that the $forum_value variable points to your forum\'s directory.');

// Get out the forum's SMF version number.
$data = substr(file_get_contents($forum_dir . '/index.php'), 0, 4096);
if (preg_match('~\*\s*Software\s+Version:\s+(SMF\s+.+?)[\s]{2}~i', $data, $match))
	$forum_version = $match[1];
elseif (preg_match('~\*\s@version\s+(.+)[\s]{2}~i', $data, $match))
	$forum_version = 'SMF ' . $match[1];

// Call the SSI magic.
require_once($forum_dir . '/SSI.php');

// Wireless? We don't support you, yet.
if (WIRELESS)
	redirectexit();

// Get our main file.
require_once($sourcedir . '/PortalMain.php');

// Re-initialize SP.
sportal_init(true);

// We'll catch you...
writeLog();

// Get the page ready.
sportal_main();

// Here we go!
obExit(true);

?>