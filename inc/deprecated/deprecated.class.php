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
 * Deprecation class.
 * Contains all deprecated functions. Is autoloaded.
 *
 * @since 2.3.4
 */
class The_SEO_Framework_Deprecated extends AutoDescription_Feed {

	/**
	 * Constructor. Load parent constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Return option from the options table and cache result.
	 *
	 * @since 2.0.0
	 *
	 * @deprecated
	 * @since 2.3.4
	 */
	public function autodescription_get_option( $key, $setting = null, $use_cache = true ) {
		$this->_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, '2.3.4', 'AutoDescription_Adminpages::the_seo_framework_get_option()' );

		return $this->the_seo_framework_get_option( $key, $setting, $use_cache );
	}

	/**
	 * Enqueues JS in the admin footer
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_javascript( $hook ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.3.3', 'AutoDescription_Admin_Init::enqueue_admin_scripts()' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Enqueues CSS in the admin header
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 *
	 * @param $hook the current page
	 */
	public function enqueue_css( $hook ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.3.3', 'AutoDescription_Admin_Init::enqueue_admin_scripts()' );

		return $this->enqueue_admin_scripts( $hook );
	}

	/**
	 * Setup var for sitemap transient.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function fetch_sitemap_transient_name() {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::$sitemap_transient' );

		return $this->sitemap_transient;
	}

	/**
	 * Delete Sitemap transient on post save.
	 *
	 * @since 2.2.9
	 *
	 * @deprecated
	 * @since 2.3.3
	 */
	public function delete_sitemap_transient_post( $post_id ) {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::delete_sitemap_transient_post()' );

		return $this->delete_transients_post( $post_id );
	}

	/**
	 * Helper function for Doing it Wrong
	 *
	 * @since 2.2.4
	 *
	 * @deprecated
	 * @since 2.3.0
	 */
	public function autodescription_version( $version = '' ) {
		$this->_deprecated_function( 'The_SEO_Framework_Load::' . __FUNCTION__, '2.3.0', 'The_SEO_Framework_Load::the_seo_framework_version()' );

		return $this->the_seo_framework_version( $version );
	}

	/**
	 * Include the necessary sortable metabox scripts.
	 *
	 * @since 2.2.2
	 *
	 * @deprecated
	 * @since 2.3.5
	 */
	public function scripts() {
		$this->_deprecated_function( 'AutoDescription_Adminpages::' . __FUNCTION__, '2.3.5', 'AutoDescription_Adminpages::metabox_scripts()' );

		return $this->metabox_scripts();
	}

	/**
	 * Setup var for sitemap transient on init/admin_init.
	 *
	 * @since 2.3.3
	 * @deprecated
	 * @since 2.3.3
	 * Oops.
	 */
	public function setup_transient_names_init() {
		$this->_deprecated_function( 'AutoDescription_Transients::' . __FUNCTION__, '2.3.3', 'AutoDescription_Transients::setup_transient_names()' );

		$this->setup_transient_names();
		return false;
	}

	/**
	 * Helper function for allowed post/page screens where this plugin is active.
	 *
	 * @param array $screens The allowed screens
	 *
	 * @since 2.1.9
	 *
	 * Applies filters the_seo_framework_supported_screens : The supported administration
	 * screens where css and javascript files are loaded.
	 *
	 * @param array $args the custom supported screens.
	 *
	 * @deprecated
	 * @since 2.5.2
	 *
	 * @return array $screens
	 */
	protected function supported_screens( $args = array() ) {
		$this->_deprecated_function( 'AutoDescription_Admin_Init::' . __FUNCTION__, '2.5.2' );

		/**
		 * Instead of supporting page ID's, we support the Page base now.
		 *
		 * @since 2.3.3
		 */
		$defaults = array(
			'edit',
			'post',
			'edit-tags',
		);

		$screens = (array) apply_filters( 'the_seo_framework_supported_screens', $defaults, $args );
		$screens = wp_parse_args( $args, $screens );

		return $screens;
	}

	/**
	 * Add doing it wrong html code in the footer.
	 *
	 * @since 2.2.5
	 *
	 * @deprecated
	 * @since 2.5.2.1
	 */
	public function title_doing_it_wrong() {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.5.2.1', 'AutoDescription_Detect::tell_title_doing_it_wrong()' );

		return;
	}

	/**
	 * Checks a theme's support for a given feature
	 *
	 * @since 2.2.5
	 *
	 * @global array $_wp_theme_features
	 *
	 * @param string $feature the feature being checked
	 * @return bool
	 *
	 * Taken from WP Core, but it now returns true on title-tag support.
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function current_theme_supports( $feature ) {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.6.0', 'current_theme_supports()' );

		return current_theme_supports();
	}

	/**
	 * Echo debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @since 2.3.4
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function echo_debug_information( $values ) {
		$this->_deprecated_function( 'AutoDescription_Debug::' . __FUNCTION__, '2.6.0', 'AutoDescription_Debug::get_debug_information()' );

		echo $this->get_debug_information( $values );

	}

	/**
	 * Get the archive Title.
	 *
	 * WordPress core function @since 4.1.0
	 *
	 * @since 2.3.6
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function get_the_archive_title() {
		$this->_deprecated_function( 'AutoDescription_Generate_Description::' . __FUNCTION__, '2.6.0', 'AutoDescription_Generate_Title::get_the_real_archive_title()' );

		return $this->get_the_real_archive_title();
	}

	/**
	 * Adds the SEO Bar.
	 *
	 * @param string $column the current column    : If it's a taxonomy, this is empty
	 * @param int $post_id the post id             : If it's a taxonomy, this is the column name
	 * @param string $tax_id this is empty         : If it's a taxonomy, this is the taxonomy id
	 *
	 * @param string $status the status in html
	 *
	 * @staticvar string $type_cache
	 * @staticvar string $column_cache
	 *
	 * @since 2.1.9
	 *
	 * @deprecated
	 * @since 2.6.0
	 */
	public function seo_column( $column, $post_id, $tax_id = '' ) {
		$this->_deprecated_function( 'AutoDescription_DoingItRight::' . __FUNCTION__, '2.6.0', 'AutoDescription_DoingItRight::seo_bar()' );

		return $this->seo_bar( $column, $post_id, $tax_id );
	}

	/**
	 * Ping Yahoo
	 *
	 * @since 2.2.9
	 * @deprecated
	 * @since 2.6.0
	 */
	public function ping_yahoo() {
		$this->_deprecated_function( 'AutoDescription_Sitemaps::' . __FUNCTION__, '2.6.0', 'AutoDescription_Sitemaps::ping_bing()' );

		$this->ping_bing();
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @param string|bool $content required The sitemap transient content.
	 *
	 * @since 2.2.9
	 * @deprecated
	 * @since 2.6.0
	 */
	public function setup_sitemap_transient( $sitemap_content ) {
		$this->_deprecated_function( 'AutoDescription_Sitemaps::' . __FUNCTION__, '2.6.0', 'AutoDescription_Sitemaps::setup_sitemap()' );

		return $this->setup_sitemap( $sitemap_content );
	}

	/**
	 * Detect WordPress language.
	 * Considers en_UK, en_US, etc.
	 *
	 * @param string $str Required, the locale.
	 * @param bool $use_cache Set to false to bypass the cache.
	 *
	 * @staticvar array $locale
	 * @staticvar string $get_locale
	 *
	 * @since 2.3.8
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function is_locale( $str, $use_cache = true ) {
		$this->_deprecated_function( 'AutoDescription_Detect::' . __FUNCTION__, '2.6.0', 'AutoDescription_Detect::check_wp_locale()' );

		return $this->check_wp_locale( $str, $use_cache );
	}

	/**
	 * Build the title based on input, without tagline.
	 * Note: Not escaped.
	 *
	 * @param string $title The Title to return
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool page_on_front Page on front condition for example generation
	 * }
	 *
	 * @since 2.4.0
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return string Title without tagline.
	 */
	public function get_placeholder_title( $title = '', $args = array() ) {
		$this->_deprecated_function( 'AutoDescription_Generate_Title::' . __FUNCTION__, '2.6.0', 'AutoDescription_Generate_Title::title()` with the argument $args[\'notagline\']' );

		$args['notagline'] = true;
		return $this->title( $title, '', '', $args );
	}

	/**
	 * Initializes default settings very early at the after_setup_theme hook.
	 * Admin only.
	 *
	 * @since 2.5.0
	 * @access private
	 * @deprecated
	 * @since 2.6.0
	 *
	 * @return void
	 */
	public function initialize_defaults_admin() {
		$this->_deprecated_function( 'AutoDescription_Siteoptions::' . __FUNCTION__, '2.6.0' );
		return;
	}

}
