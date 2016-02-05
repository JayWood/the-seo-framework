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
 * Class AutoDescription_Core
 *
 * Holds plugin core functions.
 *
 * @since 2.6.0
 */
class AutoDescription_Core {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the real page ID, also depending on CPT.
	 *
	 * @param bool $use_cache Wether to use the cache or not.
	 *
	 * @staticvar int $id the ID.
	 *
	 * @since 2.5.0
	 *
	 * @return int|false The ID.
	 */
	public function get_the_real_ID( $use_cache = true ) {

		$is_admin = is_admin();

		//* Never use cache in admin. Only causes bugs.
		$use_cache = $is_admin ? false : $use_cache;

		if ( $use_cache ) {
			static $id = null;

			if ( isset( $id ) )
				return $id;
		}

		if ( ! $is_admin )
			$id = $this->check_the_real_ID();

		if ( ! isset( $id ) || empty( $id ) ) {
			//* Does not always return false.
			$id = get_queried_object_id();

			if ( empty( $id ) )
				$id = get_the_ID();
		}

		//* Turn ID into false if empty.
		$id = ! empty( $id ) ? $id : false;

		return $id;
	}

	/**
	 * Get the real ID from plugins.
	 *
	 * Only works in front-end as there's no need to check for inconsistent
	 * functions for the current ID in the admin.
	 *
	 * @since 2.5.0
	 *
	 * Applies filters the_seo_framework_real_id : The Real ID for plugins on front-end.
	 *
	 * @staticvar int $cached_id The cached ID.
	 *
	 * @return int|empty the ID.
	 */
	public function check_the_real_ID() {

		static $cached_id = null;

		if ( isset( $cached_id ) )
			return $cached_id;

		$id = '';

		if ( $this->is_wc_shop() ) {
			//* WooCommerce Shop
			$id = get_option( 'woocommerce_shop_page_id' );
		} else if ( function_exists( 'is_anspress' ) && is_anspress() ) {
			//* Get AnsPress Question ID.
			if ( function_exists( 'get_question_id' ) )
				$id = get_question_id();
		}

		$cached_id = (int) apply_filters( 'the_seo_framework_real_id', $id );

		return $cached_id;
	}

