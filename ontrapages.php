<?php
/**
 * @package ONTRApages
 */
/*
Plugin Name: ONTRApages
Plugin URI: http://google.com
Description: ONTRApages for WordPress allows Ontraport users to connect to their accounts and easily publish their landing pages on their own WordPress sites.
Version: 1.2.25
Author: Ontraport
Author URI: http://ontraport.com/
License: GPLv2 or later
Text Domain: ONTRApages.com
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Don't show anything if called directly
if ( !function_exists( 'add_action' ) )
{
	exit;
}

if ( !defined( 'ONTRAPAGES_VERSION' ) ) define( 'ONTRAPAGES_VERSION', '1.2.25' );
if ( !defined( 'ONTRAPAGES__MINIMUM_WP_VERSION' ) ) define( 'ONTRAPAGES__MINIMUM_WP_VERSION', '4.0' );
if ( !defined( 'ONTRAPAGES__PLUGIN_URL' ) ) define( 'ONTRAPAGES__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
if ( !defined( 'ONTRAPAGES__PLUGIN_DIR' ) ) define( 'ONTRAPAGES__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if ( !defined( 'OPAPI' ) ) define( 'OPAPI', 'https://api.ontraport.com/1/' );

require_once( ONTRAPAGES__PLUGIN_DIR . 'OPCoreFunctions.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'OPAdminSettings.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'OPObjects.php' );
require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRApage.php' );

// Run these functions on WP plugin activation and deactivation
register_activation_hook( __FILE__, array( 'OPAdminSettings', 'ontrapagesActivation' ) );
register_deactivation_hook( __FILE__, array( 'OPAdminSettings', 'ontrapagesDeactivation' ) );

// Initialize OP page and form settings
add_action( 'init', array( 'ONTRApage', 'init' ) );

// Admin settings
if ( is_admin() ) 
{
	require_once( ONTRAPAGES__PLUGIN_DIR . 'OPObjects.php' );
	require_once( ONTRAPAGES__PLUGIN_DIR . 'ONTRApagesAdmin.php' );

	// Initialize OP Admin settings and add admin scripts / styles
	add_action( 'admin_menu', array( 'OPAdminSettings', 'adminSettings' ) );
	add_action( 'admin_enqueue_scripts', array( 'OPAdminSettings', 'adminScripts' ) );

	// Initialize any necessary Admin Settings &/or checks
	add_action( 'init', array( 'OPAdminSettings', 'init' ) );

	// Initialize OP Admin page and form settings
	add_action( 'init', array( 'ONTRApagesAdmin', 'init' ) );
	
	// Fix the wrong API settings that were set in v1.1
	if ( get_option('opAPIFix') === false )
	{
		OPAdminSettings::fixAPISettings();
	}
	//We need to hook into after plugins are loaded so we can hook into PilotPress
	add_action("plugins_loaded", array( "OPAdminSettings", "pluginsLoaded"));
	
}
