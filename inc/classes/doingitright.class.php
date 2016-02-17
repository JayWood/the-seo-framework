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
 * Class AutoDescription_DoingItRight
 *
 * Adds data in a column to edit.php and edit-tags.php
 * Shows you if you're doing the SEO right.
 *
 * @since 2.1.9
 */
class AutoDescription_DoingItRight extends AutoDescription_Search {

	/**
	 * Constructor, load parent constructor
	 *
	 * Initalizes columns
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'current_screen', array( $this, 'init_columns' ) );
	}

	/**
	 * Initializes columns
	 *
	 * Applies filter the_seo_framework_show_seo_column : Show the SEO column in edit.php
	 *
	 * @param array $support_admin_pages the supported admin pages
	 *
	 * @since 2.1.9
	 */
	public function init_columns() {

		$show_seo_column = (bool) apply_filters( 'the_seo_framework_show_seo_column', true );

		if ( $show_seo_column && $this->post_type_supports_custom_seo() ) {
			global $current_screen;

			$id = isset( $current_screen->id ) ? $current_screen->id : '';

			if ( '' !== $id ) {

				$type = $id;
				$slug = substr( $id, (int) 5 );

				if ( 'post' !== $type && 'page' !== $type ) {
					add_action( "manage_{$type}_columns", array( $this, 'add_column' ), 10, 1 );
					add_action( "manage_{$slug}_custom_column", array( $this, 'seo_column' ), 10, 3 );
				}

				/**
				 * Always load pages and posts.
				 * Many CPT plugins rely on these.
				 */
				add_action( 'manage_posts_columns', array( $this, 'add_column' ), 10, 1 );
				add_action( 'manage_pages_columns', array( $this, 'add_column' ), 10, 1 );
				add_action( 'manage_posts_custom_column', array( $this, 'seo_column' ), 10, 3 );
				add_action( 'manage_pages_custom_column', array( $this, 'seo_column' ), 10, 3 );
			}

		}

	}

	/**
	 * Adds SEO column on edit.php
	 *
	 * @param array $columns The existing columns
	 *
	 * @param $offset 	Determines where the column should be placed. Prefered before comments, then data, then tags.
	 *					If neither found, it will add the column to the end.
	 *
	 * @since 2.1.9
	 * @return array $columns the column data
	 */
	public function add_column( $columns ) {

		$seocolumn = array( 'ad_seo' => 'SEO' );

		$column_keys = array_keys( $columns );

		//* Column keys to look for, in order of appearance.
		$order_keys = array(
			'comments',
			'posts',
			'date',
			'tags',
			'bbp_topic_freshness',
			'bbp_forum_freshness',
		);

		foreach ( $order_keys as $key ) {
			//* Put value in $offset, if not false, break loop.
			if ( false !== ( $offset = array_search( $key, $column_keys ) ) )
				break;
		}

		// I tried but found nothing
		if ( false === $offset ) {
			//* Add SEO bar at the end of columns.
			$columns = array_merge( $columns, $seocolumn );
		} else {
			//* Add seo bar between columns.

			// Cache columns.
			$columns_before = $columns;

			$columns = array_merge(
				array_splice( $columns, 0, $offset ),
				$seocolumn,
				array_splice( $columns_before, $offset )
			);
		}

		return $columns;
	}

	/**
	 * Adds SEO column to two to the left.
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
	 */
	public function seo_column( $column, $post_id, $tax_id = '' ) {

		$status = '';

		static $type_cache = null;
		static $column_cache = null;

		if ( ! isset( $type_cache ) || ! isset( $column_cache ) ) {
			$type = get_post_type( $post_id );

			if ( false === $type || '' !== $tax_id ) {
				$screen = (object) get_current_screen();

				if ( isset( $screen->taxonomy ) )
					$type = $screen->taxonomy;
			}

			$type_cache = $type;
			$column_cache = $column;
		}

		/**
		 * Params are shifted.
		 * @link https://core.trac.wordpress.org/ticket/33521
		 */
		if ( '' !== $tax_id ) {
			$column = $post_id;
			$post_id = $tax_id;
		}

		if ( 'ad_seo' === $column )
			$status = $this->post_status( $post_id, $type_cache, true );

		echo $status;
	}