	/**
	 * Adds post type support
	 *
	 * Applies filters the_seo_framework_supported_post_types : The supported post types.
	 * @since 2.3.1
	 *
	 * @param array $args
	 *
	 * @since 2.1.6
	 */
	public function post_type_support( $args = array() ) {

		/**
		 * Added product post type.
		 *
		 * @since 2.3.1
		 */
		$defaults = array(
			'post', 'page',
			'product',
			'forum', 'topic',
			'jetpack-testimonial', 'jetpack-portfolio'
		);

		$post_types = (array) apply_filters( 'the_seo_framework_supported_post_types', $defaults, $args );

		$post_types = wp_parse_args( $args, $post_types );

		foreach ( $post_types as $type )
			add_post_type_support( $type, array( 'autodescription-meta' ) );

	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes all HTML, so `<` gets changed to `&lt;` and displays correctly.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap( $content ) {
		return '<code>' . esc_html( $content ) . '</code>';
	}

	/**
	 * Mark up content with code tags.
	 *
	 * Escapes no HTML.
	 *
	 * @since 2.2.2
	 *
	 * @param string $content Content to be wrapped in code tags.
	 *
	 * @return string Content wrapped in code tags.
	 */
	public function code_wrap_noesc( $content ) {
		return '<code>' . $content . '</code>';
	}

	/**
	 * Return custom field post meta data.
	 *
	 * Return only the first value of custom field. Return false if field is
	 * blank or not set.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field	Custom field key.
	 * @param int $post_id	The post ID
	 *
	 * @return string|boolean Return value or false on failure.
	 *
	 * @thanks StudioPress (http://www.studiopress.com/) for some code.
	 *
	 * @staticvar array $field_cache
	 * @since 2.2.5
	 */
	public function get_custom_field( $field, $post_id = null ) {

		//* No field has been provided.
		if ( empty( $field ) )
			return false;

		//* Setup cache.
		static $field_cache = array();

		//* Check field cache.
		if ( isset( $field_cache[$field][$post_id] ) )
			//* Field has been cached.
			return $field_cache[$field][$post_id];

		if ( null === $post_id || empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( null === $post_id || empty( $post_id ) )
			return '';

		$custom_field = get_post_meta( $post_id, $field, true );

		// If custom field is empty, return null.
		if ( ! $custom_field )
			$field_cache[$field][$post_id] = '';

		//* Render custom field, slashes stripped, sanitized if string
		$field_cache[$field][$post_id] = is_array( $custom_field ) ? stripslashes_deep( $custom_field ) : stripslashes( wp_kses_decode_entities( $custom_field ) );

		return $field_cache[$field][$post_id];
	}

	/**
	 * Google docs language determinator.
	 *
	 * @since 2.2.2
	 *
	 * @staticvar string $language
	 *
	 * @return string language code
	 */
	protected function google_language() {

		/**
		 * Cache value
		 * @since 2.2.4
		 */
		static $language = null;

		if ( isset( $language ) )
			return $language;

		//* Language shorttag to be used in Google help pages,
		$language = _x( 'en', 'e.g. en for English, nl for Dutch, fi for Finish, de for German', 'autodescription' );

		return $language;
	}

	/**
	 * Fetch Tax labels
	 *
	 * @param string $tax_type the Taxonomy type.
	 *
	 * @since 2.3.1
	 *
	 * @staticvar object $labels
	 *
	 * @return object|null with all the labels as member variables
	 */
	public function get_tax_labels( $tax_type ) {

		static $labels = null;

		if ( isset( $labels ) )
			return $labels;

		$tax_object = get_taxonomy( $tax_type );

		if ( is_object( $tax_object ) )
			return $labels = (object) $tax_object->labels;

		//* Nothing found.
		return null;
	}

	/**
	 * Wether to allow external redirect through the 301 redirect option.
	 *
	 * Applies filters the_seo_framework_allow_external_redirect : bool
	 * @staticvar bool $allowed
	 *
	 * @since 2.6.0
	 *
	 * @return bool Wether external redirect is allowed.
	 */
	public function allow_external_redirect() {

		static $allowed = null;

		if ( isset( $allowed ) )
			return $allowed;

		return $allowed = (bool) apply_filters( 'the_seo_framework_allow_external_redirect', true );
	}

	/**
	 * Object cache set wrapper.
	 * Applies filters 'the_seo_framework_use_object_cache' : Disable object
	 * caching for this plugin, when applicable.
	 *
	 * @param string $key The Object cache key.
	 * @param mixed $data The Object cache data.
	 * @param int $expire The Object cache expire time.
	 * @param string $group The Object cache group.
	 *
	 * @since 2.4.3
	 *
	 * @return bool true on set, false when disabled.
	 */
	public function object_cache_set( $key, $data, $expire = 0, $group = 'the_seo_framework' ) {

		if ( $this->use_object_cache )
			return wp_cache_set( $key, $data, $group, $expire );

		return false;
	}

	/**
	 * Object cache get wrapper.
	 * Applies filters 'the_seo_framework_use_object_cache' : Disable object
	 * caching for this plugin, when applicable.
	 *
	 * @param string $key The Object cache key.
	 * @param string $group The Object cache group.
	 * @param bool $force Wether to force an update of the local cache.
	 * @param bool $found Wether the key was found in the cache. Disambiguates a return of false, a storable value.
	 *
	 * @since 2.4.3
	 *
	 * @return mixed wp_cache_get if object caching is allowed. False otherwise.
	 */
	public function object_cache_get( $key, $group = 'the_seo_framework', $force = false, &$found = null ) {

		if ( $this->use_object_cache )
			return wp_cache_get( $key, $group, $force, $found );

		return false;
	}

}
