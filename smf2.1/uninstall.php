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

// Handle running this file by using SSI.php
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');

// Hmm... no SSI.php and no SMF?
elseif (!defined('SMF'))
	die('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

// Remove hooks from the database
remove_integration_function('integrate_load_permissions', '$sourcedir/PortalHooks.php|sportal_permissions');
remove_integration_function('integrate_pre_load', '$sourcedir/Subs-Portal.php|sp_smf_version');
remove_integration_function('integrate_pre_log_stats', '$sourcedir/PortalHooks.php|sportal_initialize');
remove_integration_function('integrate_actions', '$sourcedir/PortalHooks.php|sportal_actions');
remove_integration_function('integrate_admin_areas', '$sourcedir/PortalHooks.php|sportal_admin_areas');
remove_integration_function('integrate_admin_search', '$sourcedir/PortalHooks.php|sportal_admin_search');
remove_integration_function('integrate_user_info', '$sourcedir/PortalHooks.php|sportal_user_info');
remove_integration_function('integrate_mark_read_button', '$sourcedir/PortalHooks.php|sportal_mark_read_button');
remove_integration_function('integrate_display_message_list', '$sourcedir/PortalHooks.php|sportal_display_message_list');
remove_integration_function('integrate_helpadmin', '$sourcedir/PortalHooks.php|sportal_helpadmin');
remove_integration_function('integrate_prepare_db_settings', '$sourcedir/PortalHooks.php|sportal_prepare_db_settings');
remove_integration_function('integrate_buffer', '$sourcedir/PortalHooks.php|sportal_buffer');
remove_integration_function('integrate_pre_parsebbc', '$sourcedir/PortalHooks.php|sportal_pre_parsebbc');

?>