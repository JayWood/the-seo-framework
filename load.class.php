<?php
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

add_action( 'plugins_loaded', 'the_seo_framework_init', 5 );
/**
 * Load The_SEO_Framework_Load class
 *
 * @action plugins_loaded
 * @priority 5 Use anything above 5, or any action later than plugins_loaded and
 * you can access the class and functions.
 *
 * @staticvar object $the_seo_framework
 *
 * @since 2.2.5
 */
function the_seo_framework_init() {
	//* Cache the class. Do not run everything more than once.
	static $the_seo_framework = null;

	if ( the_seo_framework_active() )
		if ( ! isset( $the_seo_framework ) )
			$the_seo_framework = new The_SEO_Framework_Load();

	return $the_seo_framework;
}

/**
 * Allow this plugin to load through filter
 *
 * Applies the_seo_framework_load filters.
 *
 * @return bool allow loading of plugin
 *
 * @since 2.1.0
 *
 * New function name.
 * @since 2.3.7
 *
 * @action plugins_loaded
 */
function the_seo_framework_load() {
	return (bool) apply_filters( 'the_seo_framework_load', true );
}

/**
 * Load plugin files
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_FUNCT
 * @uses THE_SEO_FRAMEWORK_DIR_PATH_CLASS
 *
 * @since 2.1.6
 */
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'compat.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'optionsapi.php' );
//require_once( THE_SEO_FRAMEWORK_DIR_PATH_FUNCT . 'benchmark.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'core.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'debug.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'compat.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'query.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'init.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'admininit.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'render.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'detect.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'postdata.class.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'postinfo.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'terminfo.class.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-description.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-title.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-url.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-image.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-ldjson.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'generate-author.class.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'search.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'doingitright.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'pageoptions.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'authoroptions.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'inpost.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'adminpages.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'sanitize.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'siteoptions.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'networkoptions.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'metaboxes.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'sitemaps.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'transients.class.php' );
require_once( THE_SEO_FRAMEWORK_DIR_PATH_CLASS . 'feed.class.php' );

require_once( THE_SEO_FRAMEWORK_DIR_PATH . 'inc/deprecated/deprecated.class.php' );

/**
 * God class.
 *
 * Extending upon parent classes.
 *
 * @since 2.1.6
 */
class The_SEO_Framework_Load extends The_SEO_Framework_Deprecated {

	/**
	 * Cached debug/profile constants. Initialized on plugins_loaded.
	 *
	 * @since 2.2.9
	 *
	 * @var bool The SEO Framework Debug/Profile constants is/are defined.
	 */
	public $the_seo_framework_debug = false;
	public $the_seo_framework_debug_hidden = false;
	public $the_seo_framework_use_transients = true;
	public $script_debug = false;

	/**
	 * Constructor, setup debug vars and then load parent constructor.
	 */
	public function __construct() {
		//* Setup debug vars before initializing parents.
		$this->init_debug_vars();

		parent::__construct();
	}

	/**
	 * Initializes public debug variables for the class to use.
	 *
	 * @since 2.6.0
	 */
	public function init_debug_vars() {

		$this->the_seo_framework_debug = defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ? true : $this->the_seo_framework_debug;
		if ( $this->the_seo_framework_debug ) {
			//* No need to set these to true if no debugging is enabled.
			$this->the_seo_framework_debug_hidden = defined( 'THE_SEO_FRAMEWORK_DEBUG_HIDDEN' ) && THE_SEO_FRAMEWORK_DEBUG_HIDDEN ? true : $this->the_seo_framework_debug_hidden;
		}

		$this->the_seo_framework_use_transients = defined( 'THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS' ) && THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS ? false : $this->the_seo_framework_use_transients;

		//* WP Core definition.
		$this->script_debug = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? true : $this->script_debug;

	}

	/**
	 * Wrapper for function calling through parameters. The golden nugget.
	 * Is this function not working properly? Send me your code through the WordPress support forums.
	 * I'll adjust if possible.
	 *
	 * @param array|string $callback the method array or function string.
	 * @param string $version the version of AutoDescription the function is used.
	 *
	 * @since 2.2.2
	 *
	 * @return mixed $output The function called.
	 *
	 * @NOTE _doing_it_wrong notices go towards the callback. Unless this
	 * function is used wrongfully. Then the notice is about this function.
	 *
	 * @param array|string $params The arguments passed to the function.
	 * @since 2.2.4
	 */
	public function call_function( $callback, $version = '', $params = array() ) {

		$output = '';

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $callback ) ) {
			$function = array( $callback, '' );
		} else {
			$function = (array) $callback;
		}

		/**
		 * Convert string/object to array
		 */
		if ( is_object( $params ) ) {
			$args = array( $params, '' );
		} else {
			$args = (array) $params;
		}

		$class = reset( $function );
		$method = next( $function );

		/**
		 * Fetch method/function
		 */
		if ( is_object( $class ) && is_string( $method ) ) {
			$class = get_class( $class );

			if ( $class === get_class( $this ) ) {
				if ( method_exists( $this, $method ) ) {
					if ( empty( $args ) ) {
						// In-Object calling.
						$output = call_user_func( array( $this, $method ) );
					} else {
						// In-Object calling.
						$output = call_user_func_array( array( $this, $method ), $args );
					}
				} else {
					$this->_doing_it_wrong( (string) $class . '::' . (string) $method, __( "Class or Method not found.", 'autodescription' ), $version );
				}
			} else {
				if ( method_exists( $class, $method ) ) {
					if ( empty( $args ) ) {
						// Static calling
						$output = call_user_func( array( $class, $method ) );
					} else {
						// Static calling
						$output = call_user_func_array( array( $class, $method ), $args );
					}
				} else {
					$this->_doing_it_wrong( (string) $class . '::' . (string) $method, __( "Class or Method not found.", 'autodescription' ), $version );
				}
			}
		} else if ( is_string( $class ) && is_string( $method ) ) {
			//* This could be combined with the one above.
			if ( method_exists( $class, $method ) ) {
				if ( empty( $args ) ) {
					// Static calling
					$output = call_user_func( array( $class, $method ) );
				} else {
					// Static calling
					$output = call_user_func_array( array( $class, $method ), $args );
				}
			} else {
				$this->_doing_it_wrong( (string) $class . '::' . (string) $method, __( "Class or Method not found.", 'autodescription' ), $version );
			}
		} else if ( is_string( $class ) ) {
			//* Class is function.
			$func = $class;

			if ( empty( $args ) ) {
				$output = call_user_func( $func );
			} else {
				$output = call_user_func_array( $func, $args );
			}
		} else {
			$this->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, __( "Function needs to be called as a string.", 'autodescription' ), $version );
		}

		return $output;
	}

	/**
	 * Helper function for Doing it Wrong
	 *
	 * @since 2.3.0
	 */
	public function the_seo_framework_version( $version = '' ) {

		$output = empty( $version ) ? '' : sprintf( __( '%s of The SEO Framework', 'autodescription' ), esc_attr( $version ) );

		return $output;
	}

}

//* Load deprecated functions.
require_once( THE_SEO_FRAMEWORK_DIR_PATH . 'inc/deprecated/deprecated.php' );

/**
 * FLush permalinks on activation/deactivation
 *
 * Calls functions statically.
 *
 * @since 2.2.9
 */
register_activation_hook( THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE, array( 'The_SEO_Framework_Load', 'flush_rewrite_rules_activation' ) );
register_deactivation_hook( THE_SEO_FRAMEWORK_PLUGIN_BASE_FILE, array( 'The_SEO_Framework_Load', 'flush_rewrite_rules_deactivation' ) );
