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
 * Generates SEO data based on content
 * Returns strings/arrays
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Ldjson extends AutoDescription_Generate_Image {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Generate LD+Json search helper.
	 *
	 * @since 2.2.8
	 *
	 * @return escaped LD+json search helper string.
	 * @TODO Create option for output.
	 */
	public function ld_json_search() {

		/**
		 * Applies filters the_seo_framework_json_search_output
		 * @since 2.3.9
		 */
		$output = (bool) apply_filters( 'the_seo_framework_json_search_output', true );

		if ( true !== $output )
			return '';

		$context = json_encode( 'http://schema.org' );
		$webtype = json_encode( 'WebSite' );
		$url = json_encode( esc_url( home_url( '/' ) ) );
		$name = json_encode( $this->get_blogname() );
		$alternatename = $name;
		$actiontype = json_encode( 'SearchAction' );

		// Remove trailing quote and add it back.
		$target = mb_substr( json_encode( esc_url( home_url( '/?s=' ) ) ), 0, -1 ) . '{search_term_string}"';

		$queryaction = json_encode( 'required name=search_term_string' );

		$json = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"alternateName":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $alternatename, $actiontype, $target, $queryaction );

		return $json;
	}

	/**
	 * Generate LD+Json breadcrumb helper.
	 *
	 * @since 2.4.2
	 *
	 * @return escaped LD+json search helper string.
	 * @TODO Create option for output.
	 */
	public function ld_json_breadcrumbs() {

		/**
		 * Applies filters the_seo_framework_json_breadcrumb_output
		 * @since 2.4.2
		 */
		$output = (bool) apply_filters( 'the_seo_framework_json_breadcrumb_output', true );

		if ( true !== $output )
			return '';

		//* Used to count ancestors and categories.
		$count = 0;

		$output = '';

		if ( is_single() ) {
			//* Get categories.

			$post_id = $this->get_the_real_ID();

			$r = is_object_in_term( $post_id, 'category', '' );

			if ( is_wp_error( $r ) || ! $r )
				return '';

			$cats = wp_get_object_terms( $post_id, 'category', array( 'fields' => 'all_with_object_id', 'orderby' => 'parent' ) );

			if ( is_wp_error( $cats ) || empty( $cats ) )
				return '';

			$cat_ids = array();
			$kittens = array();

			//* Fetch cats children id's, if any.
			foreach ( $cats as $cat ) {
				//* The category objects. The cats.
				$cat_id = $cat->term_id;

				// Check if they have kittens.
				$children = get_term_children( $cat_id, $cat->taxonomy );

				//* No need to fetch them again, save object in the array.
				$cat_obj[$cat_id] = $cat;

				//* Save children id's as kittens.
				$kittens[$cat_id] = $children;
			}

			$todo = array();
			$trees = array();

			/**
			 * Build category ID tree.
			 * Sort by parents with children ($trees). These are recursive, 3+ item scripts.
			 * Sort by parents without children ($todo). These are singular 2 item scripts.
			 */
			foreach ( $kittens as $parent => $kitten ) {
				if ( empty( $kitten ) ) {
					$todo[] = $parent;
				} else {
					if ( 1 === count( $kitten ) ) {
						$trees[] = array( $kitten[0], $parent );
					} else {
						//* @TODO, this is very, very complicated. Requires multiple loops.
						$trees[] = array();
					}
				}
			}

			//* Remove Duplicates from $todo by comparing to $tree
			foreach ( $todo as $key => $value ) {
				foreach ( $trees as $tree ) {
					if ( $this->in_array( $value, $tree ) )
						unset( $todo[$key] );
				}
			}

			$context = json_encode( 'http://schema.org' );
			$context_type = json_encode( 'BreadcrumbList' );
			$item_type = json_encode( 'ListItem' );

			$items = '';

			foreach ( $trees as $tree ) {
				if ( ! empty( $tree ) ) {

					$tree = array_reverse( $tree );

					foreach ( $tree as $position => $parent_id ) {
						$pos = $position + 2;

						$cat = isset( $cat_obj[$parent_id] ) ? $cat_obj[$parent_id] : get_term_by( 'id', $parent_id, 'category', OBJECT, 'raw' );

						$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'is_term' => true, 'term' => $cat ) ) );

						$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
						$cat_name = ! empty( $custom_field_name ) ? $custom_field_name : $cat->name;
						$name = json_encode( $cat_name );

						$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
					}

					if ( ! empty( $items ) ) {

						$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

						//* Put it all together.
						$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
						$output .= "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
					}
				}
			}

			//* For each of the todo items, create a separated script.
			if ( ! empty( $todo ) ) {
				foreach ( $todo as $tid ) {

					$items = '';
					$cat = get_term_by( 'id', $tid, 'category', OBJECT, 'raw' );

					if ( '1' !== $cat->admeta['noindex'] ) {

						if ( empty( $children ) ) {
							// The position of the current item is always static here.
							$pos = '2';
							$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'is_term' => true, 'term' => $cat ) ) ); // Why not external???

							$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
							$cat_name = ! empty( $custom_field_name ) ? $custom_field_name : $cat->name;
							$name = json_encode( $cat_name );

							$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
						}

						if ( ! empty( $items ) ) {

							$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

							//* Put it all together.
							$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
							$output .= "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
						}
					}
				}
			}
		} else if ( ! is_front_page() && is_page() ) {
			//* Get ancestors.
			$page_id = $this->get_the_real_ID();

			$parents = get_post_ancestors( $page_id );

			if ( ! empty( $parents ) ) {

				$context = json_encode( 'http://schema.org' );
				$context_type = json_encode( 'BreadcrumbList' );
				$item_type = json_encode( 'ListItem' );

				$items = '';

				$parents = array_reverse( $parents );

				foreach ( $parents as $position => $parent_id ) {
					$pos = $position + 2;

					$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'id' => $parent_id ) ) );

					$custom_field_name = $this->get_custom_field( '_genesis_title', $parent_id );
					$parent_name = ! empty( $custom_field_name ) ? $custom_field_name : $this->title( '', '', '', array( 'term_id' => $parent_id, 'get_custom_field' => false, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );

					$name = json_encode( $parent_name );

					$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
				}

				if ( ! empty( $items ) ) {

					$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $page_id );

					//* Put it all together.
					$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
					$output = "<script type='application/ld+json'>" . $breadcrumbhelper . "</script>" . "\r\n";
				}
			}
		}

		return $output;
	}

	/**
	 * Return home page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $first_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string $item_type the breadcrumb item type.
	 *
	 * @return string Home Breadcrumb item
	 */
	public function ld_json_breadcrumb_first( $item_type ) {

		static $first_item = null;

		if ( ! isset( $first_item ) ) {

			if ( ! isset( $item_type ) )
				$item_type = json_encode( 'ListItem' );

			$id = json_encode( $this->the_home_url_from_cache() );

			$home_title = $this->get_option( 'homepage_title' );

			if ( $home_title ) {
				$custom_name = $home_title;
			} else if ( 'page' === get_option( 'show_on_front' ) ) {
				$home_id = (int) get_option( 'page_on_front' );

				$custom_name = $this->get_custom_field( '_genesis_title', $home_id );
				$custom_name = $custom_name ? $custom_name : $this->get_blogname();
			} else {
				$custom_name = $this->get_blogname();
			}

			$custom_name = json_encode( $custom_name );

			//* Add trailing comma.
			$first_item = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, '1', $id, $custom_name );
		}

		return $first_item;
	}

	/**
	 * Return current page item for LD Json Breadcrumbs.
	 *
	 * @staticvar string $last_item.
	 *
	 * @since 2.4.2
	 *
	 * @param string $item_type the breadcrumb item type.
	 * @param int $pos Last known position.
	 * @param int $post_id The current Post ID
	 *
	 * @staticvar string $type The breadcrumb item type.
	 * @staticvar string $id The current post/page/archive url.
	 * @staticvar string $name The current post/page/archive title.
	 *
	 * @return string Lat Breadcrumb item
	 */
	public function ld_json_breadcrumb_last( $item_type, $pos, $post_id ) {

		// 2 (becomes 3) holds mostly true for single term items. This shouldn't run anyway. Pos should always be provided.
		if ( ! isset( $pos ) )
			$pos = '2';

		if ( ! isset( $item_type ) ) {
			static $type = null;

			if ( ! isset( $type ) )
				$type = json_encode( 'ListItem' );

			$item_type = $type;
		}

		if ( ! isset( $post_id ) || empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Add current page.
		$pos = $pos + 1;

		static $id = null;
		static $name = null;

		if ( ! isset( $id ) )
			$id = json_encode( $this->the_url_from_cache() );

		if ( ! isset( $name ) ) {
			$custom_field = $this->get_custom_field( '_genesis_title', $post_id );
			$name = $custom_field ? $custom_field : $this->title( '', '', '', array( 'term_id' => $post_id, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );
			$name = json_encode( $name );
		}

		$last_item = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}}', $item_type, (string) $pos, $id, $name );

		return $last_item;
	}

	/**
	 * Return LD+Json Knowledge Graph helper.
	 *
	 * @since 2.2.8
	 *
	 * @return null|escaped LD+json Knowledge Graph helper string.
	 * @todo transient cache this.
	 */
	public function ld_json_knowledge() {

		if ( ! $this->get_option( 'knowledge_output' ) )
			return '';

		$knowledge_type = $this->get_option( 'knowledge_type' );

		/**
		 * Forgot to add this.
		 * @since 2.4.3
		 */
		$knowledge_name = $this->get_option( 'knowledge_name' );
		$knowledge_name = ! empty( $knowledge_name ) ? $knowledge_name : $this->get_blogname();

		$context = json_encode( 'http://schema.org' );
		$type = json_encode( ucfirst( $knowledge_type ) );
		$name = json_encode( $knowledge_name );
		$url = json_encode( esc_url( home_url( '/' ) ) );

		$logo = '';

		if ( $this->get_option( 'knowledge_logo' ) && 'organization' === $knowledge_type ) {
			$icon = $this->site_icon();

			if ( ! empty( $icon ) ) {
				$logourl = esc_url_raw( $icon );

				//* Add trailing comma
				$logo = '"logo":' . json_encode( $logourl ) . ',';
			}
		}

		/**
		 * Fetch option names
		 *
		 * @uses filter the_seo_framework_json_options
		 */
		$options = (array) apply_filters( 'the_seo_framework_json_options', array(
			'knowledge_facebook',
			'knowledge_twitter',
			'knowledge_gplus',
			'knowledge_instagram',
			'knowledge_youtube',
			'knowledge_linkedin',
			'knowledge_pinterest',
			'knowledge_soundcloud',
			'knowledge_tumblr',
		) );

		$sameurls = '';
		$comma = ',';

		//* Put the urls together from the options.
		if ( is_array( $options ) ) {
			foreach ( $options as $option ) {
				$the_option = $this->get_option( $option );

				if ( '' !== $the_option )
					$sameurls .= json_encode( $the_option ) . $comma;
			}
		}

		//* Remove trailing comma
		$sameurls = rtrim( $sameurls, $comma );
		$json = '';

		if ( ! empty( $sameurls ) )
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s,%s"sameAs":[%s]}', $context, $type, $name, $url, $logo, $sameurls );

		return $json;
	}

}
