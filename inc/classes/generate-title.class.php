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
 * Class AutoDescription_Generate_Title
 *
 * Generates title SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Title extends AutoDescription_Generate_Description {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Get the title. God function.
	 * Always use this function for the title unless you're absolutely sure what you're doing.
	 *
	 * This function is used for all these: Taxonomies and Terms, Posts, Pages, Blog, front page, front-end, back-end.
	 *
	 * @since 1.0.0
	 *
	 * Params required wp_title filter :
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 *
	 * @since 2.4.0:
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
	 * 		@param string taxonomy The Taxonomy name.
	 * 		@param bool page_on_front Page on front condition for example generation.
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool notagline Generate title without tagline.
	 * 		@param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * 		@param bool description_title Fetch title for description.
	 * 		@param bool is_front_page Fetch front page title.
	 * }
	 *
	 * @return string $title Title
	 */
	public function title( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		//* Use WordPress default feed title.
		if ( is_feed() )
			return trim( $title );

		/**
		 * Debug parameters.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			echo  "\r\n" . 'START: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n";

			if ( $this->the_seo_framework_debug_more ) {
				$this->echo_debug_information( array( 'title' => $title ) );
				$this->echo_debug_information( array( 'sep' => $sep ) );
				$this->echo_debug_information( array( 'seplocation' => $seplocation ) );
				$this->echo_debug_information( array( 'args' => $args ) );
			}

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		$default_args = $this->parse_title_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.4.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', $this->the_seo_framework_version( '2.4.0' ) );
			$args = $default_args;
		} else if ( ! empty( $args ) ) {
			$args = $this->parse_title_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		/**
		 * Return early if the request is the Title only (without tagline/blogname).
		 * Admin only.
		 */
		if ( $args['notagline'] && is_admin() )
			return $this->build_title_notagline( $title, $args );

		/**
		 * Add doing it wrong notice for better SEO consistency.
		 * Only when in wp_title.
		 *
		 * @since 2.2.5
		 */
		if ( ! $args['meta'] ) {
			if ( ! $this->current_theme_supports_title_tag() && doing_filter( 'wp_title' ) ) {
				if ( ! empty( $seplocation ) ) {
					//* Set doing it wrong parameters.
					$this->tell_title_doing_it_wrong( $title, $sep, $seplocation, false );
					//* And echo them.
					add_action( 'wp_footer', array( $this, 'tell_title_doing_it_wrong' ), 20 );

					//* Notify cache.
					$this->title_doing_it_wrong = true;

					//* Notify transients
					$this->set_theme_dir_transient( false );

					return $this->build_title_doingitwrong( $title, $sep, $seplocation, $args );
				} else if ( ! empty( $sep ) ) {
					//* Set doing it wrong parameters.
					$this->tell_title_doing_it_wrong( $title, $sep, $seplocation, false );
					//* And echo them.
					add_action( 'wp_footer', array( $this, 'tell_title_doing_it_wrong' ), 20 );

					//* Notify cache.
					$this->title_doing_it_wrong = true;

					//* Notify transients
					$this->set_theme_dir_transient( false );

					//* Title is empty.
					$args['empty_title'] = true;

					return $this->build_title_doingitwrong( $title, $sep, $seplocation, $args );
				}
			}
		}

		//* Notify cache to keep using the same output. We're doing it right :).
		if ( ! isset( $this->title_doing_it_wrong ) )
			$this->title_doing_it_wrong = false;

		//* Set transient to true if the theme is doing it right.
		if ( false !== $this->title_doing_it_wrong )
			$this->set_theme_dir_transient( true );

		//* Empty title and rebuild it.
		return $this->build_title( $title = '', $seplocation, $args );
	}

	/**
	 * Parse and sanitize title args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_title_args : {
	 * 		@param int term_id The Taxonomy Term ID when taxonomy is also filled in. Else post ID.
	 * 		@param string taxonomy The Taxonomy name.
	 * 		@param bool page_on_front Page on front condition for example generation.
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool notagline Generate title without tagline.
	 * 		@param bool meta Ignore doing_it_wrong. Used in og:title/twitter:title
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * 		@param bool description_title Fetch title for description.
	 * 		@param bool is_front_page Fetch front page title.
	 * }
	 *
	 * @since 2.4.0
	 * @return array $args parsed args.
	 */
	public function parse_title_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'term_id' 			=> $this->get_the_real_ID(),
				'taxonomy' 			=> '',
				'page_on_front'		=> false,
				'placeholder'		=> false,
				'notagline' 		=> false,
				'meta' 				=> true,
				'get_custom_field'	=> true,
				'description_title'	=> false,
				'is_front_page'		=> false,
				'escape'			=> true
			);

			//* @since 2.5.0
			$defaults = (array) apply_filters( 'the_seo_framework_title_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['term_id'] 			= isset( $args['term_id'] ) 			? (int) $args['term_id'] 			: $defaults['term_id'];
		$args['taxonomy'] 			= isset( $args['taxonomy'] ) 			? (string) $args['taxonomy'] 		: $defaults['taxonomy'];
		$args['page_on_front'] 		= isset( $args['page_on_front'] ) 		? (bool) $args['page_on_front'] 	: $defaults['page_on_front'];
		$args['placeholder'] 		= isset( $args['placeholder'] ) 		? (bool) $args['placeholder'] 		: $defaults['placeholder'];
		$args['notagline'] 			= isset( $args['notagline'] ) 			? (bool) $args['notagline'] 		: $defaults['notagline'];
		$args['meta'] 				= isset( $args['meta'] ) 				? (bool) $args['meta'] 				: $defaults['meta'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['description_title'] 	= isset( $args['description_title'] ) 	? (bool) $args['description_title'] : $defaults['description_title'];
		$args['is_front_page'] 		= isset( $args['is_front_page'] ) 		? (bool) $args['is_front_page'] 	: $defaults['is_front_page'];
		$args['escape'] 			= isset( $args['escape'] ) 				? (bool) $args['escape'] 			: $defaults['escape'];

		return $args;
	}

	/**
	 * Build the title based on input, without tagline.
	 *
	 * @param string $title The Title to return
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool page_on_front Page on front condition for example generation
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string Title without tagline.
	 */
	public function build_title_notagline( $title = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		$title = $this->get_placeholder_title( $title, $args );

		if ( empty( $title ) )
			$title = __( 'Untitled', 'autodescription' );

		if ( true === $args['escape'] ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
			$title = trim( $title );
		}

		return $title;
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
	 *
	 * @return string Title without tagline.
	 */
	public function get_placeholder_title( $title = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		/**
		 * Detect if placeholder is being generated.
		 * @since 2.2.4
		 */
		if ( $args['placeholder'] && empty( $title ) ) {
			$term_id = $args['term_id'];

			if ( $args['page_on_front'] ) {
				$title = get_the_title( get_option( 'page_on_front' ) );
			} else if ( '' !== $args['taxonomy'] ) {
				$term = get_term( $term_id, $args['taxonomy'], OBJECT, 'raw' );

				$title = $this->get_the_real_archive_title( $term );
			} else if ( ! empty( $term_id ) ) {
				$title = get_the_title( $term_id );
			} else {
				$post = get_post( $term_id, OBJECT );

				/**
				 * Memory leak fix
				 * @since 2.3.5
				 */
				$title = isset( $post->post_title ) && ! empty( $post->post_title ) ? $post->post_title : '';
			}
		}

		return $title;
	}

	/**
	 * Build the title based on input for themes that are doing it wrong.
	 * Pretty much a duplicate of build_title but contains many more variables.
	 * Keep this in mind.
	 *
	 * @param string $title The Title to return
	 * @param string $sep The Title sepeartor
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 * 		@param int term_id The Taxonomy Term ID
	 * 		@param string taxonomy The Taxonomy name
	 * 		@param bool placeholder Generate placeholder, ignoring options.
	 * 		@param bool get_custom_field Do not fetch custom title when false.
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string $title Title
	 */
	public function build_title_doingitwrong( $title = '', $sep = '', $seplocation = '', $args = array() ) {

		/**
		 * Empty the title, because most themes think they 'know' how to SEO the front page.
		 * Because, most themes know how to make the title 'pretty'.
		 * And therefor add all kinds of stuff.
		 *
		 * Moved up and return early to reduce processing.
		 * @since 2.3.8
		 */
		if ( is_front_page() )
			return $title = '';

		/**
		 * When using an empty wp_title() function, outputs are unexpected.
		 * This small piece of code will fix all that.
		 * By removing the separator from the title and adding the blog name always to the right.
		 * Which is always the case with doing_it_wrong.
		 *
		 * @thanks JW_ https://wordpress.org/support/topic/wp_title-problem-bug
		 * @since 2.4.3
		 */
		if ( isset( $args['empty_title'] ) ) {
			$title = trim( str_replace( $sep, '', $title ) );
			$seplocation = 'right';
		}

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		$blogname = $this->get_blogname();

		//* Remove separator if true.
		$sep_replace = false;

		/**
		 * Don't add/replace separator when false.
		 *
		 * @applies filters the_seo_framework_doingitwrong_add_sep
		 *
		 * @since 2.4.2
		 */
		$add_sep = (bool) apply_filters( 'the_seo_framework_doingitwrong_add_sep', true );

		//* Maybe remove separator.
		if ( $add_sep && ( ! empty( $sep ) || ! empty( $title ) ) ) {
			$sep_replace = true;
			$sep_to_replace = (string) $sep;
		}

		//* Fetch title from custom fields.
		if ( $args['get_custom_field'] && $this->is_singular( $args['term_id'] ) ) {
			$title_special = $this->title_from_special_fields();

			if ( empty( $title_special ) ) {
				$title_from_custom_field = $this->title_from_custom_field( $title, false, $args['term_id'] );
				$title = ! empty( $title_from_custom_field ) ? $title_from_custom_field : $title;
			} else {
				$title = $title_special;
			}
		}

		//* Generate the Title if empty or if home.
		if ( empty( $title ) )
			$title = (string) $this->generate_title( $args['term_id'], $args['taxonomy'], $escape = false );

		/**
		 * Applies filters the_seo_framework_title_separator : String The title separator
		 */
		if ( $add_sep )
			$sep = (string) apply_filters( 'the_seo_framework_title_separator', $this->get_separator( 'title' ) );

		/**
		 * Add $sep_to_replace
		 *
		 * @since 2.3.8
		 */
		if ( $add_sep && $sep_replace ) {
			//* Title always contains something at this point.
			$tit_len = mb_strlen( $title );

			/**
			 * Prevent double separator on date archives.
			 * This will cause manual titles with the same separator at the end to be removed.
			 * Then again, update your theme. D:
			 *
			 * A separator is at least 2 long (space + separator).
			 *
			 * @param string $sep_to_replace Already confirmed to contain the old sep string.
			 *
			 * @since ???
			 *
			 * Now also considers seplocation.
			 * @since 2.4.1
			 */
			if ( $seplocation == 'right' ) {
				if ( $tit_len > 2 && ! mb_strpos( $title, $sep_to_replace, $tit_len - 2 ) )
					$title = $title . ' ' . $sep_to_replace;
			} else {
				if ( $tit_len > 2 && ! mb_strpos( $title, $sep_to_replace, 2 ) )
					$title = $sep_to_replace . ' ' . $title;
			}
		}

		//* Sep location has no influence.
		if ( $sep_replace && $add_sep ) {
			//* Add trailing space for the tagline/blogname is stuck onto this part with trim.

			/**
			 * Convert characters to easier match and prevent removal of matching entities and title characters.
			 * Reported by Riccardo: https://wordpress.org/support/topic/problem-with-post-titles
			 * @since 2.5.2
			 */
			$sep_to_replace = html_entity_decode( $sep_to_replace );
			$title = html_entity_decode( $title );

			/**
			 * Now also considers seplocation.
			 * @since 2.4.1
			 */
			if ( $seplocation == 'right' ) {
				$title = trim( rtrim( $title, "$sep_to_replace " ) ) . " $sep ";
			} else {
				$title = " $sep " . trim( ltrim( $title, " $sep_to_replace" ) );
			}

		} else {
			$title = trim( $title ) . " $sep ";
		}

		/**
		 * From WordPress core get_the_title.
		 * Bypasses get_post() function object which causes conflict with some themes and plugins.
		 *
		 * Also bypasses the_title filters.
		 * And now also works in admin. It gives you a true representation of its output.
		 *
		 * @since 2.4.1
		 *
		 * @applies filters core : protected_title_format
		 * @applies filters core : private_title_format
		 */
		if ( ! $args['description_title'] ) {
			$post = get_post( $args['term_id'], OBJECT );

			if ( isset( $post->post_password ) && ! empty( $post->post_password ) ) {
				$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s', 'autodescription' ), $post );
				$title = sprintf( $protected_title_format, $title );
			} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s', 'autodescription' ), $post );
				$title = sprintf( $private_title_format, $title );
			}

		}

		if ( true === $args['escape'] ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		/**
		 * Debug output.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {
			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$this->echo_debug_information( array( 'title output' => $title ) );
			echo "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br><br>";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return $title;
	}

	/**
	 * Build the title based on input.
	 *
	 * @param string $title The Title to return
	 * @param string $seplocation The Title sepeartor location ( accepts 'left' or 'right' )
	 * @param array $args : accepted args : {
	 * 		@param int 		term_id The Taxonomy Term ID
	 * 		@param string 	taxonomy The Taxonomy name
	 * 		@param bool 	page_on_front Page on front condition for example generation
	 * 		@param bool 	placeholder Generate placeholder, ignoring options.
	 * 		@param bool 	get_custom_field Do not fetch custom title when false.
	 * 		@param bool 	is_front_page Fetch front page title.
	 * }
	 *
	 * @since 2.4.0
	 *
	 * @return string $title Title
	 */
	public function build_title( $title = '', $seplocation = '', $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_title_args( '', '', true );

		/**
		 * Overwrite title here, prevents duplicate title issues, since we're working with a filter.
		 *
		 * @since 2.2.2
		 */
		$title = '';

		$is_front_page = is_front_page() || $args['page_on_front'] ? true : false;
		$blogname = $this->get_blogname();

		/**
		 * Cache the seplocation for is_home()
		 * @since 2.2.2
		 */
		$seplocation_home = $seplocation;

		/**
		 * Filters the separator location
		 * @since 2.1.8
		 */
		if ( '' === $seplocation || 'right' !== $seplocation || 'left' !== $seplocation || empty( $seplocation ) ) {
			/**
			 * Applies filters 'the_seo_framework_title_seplocation' : String the title location.
			 */
			$seplocation = (string) apply_filters( 'the_seo_framework_title_seplocation', $this->get_option( 'title_location' ) );
		}

		/**
		 * Applies filters 'the_seo_framework_title_separator' : String the title separator
		 * @since 2.0.5
		 */
		$sep = (string) apply_filters( 'the_seo_framework_title_separator', $this->get_separator( 'title' ) );

		//* Fetch title from custom fields.
		if ( $args['get_custom_field'] && $this->is_singular( $args['term_id'] ) ) {
			$title_special = $this->title_from_special_fields();

			if ( empty( $title_special ) ) {
				$title_from_custom_field = $this->title_from_custom_field( $title, '', $args['term_id'] );
				$title = ! empty( $title_from_custom_field ) ? $title_from_custom_field : $title;
			} else {
				$title = $title_special;
			}
		}

		/**
		 * Tagline conditional for homepage
		 *
		 * @since 2.2.2
		 */
		$add_tagline = 0;

		/**
		 * Generate the Title if empty or if home.
		 *
		 * Generation of title has acquired its own functions.
		 * @since 2.3.4
		 */
		if ( $is_front_page || $this->is_static_frontpage( $args['term_id'] ) || $args['is_front_page'] ) {
			$generated = (array) $this->generate_home_title( $args['get_custom_field'], $seplocation, $seplocation_home, $escape = false );

			if ( ! empty( $generated ) && is_array( $generated ) ) {
				$title = $generated['title'] ? (string) $generated['title'] : $title;
				$blogname = $generated['blogname'] ? (string) $generated['blogname'] : $blogname;
				$add_tagline = $generated['add_tagline'] ? (bool) $generated['add_tagline'] : $add_tagline;
				$seplocation = $generated['seplocation'] ? (string) $generated['seplocation'] : $seplocation;
			}
		} else if ( empty( $title ) ) {
			$title = (string) $this->generate_title( $args['term_id'], $args['taxonomy'], $escape = false );
		}

		/**
		 * From WordPress core get_the_title.
		 * Bypasses get_post() function object which causes conflict with some themes and plugins.
		 *
		 * Also bypasses the_title filters.
		 * And now also works in admin. It gives you a true representation of its output.
		 *
		 * Title for the description bypasses sanitation and additions.
		 *
		 * @since 2.4.1
		 *
		 * @global $page
		 * @global $paged
		 *
		 * @applies filters core : protected_title_format
		 * @applies filters core : private_title_format
		 */
		if ( ! $args['description_title'] ) {
			global $page, $paged;

			$post = get_post( $args['term_id'], OBJECT );

			if ( isset( $post->post_password ) && ! empty( $post->post_password ) ) {
				$protected_title_format = apply_filters( 'protected_title_format', __( 'Protected: %s', 'autodescription' ), $post );
				$title = sprintf( $protected_title_format, $title );
			} else if ( isset( $post->post_status ) && 'private' == $post->post_status ) {
				$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s', 'autodescription' ), $post );
				$title = sprintf( $private_title_format, $title );
			}

			/**
			 * @since 2.4.3
			 * Adds page numbering within the title.
			 */
			if ( ! is_404() && ( $paged >= 2 || $page >= 2 ) )
				$title .= " $sep " . sprintf( __( 'Page %s', 'autodescription' ), max( $paged, $page ) );

			//* Title for title (meta) tags.
			if ( $is_front_page && ! $add_tagline ) {
				//* Render frontpage output without tagline
				$title = $blogname;
			}

			/**
			 * Get blogname additions from option, invert it and cast to bool.
			 * @since 2.5.2
			 */
			$add_blogname_option = (bool) ! $this->get_option( 'title_rem_additions' );

			//* If theme is doing it wrong, add it anyway in the admin area.
			if ( is_admin() && ! $this->theme_title_doing_it_right() )
				$add_blogname_option = true;

			/**
			 * Applies filters the_seo_framework_add_blogname_to_title.
			 * @since 2.4.3
			 */
			$add_blogname = (bool) apply_filters( 'the_seo_framework_add_blogname_to_title', $add_blogname_option );

			/**
			 * On frontpage: Add title if add_tagline is true.
			 * On all other pages: Add tagline if filters $add_blogname is true.
			 *
			 * @since 2.4.3
			 */
			if ( ( $add_blogname && ! $is_front_page ) || ( $is_front_page && $add_tagline ) ) {
				$title = trim( $title );
				$blogname = trim( $blogname );

				if ( 'right' == $seplocation ) {
					$title = $title . " $sep " . $blogname;
				} else {
					$title = $blogname . " $sep " . $title;
				}
			}

			if ( true === $args['escape'] ) {
				$title = wptexturize( $title );
				$title = convert_chars( $title );
				$title = esc_html( $title );
				$title = capital_P_dangit( $title );
				$title = trim( $title );
			}
		}

		/**
		 * Debug output.
		 * @since 2.3.4
		 */
		if ( $this->the_seo_framework_debug ) {

			if ( $this->the_seo_framework_debug_hidden )
				echo "<!--\r\n";

			$this->echo_debug_information( array( 'is static frontpage' => $this->is_static_frontpage( $this->get_the_real_ID() ) ) );
			$this->echo_debug_information( array( 'title output' => $title ) );
			echo "\r\n<br>\r\n" . 'END: ' . __CLASS__ . '::' . __FUNCTION__ .  "\r\n<br><br>";

			if ( $this->the_seo_framework_debug_hidden )
				echo "\r\n-->";
		}

		return $title;
	}

	/**
	 * Fetches title from special fields, like other plugins with special queries.
	 * Used before and has priority over custom fields.
	 * Front end only.
	 *
	 * @since 2.5.2
	 *
	 * @return string $title Title from Special Field.
	 */
	public function title_from_special_fields() {

		$title = '';

		if ( ! is_admin() ) {
			if ( $this->is_ultimate_member_user_page() && um_is_core_page( 'user' ) && um_get_requested_user() ) {
				$title = um_user( 'display_name' );
			}
		}

		return $title;
	}

	/**
	 * Generate the title based on query conditions.
	 *
	 * @since 2.3.4
	 *
	 * @param int $term_id The Taxonomy Term ID
	 * @param string $taxonomy The Taxonomy name
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return string $title The Generated Title.
	 */
	public function generate_title( $term_id = 0, $taxonomy = '', $escape = false ) {

		/**
		 * Combined the statements
		 * @since 2.2.7 && @since 2.2.8
		 *
		 * Check for singular first, like WooCommerce shop.
		 * @since 2.5.2
		 */
		if ( ! $this->is_singular( $term_id ) ) {
			if ( is_category() || is_tag() || is_tax() || ( ! empty( $term_id ) && ! empty( $taxonomy ) ) ) {
				$title = $this->title_for_terms( '', $term_id, $taxonomy );
			} else if ( is_archive() ) {
				/**
				 * Get all other archive titles
				 * @since 2.5.2
				 */
				$title = wp_strip_all_tags( $this->get_the_archive_title() );
			}
		}

		/**
		 * Applies filters string the_seo_framework_404_title
		 * @since 2.5.2
		 */
		if ( is_404() )
			$title = (string) apply_filters( 'the_seo_framework_404_title', '404' );

		if ( is_search() ) {
			/**
			 * Applies filters string the_seo_framework_404_title
			 * @since 2.5.2
			 */
			/* translators: Front-end output. */
			$search_title = (string) apply_filters( 'the_seo_framework_search_title', __( 'Search results for:', 'autodescription' ) );
			$title = $search_title . ' ' . trim( get_search_query() );
		}

		//* Generate admin placeholder for taxonomies
		if ( empty( $title ) && ! empty( $term_id ) && ! empty( $taxonomy ) ) {
			$term = get_term_by( 'id', $term_id, $taxonomy, OBJECT );

			if ( ! empty( $term ) && is_object( $term ) ) {
				$term_name = ! empty( $term->name ) ? $term->name : $term->slug;
			} else {
				/* translators: Front-end output. */
				$term_name = __( 'Untitled', 'autodescription' );
			}

			$tax_type = $term->taxonomy;

			/**
			 * Dynamically fetch the term name.
			 *
			 * @since 2.3.1
			 */
			$term_labels = $this->get_tax_labels( $tax_type );

			if ( isset( $term_labels ) && isset( $term_labels->singular_name ) ) {
				$title = $term_labels->singular_name . ': ' . $term_name;
			} else {
				/* translators: Front-end output. */
				$title = __( 'Archives', 'autodescription' );
			}
		}

		//* Fetch the post title if no title is found.
		if ( ! isset( $title ) || empty( $title ) ) {

			if ( empty( $term_id ) )
				$term_id = $this->get_the_real_ID();

			$post = get_post( $term_id, OBJECT );

			$title = '';

			/**
			 * From WordPress core get_the_title.
			 * Bypasses get_post() function object which causes conflict with some themes and plugins.
			 *
			 * Also bypasses the_title filters.
			 * And now also works in admin. It gives you a true representation of its output.
			 *
			 * @since 2.4.1
			 */
			$title = isset( $post->post_title ) ? $post->post_title : $title;
		}

		//* You forgot to enter a title "anywhere"!
		//* So it's untitled :D
		if ( empty( $title ) ) {
			/* translators: Front-end output. */
			$title = __( 'Untitled', 'autodescription' );
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return $title;
	}

	/**
	 * Generate the title based on conditions for the home page.
	 *
	 * @since 2.3.4
	 *
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param string $seplocation The separator location
	 * @param string $seplocation_home The Homepage separator location
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return array {
	 *		'title' => (string) $title : The Generated Title
	 *		'blogname' => (string) $blogname : The Generated Blogname
	 *		'add_tagline' => (bool) $add_tagline : Wether to add the tagline
	 *		'seplocation' => (string) $seplocation : The Separator Location
	 *	}
	 */
	public function generate_home_title( $get_custom_field = true, $seplocation = '', $seplocation_home = '', $escape = false ) {

		/**
		 * Tagline conditional for homepage
		 *
		 * @since 2.2.2
		 *
		 * Conditional statement.
		 * @since 2.3.4
		 */
		$add_tagline = $this->get_option( 'homepage_tagline' ) ? $this->get_option( 'homepage_tagline' ) : 0;

		/**
		 * Add tagline or not based on option
		 *
		 * @since 2.2.2
		 */
		if ( $add_tagline ) {
			/**
			 * Tagline based on option.
			 *
			 * @since 2.3.8
			 */
			$tagline = (string) $this->get_option( 'homepage_title_tagline' );
			$title = ! empty( $tagline ) ? $tagline : $this->get_blogdescription();
		} else {
			$title = '';
		}

		/**
		 * Render from function
		 * @since 2.2.8
		 */
		$title_for_home = $this->title_for_home( '', $get_custom_field, false );
		$blogname = ! empty( $title_for_home ) ? $title_for_home : $this->get_blogname();

		if ( empty( $seplocation_home ) || $seplocation_home !== 'left' || $seplocation_home !== 'right' ) {
			/**
			 * Applies filters the_seo_framework_title_seplocation_front : string the home page title location.
			 */
			$seplocation = (string) apply_filters( 'the_seo_framework_title_seplocation_front', $this->get_option( 'home_title_location' ) );
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return array(
			'title' => $title,
			'blogname' => $blogname,
			'add_tagline' => $add_tagline,
			'seplocation' => $seplocation
		);
	}

	/**
	 * Gets the title for the static home page.
	 *
	 * @since 2.2.8
	 *
	 * @param string $home_title The fallback title.
	 * @param bool $get_custom_field Fetch Title from Custom Fields.
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @return string The Title.
	 */
	public function title_for_home( $home_title = '', $get_custom_field = true, $escape = false ) {

		/**
		 * Get blogname title based on option
		 *
		 * @since 2.2.2
		 */
		$home_title_option = (string) $this->get_option( 'homepage_title' );
		$home_title = ! empty( $home_title_option ) ? $home_title_option : $home_title;

		/**
		 * Fetch from Home Page InPost SEO Box if empty.
		 *
		 * @since 2.2.4
		 *
		 * Add home is page check.
		 * @since 2.2.5
		 *
		 * Add get custom Inpost field check
		 * @since 2.3.4
		 */
		if ( $get_custom_field && 'page' === get_option( 'show_on_front' ) && empty( $home_title ) ) {
			$custom_field = $this->get_custom_field( '_genesis_title' );
			$home_title = ! empty( $custom_field ) ? (string) $custom_field : $home_title;
		}

		if ( $escape ) {
			$home_title = wptexturize( $home_title );
			$home_title = convert_chars( $home_title );
			$home_title = esc_html( $home_title );
			$home_title = capital_P_dangit( $home_title );
		}

		return (string) $home_title;
	}

	/**
	 * Gets the title for Category, Tag or Taxonomy
	 *
	 * @since 2.2.8
	 *
	 * @param string $title the fallback title.
	 * @param int $term_id The term ID.
	 * @param string $taxonomy The taxonomy name.
	 * @param bool $escape Parse Title through saninitation calls.
	 *
	 * @todo put args in array.
	 *
	 * @return string The Title.
	 */
	public function title_for_terms( $title = '', $term_id = '', $taxonomy = '', $escape = false ) {

		if ( '' !== $term_id && '' !== $taxonomy )
			$term = get_term( $term_id, $taxonomy, OBJECT, 'raw' );

		if ( isset( $term ) || is_category() || is_tag() ) {

			if ( ! isset( $term ) ) {
				global $wp_query;

				$term = $wp_query->get_queried_object();
			}

			$title = ! empty( $term->admeta['doctitle'] ) ? $term->admeta['doctitle'] : $title;

			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $title ) && isset( $term->meta['doctitle'] ) )
				$title = ! empty( $term->meta['doctitle'] ) ? $term->meta['doctitle'] : $title;

			if ( empty( $title ) )
				$title = $this->get_the_real_archive_title( $term );

		} else if ( is_tax() ) {

			if ( ! isset( $term ) ) {
				$term  = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			}

			$title = ! empty( $term->admeta['doctitle'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->admeta['doctitle'] ) ) : $title;
			$flag = $term->admeta['saved_flag'] != '0' ? true : false;

			if ( ! $flag && empty( $title ) && isset( $term->meta['doctitle'] ) )
				$title = ! empty( $term->meta['doctitle'] ) ? wp_kses_stripslashes( wp_kses_decode_entities( $term->meta['doctitle'] ) ) : $title;

			if ( empty( $title ) )
				$title = $this->get_the_real_archive_title( $term );

		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return (string) $title;
	}

	/**
	 * Gets the title from custom field
	 *
	 * @since 2.2.8
	 *
	 * @param string $title the fallback title.
	 * @param bool $escape Parse Title through saninitation calls.
	 * @param int $id The Post ID.
	 *
	 * @return string The Title.
	 */
	public function title_from_custom_field( $title = '', $escape = false, $id = null ) {

		$id = isset( $id ) ? $id : $this->get_the_real_ID();

		/**
		 * Create something special for blog page.
		 * Only if it's not the home page.
		 *
		 * @since 2.2.8
		 */
		if ( $this->is_blog_page( $id ) ) {
			//* Posts page title.
			$title = $this->get_custom_field( '_genesis_title', $id ) ? $this->get_custom_field( '_genesis_title', $id ) : get_the_title( $id );
		} else {
			//* Get title from custom field, empty it if it's not there to override the default title
			$title = $this->get_custom_field( '_genesis_title', $id ) ? $this->get_custom_field( '_genesis_title', $id ) : $title;
		}

		/**
		 * Fetch Title from WordPress page title input.
		 */
		if ( empty( $title ) ) {
			$post = get_post( $id, OBJECT );
			$title = isset( $post->post_title ) ? $post->post_title : '';
		}

		if ( $escape ) {
			$title = wptexturize( $title );
			$title = convert_chars( $title );
			$title = esc_html( $title );
			$title = capital_P_dangit( $title );
		}

		return (string) $title;
	}


	/**
	 * Get the archive Title.
	 *
	 * WordPress core function 4.1.0
	 *
	 * @since 2.3.6
	 */
	public function get_the_archive_title() {

		//* Return WP Core function.
		if ( function_exists( 'get_the_archive_title' ) )
			return get_the_archive_title();

		if ( is_category() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Category: %s', 'autodescription' ), single_cat_title( '', false ) );
		} elseif ( is_tag() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Tag: %s', 'autodescription' ), single_tag_title( '', false ) );
		} elseif ( is_author() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Author: %s', 'autodescription' ), '<span class="vcard">' . get_the_author() . '</span>' );
		} elseif ( is_year() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Year: %s', 'autodescription' ), get_the_date( _x( 'Y', 'yearly archives date format', 'autodescription' ) ) );
		} elseif ( is_month() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Month: %s', 'autodescription' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'autodescription' ) ) );
		} elseif ( is_day() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Day: %s', 'autodescription' ), get_the_date( _x( 'F j, Y', 'daily archives date format', 'autodescription' ) ) );
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Asides', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Galleries', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Images', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Videos', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Quotes', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Links', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Statuses', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Audio', 'post format archive title', 'autodescription' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				/* translators: Front-end output. */
				$title = _x( 'Chats', 'post format archive title', 'autodescription' );
			}
		} elseif ( is_post_type_archive() ) {
			/* translators: Front-end output. */
			$title = sprintf( __( 'Archives: %s' ), post_type_archive_title( '', false ) );
		} elseif ( is_tax() ) {
			$tax = get_taxonomy( get_queried_object()->taxonomy );
			/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
			$title = sprintf( __( '%1$s: %2$s', 'autodescription' ), $tax->labels->singular_name, single_term_title( '', false ) );
		} else {
			/* translators: Front-end output. */
			$title = __( 'Archives', 'autodescription' );
		}

		/**
		* Filter the archive title.
		*
		* @since 4.1.0
		*
		* @param string $title Archive title to be displayed.
		*/
		return apply_filters( 'get_the_archive_title', $title );
	}

	/**
	 * Get the archive Title, including filter. Also works in admin.
	 * Based from WordPress core function 4.1.0
	 *
	 * @param object $term The Term object.
	 *
	 * @since 2.5.2.2
	 */
	public function get_the_real_archive_title( $term = null ) {

		if ( ! isset( $term ) )
			$term = get_queried_object();

		if ( isset( $term ) || is_archive() ) {

			/**
			 * Applies filters the_seo_framework_the_archive_title : {
			 *		string Title to short circuit title output.
			 * 		@param object $term The Term object.
			 *	}
			 *
			 * @since 2.5.2.2
			 */
			$title = (string) apply_filters( 'the_seo_framework_the_archive_title', null, $term );

			if ( isset( $title ) && ! empty( $title ) )
				return $title;

			/**
			 * Applies filters the_seo_framework_use_archive_title_prefix : {
			 *		Boolean true to add prefix.
			 * 		@param object $term The Term object.
			 *	}
			 *
			 * @since 2.5.2.2
			 */
			$prefix = (bool) apply_filters( 'the_seo_framework_use_archive_title_prefix', true, $term );

			if ( is_admin() ) {
				$tax_type = $term->taxonomy;

				/**
				 * Dynamically fetch the term name.
				 *
				 * @since 2.3.1
				 */
				$term_labels = $this->get_tax_labels( $tax_type );

				if ( isset( $term_labels ) )
					$singular_name = $term_labels->singular_name;

				$title = isset( $term->name ) ? $term->name : '';
				/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
				$title = $prefix ? sprintf( __( '%1$s: %2$s', 'autodescription' ), $singular_name, $title ) : $title;
			} else {
				if ( is_category() ) {
					$title = single_cat_title( '', false );
					/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Category: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_tag() ) {
					$title = single_cat_title( '', false );
						/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Tag: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_author() ) {
					$title = get_the_author();
						/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Author: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_year() ) {
					/* translators: Front-end output. */
					$title = get_the_date( _x( 'Y', 'yearly archives date format', 'autodescription' ) );
					/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Year: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_month() ) {
					/* translators: Front-end output. */
					$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'autodescription' ) );
					/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Month: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_day() ) {
					/* translators: Front-end output. */
					$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'autodescription' ) );
					/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Day: %s', 'autodescription' ), $title ) : $title;
				} else if ( is_tax( 'post_format' ) ) {
					if ( is_tax( 'post_format', 'post-format-aside' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Asides', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-gallery' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Galleries', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-image' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Images', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-video' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Videos', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-quote' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Quotes', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-link' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Links', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-status' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Statuses', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-audio' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Audio', 'post format archive title', 'autodescription' );
					} else if ( is_tax( 'post_format', 'post-format-chat' ) ) {
						/* translators: Front-end output. */
						$title = _x( 'Chats', 'post format archive title', 'autodescription' );
					}
				} else if ( is_post_type_archive() ) {
					$title = post_type_archive_title( '', false );
					/* translators: Front-end output. */
					$title = $prefix ? sprintf( __( 'Archives: %s' ), $title ) : $title;
				} else if ( is_tax() ) {
					$tax = get_taxonomy( get_queried_object()->taxonomy );

					$title = single_term_title( '', false );
					/* translators: Front-end output. 1: Taxonomy singular name, 2: Current taxonomy term */
					$title = $prefix ? sprintf( __( '%1$s: %2$s', 'autodescription' ), $tax->labels->singular_name, $title ) : $title;
				} else {
					/* translators: Front-end output. */
					$title = __( 'Archives', 'autodescription' );
				}
			}

			return $title;
		}

		//* Not a taxonomy.
		return '';
	}

}
