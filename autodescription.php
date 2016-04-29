<?php
/**
 * Plugin Name: The SEO Framework
 * Plugin URI: https://wordpress.org/plugins/autodescription/
 * Description: An automated, advanced, accessible, unbranded and extremely fast SEO solution for any WordPress website.
 * Version: 2.5.2.1dev2.6.0
 * Author: Sybre Waaijer
 * Author URI: https://cyberwire.nl/
 * License: GPLv3
 * Text Domain: autodescription
 * Domain Path: /language
 */

/**
 * The SEO Framework plugin
 * Copyright (C) 2015 - 2016 Sybre Waaijer, CyberWire (https://cyberwire.nl/)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 3 as published
 * by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

//* Debug. Not to be used on production websites as it dumps and/or disables all kinds of stuff everywhere.
//add_action( 'plugins_loaded', function() { if ( is_super_admin() ) {
	//if ( is_admin() ) {
	//		define( 'THE_SEO_FRAMEWORK_DEBUG', true );
	//		define( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN', true );
			define( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS', true );
	//}
//}},0);

// Regex finding possible static initiators: ([$])([a-z_A-Z])([^=].*(null;))
// Regex finding possible static initiators: ([$])([a-z_A-Z])([^=].*(array\(\);))
// Regex finding static initiators: (static [$])([a-z_A-Z])([^=].*(null;))
// Regex finding static initiators: (static [$])([a-z_A-Z])([^=].*(array\(\);))

/**
 * CDN Cache buster. 3 to 4 point.
 * Not many caching plugins use CDN in dashboard. What a shame. Firefox does cache.
 *
 * @since 1.0.0
 */
define( 'THE_SEO_FRAMEWORK_VERSION', '2.6.0-BETA' );

/**
 * Plugin options filter
 * We can't change the options name without erasing the settings.
 * We can change the filter, however. So we did.
 *
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_SITE_OPTIONS', (string) apply_filters( 'the_seo_framework_site_options', 'autodescription-site-settings' ) );

/**
 * Plugin options filter
 * We can't change the options name without erasing the settings.
 * We can change the filter, however. So we did.
 *
 * @since 2.2.2
 */
define( 'THE_SEO_FRAMEWORK_NETWORK_OPTIONS', (string) apply_filters( 'the_seo_framework_network_settings', 'autodescription-network-settings' ) );

/**
 * The plugin map url.
 * Used for calling browser files.
 *
 * @since 1.0.0
 */
define( 'THE_SEO_FRAMEWORK_DIR_URL', plugin_dir_url( __FILE__ ) );

/**
 * The plugin map absolute path.
 * Used for calling php files.
 *
 * @since 1.0.0
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The plugin file relative to the plugins dir.
 *
 * @since 2.2.8
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The plugin file, absolute unix path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE', __FILE__ );

/**
 * The plugin class map absolute path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_CLASS', THE_SEO_FRAMEWORK_DIR_PATH . '/inc/classes/' );

/**
 * The plugin function map absolute path.
 * @since 2.2.9
 */
define( 'THE_SEO_FRAMEWORK_DIR_PATH_FUNCT', THE_SEO_FRAMEWORK_DIR_PATH . '/inc/functions/' );

add_action( 'plugins_loaded', 'the_seo_framework_locale_init', 10 );
/**
 * Plugin locale 'autodescription'
 *
 * File located in plugin folder autodescription/language/
 *
 * @since 1.0.0
 */
function the_seo_framework_locale_init() {
	load_plugin_textdomain( 'autodescription', false, basename( dirname( __FILE__ ) ) . '/language/' );
}

/**
 * Load plugin files
 *
 * @since 1.0.0
 *
 * @uses THE_SEO_FRAMEWORK_DIR_PATH
 */
require_once( THE_SEO_FRAMEWORK_DIR_PATH . '/load.class.php' );

add_filter( 'the_seo_framework_after_output', 'my_itemprop_description_init', 11 );
/**
 * Special filter to keep adding onto The SEO Framework.
 *
 * @param array $functions All the hooked functions.
 * @return array $functions The hooked functions.
 */
function my_itemprop_description_init( $functions ) {

	$functions[] = array(
		'callback' => array( 'my_itemprop_description_output' ),
	);
	$functions[] = array(
		'callback' => array( 'my_itemprop_title_output' ),
	);

	return $functions;
}

/**
 * Output Google+ Social Description.
 *
 * @return string The Google+ description.
 */
function my_itemprop_description_output() {

	//* Call the class.
	$the_seo_framework = the_seo_framework();

	//* Get the social description. Already escaped.
	$description = $the_seo_framework->description_from_cache( true );

	return '<meta itemprop="description" content="' . $description . '" />' . "\r\n";
}

/**
 * Output Google+ Social Title.
 *
 * @return string The Google+ title.
 */
function my_itemprop_title_output() {

	//* Call the class.
	$the_seo_framework = the_seo_framework();

	//* Get the title from cache. Already escaped.
	$title = $the_seo_framework->title_from_cache( '', '', '', true );

	return '<meta itemprop="name" content="' . $title . '" />' . "\r\n";
}