	/**
	 * Renders post status. Caches the output.
	 *
	 * Applies filter the_seo_framework_seo_bar_squared : Make the SEO Bar squared.
	 *
	 * @param int $post_id The Post ID or taxonomy ID
	 * @param string $type Is fetched on edit.php, inpost, taxonomies, etc.
	 * @param bool $html return the status in html or string
	 *
	 * @staticvar string $post_i18n The post type slug.
	 * @staticvar bool $is_term If we're dealing with TT pages.
	 *
	 * @since 2.1.9
	 * @return string $content the post SEO status
	 */
	public function post_status( $post_id = '', $type = 'inpost', $html = true ) {

		$content = '';

		//* Fetch Post ID if it hasn't been provided.
		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		$run = isset( $post_id ) && false !== $post_id ? true : false;

		//* Only run when post ID is found.
		if ( $run ) {

			//* Fetch Post Type.
			if ( 'inpost' === $type || '' === $type )
				$type = get_post_type( $post_id );

			//* No need to re-evalute these.
			static $post_i18n = null;
			static $is_term = null;

			$term = false;
			/**
			 * Static caching.
			 * @since 2.3.8
			 */
			if ( ! isset( $post_i18n ) && ! isset( $is_term ) ) {

				//* Setup i18n values for posts and pages.
				if ( 'post' === $type ) {
					$post_i18n = __( 'Post', 'autodescription' );
					$is_term = false;
					$term = false;
				} else if ( 'page' === $type ) {
					$post_i18n = __( 'Page', 'autodescription' );
					$is_term = false;
					$term = false;
				} else {
					/**
					 * Because of static caching, $is_term was never assigned.
					 * @since 2.4.1
					 */
					$is_term = true;
				}
			}

			if ( $is_term ) {
				//* We're on a term or taxonomy. Try fetching names. Default back to "Page".
				$term = get_term_by( 'id', $post_id, $type, OBJECT );
				$post_i18n = $this->get_the_seo_bar_term_name( $term );

				/**
				 * Check if current post type is a page or taxonomy.
				 * Only check if is_term is not yet changed to false. To save processing power.
				 *
				 * @since 2.3.1
				 */
				if ( $is_term && $this->is_post_type_page( $type ) )
					$is_term = false;
			}

			$post_low = $this->maybe_lowercase_noun( $post_i18n );

			$args = array(
				'is_term' => $is_term,
				'term' => $term,
				'post_id' => $post_id,
				'post_i18n' => $post_i18n,
				'post_low' => $post_low,
				'type' => $type,
			);

			if ( $is_term ) {
				return $this->the_seo_bar_term( $args );
			} else {
				return $this->the_seo_bar_page( $args );
			}
		} else {
			return '<span>' . __( 'Failed to fetch post ID.', 'autodescription' ) . '</span>';
		}
	}

