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
	 * Render the LD+Json scripts.
	 *
	 * @since 2.6.0
	 *
	 * @return string The LD+Json scripts.
	 */
	public function render_ld_json_scripts() {

		$this->setup_ld_json_transient( $this->get_the_real_ID() );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, array( 'LD Json transient' => $this->ld_json_transient, 'Is output' => (bool) $this->get_transient( $this->ld_json_transient ) ) );

		$output = $this->get_transient( $this->ld_json_transient );
		if ( false === $output ) {

			$output = '';

			//* Only display search helper and knowledge graph on front page.
			if ( $this->is_front_page() ) {

				$sitelinks = $this->ld_json_search();
				$knowledgegraph = $this->ld_json_knowledge();
				$sitename = $this->ld_json_name();

				if ( $sitelinks )
					$output .= $sitelinks;

				if ( $knowledgegraph )
					$output .= $knowledgegraph;

				if ( $sitename )
					$output .= $sitename;
			} else {
				$breadcrumbhelper = $this->ld_json_breadcrumbs();

				//* No wrapper, is done within script generator.
				if ( $breadcrumbhelper )
					$output .= $breadcrumbhelper;
			}

			/**
			 * Transient expiration: 1 week.
			 * Keep the description for at most 1 week.
			 *
			 * 60s * 60m * 24h * 7d
			 */
			$expiration = 60 * 60 * 24 * 7;

			set_transient( $this->ld_json_transient, $output, $expiration );
		}

		/**
		 * Debug output.
		 * @since 2.4.2
		 */
		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, array( 'LD Json transient output' => $output ) );

		return $output;
	}

	/**
	 * Returns http://schema.org json encoded context URL.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 *
	 * @return string The json encoded context url.
	 */
	public function schema_context() {

		static $context;

		if ( isset( $context ) )
			return $context;

		return $context = json_encode( 'http://schema.org' );
	}

	/**
	 * Returns 'WebSite' json encoded type name.
	 *
	 * @staticvar string $context
	 * @since 2.6.0
	 *
	 * @return string The json encoded type name.
	 */
	public function schema_type() {

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( 'WebSite' );
	}

	/**
	 * Returns json encoded home url.
	 *
	 * @staticvar string $url
	 * @since 2.6.0
	 *
	 * @return string The json encoded home url.
	 */
	public function schema_home_url() {

		static $type;

		if ( isset( $type ) )
			return $type;

		return $type = json_encode( $this->the_home_url_from_cache() );
	}

	/**
	 * Returns json encoded blogname.
	 *
	 * @staticvar string $name
	 * @since 2.6.0
	 *
	 * @return string The json encoded blogname.
	 */
	public function schema_blog_name() {

		static $name;

		if ( isset( $name ) )
			return $name;

		return $name = json_encode( $this->get_blogname() );
	}

	/**
	 * Returns 'BreadcrumbList' json encoded type name.
	 *
	 * @staticvar string $crumblist
	 * @since 2.6.0
	 *
	 * @return string The json encoded 'BreadcrumbList'.
	 */
	public function schema_breadcrumblist() {

		static $crumblist;

		if ( isset( $crumblist ) )
			return $crumblist;

		return $crumblist = json_encode( 'BreadcrumbList' );
	}

	/**
	 * Returns 'ListItem' json encoded type name.
	 *
	 * @staticvar string $crumblist
	 * @since 2.6.0
	 *
	 * @return string The json encoded 'ListItem'.
	 */
	public function schema_listitem() {

		static $listitem;

		if ( isset( $listitem ) )
			return $listitem;

		return $listitem = json_encode( 'ListItem' );
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

		if ( false === $output )
			return '';

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();
		$actiontype = json_encode( 'SearchAction' );

		// Remove trailing quote and add it back.
		$target = mb_substr( json_encode( $this->the_home_url_from_cache( true ) . '?s=' ), 0, -1 ) . '{search_term_string}"';

		$queryaction = json_encode( 'required name=search_term_string' );

		$json = sprintf( '{"@context":%s,"@type":%s,"url":%s,"name":%s,"potentialAction":{"@type":%s,"target":%s,"query-input":%s}}', $context, $webtype, $url, $name, $actiontype, $target, $queryaction );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . "</script>" . "\r\n";

		return $output;
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

		if ( false === $output )
			return '';

		//* Used to count ancestors and categories.
		$count = 0;

		$output = '';

		if ( $this->is_single() ) {
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

			$context = $this->schema_context();
			$context_type = $this->schema_breadcrumblist();
			$item_type = $this->schema_listitem();

			$items = '';

			foreach ( $trees as $tree ) {
				if ( $tree ) {

					$tree = array_reverse( $tree );

					foreach ( $tree as $position => $parent_id ) {
						$pos = $position + 2;

						$cat = isset( $cat_obj[$parent_id] ) ? $cat_obj[$parent_id] : get_term_by( 'id', $parent_id, 'category', OBJECT, 'raw' );

						$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'is_term' => true, 'term' => $cat ) ) );

						$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
						$cat_name = $custom_field_name ? $custom_field_name : $cat->name;
						$name = json_encode( $cat_name );

						$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
					}

					if ( $items ) {

						$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

						//* Put it all together.
						$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
						$output .= '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
					}
				}
			}

			//* For each of the todo items, create a separated script.
			if ( $todo ) {
				foreach ( $todo as $tid ) {

					$items = '';
					$cat = get_term_by( 'id', $tid, 'category', OBJECT, 'raw' );

					if ( '1' !== $cat->admeta['noindex'] ) {

						if ( empty( $children ) ) {
							// The position of the current item is always static here.
							$pos = '2';
							$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'is_term' => true, 'term' => $cat ) ) ); // Why not external???

							$custom_field_name = isset( $cat->admeta['doctitle'] ) ? $cat->admeta['doctitle'] : '';
							$cat_name = $custom_field_name ? $custom_field_name : $cat->name;
							$name = json_encode( $cat_name );

							$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
						}

						if ( $items ) {

							$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $post_id );

							//* Put it all together.
							$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
							$output .= '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
						}
					}
				}
			}
		} else if ( false === $this->is_front_page() && $this->is_page() ) {
			//* Get ancestors.
			$page_id = $this->get_the_real_ID();

			$parents = get_post_ancestors( $page_id );

			if ( $parents ) {

				$context = $this->schema_context();
				$context_type = $this->schema_breadcrumblist();
				$item_type = $this->schema_listitem();

				$items = '';

				$parents = array_reverse( $parents );

				foreach ( $parents as $position => $parent_id ) {
					$pos = $position + 2;

					$id = json_encode( $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'id' => $parent_id ) ) );

					$custom_field_name = $this->get_custom_field( '_genesis_title', $parent_id );
					$parent_name = $custom_field_name ? $custom_field_name : $this->title( '', '', '', array( 'term_id' => $parent_id, 'get_custom_field' => false, 'placeholder' => true, 'notagline' => true, 'description_title' => true ) );

					$name = json_encode( $parent_name );

					$items .= sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, (string) $pos, $id, $name );
				}

				if ( $items ) {

					$items = $this->ld_json_breadcrumb_first( $item_type ) . $items . $this->ld_json_breadcrumb_last( $item_type, $pos, $page_id );

					//* Put it all together.
					$breadcrumbhelper = sprintf( '{"@context":%s,"@type":%s,"itemListElement":[%s]}', $context, $context_type, $items );
					$output = '<script type="application/ld+json">' . $breadcrumbhelper . '</script>' . "\r\n";
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
	 * @param string|null $item_type the breadcrumb item type.
	 *
	 * @return string Home Breadcrumb item
	 */
	public function ld_json_breadcrumb_first( $item_type = null ) {

		static $first_item = null;

		if ( isset( $first_item ) )
			return $first_item;

		if ( is_null( $item_type ) )
			$item_type = json_encode( 'ListItem' );

		$id = json_encode( $this->the_home_url_from_cache() );

		$home_title = $this->get_option( 'homepage_title' );

		if ( $home_title ) {
			$custom_name = $home_title;
		} else if ( $this->has_page_on_front() ) {
			$home_id = (int) get_option( 'page_on_front' );

			$custom_name = $this->get_custom_field( '_genesis_title', $home_id );
			$custom_name = $custom_name ? $custom_name : $this->get_blogname();
		} else {
			$custom_name = $this->get_blogname();
		}

		$custom_name = json_encode( $custom_name );

		//* Add trailing comma.
		$first_item = sprintf( '{"@type":%s,"position":%s,"item":{"@id":%s,"name":%s}},', $item_type, '1', $id, $custom_name );

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
	public function ld_json_breadcrumb_last( $item_type = null, $pos = null, $post_id = null ) {

		// 2 (becomes 3) holds mostly true for single term items. This shouldn't run anyway. Pos should always be provided.
		if ( is_null( $pos ) )
			$pos = '2';

		if ( is_null( $item_type ) ) {
			static $type = null;

			if ( ! isset( $type ) )
				$type = json_encode( 'ListItem' );

			$item_type = $type;
		}

		if ( is_null( $post_id ) || empty( $post_id ) )
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
	 * @return string LD+json Knowledge Graph helper.
	 */
	public function ld_json_knowledge() {

		if ( false === $this->is_option_checked( 'knowledge_output' ) )
			return '';

		$knowledge_type = $this->get_option( 'knowledge_type' );

		/**
		 * Forgot to add this.
		 * @since 2.4.3
		 */
		$knowledge_name = $this->get_option( 'knowledge_name' );
		$knowledge_name = $knowledge_name ? $knowledge_name : $this->get_blogname();

		$context = $this->schema_context();
		$type = json_encode( ucfirst( $knowledge_type ) );
		$name = json_encode( $knowledge_name );
		$url = json_encode( esc_url( home_url( '/' ) ) );

		$logo = '';

		if ( $this->get_option( 'knowledge_logo' ) && 'organization' === $knowledge_type ) {
			$icon = $this->site_icon();

			if ( $icon ) {
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

		if ( $sameurls )
			$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s,%s"sameAs":[%s]}', $context, $type, $name, $url, $logo, $sameurls );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

	/**
	 * Generate Site Name LD+Json script.
	 *
	 * @since 2.6.0
	 *
	 * @return string The LD+JSon Site Name script.
	 */
	public function ld_json_name() {

		/**
		 * Applies filters 'the_seo_framework_json_name_output' : bool
		 */
		$output = (bool) apply_filters( 'the_seo_framework_json_name_output', true );

		if ( false === $output )
			return '';

		$context = $this->schema_context();
		$webtype = $this->schema_type();
		$url = $this->schema_home_url();
		$name = $this->schema_blog_name();

		$json = sprintf( '{"@context":%s,"@type":%s,"name":%s,"url":%s}', $context, $webtype, $name, $url );

		$output = '';
		if ( $json )
			$output = '<script type="application/ld+json">' . $json . '</script>' . "\r\n";

		return $output;
	}

}
