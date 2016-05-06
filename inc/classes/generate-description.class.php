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
	 * Whether we're parsing the manual Excerpt for the automated description.
	 *
	 * @since 2.6.0
	 *
	 * @var bool Using manual excerpt.
	 */
	protected $using_manual_excerpt = false;

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

		//* Add the term to the arguments, if any.
		$args = $this->get_term_for_args( $args, $args['id'], $args['taxonomy'] );

		if ( $args['get_custom_field'] && empty( $description ) ) {
			//* Fetch from options, if any.
			$description = (string) $this->description_from_custom_field( $args, false );

			//* We've already checked the custom fields, so let's remove the check in the generation.
			$args['get_custom_field'] = false;
		}

		//* Still no description found? Create an auto description based on content.
		if ( empty( $description ) || ! is_scalar( $description ) )
			$description = $this->generate_description_from_id( $args, false );

		/**
		 * Beautify.
		 * @since 2.3.4
		 */
		$description = $this->escape_description( $description );

		return $description;
	}

	/**
	 * Escapes and beautifies description.
	 *
	 * @param string $description The description to escape and beautify.
	 *
	 * @since 2.5.2
	 *
	 * @return string Escaped and beautified description.
	 */
	public function escape_description( $description = '' ) {

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
	 *
	 * @since 2.6.0
	 * @return array $args parsed args.
	 */
	public function reparse_description_args( $args = array() ) {

		$default_args = $this->parse_description_args( '', '', true );

		if ( is_array( $args ) ) {
			 if ( empty( $args ) ) {
				$args = $default_args;
			} else {
				$args = $this->parse_description_args( $args, $default_args );
			}
		} else {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', '2.5.0' );
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
		if ( empty( $description ) && empty( $args['taxonomy'] ) )
			$description = $this->get_custom_singular_description( $args['id'] );

		//* Archive Description.
		if ( empty( $description ) )
			$description = $this->get_custom_archive_description( $args );

		if ( $escape && $description )
			$description = $this->escape_description( $description );

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
	 * @param array $args
	 *
	 * @since 2.6.0
	 *
	 * @return string The Description
	 */
	protected function get_custom_archive_description( $args ) {

		$description = '';

		if ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() ) {

				$args = $this->reparse_description_args( $args );
				$term = $args['term'];

				if ( isset( $term->admeta['description'] ) )
					$description = empty( $term->admeta['description'] ) ? $description : $term->admeta['description'];

				$flag = isset( $term->admeta['saved_flag'] ) ? $this->is_checked( $term->admeta['saved_flag'] ) : false;

				if ( false === $flag && empty( $description ) && isset( $term->meta['description'] ) )
					$description = empty( $term->meta['description'] ) ? $description : $term->meta['description'];
			}

			if ( $this->is_tax() ) {
				$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );

				if ( isset( $term->admeta['description'] ) )
					$description = empty( $term->admeta['description'] ) ? $description : wp_kses_stripslashes( wp_kses_decode_entities( $term->admeta['description'] ) );

				$flag = isset( $term->admeta['saved_flag'] ) ? $this->is_checked( $term->admeta['saved_flag'] ) : false;

				if ( false === $flag && empty( $description ) && isset( $term->meta['description'] ) )
					$description = empty( $term->meta['description'] ) ? $description : $term->meta['description'];
			}

			if ( $this->is_author() ) {
				$user_description = get_the_author_meta( 'meta_description', (int) get_query_var( 'author' ) );

				$description = $user_description ? $user_description : $description;
			}
		}


		return $description;
	}

	/**
	 * Generate description from content.
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

		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, true, $debug_key = microtime(true), get_defined_vars() );

		/**
		 * Applies filters bool 'the_seo_framework_enable_auto_description' : Enable or disable the description.
		 *
		 * @since 2.5.0
		 */
		$autodescription = (bool) apply_filters( 'the_seo_framework_enable_auto_description', true );
		if ( false === $autodescription )
			return '';

		$description = $this->generate_the_description( $args, false );

		if ( $escape )
			$description = $this->escape_description( $description );

		if ( $this->the_seo_framework_debug ) $this->debug_init( __CLASS__, __FUNCTION__, false, $debug_key, array( 'description' => $description, 'transient_key' => $this->auto_description_transient ) );

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
	 * @param bool $escape Whether to escape the description.
	 *
	 * @staticvar string $title
	 *
	 * @return string The description.
	 */
	protected function generate_the_description( $args, $escape = true ) {

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		$args = $this->reparse_description_args( $args );

		//* Home Page description
		if ( $args['is_home'] || $this->is_front_page() || $this->is_static_frontpage( $args['id'] ) )
			return $this->generate_home_page_description( $args['get_custom_field'] );

		if ( ! isset( $args['term'] ) )
			$args = $this->get_term_for_args( $args, $args['id'], $args['taxonomy'] );

		$term = $args['term'];

		//* Whether the post ID has a manual excerpt.
		if ( empty( $term ) && has_excerpt( $args['id'] ) )
			$this->using_manual_excerpt = true;

		$title_on_blogname = $this->generate_description_additions( $args['id'], $term, false );
		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];
		$sep = $title_on_blogname['sep'];

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
			 * Get max char length.
			 * Default to 200 when $args['social'] as there are no additions.
			 */
			$additions = trim( $title . " $on " . $blogname );
			//* If there are additions, add a trailing space.
			if ( $additions )
				$additions .= " ";

			$max_char_length_normal = 155 - mb_strlen( html_entity_decode( $additions ) );
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
			if ( $excerpt['social'] ) {
				$description = $excerpt['social'];
			} else {
				//* No social description if nothing is found.
				$description = '';
			}
		} else {

			if ( empty( $excerpt['normal'] ) ) {
				//* Fetch additions ignoring options.

				$title_on_blogname = $this->generate_description_additions( $args['id'], $term, true );
				$title = $title_on_blogname['title'];
				$on = $title_on_blogname['on'];
				$blogname = $title_on_blogname['blogname'];
				$sep = $title_on_blogname['sep'];
			}

			$title_on_blogname = trim( sprintf( _x( '%1$s %2$s %3$s', '1: Title, 2: on, 3: Blogname', 'autodescription' ), $title, $on, $blogname ) );

			if ( $excerpt['normal'] ) {
				$description = sprintf( _x( '%1$s %2$s %3$s', '1: Title on Blogname, 2: Separator, 3: Excerpt', 'autodescription' ), $title_on_blogname, $sep, $excerpt['normal'] );
			} else {
				//* We still add the additions when no excerpt has been found.
				// i.e. home page or empty/shortcode filled page.
				$description = $title_on_blogname;
			}
		}

		if ( $escape )
			$description = $this->escape_description( $description );

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
			if ( $description )
				return $description;
		}

		$title_on_blogname = $this->generate_description_additions( $id, '', true );

		$title = $title_on_blogname['title'];
		$on = $title_on_blogname['on'];
		$blogname = $title_on_blogname['blogname'];

		return $description = sprintf( '%s %s %s', $title, $on, $blogname );
	}

	/**
	 * Whether to add description additions. (╯°□°）╯︵ ┻━┻
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @param int $id The current page or post ID.
	 * @param object|emptystring $term The current Term.
	 *
	 * @return bool
	 */
	public function add_description_additions( $id = '', $term = '' ) {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Applies filters the_seo_framework_add_description_additions : {
		 *		@param bool true to add prefix.
		 * 		@param int $id The Term object ID or The Page ID.
		 * 		@param object $term The Term object.
		 *	}
		 *
		 * @since 2.6.0
		 */
		$filter = (bool) apply_filters( 'the_seo_framework_add_description_additions', true, $id, $term );
		$option = (bool) $this->get_option( 'description_additions' );
		$excerpt = ! $this->using_manual_excerpt;

		return $cache = $option && $filter && $excerpt ? true : false;
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
	 * @param bool $ignore Whether to ignore options and filters.
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
	public function generate_description_additions( $id = '', $term = '', $ignore = false ) {

		if ( $ignore || $this->add_description_additions( $id, $term ) ) {

			static $title = array();
			if ( ! isset( $title[$id] ) )
				$title[$id] = $this->generate_description_title( $id, $term, $ignore );

			if ( $ignore || $this->is_option_checked( 'description_blogname' ) ) {

				static $on = null;
				if ( is_null( $on ) ) {
					/* translators: Front-end output. */
					$on = _x( 'on', 'Placement. e.g. Post Title "on" Blog Name', 'autodescription' );
				}

				//* Already cached.
				$blogname = $this->get_blogname();
			} else {
				$on = '';
				$blogname = '';
			}

			//* Already cached.
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

		if ( $page_on_front || $this->is_static_frontpage( $id ) ) {
			$tagline = $this->get_option( 'homepage_title_tagline' );
			$title = $tagline ? $tagline : $this->get_blogdescription();
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
				$title = $this->title( '', '', '', array( 'term_id' => $id, 'notagline' => true, 'description_title' => true, 'escape' => false ) );

				/**
				 * @TODO create option.
				 * @priority medium 2.8.0+
				 */
				/* translators: Front-end output. */
				$title = __( 'Latest posts:', 'autodescription' ) . ' ' . $title;
			} else if ( $term && is_object( $term ) ) {
				//* We're on a taxonomy now.

				if ( isset( $term->admeta['doctitle'] ) && $term->admeta['doctitle'] ) {
					$title = $term->admeta['doctitle'];
				} else if ( isset( $term->name ) && $term->name ) {
					$title = $term->name;
				} else if ( isset( $term->slug ) && $term->slug ) {
					$title = $term->slug;
				}
			} else {
				//* We're on a page now.
				$title = $this->title( '', '', '', array( 'term_id' => $id, 'notagline' => true, 'description_title' => true, 'escape' => false ) );
			}
		}

		/**
		 * Use Untitled on empty titles.
		 * @since 2.2.8
		 */
		/* translators: Front-end output. */
		$title = empty( $title ) ? $this->untitled() : trim( $title );

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
	public function generate_excerpt( $page_id, $term = '', $max_char_length = 154 ) {

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
			$excerptlength_cache[$page_id][$term_id] = mb_strlen( $excerpt );

		//* Fetch the length from cache.
		$excerpt_length = $excerptlength_cache[$page_id][$term_id];

		//* Trunculate if the excerpt is longer than the max char length
		$excerpt = $this->trim_excerpt( $excerpt, $excerpt_length, $max_char_length );

		return (string) $excerpt;
	}

	/**
	 * Trim the excerpt.
	 *
	 * @param string $excerpt The untrimmed excerpt.
	 * @param int $excerpt_length The current excerpt length.
	 * @param int $max_char_length At what point to shave off the excerpt.
	 *
	 * @since 2.6.0
	 *
	 * @return string The trimmed excerpt.
	 */
	protected function trim_excerpt( $excerpt, $excerpt_length, $max_char_length ) {

		if ( $excerpt_length > $max_char_length ) {

			//* Cut string to fit $max_char_length.
			$sub_ex = mb_substr( $excerpt, 0, $max_char_length );
			$sub_ex = trim( html_entity_decode( $sub_ex ) );

			//* Split words in array separated by delimiter.
			$ex_words = explode( ' ', $sub_ex );

			//* Count to total words in the excerpt.
			$ex_total = count( $ex_words );

			//* Slice the complete excerpt and count the amount of words.
			$extra_ex_words = explode( ' ', trim( $excerpt ), $ex_total + 1 );
			$extra_ex_total = count( $extra_ex_words ) - 1;
			unset( $extra_ex_words[ $extra_ex_total ] );

			//* Calculate if last word exceeds.
			if ( $extra_ex_total >= $ex_total ) {
				$ex_cut = mb_strlen( $ex_words[ $ex_total - 1 ] );

				if ( $extra_ex_total > $ex_total ) {
					/**
					 * There are more words in the trimmed excerpt than the compared total excerpt.
					 * Remove the exceeding word.
					 */
					$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
				} else {
					/**
					 * The amount of words are the same in the comparison.
					 * Calculate if the chacterers are exceeding.
					 */
					$ex_extra_cut = mb_strlen( $extra_ex_words[ $extra_ex_total - 1 ] );

					if ( $ex_extra_cut > $ex_cut ) {
						//* Final word is falling off. Remove it.
						$excerpt = mb_substr( $sub_ex, 0, - $ex_cut );
					} else {
						//* We're all good here, continue.
						$excerpt = $sub_ex;
					}
				}
			}

			//* Remove comma's and spaces.
			$excerpt = trim( $excerpt, ' ,' );

			//* Fetch last character.
			$last_char = substr( $excerpt, -1 );

			$stops = array( '.', '?', '!' );
			//* Add three dots if there's no full stop at the end of the excerpt.
			if ( ! in_array( $last_char, $stops ) )
				$excerpt .= '...';

		}

		return $excerpt;
	}

}
