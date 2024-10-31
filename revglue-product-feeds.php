<?php 
/*
Plugin Name: RevGlue Product Feeds
Description: This plugin connects your wordpress site with RevGlue. It imports the stores, categories, deals and banners you selected at RevGlue to your wordpress website.
Version:     1.0.0
Author:      RevGlue
Author URI:  http://www.revglue.com/
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: revglue-pfeeds
 
RevGlue Product Feeds is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
RevGlue Product Feeds is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with RevGlue Product Feeds. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Global Variables 
define( 'RGPFEEDS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'RGPFEEDS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'RGPFEEDS_API_URL', 'https://www.revglue.com/' );
define( 'REVGLUE_STORE_ICONS', 'https://revglue.com/resources/wp-theme/productfeed/categoryicons/' );
define( 'REVGLUE_CATEGORY_BANNERS', 'https://revglue.com/resources/wp-theme/productfeed/categorybanners/' );

function rg_pfeeds_install() 
{
	// List of folders we need to create for product feeds in WordPress uploads folder
	$dir_structure_array = array( 'revglue', 'product-feeds', 'banners' );
	rg_pfeeds_create_directory_structures( $dir_structure_array );
	
	rg_pfeeds_create_directory_structures( $dir_structure_array );
	
	include( RGPFEEDS_PLUGIN_DIR . 'includes/rg-install.php' );
}
register_activation_hook( __FILE__, 'rg_pfeeds_install' );
	
function rg_pfeeds_uninstall() 
{
	rg_pfeeds_remove_directory_structures();
	include( RGPFEEDS_PLUGIN_DIR . 'includes/rg-uninstall.php' );
}
register_uninstall_hook( __FILE__, 'rg_pfeeds_uninstall' );

require_once( RGPFEEDS_PLUGIN_DIR . 'includes/core.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/ajax-calls.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/main-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/import-stores-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/import-banners-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/import-pfeeds-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/stores-listing-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/categories-listing-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/pfeeds-listing-page.php' );
require_once( RGPFEEDS_PLUGIN_DIR . 'includes/pages/banners-listing-page.php' );
?>