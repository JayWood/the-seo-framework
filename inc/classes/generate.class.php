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
 * Class AutoDescription_Generate
 *
 * Generates general SEO data based on content.
 *
 * @since 2.1.6
 */
class AutoDescription_Generate extends AutoDescription_PostData {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Output the `index`, `follow`, `noodp`, `noydir`, `noarchive` robots meta code in array
	 *
	 * @since 2.2.2
	 *
	 * @uses genesis_get_seo_option()   Get SEO setting value.
	 * @uses genesis_get_custom_field() Get custom field value.
	 *
	 * @global WP_Query $wp_query Query object.
	 *
	 * @return array|null robots
	 */
	public function robots_meta() {
		global $wp_query;

		$query_vars = is_object( $wp_query ) ? (array) $wp_query->query_vars : '';
		$paged = is_array( $query_vars ) ? $query_vars["paged"] : '';

		//* Defaults
		$meta = array(
			'noindex'   => $this->get_option( 'site_noindex' ) ? 'noindex' : '',
			'nofollow'  => $this->get_option( 'site_nofollow' ) ? 'nofollow' : '',
			'noarchive' => $this->get_option( 'site_noarchive' ) ? 'noarchive' : '',
			'noodp'     => $this->get_option( 'noodp' ) ? 'noodp' : '',
			'noydir'    => $this->get_option( 'noydir' ) ? 'noydir' : '',
		);

		/**
		 * Check the Robots SEO settings, set noindex for paged archives.
		 * @since 2.2.4
		 */
		if ( (int) $paged > (int) 1 )
			$meta['noindex'] = $this->get_option( 'paged_noindex' ) ? 'noindex' : $meta['noindex'];

		/**
		 * Check if archive is empty, set noindex for those.
		 *
		 * @todo maybe create option
		 * @since 2.2.8
		 */
		if ( isset( $wp_query->post_count ) && (int) 0 === $wp_query->post_count )
			$meta['noindex'] = 'noindex';

		//* Check home page SEO settings, set noindex, nofollow and noarchive
		if ( is_front_page() ) {
			$meta['noindex']   = $this->get_option( 'homepage_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_option( 'homepage_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'homepage_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_category() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			if ( empty( $meta['noindex'] ) )
				$meta['noindex'] = $this->get_option( 'category_noindex' ) ? 'noindex' : $meta['noindex'];

			if ( empty( $meta['nofollow'] ) )
				$meta['nofollow'] = $this->get_option( 'category_nofollow' ) ? 'nofollow' : $meta['nofollow'];

			if ( empty( $meta['noarchive'] ) )
				$meta['noarchive'] = $this->get_option( 'category_noindex' ) ? 'noarchive' : $meta['noarchive'];

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && isset( $term->meta ) ) {
				if ( empty( $meta['noindex'] ) )
					$meta['noindex'] = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];

				if ( empty( $meta['nofollow'] ) )
					$meta['nofollow'] = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];

				if ( empty( $meta['noarchive'] ) )
					$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];
			}
		}

		if ( is_tag() ) {
			$term = $wp_query->get_queried_object();

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];

			if ( empty( $meta['noindex'] ) )
				$meta['noindex'] = $this->get_option( 'tag_noindex' ) ? 'noindex' : $meta['noindex'];

			if ( empty( $meta['nofollow'] ) )
				$meta['nofollow'] = $this->get_option( 'tag_nofollow' ) ? 'nofollow' : $meta['nofollow'];

			if ( empty( $meta['noarchive'] ) )
				$meta['noarchive'] = $this->get_option( 'tag_noindex' ) ? 'noarchive' : $meta['noarchive'];

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && isset( $term->meta ) ) {
				if ( empty( $meta['noindex'] ) )
					$meta['noindex'] = $term->meta['noindex'] ? 'noindex' : $meta['noindex'];

				if ( empty( $meta['nofollow'] ) )
					$meta['nofollow'] = $term->meta['nofollow'] ? 'nofollow' : $meta['nofollow'];

				if ( empty( $meta['noarchive'] ) )
					$meta['noarchive'] = $term->meta['noarchive'] ? 'noarchive' : $meta['noarchive'];
			}
		}

		// Is custom Taxonomy page. But not a category or tag. Should've recieved specific term SEO settings.
		if ( is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

			$meta['noindex']   = $term->admeta['noindex'] ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $term->admeta['nofollow'] ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $term->admeta['noarchive'] ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_author() ) {

			/**
			 * @todo really, @todo. External plugin?
			 */
			/*
			$meta['noindex']   = get_the_author_meta( 'noindex', (int) get_query_var( 'author' ) ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = get_the_author_meta( 'nofollow', (int) get_query_var( 'author' ) ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = get_the_author_meta( 'noarchive', (int) get_query_var( 'author' ) ) ? 'noarchive' : $meta['noarchive'];
			*/

			$meta['noindex'] = $this->get_option( 'author_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'author_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'author_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_date() ) {
			$meta['noindex'] = $this->get_option( 'date_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'date_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'date_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_search() ) {
			$meta['noindex'] = $this->get_option( 'search_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_option( 'search_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'search_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( is_attachment() ) {
			$meta['noindex']   = $this->get_option( 'attachment_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow']  = $this->get_option( 'attachment_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_option( 'attachment_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		if ( $this->is_singular() ) {
			$meta['noindex'] = $this->get_custom_field( '_genesis_noindex' ) ? 'noindex' : $meta['noindex'];
			$meta['nofollow'] = $this->get_custom_field( '_genesis_nofollow' ) ? 'nofollow' : $meta['nofollow'];
			$meta['noarchive'] = $this->get_custom_field( '_genesis_noarchive' ) ? 'noarchive' : $meta['noarchive'];
		}

		//* Strip empty array items
		$meta = array_filter( $meta );

		return $meta;
	}

	/**
	 * Returns cached and parsed separator option.
	 *
	 * @param string $type The separator type. Used to fetch option.
	 * @param bool $escape Escape the separator.
	 *
	 * @staticvar array $sepcache The separator cache.
	 * @staticvar array $sep_esc The escaped separator cache.
	 *
	 * @since 2.3.9
	 */
	public function get_separator( $type = 'title', $escape = false ) {

		static $sepcache = array();
		static $sep_esc = array();

		if ( isset( $sep_esc[$type][$escape] ) )
			return $sep_esc[$type][$escape];

		if ( ! isset( $sepcache[$type] ) ) {
			if ( 'title' == $type ) {
				$sep_option = $this->get_option( 'title_seperator' ); // Note: typo.
			} else {
				$sep_option = $this->get_option( $type . '_separator' );
			}

			if ( 'pipe' === $sep_option ) {
				$sep = '|';
			} else if ( 'dash' === $sep_option ) {
				$sep = '-';
			} else if ( ! empty( $sep_option ) ) {
				//* Encapsulate within html entities.
				$sep = '&' . $sep_option . ';';
			} else {
				//* Nothing found.
				$sep = '|';
			}

			$sepcache[$type] = $sep;
		}

		if ( $escape ) {
			return $sep_esc[$type][$escape] = esc_html( $sepcache[$type] );
		} else {
			return $sep_esc[$type][$escape] = $sepcache[$type];
		}
	}

	/**
	 * Fetch blogname
	 *
	 * @staticvar string $blogname
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blogname
	 */
	public function get_blogname() {

		$blogname = null;

		if ( isset( $blogname ) )
			return $blogname;

		return $blogname = trim( get_bloginfo( 'name', 'display' ) );
	}

	/**
	 * Fetch blog description.
	 *
	 * @staticvar string $description
	 *
	 * @since 2.5.2
	 * @return string $blogname The trimmed and sanitized blog description.
	 */
	public function get_blogdescription() {

		$description = null;

		if ( isset( $description ) )
			return $description;

		return $description = trim( get_bloginfo( 'description', 'display' ) );
	}

	/**
	 * Matches WordPress locales.
	 * If not matched, it will calculate a locale.
	 *
	 * @param $match the locale to match. Defaults to WordPress locale.
	 *
	 * @since 2.5.2
	 *
	 * @return string Facebook acceptable OG locale.
	 */
	public function fetch_locale( $match = '' ) {

		if ( empty( $match ) )
			$match = get_locale();

		$match_len = strlen( $match );
		$valid_locales = (array) $this->fb_locales();
		$default = 'en_US';

		if ( 5 === $match_len ) {
			//* Full locale is used.

			//* Return the match if found.
			if ( $this->in_array( $match, $valid_locales ) )
				return $match;

			//* Convert to only language portion.
			$match = substr( $match, 0, 2 );
			$match_len = 2;
		}

		if ( 2 === $match_len ) {
			//* Language key is provided.

			$locale_keys = (array) $this->language_keys();

			//* No need to do for each loop. Just match the keys.
			if ( $key = array_search( $match, $locale_keys ) ) {
				//* Fetch the corresponding value from key within the language array.
				return $valid_locales[$key];
			}
		}

		return $default;
	}

}
