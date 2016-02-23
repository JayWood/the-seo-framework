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
 * Class AutoDescription_Generate_Description
 *
 * Generates Description SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Description extends AutoDescription_Generate {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Create description
	 *
	 * @param string $description the description.
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 *
	 * @since 1.0.0
	 *
	 * @return string The description
	 */
	public function generate_description( $description = '', $args = array() ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		if ( $args['get_custom_field'] && empty( $description ) ) {
			//* Fetch from options, if any.
			$description = (string) $this->description_from_custom_field( $args, false );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || ! is_string( $description ) )
			$description = $this->generate_description_from_id( $args, false );

		/**
		 * Beautify.
		 * @since 2.3.4
		 */
		$description = wptexturize( $description );
		$description = convert_chars( $description );
		$description = esc_html( $description );
		$description = capital_P_dangit( $description );
		$description = trim( $description );

		return $description;
	}

	/**
	 * Parse and sanitize description args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_description_args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 *
	 * @since 2.5.0
	 * @return array $args parsed args.
	 */
	public function parse_description_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'id' 				=> $this->get_the_real_ID(),
				'taxonomy'			=> '',
				'is_home'			=> false,
				'get_custom_field' 	=> true,
				'social' 			=> false,
			);

			$defaults = (array) apply_filters( 'the_seo_framework_description_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['id'] 				= isset( $args['id'] ) 					? (int) $args['id'] 				: $defaults['id'];
		$args['taxonomy'] 			= isset( $args['taxonomy'] ) 			? (string) $args['taxonomy'] 		: $defaults['taxonomy'];
		$args['is_home'] 			= isset( $args['is_home'] ) 			? (bool) $args['is_home'] 			: $defaults['is_home'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['social'] 			= isset( $args['social'] ) 				? (bool) $args['social'] 			: $defaults['social'];

		return $args;
	}

	/**
	 * Reparse description args.
	 *
	 * @param array $args required The passed arguments.
	 * @param int $line the line number the function is called.
	 *
	 * @since 2.6.0
	 * @return array $args parsed args.
	 */
	public function reparse_description_args( $args = array(), $line = 0 ) {

		$default_args = $this->parse_description_args( '', '', true );

		if ( is_array( $args ) ) {
			 if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_description_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', '2.5.0', __LINE__ );
			$args = $default_args;
		}

		return $args;
	}

	/**
	 * Create description
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * }
	 * @param bool $escape Escape the output if true.
	 *
	 * @since 2.4.1
	 *
	 * @return string|mixed The description, might be unsafe for html output.
	 */
	public function description_from_custom_field( $args = array(), $escape = true ) {
		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* HomePage Description.
		$description = $this->get_custom_homepage_description( $args );

		//* Singular Description.
		$description = empty( $description ) ? $this->get_custom_singular_description( $args['id'] ) : $description;

		//* Archive Description.
		$description = empty( $description ) ? $this->get_custom_archive_description() : $description;

		if ( $escape && '' !== $description ) {
			$description = wptexturize( $description );
			$description = convert_chars( $description );
			$description = esc_html( $description );
			$description = capital_P_dangit( $description );
			$description = trim( $description );
		}

		return $description;
	}

	/**
	 * Fetch HomePage Description from custom field.
	 *
	 * @access protected
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param array $args Description args.
	 *
	 * @since 2.6.0
	 *
	 * @return string The Description
	 */
	protected function get_custom_homepage_description( $args ) {

		$description = '';

		if ( $args['is_home'] || $this->is_front_page() || ( empty( $args['taxonomy'] ) && $this->is_static_frontpage( $args['id'] ) ) ) {
			$homedesc = $this->get_option( 'homepage_description' );
			$description = $homedesc ? $homedesc : '';
		}

		return $description;
	}

	/**
	 * Fetch Singular Description from custom field.
	 *
	 * @access protected
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @param int $id The page ID.
	 *
	 * @since 2.6.0
	 *
	 * @return string The Description
	 */
	protected function get_custom_singular_description( $id ) {

		$description = '';

		if ( $this->is_singular( $id ) ) {
			$custom_desc = $this->get_custom_field( '_genesis_description', $id );
			$description = $custom_desc ? $custom_desc : $description;
		}

		return $description;
	}

	/**
	 * Fetch Archive Description from custom field.
	 *
	 * @access protected
	 * Use $this->description_from_custom_field() instead.
	 *
	 * @since 2.6.0
	 *
	 * @return string The Description
	 */
	protected function get_custom_archive_description() {

		$description = '';

		if ( $this->is_category() || $this->is_tag() ) {
			global $wp_query;

			$term = $wp_query->get_queried_object();

			$description = empty( $term->admeta['description'] ) ? $description : $term->admeta['description'];

			$flag = $this->is_checked( $term->admeta['saved_flag'] );

			if ( false === $flag && empty( $description ) && isset( $term->meta['description'] ) )
				$description = empty( $term->meta['description'] ) ? $description : $term->meta['description'];
		}

		if ( $this->is_tax() ) {
			$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$description = empty( $term->admeta['description'] ) ? $description : wp_kses_stripslashes( wp_kses_decode_entities( $term->admeta['description'] ) );

			$flag = $this->is_checked( $term->admeta['saved_flag'] );

			if ( false === $flag && empty( $description ) && isset( $term->meta['description'] ) )
				$description = empty( $term->meta['description'] ) ? $description : $term->meta['description'];
		}

		if ( $this->is_author() ) {
			$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );

			$description = $user_description ? $user_description : $description;
		}

		return $description;
	}

	/**
	 * Generate description from content
	 *
	 * @since 2.3.3
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 * @param bool $escape Escape output when true.
	 *
	 * @return string $output The description.
	 */
	public function generate_description_from_id( $args = array(), $escape = true ) {

		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, func_get_args() );

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description' : Enable or disable the description.
		 *
		 * @since 2.5.0
		 */
		$autodescription = (bool) apply_filters( 'the_seo_framework_enable_auto_description', true );
		if ( false === $autodescription )
			return '';

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		$description = $this->generate_the_description( $args );

		if ( $escape ) {
			$description = wptexturize( $description );
			$description = convert_chars( $description );
			$description = esc_html( $description );
			$description = capital_P_dangit( $description );
			$description = trim( $description );
		}

		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, array( 'description' => $description ) );

		return (string) $description;
	}

	/**
	 * Generate description from content
	 *
	 * @since 2.6.0
	 *
	 * @param array $args description args : {
	 * 		@param int $id the term or page id.
	 * 		@param string $taxonomy taxonomy name.
	 * 		@param bool $is_home We're generating for the home page.
	 * 		@param bool $get_custom_field Do not fetch custom title when false.
	 * 		@param bool $social Generate Social Description when true.
	 * }
	 *
	 * @staticvar string $title
	 *
	 * @return string The description.
	 */
	protected function generate_the_description( $args ) {

		//* Home Page description
		if ( $this->is_front_page() || $args['is_home'] || $this->is_static_frontpage( $args['id'] ) )
			return $this->generate_home_page_description( $args['get_custom_field'] );

		$term = '';
		if ( is_admin() && 0 !== $args['id'] ) {
			//* Fetch taxonomy from args.
			//* This only runs in admin, because we provide these arg values there.
			if ( empty( $args['taxonomy'] ) ) {
				//* Test other admin screens.
				global $current_screen;

				if ( isset( $current_screen->taxonomy ) && $current_screen->taxonomy ) {
					//* Fetch taxonomy in admin.
					$args['taxonomy'] = $current_screen->taxonomy;
					$term = get_term_by( 'id', $args['id'], $args['taxonomy'], OBJECT );
				}
			} else {
				$term = get_term_by( 'id', $args['id'], $args['taxonomy'], OBJECT );
			}
		} else if ( $this->is_archive() && false === $this->is_front_page() && ! $this->is_singular( $args['id'] ) ) {
			//* Fetch Taxonomy through wp_query on front-end
			global $wp_query;

			$term = $wp_query->get_queried_object();
			$args['taxonomy'] = isset( $term->taxonomy ) ? $term->taxonomy : '';
		}

		$title_on_blogname = $this->generate_description_additions( $args['id'], $term, false );
		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];
		$sep = $title_on_blogname['sep'];

		//* Whether to add "on blogname"
		$description_additions = $this->get_option( 'description_blogname' );

		/**
		 * Setup transient.
		 */
		$this->setup_auto_description_transient( $args['id'], $args['taxonomy'] );

		/**
		 * Cache the generated description within a transient.
		 *
		 * @since 2.3.3
		 *
		 * Put inside a different function.
		 * @since 2.3.4
		 */
		$excerpt = $this->get_transient( $this->auto_description_transient );
		if ( false === $excerpt ) {

			/**
			 * Get max char length
			 * 149 will account for the added (single char) ... and two spaces around $on and the separator + 2 spaces around the separator: makes 155
			 *
			 * 151 will count for the added (single char) ... and the separator + 2 spaces around the separator: makes 155
			 *
			 * Default to 200 when $args['social'] as there are no additions.
			 */
			$max_char_length_normal = $description_additions ? (int) 149 - mb_strlen( html_entity_decode( $title . $on . $blogname ) ) : (int) 151 - mb_strlen( html_entity_decode( $title ) );
			$max_char_length_social = 200;

			//* Generate Excerpts.
			$excerpt_normal = $this->generate_excerpt( $args['id'], $term, $max_char_length_normal );
			$excerpt_social = $this->generate_excerpt( $args['id'], $term, $max_char_length_social );

			//* Put in array to be accessed later.
			$excerpt = array(
				'normal' => $excerpt_normal,
				'social' => $excerpt_social
			);

			/**
			 * Transient expiration: 1 week.
			 * Keep the description for at most 1 week.
			 *
			 * 60s * 60m * 24h * 7d
			 */
			$expiration = 60 * 60 * 24 * 7;

			$this->set_transient( $this->auto_description_transient, $excerpt, $expiration );
		}

		/**
		 * Check for Social description, don't add blogname then.
		 * Also continues normally if it's the front page.
		 *
		 * @since 2.5.0
		 */
		if ( $args['social'] ) {
			/**
			 * @since 2.5.2
			 */
			$excerpt_exists = empty( $excerpt['social'] ) ? false : true;

			if ( $excerpt_exists ) {
				$description = $excerpt['social'];
			} else {
				$description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
			}
		} else {
			$excerpt_exists = empty( $excerpt['normal'] ) ? false : true;

			if ( true === $excerpt_exists ) {
				if ( $description_additions ) {
					$description = (string) sprintf( '%s %s %s %s %s', $title, $on, $blogname, $sep, $excerpt['normal'] );
				} else {
					$description = (string) sprintf( '%s %s %s', $title, $sep, $excerpt['normal'] );
				}
			} else {
				//* We still add the additions when no excerpt has been found.
				// i.e. home page or empty/shortcode filled page.
				$description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
			}
		}

		return $description;
	}

	/**
	 * Generate the home page description.
	 *
	 * @param bool $custom_field whether to check the Custom Field.
	 *
	 * @since 2.6.0
	 *
	 * @return string The description.
	 */
	public function generate_home_page_description( $custom_field = true ) {

		$id = $this->get_the_front_page_ID();

		/**
		 * Return early if description is found from Home Page Settings.
		 * Only do so when $args['get_custom_field'] is true.
		 * @since 2.3.4
		 */
		if ( $custom_field ) {
			$description = $this->get_custom_homepage_description( array( 'is_home' => true ) );
			if ( '' !== $description )
				return $description;
		}

		$title_on_blogname = $this->generate_description_additions( $id, '', true );

		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];

		return $description = (string) sprintf( '%s %s %s', $title, $on, $blogname );
	}

	/**
	 * Whether to add description additions.
	 *
	 * Applies filters the_seo_framework_add_description_additions : boolean
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function add_description_additions() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		return $cache = (bool) apply_filters( 'the_seo_framework_add_description_additions', true );
	}

	/**
	 * Get Description Separator.
	 *
	 * Applies filters the_seo_framework_description_separator
	 * @since 2.3.9
	 *
	 * @staticvar $sep
	 * @since 2.6.0
	 *
	 * @return string The Separator
	 */
	public function get_description_separator() {

		static $sep = null;

		if ( isset( $sep ) )
			return $sep;

		return $sep = (string) apply_filters( 'the_seo_framework_description_separator', $this->get_separator( 'description' ) );
	}

	/**
	 * Generate description additions.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param int $id The post or term ID
	 * @param object|empty $term The term object
	 * @param bool $page_on_front Whether the page is on front.
	 *
	 * @staticvar array $title string of titles.
	 * @staticvar string $on
	 *
	 * @return array : {
	 *		$title		=> The title
	 *		$on 		=> The word separator
	 *		$blogname	=> The blogname
	 *		$sep		=> The separator
	 * }
	 */
	public function generate_description_additions( $id, $term, $page_on_front ) {

		static $title = array();
		static $on = null;

		if ( $page_on_front || $this->add_description_additions() ) {
			if ( ! isset( $title[$id] ) )
				$title[$id] = $this->generate_description_title( $id, $term, $page_on_front );

			if ( ! isset( $on ) ) {
				/* translators: Front-end output. */
				$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
			}

			//* Already cached.
			$blogname = $this->get_blogname();
			$sep = $this->get_description_separator();
		} else {
			$title[$id] = '';
			$on = '';
			$blogname = '';
			$sep = '';
		}

		return array(
			'title' => $title[$id],
			'on' => $on,
			'blogname' => $blogname,
			'sep' => $sep,
		);
	}

	/**
	 * Generates the Title for description.
	 *
	 * @param int $id The page ID.
	 * @param void|object $term The term object.
	 * @param bool $page_on_front If front page.
	 *
	 * @since 2.5.2
	 *
	 * @return string The description title.
	 */
	public function generate_description_title( $id = '', $term = '', $page_on_front = false ) {

		if ( '' === $id )
			$id = $this->get_the_real_ID();

		if ( $page_on_front ) {
			$title = $this->get_blogdescription();
		} else {
			/**
			 * No need to parse these when generating social description.
			 *
			 * @since 2.5.0
			 */
			if ( $this->is_blog_page( $id ) ) {
				/**
				 * We're on the blog page now.
				 * @since 2.2.8
				 */
				$custom_title = $this->get_custom_field( '_genesis_title', $id );
				$title = $custom_title ? $custom_title : $this->title( '', '', '', array( 'term_id' => $id, 'placeholder' => true, 'notagline' => true, 'description_title' => true, 'escape' => false ) );

				// @TODO create option.
				/* translators: Front-end output. */
				$title = __( 'Latest posts:', 'autodescription' ) . ' ' . $title;
			} else if ( '' !== $term && is_object( $term ) ) {
				//* We're on a taxonomy now.

				if ( isset( $term->admeta['doctitle'] ) && '' !== $term->admeta['doctitle'] ) {
					$title = $term->admeta['doctitle'];
				} else if ( isset( $term->name ) && '' !== $term->name ) {
					$title = $term->name;
				} else if ( isset( $term->slug ) && '' !== $term->slug ) {
					$title = $term->slug;
				}
			} else {
				//* We're on a page now.
				$custom_title = $this->get_custom_field( '_genesis_title', $id );
				$title = '' !== $custom_title ? $custom_title : $this->title( '', '', '', array( 'term_id' => $id, 'placeholder' => true, 'notagline' => true, 'description_title' => true, 'escape' => false ) );
			}
		}

		/**
		 * Use Untitled on empty titles.
		 * @since 2.2.8
		 */
		/* translators: Front-end output. */
		$title = empty( $title ) ? __( 'Untitled', 'autodescription' ) : trim( $title );

		return $title;
	}

	/**
	 * Generate the excerpt.
	 *
	 * @param int|string $page_id required : The Page ID
	 * @param object|null $term The Taxonomy Term.
	 * @param int $max_char_length The maximum excerpt char length.
	 *
	 * @since 2.3.4
	 *
	 * @staticvar array $excerpt_cache Holds the excerpt
	 * @staticvar array $excerptlength_cache Holds the excerpt length
	 *
	 * Please note that this does not reflect the actual output becaue the $max_char_length isn't calculated on direct call.
	 */
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 155 ) {

		static $excerpt_cache = array();
		static $excerptlength_cache = array();

		$term_id = isset( $term->term_id ) ? $term->term_id : false;

		//* Put excerpt in cache.
		if ( ! isset( $excerpt_cache[$page_id][$term_id] ) ) {
			if ( $this->is_singular( $page_id ) ) {
				//* We're on the blog page now.
				$excerpt = $this->get_excerpt_by_id( '', $page_id );
			} else if ( $term && is_object( $term ) ) {
				//* We're on a taxonomy now.
				$excerpt = empty( $term->description ) ? $this->get_excerpt_by_id( '', '', $page_id ) : $term->description;
			} else {
				$excerpt = '';
			}

			$excerpt_cache[$page_id][$term_id] = $excerpt;
		}

		//* Fetch excerpt from cache.
		$excerpt = $excerpt_cache[$page_id][$term_id];

		/**
		 * Put excerptlength in cache.
		 * Why cache? My tests have shown that mb_strlen is 1.03x faster than cache fetching.
		 * However, _mb_strlen (compat) is about 1740x slower. And this is the reason it's cached!
		 */
		if ( ! isset( $excerptlength_cache[$page_id][$term_id] ) )
			$excerptlength_cache[$page_id][$term_id] = (int) mb_strlen( $excerpt );

		//* Fetch the length from cache.
		$excerptlength = $excerptlength_cache[$page_id][$term_id];

		// Trunculate if the excerpt is longer than the max char length
		if ( $excerptlength > $max_char_length ) {

			// Cut string to fit $max_char_length.
			$subex = mb_substr( $excerpt, 0, $max_char_length );
			// Split words in array. Boom.
			$exwords = explode( ' ', $subex );
			// Calculate if last word exceeds.
			$excut = - ( mb_strlen( $exwords[ count( $exwords ) - (int) 1 ] ) );

			if ( $excut < (int) 0 ) {
				//* Cut out exceeding word.
				$excerpt = mb_substr( $subex, 0, $excut );
			} else {
				// We're all good here, continue.
				$excerpt = $subex;
			}

			$excerpt = rtrim( $excerpt ) . '...';
		}

		return (string) $excerpt;
	}

}