	/**
	 * Output the SEO bar for Terms and Taxonomies.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args {
	 *	 'is_term' => bool $is_term,
	 *	 'term' => object $term,
	 *	 'post_id' => int $post_id,
	 *	 'post_i18n' => string $post_i18n,
	 *	 'post_low' => string $post_low,
	 *	 'type' => string $type,
	 * }
	 *
	 * @return string $content The SEO bar.
	 */
	protected function the_seo_bar_term( $args ) {

		$post_id = $args['post_id'];
		$term = $args['term'];
		$post = $args['post_i18n'];
		$is_term = true;

		$noindex = isset( $term->admeta['noindex'] ) && '0' !== $term->admeta['noindex'] ? true : false;
		$redirect = false; // We don't apply redirect on taxonomies (yet)

		$ad_savedflag = isset( $term->admeta['saved_flag'] ) && '0' !== $term->admeta['saved_flag'] ? true : false;
		$flag = $ad_savedflag;

		//* Genesis data fetch
		if ( false === $noindex && false === $flag && isset( $term->meta['noindex'] ) )
			$noindex = '' !== $term->meta['noindex'] ? true : false;

		if ( $redirect || $noindex )
			return $this->the_seo_bar_blocked( array( 'is_term' => $is_term, 'redirect' => $redirect, 'noindex' => $noindex, 'post_i18n' => $post ) );

		$classes = $this->get_the_seo_bar_classes();
		$square = $this->square_the_seo_bar() ? ' ' . $classes['square'] : '';
		$ad_100 = $classes['100%'];

		$title_notice		= $this->the_seo_bar_title_notice( $args );
		$description_notice	= $this->the_seo_bar_description_notice( $args );
		$index_notice 		= $this->the_seo_bar_index_notice( $args );
		$follow_notice		= $this->the_seo_bar_follow_notice( $args );
		$archive_notice		= $this->the_seo_bar_archive_notice( $args );

		$content = sprintf( '<span class="ad-seo clearfix ' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s %s %s %s %s</span></span>', $title_notice, $description_notice, $index_notice, $follow_notice, $archive_notice );

		return $content;
	}

	/**
	 * Output the SEO bar for Terms and Taxonomies.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args {
	 *	 'is_term' => $is_term,
	 *	 'term' => $term,
	 *	 'post_id' => $post_id,
	 *	 'post_i18n' => $post_i18n,
	 *	 'post_low' => $post_low,
	 *	 'type' => $type,
	 * }
	 *
	 * @return string $content The SEO bar.
	 */
	protected function the_seo_bar_page( $args ) {

		$post_id = $args['post_id'];
		$post = $args['post_i18n'];
		$is_term = false;
		$is_front_page = $this->is_static_frontpage( $post_id );

		$redirect = $this->get_custom_field( 'redirect' );
		$redirect = empty( $redirect ) ? false : true;

		$noindex = $this->get_custom_field( '_genesis_noindex' );
		$noindex = $this->is_checked( $noindex );

		if ( $is_front_page )
			$noindex = $this->is_option_checked( 'homepage_noindex' ) ? true : $noindex;

		if ( $redirect || $noindex )
			return $this->the_seo_bar_blocked( array( 'is_term' => $is_term, 'redirect' => $redirect, 'noindex' => $noindex, 'post_i18n' => $post ) );

		$classes = $this->get_the_seo_bar_classes();
		$square = $this->square_the_seo_bar() ? ' ' . $classes['square'] : '';

		$title_notice		= $this->the_seo_bar_title_notice( $args );
		$description_notice	= $this->the_seo_bar_description_notice( $args );
		$index_notice 		= $this->the_seo_bar_index_notice( $args );
		$follow_notice		= $this->the_seo_bar_follow_notice( $args );
		$archive_notice		= $this->the_seo_bar_archive_notice( $args );
		$redirect_notice	= $this->the_seo_bar_redirect_notice( $args );

		$content = sprintf( '<span class="ad-seo clearfix ' . $square . '"><span class="ad-bar-wrap">%s %s %s %s %s %s</span></span>', $title_notice, $description_notice, $index_notice, $follow_notice, $archive_notice, $redirect_notice );

		return $content;
	}

	/**
	 * Fetch the post or term data for The SEO Bar, structured and cached.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The term/post args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_data( $args ) {

		$post_id = $args['post_id'];

		static $data = array();

		if ( isset( $data[$post_id] ) )
			return $data[$post_id];

		if ( $args['is_term'] ) {
			return $data[$post_id] = $this->the_seo_bar_term_data( $args );
		} else {
			return $data[$post_id] = $this->the_seo_bar_post_data( $args );
		}
	}

	/**
	 * Fetch the term data for The SEO Bar.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The term args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_term_data( $args ) {

		$term = $args['term'];
		$post_id = $args['post_id'];
		$taxonomy = $args['type'];

		$flag = isset( $term->admeta['saved_flag'] ) && '1' === $term->admeta['saved_flag'] ? true : false;

		$title_custom_field = isset( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : '';
		$description_custom_field = isset( $term->admeta['description'] ) ? $term->admeta['description'] : '';
		$nofollow = isset( $term->admeta['nofollow'] ) ? $term->admeta['nofollow'] : '';
		$noarchive = isset( $term->admeta['noarchive'] ) ? $term->admeta['noarchive'] : '';

		//* Genesis data fetch
		if ( false === $flag && isset( $term->meta ) ) {
			if ( empty( $title_custom_field ) && isset( $term->meta['doctitle'] ) )
				$title_custom_field = $term->meta['doctitle'];

			if ( empty( $description_custom_field ) && isset( $term->meta['description'] ) )
				$description_custom_field = $term->meta['description'];

			if ( empty( $nofollow ) && isset( $term->meta['nofollow'] ) )
				$nofollow = $term->meta['nofollow'];

			if ( empty( $noarchive ) && isset( $term->meta['noarchive'] ) )
				$noarchive = $term->meta['noarchive'];
		}

		$title_is_from_custom_field = (bool) $title_custom_field;
		if ( $title_is_from_custom_field ) {
			$title = $title_custom_field;
		} else {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'taxonomy' => $taxonomy, 'meta' => true ) );
		}

		$description_is_from_custom_field = (bool) $description_custom_field;
		if ( $description_is_from_custom_field ) {
			$description = $description_custom_field;
		} else {
			$taxonomy = isset( $term->taxonomy ) && $term->taxonomy ? $term->taxonomy : false;
			$description_args = $taxonomy ? array( 'id' => $post_id, 'taxonomy' => $term->taxonomy, 'get_custom_field' => false ) : array( 'get_custom_field' => false );

			$description = $this->generate_description( '', $description_args );
		}

		$nofollow = $this->is_checked( $nofollow ) ? false : true;
		$noarchive = $this->is_checked( $noarchive ) ? false : true;

		return array(
			'title' => $title,
			'title_is_from_custom_field' => $title_is_from_custom_field,
			'description' => $description,
			'description_is_from_custom_field' => $description_is_from_custom_field,
			'nofollow' => $nofollow,
			'noarchive' => $noarchive
		);
	}

	/**
	 * Fetch the post data for The SEO Bar.
	 *
	 * @staticvar array $data
	 * @since 2.6.0
	 *
	 * @param array $args The post args.
	 *
	 * @return array $data {
	 *	 'title' => $title,
	 *	 'title_is_from_custom_field' => $title_is_from_custom_field,
	 *	 'description' => $description,
	 *	 'description_is_from_custom_field' => $description_is_from_custom_field,
	 *	 'nofollow' => $nofollow,
	 *	 'noarchive' => $noarchive
	 * }
	 */
	protected function the_seo_bar_post_data( $args ) {

		$post_id = $args['post_id'];
		$page_on_front = $this->is_static_frontpage( $post_id );

		$title_custom_field = $this->get_custom_field( '_genesis_title', $post_id );
		$description_custom_field = $this->get_custom_field( '_genesis_description', $post_id );
		$nofollow = $this->get_custom_field( '_genesis_nofollow', $post_id );
		$noarchive = $this->get_custom_field( '_genesis_noarchive', $post_id );

		if ( $page_on_front ) {
			$title_custom_field = $this->get_option( 'homepage_title' ) ? $this->get_option( 'homepage_title' ) : $title_custom_field;
			$description_custom_field = $this->get_option( 'homepage_description' ) ? $this->get_option( 'homepage_description' ) : $description_custom_field;
			$nofollow = $this->get_option( 'homepage_nofollow' ) ? $this->get_option( 'homepage_nofollow' ) : $nofollow;
			$noarchive = $this->get_option( 'homepage_noarchive' ) ? $this->get_option( 'homepage_noarchive' ) : $noarchive;
		}

		$title_is_from_custom_field = (bool) $title_custom_field;
		if ( $title_is_from_custom_field ) {
			$title = $title_custom_field;
		} else {
			$title = $this->title( '', '', '', array( 'term_id' => $post_id, 'page_on_front' => $page_on_front, 'meta' => true ) );
		}

		$description_is_from_custom_field = (bool) $description_custom_field;
		if ( $description_is_from_custom_field ) {
			$description = $description_custom_field;
		} else {
			$description_args = array( 'id' => $post_id, 'get_custom_field' => false );
			$description = $this->generate_description( '', $description_args );
		}

		$nofollow = $this->is_checked( $nofollow ) ? false : true;
		$noarchive = $this->is_checked( $noarchive ) ? false : true;

		return array(
			'title' => $title,
			'title_is_from_custom_field' => $title_is_from_custom_field,
			'description' => $description,
			'description_is_from_custom_field' => $description_is_from_custom_field,
			'nofollow' => $nofollow,
			'noarchive' => $noarchive,
		);
	}

	/**
	 * Render the SEO bar title block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Title Block
	 */
	protected function the_seo_bar_title_notice( $args ) {

		$i18n = $this->get_the_seo_bar_i18n();
		$data = $this->the_seo_bar_data( $args );

		$title = $data['title'];
		$title_is_from_custom_field = $data['title_is_from_custom_field'];

		$generated_notice = '<br>' . $i18n['generated'];
		$generated = ' ' . $i18n['generated_short'];
		$gen_t = $title_is_from_custom_field ? '' : $generated;
		$gen_t_notice = $title_is_from_custom_field ? '' : $generated_notice;

		$title_i18n = $i18n['title'];
		$title_short = $i18n['title_short'];

		//* Convert to what Google outputs. This will convert e.g. &raquo; to a single length character.
		$title = trim( html_entity_decode( $title ) );

		//* Calculate length.
		$tit_len = mb_strlen( $title );

		$titlen_notice = $title_i18n;

		$title_length_warning = $this->get_the_seo_bar_title_length_warning( $tit_len );
		$titlen_notice .= '' !== $title_length_warning ? ' ' . $title_length_warning['notice'] : '';
		$titlen_class = $title_length_warning['class'];

		if ( '' !== $titlen_notice ) {
			$title_notice		= '<span class="ad-sec-wrap ad-25">'
								. '<a href="#" onclick="return false;" class="' . $titlen_class . '"  data-desc="' . $titlen_notice . $gen_t_notice . '">' . $title_short . $gen_t . '</a>'
								. '<span class="screen-reader-text">' . $titlen_notice . $gen_t_notice . '</span>'
								. '</span>'
								;
		} else {
			$title_notice = '';
		}

		return $title_notice;
	}

	/**
	 * Render the SEO bar description block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Description Block
	 */
	protected function the_seo_bar_description_notice( $args ) {

		//* Fetch data
		$data = $this->the_seo_bar_data( $args );
		$description 						= $data['description'];
		$description_is_from_custom_field 	= $data['description_is_from_custom_field'];

		//* Fetch i18n and put in vars
		$i18n = $this->get_the_seo_bar_i18n();
		$description_short 	= $i18n['description_short'];
		$generated_short 	= $i18n['generated_short'];

		//* Description length.
		$desc_parsed = trim( html_entity_decode( $description ) );
		$desc_len = mb_strlen( $desc_parsed );

		//* Fetch CSS classes.
		$classes = $this->get_the_seo_bar_classes();

		//* Initialize notice.
		$notice = $i18n['description'];
		$class = $classes['good'];

		//* Length notice.
		$desc_length_warning = $this->get_the_seo_bar_description_length_warning( $desc_len, $class );
		$notice .= $desc_length_warning['notice'] ? $desc_length_warning['notice'] . '<br>' : '';
		$class = $desc_length_warning['class'];

		//* Duplicated Words notice.
		$desc_too_many = $this->get_the_seo_bar_description_words_warning( $description, $class );
		$notice .= $desc_too_many['notice'] ? $desc_too_many['notice'] . '<br>' : '';
		$class = $desc_too_many['class'];

		//* Generation notice.
		$generated_notice 	= $i18n['generated'] . ' ';
		$gen_d = $description_is_from_custom_field ? '' : $generated_short;
		$gen_d_notice = $description_is_from_custom_field ? '' : $generated_notice;

		if ( '' !== $notice ) {
			$description_notice	= '<span class="ad-sec-wrap ad-25">'
								. '<a href="#" onclick="return false;" class="' . $class . '" data-desc="' . $notice . $gen_d_notice . '">' . $description_short . $gen_d . '</a>'
								. '<span class="screen-reader-text">' . $notice . $gen_d_notice . '</span>'
								. '</span>'
								;
		} else {
			$description_notice = '';
		}

		return $description_notice;
	}

	/**
	 * Render the SEO bar index block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Index Block
	 */
	protected function the_seo_bar_index_notice( $args ) {

		$term = $args['term'];
		$is_term = $args['is_term'];
		$post_i18n = $args['post_i18n'];

		$data = $this->the_seo_bar_data( $args );

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$index_short	= $i18n['index_short'];
		$but_i18n		= $i18n['but'];
		$and_i18n		= $i18n['and'];
		$ind_notice		= $i18n['index'];

		$ind_notice .= ' ' . sprintf( __( "%s is being indexed.", 'autodescription' ), $post_i18n );
		$ind_class = $good;

		/**
		 * Get noindex site option
		 *
		 * @since 2.2.2
		 */
		if ( $this->is_option_checked( 'site_noindex' ) ) {
			$ind_notice .= '<br>' . __( "But you've disabled indexing for the whole site.", 'autodescription' );
			$ind_class = $unknown;
			$ind_but = true;
		}

		/**
		 * Get taxonomy and term indexing options.
		 *
		 * @since 2.6.0
		 */
		if ( false && term_type() ) {
			var_dump(); // ADD CATEGORY AND TAG INDEXING OPTIONS
			// category_noindex
			// tag_noindex
		}

		if ( false === $this->is_blog_public() ) {
			$but_and = isset( $ind_but ) ? $and_i18n : $but_i18n;
			/* translators: %s = But or And */
			$ind_notice .= '<br>' . sprintf( __( "%s the blog isn't set to public. This means WordPress discourages indexing.", 'autodescription' ), $but_and );
			$ind_class = $bad;
			$ind_but = true;
		}

		/**
		 * Check if archive is empty, and therefore has set noindex for those.
		 *
		 * @since 2.2.8
		 */
		if ( $is_term && isset( $term->count ) && 0 === $term->count ) {
			$but_and = isset( $ind_but ) ? $and_i18n : $but_i18n;

			/* translators: %s = But or And */
			$ind_notice .= '<br>' . sprintf( __( "%s there are no posts in this term. Therefore, indexing has been disabled.", 'autodescription' ), $but_and );
			//* Don't make it unknown if it's not good.
			$ind_class = $ind_class !== $good ? $ind_class : $unknown;
		}

		$index_notice	= '<span class="ad-sec-wrap ' . $ad_125 . '">'
						. '<a href="#" onclick="return false;" class="' . $ind_class . '" data-desc="' . $ind_notice . '">' . $index_short . '</a>'
						. '<span class="screen-reader-text">' . $ind_notice . '</span>'
						. '</span>'
						;

		return $index_notice;
	}

	/**
	 * Render the SEO bar follow block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Follow Block
	 */
	protected function the_seo_bar_follow_notice( $args ) {

		$post_i18n = $args['post_i18n'];

		$data = $this->the_seo_bar_data( $args );
		$nofollow = $data['nofollow'];

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$follow_i18n	= $i18n['follow'];
		$follow_short	= $i18n['follow_short'];

		if ( $nofollow ) {
			$fol_notice = $follow_i18n . ' ' . sprintf( __( '%s links are being followed.', 'autodescription' ), $post_i18n );
			$fol_class = $good;

			/**
			 * Get nofolow site option
			 *
			 * @since 2.2.2
			 */
			if ( $this->is_option_checked( 'site_nofollow' ) ) {
				$fol_notice .= '<br>' . __( "But you've disabled following of links for the whole site.", 'autodescription' );
				$fol_class = $unknown;
			}
		} else {
			$fol_notice = $follow_i18n . ' ' . sprintf( __( "%s links aren't being followed.", 'autodescription' ), $post_i18n );
			$fol_class = $unknown;

			if ( false === $this->is_blog_public() ) {
				$fol_notice .= '<br>' . __( "But the blog isn't set to public. This means WordPress allows the links to be followed regardless.", 'autodescription' );
			}
		}

		if ( $fol_notice ) {
			$follow_notice	= '<span class="ad-sec-wrap ' . $ad_125 . '">'
							. '<a href="#" onclick="return false;" class="' . $fol_class . '" data-desc="' . $fol_notice . '">' . $follow_short . '</a>'
							. '<span class="screen-reader-text">' . $fol_notice . '</span>'
							. '</span>'
							;
		} else {
			$follow_notice = '';
		}

		return $follow_notice;
	}

	/**
	 * Render the SEO bar archive block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Follow Block
	 */
	protected function the_seo_bar_archive_notice( $args ) {

		$post_low = $args['post_low'];

		$data = $this->the_seo_bar_data( $args );
		$noarchive	= $data['noarchive'];

		$classes = $this->get_the_seo_bar_classes();
		$unknown	= $classes['unknown'];
		$bad		= $classes['bad'];
		$okay		= $classes['okay'];
		$good		= $classes['good'];
		$ad_125		= $classes['12.5%'];

		$i18n = $this->get_the_seo_bar_i18n();
		$archive_i18n	= $i18n['archive'];
		$archive_short	= $i18n['archive_short'];

		if ( $noarchive ) {
			$arc_notice = $archive_i18n . ' ' . sprintf( __( 'Search Engine are allowed to archive this %s.', 'autodescription' ), $post_low );
			$arc_class = $good;

			/**
			 * Get noarchive site option
			 *
			 * @since 2.2.2
			 */
			if ( $this->is_option_checked( 'site_noarchive' ) ) {
				$arc_notice .= '<br>' . __( "But you've disabled archiving for the whole site.", 'autodescription' );
				$arc_class = $unknown;
			}

		} else {
			$arc_notice = $archive_i18n . ' ' . sprintf( __( "Search Engine aren't allowed to archive this %s.", 'autodescription' ), $post_low );
			$arc_class = $unknown;

			if ( false === $this->is_blog_public() ) {
				$arc_notice .= '<br>' . __( "But the blog isn't set to public. This means WordPress allows the blog to be archived regardless.", 'autodescription' );
			}
		}

		if ( $arc_notice ) {
			$archive_notice	= '<span class="ad-sec-wrap ' . $ad_125 . '">'
							. '<a href="#" onclick="return false;" class="' . $arc_class . '" data-desc="' . $arc_notice . '">' . $archive_short . '</a>'
							. '<span class="screen-reader-text">' . $arc_notice . '</span>'
							. '</span>'
							;
		} else {
			$archive_notice = '';
		}

		return $archive_notice;
	}

	/**
	 * Render the SEO bar redirect block and notice.
	 *
	 * @param array $args
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar Redirect Block
	 */
	protected function the_seo_bar_redirect_notice( $args ) {

		//* No redirection on taxonomies (yet).
		$is_term = $args['is_term'];

		if ( false === $is_term ) {
			//* Pretty much outputs that it's not being redirected.

			$post = $args['post_i18n'];

			$classes = $this->get_the_seo_bar_classes();
			$ad_125 = $classes['12.5%'];

			$i18n = $this->get_the_seo_bar_i18n();
			$redirect_i18n = $i18n['redirect'];
			$redirect_short = $i18n['redirect_short'];

			$red_notice = $redirect_i18n . ' ' . sprintf( __( "%s isn't being redirected.", 'autodescription' ), $post );
			$red_class = $classes['good'];

			$redirect_notice	= '<span class="ad-sec-wrap ' . $ad_125 . '">'
								. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . $redirect_short . '</a>'
								. '<span class="screen-reader-text">' . $red_notice . '</span>'
								. '</span>'
								;
		} else {
			$redirect_notice = '';
		}

		return $redirect_notice;
	}

	/**
	 * Render the SEO bar when the page/term is blocked.
	 *
	 * @param array $args {
	 *		$is_term => bool,
	 *		$redirect => bool,
	 *		$noindex => bool,
	 *		$post_i18n => string
	 * }
	 *
	 * @since 2.6.0
	 *
	 * @return string The SEO Bar
	 */
	protected function the_seo_bar_blocked( $args ) {

		$classes = $this->get_the_seo_bar_classes();
		$i18n = $this->get_the_seo_bar_i18n();

		$square = $this->square_the_seo_bar() ? ' ' . $classes['square'] : '';
		$ad_100 = $args['is_term'] ? ' ' . $classes['100%'] : '';

		$redirect = $args['redirect'];
		$noindex = $args['noindex'];
		$post = $args['post_i18n'];

		if ( $redirect && $noindex ) {
			//* Redirect and noindex found, why bother showing SEO.

			$red_notice = $i18n['redirect'] . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
			$red_class = $classes['unknown'];

			$redirect_notice	= '<span class="ad-sec-wrap ad-50">'
								. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . $i18n['redirect_short'] . '</a>'
								. '<span class="screen-reader-text">' . $red_notice . '</span>'
								. '</span>'
								;

			$noi_notice = $i18n['index'] . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
			$noi_class = $classes['unknown'];

			$noindex_notice		= '<span class="ad-sec-wrap ad-50">'
								. '<a href="#" onclick="return false;" class="' . $noi_class . '" data-desc="' . $noi_notice . '">' . $i18n['index_short'] . '</a>'
								. '<span class="screen-reader-text">' . $noi_notice . '</span>'
								. '</span>'
								;

			$content = sprintf( '<span class="ad-seo clearfix' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s %s</span></span>', $redirect_notice, $noindex_notice );

		} else if ( $redirect && false === $noindex ) {
			//* Redirect found, why bother showing SEO info?

			$red_notice = $i18n['redirect'] . ' ' . sprintf( __( "%s is being redirected. This means no SEO values have to be set.", 'autodescription' ), $post );
			$red_class = $classes['unknown'];

			$redirect_notice	= '<span class="ad-sec-wrap ad-100">'
								. '<a href="#" onclick="return false;" class="' . $red_class . '" data-desc="' . $red_notice . '">' . $i18n['redirect_short'] . '</a>'
								. '<span class="screen-reader-text">' . $red_notice . '</span>'
								. '</span>'
								;

			$content = sprintf( '<span class="ad-seo clearfix' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s</span></span>', $redirect_notice );

		} else if ( false === $redirect && $noindex ) {
			//* Noindex found, why bother showing SEO info?

			$noi_notice = $i18n['index'] . ' ' . sprintf( __( "%s is not being indexed. This means no SEO values have to be set.", 'autodescription' ), $post );
			$noi_class = $classes['unknown'];

			$noindex_notice	= '<span class="ad-sec-wrap ad-100">'
							. '<a href="#" onclick="return false;" class="' . $noi_class . '" data-desc="' . $noi_notice . '">' . $i18n['index_short'] . '</a>'
							. '<span class="screen-reader-text">' . $noi_notice . '</span>'
							. '</span>'
							;

			$content = sprintf( '<span class="ad-seo clearfix' . $ad_100 . $square . '"><span class="ad-bar-wrap">%s</span></span>', $noindex_notice );
		}

		return $content;
	}

	/**
	 * @TODO
	 */
	protected function wrap_the_seo_bar_block() {
		$wrap 	= '<span class="ad-sec-wrap ' . $width . '">'
				. '<a href="#" onclick="return false;" class="' . $class . '" data-desc="' . $notice . '">' . $indicator . '</a>'
				. '<span class="screen-reader-text">' . $notice . '</span>'
				. '</span>';
	}

	/**
	 * @TODO
	 */
	protected function get_the_seo_bar_wrap() {

	}

	/**
	 * Get the term labels.
	 *
	 * @since 2.6.0
	 *
	 * @return string the Term name.
	 */
	protected function get_the_seo_bar_term_name( $term ) {

		static $term_name = null;

		if ( isset( $term_name ) )
			return $term_name;

		if ( $term && is_object( $term ) ) {
			$tax_type = $term->taxonomy;

			/**
			 * Dynamically fetch the term name.
			 *
			 * @since 2.3.1
			 */
			$term_labels = $this->get_tax_labels( $tax_type );

			if ( isset( $term_labels->singular_name ) )
				return $term_name = $term_labels->singular_name;
		}

		//* Fallback to Page as it is generic.
		return $term_name = __( 'Page', 'autodescription' );
	}

	/**
	 * Title Length notices.
	 *
	 * @param int $tit_len The Title length
	 * @since 2.6.0
	 *
	 * @return array {
	 * 		notice => The notice,
	 * 		class => The class,
	 * }
	 */
	protected function get_the_seo_bar_title_length_warning( $tit_len ) {

		$classes = $this->get_the_seo_bar_classes();
		$bad	= $classes['bad'];
		$okay	= $classes['okay'];
		$good	= $classes['good'];

		if ( $tit_len < 25 ) {
			$notice = ' ' . __( 'Length is far too short.', 'autodescription' );
			$class = $bad;
		} else if ( $tit_len < 42 ) {
			$notice = ' ' . __( 'Length is too short.', 'autodescription' );
			$class = $okay;
		} else if ( $tit_len > 55 && $tit_len < 75 ) {
			$notice = ' ' . __( 'Length is too long.', 'autodescription' );
			$class = $okay;
		} else if ( $tit_len >= 75 ) {
			$notice = ' ' . __( 'Length is far too long.', 'autodescription' );
			$class = $bad;
		} else {
			$notice = ' ' . __( 'Length is good.', 'autodescription' );
			$class = $good;
		}

		return array(
			'notice' => $notice,
			'class' => $class
		);
	}

	/**
	 * Description Length notices.
	 *
	 * @param int $desc_len The Title length
	 * @param string $class The current color class.
	 *
	 * @since 2.6.0
	 *
	 * @return array {
	 * 		notice => The notice,
	 * 		class => The class,
	 * }
	 */
	protected function get_the_seo_bar_description_length_warning( $desc_len, $class ) {

		$classes = $this->get_the_seo_bar_classes();
		$bad	= $classes['bad'];
		$okay	= $classes['okay'];
		$good	= $classes['good'];

		if ( $desc_len < 100 ) {
			$notice = ' ' . __( 'Length is far too short.', 'autodescription' );
			$class = $bad;
		} else if ( $desc_len < 145 ) {
			$notice = ' ' . __( 'Length is too short.', 'autodescription' );

			// Don't make it okay if it's already bad.
			$class = $bad === $class ? $class : $okay;
		} else if ( $desc_len > 155 && $desc_len < 175 ) {
			$notice = ' ' . __( 'Length is too long.', 'autodescription' );

			// Don't make it okay if it's already bad.
			$class = $bad === $class ? $class : $okay;
		} else if ( $desc_len >= 175 ) {
			$notice = ' ' . __( 'Length is far too long.', 'autodescription' );
			$class = $bad;
		} else {
			$notice = ' ' . __( 'Length is good.', 'autodescription' );

			// Don't make it good if it's already bad or okay.
			$class = $good !== $class ? $class : $good;
		}

		return array(
			'notice' => $notice,
			'class' => $class
		);
	}

	/**
	 * Calculates the word count and returns a warning with the words used.
	 * Only when count is over 3.
	 *
	 * @param string $description The Description with maybe words too many.
	 * @param string $class The current color class.
	 *
	 * @since 2.6.0
	 *
	 * @return string The warning notice.
	 */
	protected function get_the_seo_bar_description_words_warning( $description, $class ) {

		$notice = '';
		$desc_too_many = '';

		//* Count the words.
		$desc_words = str_word_count( strtolower( $description ), 2 );

		if ( is_array( $desc_words ) ) {
			//* We're going to fetch word based on key, and the last element (as first)
			$word_keys = array_flip( array_reverse( $desc_words, true ) );

			$desc_word_count = array_count_values( $desc_words );

			//* Parse word counting.
			if ( is_array( $desc_word_count ) ) {
				foreach ( $desc_word_count as $desc_word => $desc_word_count ) {
					if ( $desc_word_count >= 3 ) {
						$position = $word_keys[$desc_word];

						$word_len = mb_strlen( $desc_word );
						$first_word_original = mb_substr( $description, $position, $word_len );

						//* Found words that are used too frequently.
						$desc_too_many[] = array( $first_word_original => $desc_word_count );
					}
				}
			}
		}

		if ( '' !== $desc_too_many && is_array( $desc_too_many ) ) {

			$classes = $this->get_the_seo_bar_classes();
			$bad = $classes['bad'];
			$okay = $classes['okay'];

			$words_count = count( $desc_too_many );
			//* Don't make it okay if it's already bad.
			$class = $bad !== $class && $words_count <= 1 ? $okay : $bad;

			$i = 1;
			$count = count( $desc_too_many );
			foreach ( $desc_too_many as $desc_array ) {
				foreach ( $desc_array as $desc_value => $desc_count ) {
					$notice .= ' ';

					/**
					 * Don't ucfirst abbrivations.
					 * @since 2.4.1
					 */
					$desc_value = ctype_upper( $desc_value ) ? $desc_value : ucfirst( $desc_value );

					$notice .= sprintf( __( '%s is used %d times.', 'autodescription' ), '<span>' . $desc_value . '</span>', $desc_count );

					//* Don't add break at last occurence.
					$notice .= $i === $count ? '' : '<br>';
					$i++;
				}
			}
		}

		return array(
			'notice' => $notice,
			'class' => $class
		);
	}

	/**
	 * Returns an array of the classes used for CSS within The SEO Bar.
	 *
	 * @since 2.6.0
	 *
	 * @return array The class names.
	 */
	public function get_the_seo_bar_classes() {
		return array(
			'bad' 		=> 'ad-seo-bad',
			'okay' 		=> 'ad-seo-okay',
			'good' 		=> 'ad-seo-good',
			'unknown' 	=> 'ad-seo-unknown',

			'square' => 'square',

			'100%' 	=> 'ad-100',
			'60%' 	=> 'ad-60',
			'50%' 	=> 'ad-50',
			'40%' 	=> 'ad-40',
			'33%' 	=> 'ad-33',
			'25%' 	=> 'ad-25',
			'25%' 	=> 'ad-25',
			'20%' 	=> 'ad-20',
			'16%' 	=> 'ad-16',
			'12.5%' => 'ad-12-5',
			'11%' 	=> 'ad-11',
			'10%' 	=> 'ad-10',
		);
	}

	/**
	 * Returns an array of the i18n notices for The SEO Bar.
	 *
	 * @staticvar array $i18n
	 * @since 2.6.0
	 *
	 * @return array The i18n sentences.
	 */
	public function get_the_seo_bar_i18n() {

		static $i18n = null;

		if ( isset( $i18n ) )
			return $i18n;

		return $i18n = array(
			'title'			=> __( 'Title:', 'autodescription' ),
			'description' 	=> __( 'Description:', 'autodescription' ),
			'index'			=> __( 'Index:', 'autodescription' ),
			'follow'		=> __( 'Follow:', 'autodescription' ),
			'archive'		=> __( 'Archive:', 'autodescription' ),
			'redirect'		=> __( 'Redirect:', 'autodescription' ),

			'generated' => __( 'Generated: Automatically generated.', 'autodescription'),

			'generated_short'	=> _x( 'G', 'Generated', 'autodescription' ),
			'title_short'		=> _x( 'T', 'Title', 'autodescription' ),
			'description_short'	=> _x( 'D', 'Description', 'autodescription' ),
			'index_short'		=> _x( 'I', 'no-Index', 'autodescription' ),
			'follow_short'		=> _x( 'F', 'no-Follow', 'autodescription' ),
			'archive_short'		=> _x( 'A', 'no-Archive', 'autodescription' ),
			'redirect_short'	=> _x( 'R', 'Redirect', 'autodescription' ),

			'but' => _x( 'But', 'But there are...', 'autodescription' ),
			'and' => _x( 'And', 'And there are...', 'autodescription' ),
		);
	}

	/**
	 * Whether to square the seo bar.
	 *
	 * Applies filters 'the_seo_framework_seo_bar_squared' : boolean
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	protected function square_the_seo_bar() {

		$cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = (bool) apply_filters( 'the_seo_framework_seo_bar_squared', false );
	}

}
