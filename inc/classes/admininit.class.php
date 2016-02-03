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

/**
 * Class AutoDescription_Admin_Init
 *
 * Initializes the plugin for the wp-admin screens.
 * Enqueues css and javascript.
 *
 * @since 2.1.6
 */
class AutoDescription_Admin_Init extends AutoDescription_Init {

	/**
	 * Page Hook.
	 *
	 * @since 2.5.2.2
	 *
	 * @var String Holds Admin Page hook.
	 */
	protected $page_hook;

	/**
	 * JavaScript name identifier to be used with enqueuing.
	 *
	 * @since 2.5.2.2
	 *
	 * @var array JavaScript name identifier.
	 */
	protected $js_name;

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes wp-admin functions
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_init', array( $this, 'post_state' ) );
		add_action( 'init', array( $this, 'post_type_support' ) );

		/**
		 * @since 2.2.4
		 */
		add_filter( 'genesis_detect_seo_plugins', array( $this, 'no_more_genesis_seo' ), 10 );

		/**
		 * @since 2.5.0
		 * Doesn't work. ePanel filters are buggy and inconsistent.
		 */
		// add_filter( 'epanel_page_maintabs', array( $this, 'no_more_elegant_seo' ), 10, 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10, 1 );

		$this->js_name = 'autodescription-js';
	}

	/**
	 * Add post state on edit.php to the page or post that has been altered
	 *
	 * Called outside autodescription_run
	 *
	 * Applies filters `the_seo_framework_allow_states` : boolean
	 *
	 * @uses $this->add_post_state
	 *
	 * @since 2.1.0
	 */
	public function post_state() {

		$allow_states = (bool) apply_filters( 'the_seo_framework_allow_states', true );

		//* Prevent this function from running if this plugin is set to disabled.
		if ( ! $allow_states )
			return;

		add_filter( 'display_post_states', array( $this, 'add_post_state' ) );

	}

	/**
	 * Adds post states in post/page edit.php query
	 *
	 * @param array states 		the current post state
	 * @param string redirected	$this->get_custom_field( 'redirect' );
	 * @param string noindex	$this->get_custom_field( '_genesis_noindex' );
	 *
	 * @since 2.1.0
	 */
	public function add_post_state( $states = array() ) {

		$post_id = $this->get_the_real_ID( false );

		$searchexclude = (bool) $this->get_custom_field( 'exclude_local_search', $post_id );

		if ( $searchexclude === true )
			$states[] = __( 'No Search', 'autodescription' );

		return $states;
	}

	/**
	 * Removes the Genesis SEO meta boxes on the SEO Settings page
	 *
	 * @since 2.2.4
	 */
	public function no_more_genesis_seo() {

		$plugins = array(
				// Classes to detect.
				'classes' => array(
					'The_SEO_Framework_Load',
				),

				// Functions to detect.
				'functions' => array(),

				// Constants to detect.
				'constants' => array(),
			);

		return (array) $plugins;
	}

	/**
	 * Removes ePanel (Elegant Themes) SEO options.
	 *
	 * @since 2.5.0
	 */
	public function no_more_elegant_seo( $modules = array() ) {

		//* Something went wrong here.
		if ( ! is_array( $modules ) )
			return $modules;

		$modules = array_flip( $modules );
		unset( $modules['seo'] );
		//* Fill the keys back in order.
		$modules = array_values( array_flip( $modules ) );

		/**
		 * Unset globals $options['randomkeyforseo']
		 *
		 * @NOTE to Elegant Themes:
		 * Why Elegant Themes? This is why I never trusted your themes. :(
		 * Uploading most of them in binary will crash also the layout.
		 * And having unsanitized globals $options (great name for a global!), shouldn't be used.
		 *
		 * Try statically cached functions, take a look at the `the_seo_framework_init` function for a great example of countering globals.
		 *
		 * Please also provide more documentation for developers.
		 *
		 * Please rewrite your ePanel. Try to start by adding keys to options and removing globals.
		 * More filters are also for everyone's pleasure :).
		 *
		 * I also recommend using Atom.io or Notepad++, because whatever you're using:
		 * It's not working well with UTF-8.
		 *
		 * @global $options
		 */
		global $options;

		if ( is_array( $options ) ) {
			$keys = array();

			foreach ( $options as $key => $array ) {
				$seo_key = array_search( 'seo', $array );
				if ( false !== $seo_key && 'name' === $seo_key )
					$keys[] = $seo_key;
			}

			foreach ( $keys as $key )
				unset( $options[$key] );
		}

		return (array) $modules;
	}

	/**
	 * Enqueues scripts in the admin area on the supported screens.
	 *
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_admin_scripts( $hook ) {

		/**
		 * Check hook first.
		 * @since 2.3.9
		 */
		if ( isset( $hook ) && ! empty( $hook ) && ( $hook == 'edit.php' || $hook == 'post.php' || $hook = 'edit-tags.php' ) ) {
			/**
			 * @uses $this->post_type_supports_custom_seo()
			 * @since 2.3.9
			 */
			if ( $this->post_type_supports_custom_seo() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_css' ), 11 );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_javascript' ), 11 );
			}
		}

	}

	/**
	 * AutoDescription Javascript helper file
	 *
	 * @since 2.0.2
	 *
	 * @usedby add_inpost_seo_box
	 * @usedby enqueue_javascript
	 *
	 * @param string|array|object $hook the current page
	 */
	public function enqueue_admin_javascript( $hook ) {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_script( $this->js_name, THE_SEO_FRAMEWORK_DIR_URL . "lib/js/autodescription{$suffix}.js", array( 'jquery' ), THE_SEO_FRAMEWORK_VERSION, true );

		/**
		 * Put hook and js name in class vars.
		 * @since 2.5.2.2
		 */
		$this->page_hook = $hook;

		//* @since 2.5.2.2
		add_action( 'admin_footer', array( $this, 'localize_admin_javascript' ) );
	}

	/**
	 * Localizes admin javascript.
	 *
	 * @since 2.5.2.2
	 */
	public function localize_admin_javascript() {

		$blog_name = $this->get_blogname();
		$description = $this->get_blogdescription();
		$title = '';
		$additions = '';

		$tagline = (bool) $this->get_option( 'homepage_tagline' );
		$home_tagline = $this->get_option( 'homepage_title_tagline' );
		$title_location = $this->get_option( 'title_location' );
		$title_rem_additions = (bool) $this->get_option( 'title_rem_additions' );

		$separator = $this->get_separator( 'title', true );

		$rtl = (bool) is_rtl();
		$ishome = false;

		/**
		 * We're gaining UX in exchange for resource usage.
		 *
		 * Any way to cache this?
		 *
		 * @since 2.2.4
		 */
		if ( '' !== $this->page_hook ) {
			// We're somewhere within default WordPress pages.
			$post_id = $this->get_the_real_ID();

			if ( $this->is_static_frontpage( $post_id ) ) {
				$title = $blog_name;
				$title_location = $this->get_option( 'home_title_location' );
				$ishome = true;

				if ( $tagline ) {
					$additions = $home_tagline ? $home_tagline : $description;
				} else {
					$additions = '';
				}
			} else if ( $post_id ) {
				//* We're on post.php
				$generated_doctitle_args = array(
					'term_id' => $post_id,
					'placeholder' => true,
					'meta' => true,
					'get_custom_field' => false,
					'notagline' => true
				);

				$title = $this->title( '', '', '', $generated_doctitle_args );

				if ( ! $title_rem_additions || ! $this->theme_title_doing_it_right() ) {
					$additions = $blog_name;
					$tagline = true;
				} else {
					$additions = '';
					$tagline = false;
				}
			} else if ( 'term.php' === $this->page_hook ) {
				//* Category or Tag.
				global $current_screen;

				if ( isset( $current_screen->taxonomy ) ) {

					$term_id = absint( $_REQUEST['term_id'] );

					$generated_doctitle_args = array(
						'term_id' => $term_id,
						'taxonomy' => $current_screen->taxonomy,
						'placeholder' => true,
						'meta' => true,
						'get_custom_field' => false,
						'notagline' => true
					);

					$title = $this->title( '', '', '', $generated_doctitle_args );
					$additions = $title_rem_additions ? '' : $blog_name;
				}

			} else {
				//* We're in a special place.
				// Can't fetch title.
				$title = '';
				$additions = $title_rem_additions ? '' : $blog_name;
			}

		} else {
			// We're on our SEO settings pages.
			if ( 'page' === get_option( 'show_on_front' ) ) {
				// Home is a page.
				$inpost_title = $this->get_custom_field( '_genesis_title', get_option( 'page_on_front' ) );
			} else {
				// Home is a blog.
				$inpost_title = '';
			}
			$title = ! empty( $inpost_title ) ? $inpost_title : $blog_name;
			$additions = $home_tagline ? $home_tagline : $description;
		}

		$strings = array(
			'saveAlert'		=> __( 'The changes you made will be lost if you navigate away from this page.', 'autodescription' ),
			'confirmReset'	=> __( 'Are you sure you want to reset all SEO settings to their defaults?', 'autodescription' ),
			'siteTitle' 	=> $title,
			'titleAdditions' => $additions,
			'blogDescription' => $description,
			'titleTagline' 	=> $tagline,
			'titleSeparator' => $separator,
			'titleLocation' => $title_location,
			'isRTL' => $rtl,
			'isHome' => $ishome,
		);

		wp_localize_script( 'autodescription-js', 'autodescriptionL10n', $strings );
	}

	/**
	 * CSS for the AutoDescription Bar
	 *
	 * @since 2.1.9
	 *
	 * @param $hook the current page
	 *
	 * @todo get_network_option
	 */
	public function enqueue_admin_css( $hook ) {

		$rtl = '';

		if ( is_rtl() )
			$rtl = '-rtl';

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style( 'autodescription-css', THE_SEO_FRAMEWORK_DIR_URL . "lib/css/autodescription{$rtl}{$suffix}.css", array(), THE_SEO_FRAMEWORK_VERSION, 'all' );

	}

	/**
	 * Checks the screen hook.
	 *
	 * @since 2.2.2
	 *
	 * @return bool true if screen match.
	 */
	public function is_menu_page( $pagehook = '' ) {
		global $page_hook;

		if ( isset( $page_hook ) && $page_hook === $pagehook )
			return true;

			//* May be too early for $page_hook
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] === $pagehook )
			return true;

		return false;
	}

	/**
	 * Redirect the user to an admin page, and add query args to the URL string
	 * for alerts, etc.
	 *
	 * @since 2.2.2
	 *
	 * @param string $page			Menu slug.
	 * @param array  $query_args 	Optional. Associative array of query string arguments
	 * 								(key => value). Default is an empty array.
	 *
	 * @return null Return early if first argument is false.
	 */
	public function admin_redirect( $page, array $query_args = array() ) {

		if ( ! $page )
			return;

		$url = html_entity_decode( menu_page_url( $page, 0 ) );

		foreach ( (array) $query_args as $key => $value ) {
			if ( empty( $key ) && empty( $value ) ) {
				unset( $query_args[$key] );
			}
		}

		$url = add_query_arg( $query_args, $url );

		wp_redirect( esc_url_raw( $url ) );
		exit;

	}

}
