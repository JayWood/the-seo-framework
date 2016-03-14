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
 * Class AutoDescription_TermInfo
 *
 * Renders terms and taxonomy states.
 *
 * @since 2.6.0
 */
class AutoDescription_TermInfo extends AutoDescription_PostInfo {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Fetch set Term data.
	 *
	 * @param object|null $term The TT object, if it isn't set, one is fetched.
	 *
	 * @since 2.6.0
	 *
	 * @return array $data The SEO Framework TT data.
	 */
	public function get_term_data( $term = null ) {

		if ( is_null( $term ) ) {
			if ( $this->is_author() ) {
				//* Special handling.
				return $this->get_author_data();
			}

			$term = $this->fetch_the_term();
		}

		if ( $term ) {
			$data = array();

			$data['title'] = isset( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : '';
			$data['description'] = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';
			$data['noindex'] = isset( $term->admeta['noindex'] ) ? $term->admeta['noindex'] : '';
			$data['nofollow'] = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
			$data['noarchive'] = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';
			$flag = isset( $term->admeta['saved_flag'] ) ? (bool) $term->admeta['saved_flag'] : false;

			//* Genesis data fetch. This will override our options with Genesis options on save.
			if ( false === $flag && isset( $term->meta ) ) {
				$data['title'] = empty( $data['title'] ) && isset( $term->meta['doctitle'] ) 				? $term->meta['doctitle'] : $data['noindex'];
				$data['description'] = empty( $data['description'] ) && isset( $term->meta['description'] )	? $term->meta['description'] : $data['description'];
				$data['noindex'] = empty( $data['noindex'] ) && isset( $term->meta['noindex'] ) 			? $term->meta['noindex'] : $data['noindex'];
				$data['nofollow'] = empty( $data['nofollow'] ) && isset( $term->meta['nofollow'] )			? $term->meta['nofollow'] : $data['nofollow'];
				$data['noarchive'] = empty( $data['noarchive'] ) && isset( $term->meta['noarchive'] )		? $term->meta['noarchive'] : $data['noarchive'];
			}

			return $data;
		}

		//* Return null if no term can be set.
		return null;
	}

	public function get_author_data( $id ) {
		//* TODO
		//* Return null if no term can be set.
		return null;
	}

	/**
	 * Try to fetch a term if none can be found.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param int $id The possible taxonomy Term ID.
	 *
	 * @return null|object The Term object.
	 */
	public function fetch_the_term( $id = '' ) {

		static $term = null;

		if ( isset( $term[$id] ) )
			return $term[$id];

		//* Return null if no term can be set.
		if ( false === $this->is_archive() )
			return false;

		if ( $this->is_admin() ) {
			if ( 'term.php' === $this->page_hook ) {
				global $current_screen;

				if ( isset( $current_screen->taxonomy ) ) {
					$term_id = $id ? $id : abs( (int) $_REQUEST['term_id'] );
					$term[$id] = get_term_by( 'id', $term_id, $current_screen->taxonomy );
				}
			}
		} else {
			if ( $this->is_category() || $this->is_tag() ) {
				global $wp_query;

				$term[$id] = $wp_query->get_queried_object();
			} else if ( $this->is_tax() ) {
				$term[$id] = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			}
		}

		if ( isset( $term[$id] ) )
			return $term[$id];

		return $term[$id] = false;
	}

	/**
	 * Get term from args.
	 *
	 * @since 2.6.0
	 *
	 * @staticvar bool|object $cache
	 *
	 * @param array $args : The current args.
	 * @param int $id : Taxonomy Term ID.
	 * @param string $taxonomy : Optional.
	 *
	 * @return array $args with $args['term'] filled in as Object or null.
	 */
	public function get_term_for_args( $args, $id = '', $taxonomy = '' ) {

		static $cache = array();

		if ( isset( $cache[$taxonomy][$id] ) ) {
			$args['term'] = $cache[$taxonomy][$id];
			return $args;
		}

		$term = '';

		if ( $taxonomy && $id ) {
			$term = get_term_by( 'id', (int) $id, $taxonomy, OBJECT );
		} else if ( $this->is_archive() ) {
			$term = $this->fetch_the_term( $id );
		}

		$cache[$taxonomy][$id] = $term;
		$args['term'] = $term;

		return $args;
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
	 * Get the current screen term labels.
	 *
	 * @since 2.6.0
	 *
	 * @staticvar string $term_name : Caution: This function only runs once per screen and doesn't check the term type more than once.
	 *
	 * @param object $term The Taxonomy Term object.
	 * @param bool $singular Whether to fetch a singular or plural name.
	 * @param bool $fallback Whether to fallback on a generic name.
	 *
	 * @return string the Term name.
	 */
	protected function get_the_term_name( $term, $singular = true, $fallback = true ) {

		static $term_name = array();

		if ( isset( $term_name[$singular] ) )
			return $term_name[$singular];

		if ( $term && is_object( $term ) ) {
			$tax_type = $term->taxonomy;

			static $term_labels = null;

			/**
			 * Dynamically fetch the term name.
			 *
			 * @since 2.3.1
			 */
			if ( is_null( $term_labels ) )
				$term_labels = $this->get_tax_labels( $tax_type );

			if ( $singular ) {
				if ( isset( $term_labels->singular_name ) )
					return $term_name[$singular] = $term_labels->singular_name;
			} else {
				if ( isset( $term_labels->name ) )
					return $term_name[$singular] = $term_labels->name;
			}
		}

		if ( $fallback ) {
			//* Fallback to Page as it is generic.
			if ( $singular )
				return $term_name[$singular] = __( 'Page', 'autodescription' );

			return $term_name[$singular] = __( 'Pages', 'autodescription' );
		}

		return $term_name[$singular] = '';
	}

}
