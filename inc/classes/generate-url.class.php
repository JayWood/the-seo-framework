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
 * Class AutoDescription_Generate_Url
 *
 * Generates URL and permalink SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Url extends AutoDescription_Generate_Title {

	/**
	 * Wether to slash the url or not. Used when query vars are in url.
	 *
	 * @since 2.6.0
	 *
	 * @var bool Wether to slash the url.
	 */
	protected $url_slashit;

	/**
	 * Wether to add a subdomain to the url.
	 *
	 * @since 2.6.0
	 *
	 * @var string The subdomain.
	 */
	protected $add_subdomain;

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Creates canonical url
	 *
	 * @param string $url the url
	 *
	 * @since 2.4.2
	 * @param array $args : accepted args : {
	 * 			@param bool $paged Return current page URL without pagination if false.
	 * 			@param bool $from_option Get the canonical uri option
	 * 			@param object $post The Post Object.
	 * 			@param bool $external Wether to fetch the current WP Request or get the permalink by Post Object.
	 * 			@param bool $is_term Fetch url for term.
	 * 			@param object $term The term object.
	 * 			@param bool $home Fetch home URL.
	 * 			@param bool $forceslash Fetch home URL and slash it, always.
	 *			@param int $id The Page id.
	 * }
	 *
	 * @since 2.0.0
	 */
	public function the_url( $url = '', $args = array() ) {

		if ( $this->the_seo_framework_debug && false === $this->doing_sitemap ) $this->debug_init( __CLASS__, __FUNCTION__, true, get_defined_vars() );

		//* Reset cache.
		$this->url_slashit = true;
		$this->add_subdomain = '';

		$default_args = $this->parse_url_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.4.2
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', '2.4.2' );
			$args = $default_args;
		} else if ( $args ) {
			$args = $this->parse_url_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		$path = '';
		$scheme = '';

		/**
		 * Trailing slash the post, or not.
		 * @since 2.2.4
		 */
		$slashit = true;

		/**
		 * Fetch permalink if Feed.
		 * @since 2.5.2
		 */
		if ( is_feed() )
			$url = get_permalink( $args['post'] );

		if ( ! $args['home'] && empty( $url ) ) {
			/**
			 * Get url from options
			 * @since 2.2.9
			 */
			if ( $args['get_custom_field'] && $this->is_singular() )
				$url = $this->get_custom_field( '_genesis_canonical_uri' ) ? $this->get_custom_field( '_genesis_canonical_uri' ) : $url;

			if ( empty( $url ) )
				$path = $this->generate_url_path( $args );
		}

		//* Translate the URL.
		$path = $this->get_translation_path( $path, $args['id'], $args['external'] );

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$wpmu_url = $this->the_url_wpmudev_domainmap( $path, true );
			if ( $wpmu_url && is_array( $wpmu_url ) ) {
				$url = $wpmu_url[0];
				$scheme = $wpmu_url[1];
			}
		}

		//* Domain Mapping canonical URL
		if ( empty( $url ) ) {
			$dm_url = $this->the_url_donncha_domainmap( $path, true );
			if ( $dm_url && is_array( $dm_url ) ) {
				$url = $dm_url[0];
				$scheme = $dm_url[1];
			}
		}

		//* Non-domainmap URL
		if ( empty( $url ) ) {
			if ( $args['home'] || '' === $path ) {
				$url = user_trailingslashit( get_option( 'home' ) );
				$slashit = false;
			} else {
				$url = $this->generate_full_url( $path );

				$scheme = is_ssl() ? 'https' : 'http';
			}
		}

		//* Add subdomain, if any.
		if ( '' !== $this->add_subdomain ) {
			$parsed_url = parse_url( $url );
			$url = str_replace( $parsed_url['scheme'] . '://', '', $url );

			//* Put it together.
			$url = $this->add_subdomain . '.' . $url;
		}

		//* URL has been given manually or $args['home'] is true.
		if ( ! isset( $scheme ) )
			$scheme = is_ssl() ? 'https' : 'http';

		$output = $this->set_url_scheme( $url, $scheme );

		if ( $this->url_slashit ) {
			/**
			 * Slash it only if $slashit is true
			 *
			 * @since 2.2.4
			 */
			if ( $slashit && ! $args['forceslash'] )
				$output = user_trailingslashit( $output );

			//* Be careful with the default permalink structure.
			if ( $args['forceslash'] )
				$output = trailingslashit( $output );
		}

		$url = esc_url( $output );

		if ( $this->the_seo_framework_debug && false === $this->doing_sitemap ) $this->debug_init( __CLASS__, __FUNCTION__, false, array( 'url_output' => $url ) );

		return $url;
	}

	/**
	 * Parse and sanitize url args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * @applies filters the_seo_framework_url_args : {
	 * 		@param bool $paged Return current page URL without pagination if false
	 * 		@param bool $from_option Get the canonical uri option
	 * 		@param object $post The Post Object.
	 * 		@param bool $external Wether to fetch the current WP Request or get the permalink by Post Object.
	 * 		@param bool $is_term Fetch url for term.
	 * 		@param object $term The term object.
	 * 		@param bool $home Fetch home URL.
	 * 		@param bool $forceslash Fetch home URL and slash it, always.
	 * }
	 *
	 * @since 2.4.2
	 * @return array $args parsed args.
	 */
	public function parse_url_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'paged' 			=> false,
				'get_custom_field'	=> true,
				'external'			=> false,
				'is_term' 			=> false,
				'post' 				=> null,
				'term'				=> null,
				'home'				=> false,
				'forceslash'		=> false,
				'id'				=> $this->get_the_real_ID()
			);

			//* @since 2.5.0
			$defaults = (array) apply_filters( 'the_seo_framework_url_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['paged'] 				= isset( $args['paged'] ) 				? (bool) $args['paged'] 			: $defaults['paged'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['external'] 			= isset( $args['external'] ) 			? (bool) $args['external'] 			: $defaults['external'];
		$args['is_term'] 			= isset( $args['is_term'] ) 			? (bool) $args['is_term'] 			: $defaults['is_term'];
		$args['get_custom_field'] 	= isset( $args['get_custom_field'] ) 	? (bool) $args['get_custom_field'] 	: $defaults['get_custom_field'];
		$args['post'] 				= isset( $args['post'] ) 				? (object) $args['post'] 			: $defaults['post'];
		$args['term'] 				= isset( $args['term'] ) 				? (object) $args['term'] 			: $defaults['term'];
		$args['home'] 				= isset( $args['home'] ) 				? (bool) $args['home'] 				: $defaults['home'];
		$args['forceslash'] 		= isset( $args['forceslash'] ) 			? (bool) $args['forceslash'] 		: $defaults['forceslash'];
		$args['id'] 				= isset( $args['id'] ) 					? (int) $args['id'] 				: $defaults['id'];

		return $args;
	}

	/**
	 * Generate url from Args.
	 *
	 * @since 2.6.0
	 *
	 * @param array $args the URL args.
	 *
	 * @return string $path
	 */
	public function generate_url_path( $args = array() ) {

		if ( empty( $args ) )
			$args = $this->parse_url_args( '', '', true );

		if ( $args['is_term'] || $this->is_archive() ) {
			$term = $args['term'];

			//* Term or Taxonomy.
			if ( ! isset( $term ) )
				$term = get_queried_object();

			if ( isset( $term->taxonomy ) ) {
				//* Registered Terms and Taxonomies.
				$path = $this->get_relative_term_url( $term, $args['external'] );
			} else if ( ! $args['external'] ) {
				//* Everything else.
				global $wp;
				$path = $wp->request;
			} else {
				//* Nothing to see here...
				$path = '';
			}
		} else {

			$post = $args['post'];

			/**
			 * Fetch post object
			 * @since 2.2.4
			 */
			if ( ! isset( $post ) )
				$post = get_post( $args['id'], OBJECT );

			/**
			 * Reworked to use the $args['id'] check based on get_the_real_ID.
			 * @since 2.6.0
			 */
			if ( isset( $post ) ) {

				$post_id = isset( $post->ID ) ? $post->ID : $args['id'];

				if ( $post_id ) {

					if ( '' === $this->permalink_structure() ) {
						$path = $this->the_url_path_default_permalink_structure( $post );
					} else {

						//* Don't slash draft shortlinks.
						if ( isset( $post->post_status ) && ( 'auto-draft' === $post->post_status || 'draft' === $post->post_status ) )
							$this->url_slashit = false;

						$path = $this->get_relative_url( $post, $args['external'], $post_id );
					}
				}

			}
		}

		if ( isset( $path ) )
			return $path;

		return '';
	}

	/**
	 * Generates relative URL for current post_ID.
	 *
	 * @param object $post The post.
	 * @param bool $external Wether to fetch the WP Request or get the permalink by Post Object.
	 * @param id $post_id The page id.
	 *
	 * @since 2.3.0
	 *
	 * @global object $post
	 *
	 * @return relative Post or Page url.
	 */
	public function get_relative_url( $post = null, $external = false, $post_id = null ) {

		if ( ! isset( $post_id ) ) {
			if ( isset( $post->ID ) )
				$post_id = $post->ID;

			if ( ! isset( $post_id ) && ! $external )
				$post_id = $this->get_the_real_ID();
		}

		if ( ! isset( $post_id ) )
			return '';

		if ( $post_id && ( $external || ! $this->is_home() ) ) {
			$permalink = get_permalink( $post_id );
		} else if ( ! $external ) {
			global $wp;

			if ( isset( $wp->request ) )
				$permalink = $wp->request;
		}

		//* No permalink found.
		if ( ! isset( $permalink ) )
			return '';

		/**
		 * @since 2.4.2
		 */
		$path = $this->set_url_scheme( $permalink, 'relative' );

		return $path;
	}

	/**
	 * Generate full URL from path.
	 *
	 * @since 2.6.0
	 * @staticvar string $home_url The Home URL.
	 * @staticvar string|bool $home_path The Home Directory Path.
	 *
	 * @return string URL the full URL.
	 */
	protected function generate_full_url( $path = '' ) {

		static $home_url = null;
		static $home_path = null;
		static $home_url_slashed = null;

		//* Set up caches.
		if ( is_null( $home_url ) ) {
			$home_url = get_option( 'home' );

			$home_url_parsed = parse_url( $home_url );
			$home_path = isset( $home_url_parsed['path'] ) ? $home_url_parsed['path'] : false;

			$home_url_slashed = trailingslashit( $home_url );
		}

		//* Prevent duplicated first path from Site Address config.
		if ( $home_path ) {
			$count = 1;
			$url = $home_url_slashed . ltrim( str_replace( $home_path, '', $path ), '\/ ' );
		} else {
			$url = $home_url_slashed . ltrim( $path, '\/ ' );
		}

		return $url;
	}

	/**
	 * Generates relative URL for current post_ID for translation plugins.
	 *
	 * @param string $path the current URL path.
	 * @param int $post_id The post ID.
	 * @param bool $external Wether to fetch the WP Request or get the permalink by Post Object.
	 *
	 * @since 2.6.0
	 *
	 * @global object $post
	 *
	 * @return relative Post or Page url.
	 */
	public function get_translation_path( $path = '', $post_id = null, $external = false ) {

		if ( is_object( $post_id ) )
			$post_id = isset( $post_id->ID ) ? $post_id->ID : $this->get_the_real_ID();

		if ( ! isset( $post_id ) )
			$post_id = $this->get_the_real_ID();

		//* Cache the definition.
		static $icl_exists = null;
		if ( ! isset( $icl_exists ) )
			$icl_exists = (bool) defined( 'ICL_LANGUAGE_CODE' );

		//* WPML support.
		if ( $icl_exists )
			$path = $this->get_relative_wmpl_url( $path, $post_id );

		/**
		 * @since 2.5.2
		 */
		static $qt_exists = null;

		if ( ! isset( $qt_exists ) )
			$qt_exists = (bool) class_exists( 'QTX_Translator' );

		//* qTranslate X support. Can't work externally as we can't fetch post current language.
		if ( ! $external && $qt_exists ) {
			static $q_config = null;

			if ( ! isset( $q_config ) )
				global $q_config;

			$mode = $q_config['url_mode'];

			//* Only change URL on Pre-Path mode.
			if ( (int) 2 === $mode ) {

				//* If false, change canonical URL for every page.
				$hide = $q_config['hide_default_language'];

				$current_lang = $q_config['language'];
				$default_lang = $q_config['default_language'];

				//* Add prefix to path.
				if ( ! $hide || $current_lang !== $default_lang )
					$path = '/' . $current_lang . '/' . ltrim( $path, '\/ ' );

			}
		}

		return $path;
	}

	/**
	 * Generate relative WPML url.
	 *
	 * @param string $path The current path.
	 * @param int $post_id The Post ID.
	 *
	 * @staticvar bool $gli_exists
	 *
	 * @since 2.4.3
	 *
	 * @return relative path for WPML urls.
	 */
	public function get_relative_wmpl_url( $path = '', $post_id = '' ) {
		global $sitepress;

		//* Reset cache.
		$this->url_slashit = true;
		$this->add_subdomain = '';

		if ( isset( $sitepress ) ) {

			static $gli_exists = null;

			if ( ! isset( $gli_exists ) )
				$gli_exists = function_exists( 'wpml_get_language_information' );

			if ( $gli_exists ) {

				if ( '' === $post_id )
					$post_id = $this->get_the_real_ID();

				//* Cache default language.
				static $default_lang = null;
				if ( ! isset( $default_lang ) )
					$default_lang = $sitepress->get_default_language();

				/**
				 * Applies filters wpml_post_language_details : array|wp_error
				 *
				 * ... Somehow WPML thought this would be great and understandable.
				 * @since 2.6.0
				 */
				$lang_info = apply_filters( 'wpml_post_language_details', NULL, $post_id );

				if ( is_wp_error( $lang_info ) ) {
					//* Terms and Taxonomies.
					$lang_info = array();

					//* Cache the code.
					static $lang_code = null;
					if ( ! isset( $lang_code ) && defined( 'ICL_LANGUAGE_CODE' ) )
						$lang_code = ICL_LANGUAGE_CODE;

					$lang_info['language_code'] = $lang_code;
				}

				//* If filter isn't used, bail.
				if ( ! isset( $lang_info['language_code'] ) )
					return $path;

				$current_lang = $lang_info['language_code'];

				//* No need to alter URL if we're on default lang.
				if ( $current_lang === $default_lang )
					return $path;

				//* Cache negotiation type.
				static $negotiation_type = null;
				if ( ! isset( $negotiation_type ) )
					$negotiation_type = $sitepress->get_setting( 'language_negotiation_type' );

				switch ( $negotiation_type ) {

					case '1' :
						$contains_path = strpos( $path, '/' . $current_lang . '/' );
						//* Subdirectory
						if ( $contains_path !== false && (int) 0 === $contains_path )
							return $path;
						else
							return $path = trailingslashit( $current_lang ) . ltrim( $path, '\/ ' );
						break;

					case '2' :
						//* Notify cache of subdomain addition.
						$this->add_subdomain = $current_lang;

						//* No need to alter the path.
						return $path;
						break;

					case '3' :
						//* Negotiation type query var.

						//* Don't slash it further.
						$this->url_slashit = false;

						/**
						 * Path must have trailing slash for pagination permalinks to work.
						 * So we remove the query string and add it back with slash.
						 */
						if ( strpos( $path, '?lang=' . $current_lang ) !== false )
							$path = str_replace( '?lang=' . $current_lang, '', $path );

						return trailingslashit( $path ) . '?lang=' . $current_lang;
						break;

				}

			}
		}

		return $path;
	}

	/**
	 * Generates relative URL for current term.
	 *
	 * @global WP_Query object $wp_query
	 * @global WP_Rewrite $wp_rewrite
	 * @global Paged $paged
	 *
	 * @param object $term The term object.
	 * @param bool $no_request wether to fetch the WP Request or get the permalink by Post Object.
	 *
	 * @since 2.4.2
	 *
	 * @return Relative term or taxonomy URL.
	 */
	public function get_relative_term_url( $term = null, $no_request = false ) {

		// We can't fetch the Term object within sitemaps.
		if ( $no_request && is_null( $term ) )
			return '';

		if ( is_null( $term ) ) {
			global $wp_query;
			$term = $wp_query->get_queried_object();
		}

		$paged = $this->paged();

		$taxonomy = $term->taxonomy;

		global $wp_rewrite;
		$termlink = $wp_rewrite->get_extra_permastruct( $taxonomy );

		$slug = $term->slug;
		$t = get_taxonomy( $taxonomy );

		if ( empty( $termlink ) ) {
			if ( 'category' === $taxonomy ) {
				$termlink = '?cat=' . $term->term_id;
			} elseif ( isset( $t->query_var ) && '' !== $t->query_var ) {
				$termlink = "?$t->query_var=$slug";
			} else {
				$termlink = "?taxonomy=$taxonomy&term=$slug";
			}

			if ( $paged )
				$termlink .= '&page=' . $paged;

		} else {
			if ( $t->rewrite['hierarchical'] ) {
				$hierarchical_slugs = array();
				$ancestors = get_ancestors( $term->term_id, $taxonomy, 'taxonomy' );

				foreach ( (array) $ancestors as $ancestor ) {
					$ancestor_term = get_term( $ancestor, $taxonomy );
					$hierarchical_slugs[] = $ancestor_term->slug;
				}

				$hierarchical_slugs = array_reverse( $hierarchical_slugs );
				$hierarchical_slugs[] = $slug;

				$termlink = str_replace( "%$taxonomy%", implode( '/', $hierarchical_slugs ), $termlink );
			} else {
				$termlink = str_replace( "%$taxonomy%", $slug, $termlink );
			}

			if ( $paged )
				$termlink = trailingslashit( $termlink )  . 'page/' . $paged;

			$termlink = user_trailingslashit( $termlink, 'category' );
		}

		$path = $this->set_url_scheme( $termlink, 'relative' );

		return $path;
	}

	/**
	 * Set url scheme.
	 * WordPress core function, without filter.
	 *
	 * @param string $url Absolute url that includes a scheme.
	 * @param string $scheme optional. Scheme to give $url. Currently 'http', 'https', 'login', 'login_post', 'admin', or 'relative'.
	 * @param bool $use_filter
	 *
	 * @since 2.4.2
	 * @return string url with chosen scheme.
	 */
	public function set_url_scheme( $url, $scheme = null, $use_filter = true ) {

		if ( ! isset( $scheme ) ) {
			$scheme = is_ssl() ? 'https' : 'http';
		} else if ( $scheme === 'admin' || $scheme === 'login' || $scheme === 'login_post' || $scheme === 'rpc' ) {
			$scheme = is_ssl() || force_ssl_admin() ? 'https' : 'http';
		} else if ( $scheme !== 'http' && $scheme !== 'https' && $scheme !== 'relative' ) {
			$scheme = is_ssl() ? 'https' : 'http';
		}

		$url = trim( $url );
		if ( substr( $url, 0, 2 ) === '//' )
			$url = 'http:' . $url;

		if ( 'relative' === $scheme ) {
			$url = ltrim( preg_replace( '#^\w+://[^/]*#', '', $url ) );
			if ( $url !== '' && $url[0] === '/' )
				$url = '/' . ltrim( $url , "/ \t\n\r\0\x0B" );

		} else {
			//* This will break if $scheme is set to false.
			$url = preg_replace( '#^\w+://#', $scheme . '://', $url );
		}

		if ( false !== $use_filter )
			return $this->set_url_scheme_filter( $url, $scheme );

		return $url;
	}

	/**
	 * Set URL scheme based on filter.
	 *
	 * @since 2.6.0
	 *
	 * @param string $url The url with scheme.
	 * @param string $scheme The current scheme.
	 *
	 * @return $url with applied filters.
	 */
	public function set_url_scheme_filter( $url, $scheme ) {

		/**
		 * Applies filters the_seo_framework_canonical_force_scheme : Changes scheme.
		 *
		 * Accepted variables:
		 * (string) 'https'		: 	Force https
		 * (bool) true 			: 	Force https
		 * (bool) false			: 	Force http
		 * (string) 'http'		: 	Force http
		 * (string) 'relative' 	:	Scheme relative
		 * (void) null			: 	Do nothing
		 *
		 * @param string $scheme the current used scheme.
		 *
		 * @since 2.4.2
		 */
		$scheme_settings = apply_filters( 'the_seo_framework_canonical_force_scheme', null, $scheme );

		if ( isset( $scheme_settings ) ) {
			if ( 'https' ===  $scheme_settings || 'http' === $scheme_settings || 'relative' === $scheme_settings ) {
				$url = $this->set_url_scheme( $url, $scheme_settings, false );
			} else if ( ! $scheme_settings ) {
				$url = $this->set_url_scheme( $url, 'http', false );
			} else if ( $scheme_setting ) {
				$url = $this->set_url_scheme( $url, 'https', false );
			}
		}

		return $url;
	}

	/**
	 * Creates canonical url for the default permalink structure.
	 *
	 * @param object $post The post.
	 *
	 * @since 2.3.0
	 */
	public function the_url_path_default_permalink_structure( $post = null ) {

		//* Don't slash it.
		$this->url_slashit = false;

		if ( ! $this->is_singular() ) {
			//* We're on a taxonomy
			$object = get_queried_object();

			if ( is_object( $object ) ) {
				if ( $this->is_category() ) {
					$id = $object->term_id;
					$path = '?cat=' . $id;
				} else if ( $this->is_tag() ) {
					$name = $object->name;
					$path = '?tag=' . $id;
				} else if ( $this->is_date() ) {
					global $wp_query;

					$query = $wp_query->query;

					$year = $query->year;
					$month = $query->monthnum ? '&monthnum=' . $query->monthnum : '';
					$day = $query->day ? '&day=' . $query->day : '';

					$path = '?year=' . $year . $month . $day;
				} else if ( $this->is_author() ) {
					$name = $object->author_name;
					$path = '?author=' . $name;
				} else if ( $this->is_tax() ) {
					$name = $object->taxonomy;
					$path = '?taxonomy=' . $name;
				} else {
					$id = $object->ID;
					$path = '?p=' . $id;
				}
			}
		}

		if ( ! isset( $path ) ) {
			if ( isset( $post->ID ) ) {
				$id = $post->ID;
			} else {
				$id = $this->get_the_real_ID();
			}

			$path = '?p=' . $id;
		}

		return $path;
	}

	/**
	 * Try to get an canonical URL when WPMUdev Domain Mapping is active.
	 *
	 * @param string $path The post relative path.
	 *
	 * @since 2.3.0
	 *
	 * @param bool $get_scheme Output array with scheme.
	 * @since 2.4.0
	 *
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_wpmudev_domainmap( $path, $get_scheme = false ) {

		if ( $this->is_domainmapping_active() ) {
			global $wpdb, $blog_id;

			/**
			 * Cache revisions. Hexadecimal.
			 *
			 * @since 2.6.0
			 */
			$revision = '1';

			$cache_key = 'wpmudev_mapped_domain_' . $revision . '_' . $blog_id;

			//* Check if the domain is mapped
			$mapped_domain = $this->object_cache_get( $cache_key );
			if ( false === $mapped_domain ) {
				//* Setup cache. Results may only contain one object.

				$mapped_domains = $wpdb->get_results( $wpdb->prepare( "SELECT id, domain, is_primary, scheme FROM {$wpdb->base_prefix}domain_mapping WHERE blog_id = %d", $blog_id ), OBJECT );

				$primary_key = 0;
				$domain_ids = array();
				foreach ( $mapped_domains as $key => $domain ) {
					if ( isset( $domain->is_primary ) && '1' === $domain->is_primary ) {
						$primary_key = $key;

						//* We've found the primary key, break loop.
						break;
					} else {
						//* Save IDs.
						if ( isset( $domain->id ) && $domain->id )
							$domain_ids[$key] = $domain->id;
					}
				}

				if ( 0 === $primary_key && ! empty( $domain_ids ) ) {
					//* No primary ID has been found. Get the one with the lowest ID, which has been added first.
					$primary_key = array_keys( $domain_ids, min( $domain_ids ), true );
					$primary_key = reset( $primary_key );
				}

				//* Set 0, as we check for false to begin with.
				$mapped_domain = isset( $mapped_domains[$primary_key] ) ? $mapped_domains[$primary_key] : 0;

				$this->object_cache_set( $cache_key, $mapped_domain, 3600 );
			}

			if ( $mapped_domain ) {

				$domain = isset( $mapped_domain->domain ) ? $mapped_domain->domain : '0';
				$scheme = isset( $mapped_domain->scheme ) ? $mapped_domain->scheme : '';

				//* Fallback to is_ssl if no scheme has been found.
				if ( '' === $scheme )
					$scheme = is_ssl() ? '1' : '0';

				if ( '1' === $scheme ) {
					$scheme_full = 'https://';
					$scheme = 'https';
				} else {
					$scheme_full = 'http://';
					$scheme = 'http';
				}

				//* Put it all together.
				$url = trailingslashit( $scheme_full . $domain ) . ltrim( $path, '\/' );

				if ( ! $get_scheme ) {
					return $url;
				} else {
					return array( $url, $scheme );
				}
			}
		}

		return '';
	}

	/**
	 * Try to get an canonical URL when Donncha Domain Mapping is active.
	 *
	 * @param string $path The post relative path.
	 * @param bool $get_scheme Output array with scheme.
	 *
	 * @since 2.4.0
	 *
	 * @return string|array|void The unescaped URL, the scheme
	 */
	public function the_url_donncha_domainmap( $path, $get_scheme = false ) {

		if ( $this->is_donncha_domainmapping_active() ) {
			global $wpdb,$current_blog;

			$scheme = is_ssl() ? 'https' : 'http';

			//* This url is cached statically.
			$url = function_exists( 'domain_mapping_siteurl' ) ? domain_mapping_siteurl( false ) : false;

			$request_uri = '';

			if ( $url && $url !== untrailingslashit( $scheme . '://' . $current_blog->domain . $current_blog->path ) ) {
				if ( ( defined( 'VHOST' ) && 'yes' !== VHOST ) || ( defined( 'SUBDOMAIN_INSTALL' ) && false === SUBDOMAIN_INSTALL ) ) {
					$request_uri = str_replace( $current_blog->path, '/', $_SERVER['REQUEST_URI'] );
				}

				$url = trailingslashit( $url . $request_uri ) . ltrim( $path, '\/ ' );

				if ( $get_scheme ) {
					return array( $url, $scheme );
				} else {
					return $url;
				}
			}
		}

		return '';
	}

	/**
	 * Generates shortlink url
	 *
	 * @since 2.2.2
	 *
	 * @param int $post_id The post ID
	 * @return string|null Escaped site Shortlink URL
	 */
	public function get_shortlink( $post_id = 0 ) {

		if ( $this->get_option( 'shortlink_tag' ) ) {

			$path = null;

			if ( ! is_front_page() ) {
				if ( $this->is_singular( $post_id ) ) {

					if ( 0 === $post_id )
						$post_id = $this->get_the_real_ID();

					if ( $post_id ) {
						if ( $this->is_static_frontpage( $post_id ) ) {
							$path = '';
						} else {
							$path = '?p=' . $post_id;
						}
					}
				} else if ( ! is_front_page() && is_archive() ) {

					$object = get_queried_object();

					if ( is_category() ) {
						$id = $object->term_id;
						$path = '?cat=' . $id;
					}

					if ( is_tag() ) {
						$name = $object->name;
						$path = '?tag=' . $name;
					}

					if ( is_date() ) {
						// This isn't exactly "short" for a shortlink...
						$year = get_query_var( 'year' );
						$month = get_query_var( 'monthnum' ) ? '&monthnum=' . get_query_var( 'monthnum' ) : '';
						$day = get_query_var( 'day' ) ? '&day=' . get_query_var( 'day' ) : '';

						$path = '?year=' . $year . $month . $day;
					}

					if ( is_author() ) {
						$id = $object->ID;
						$path = '?author=' . $id;
					}

					if ( is_tax() ) {
						$id = $object->ID;
						$path = '?taxonomy=' . $id;
					}

				}
			}

			if ( isset( $path ) ) {

				$url = $this->the_url_from_cache();
				$parsed_url = parse_url( $url );

				$additions = '';
				if ( isset( $parsed_url['query'] ) )
					$additions = '&' . $parsed_url['query'];

				$home_url = $this->the_home_url_from_cache( true );
				$url = $home_url . $path . $additions;

				return esc_url_raw( $url );
			}
		}

		return '';
	}

	/**
	 * Generates Previous and Next links
	 *
	 * @since 2.2.4
	 *
	 * @param string $prev_next Previous or next page link
	 * @param int $post_id The post ID
	 *
	 * @return string|null Escaped site Pagination URL
	 */
	public function get_paged_url( $prev_next = 'next', $post_id = 0 ) {

		if ( ! $this->get_option( 'prev_next_posts' ) && ! $this->get_option( 'prev_next_archives' ) )
			return '';

		global $wp_query;

		$prev = '';
		$next = '';

		if ( $this->get_option( 'prev_next_archives' ) && ! $this->is_singular() ) {

			$paged = $this->paged();

			if ( 'prev' === $prev_next )
				$prev = $paged > 1 ? get_previous_posts_page_link() : $prev;

			if ( 'next' === $prev_next )
				$next = $paged < $wp_query->max_num_pages ? get_next_posts_page_link() : $next;

		} else if ( $this->get_option( 'prev_next_posts' ) && $this->is_singular() ) {

			$page = $this->page();
			$numpages = substr_count( $wp_query->post->post_content, '<!--nextpage-->' ) + 1;

			if ( ! $page && $numpages ) {
				$page = 1;
			}

			if ( 'prev' === $prev_next ) {
				if ( $page > 1 ) {
					$prev = (string) $this->get_paged_post_url( $page - 1, $post_id, 'prev' );
				}
			}

			if ( 'next' === $prev_next ) {
				if ( $page < $numpages ) {
					$next = (string) $this->get_paged_post_url( $page + 1, $post_id, 'next' );
				}
			}

		}

		if ( $prev )
			return esc_url_raw( $prev );

		if ( $next )
			return esc_url_raw( $next );

		return '';
	}

	/**
	 * Return the special URL of a paged post.
	 *
	 * Taken from _wp_link_page() in WordPress core, but instead of anchor markup, just return the URL.
	 * Also adds WPMUdev Domain Mapping support and is optimized for speed.
	 *
	 * @uses $this->the_url_from_cache();
	 * @since 2.2.4
	 *
	 * @param int $i The page number to generate the URL from.
	 * @param int $post_id The post ID
	 * @param string $pos Which url to get, accepts next|prev
	 *
	 * @return string Unescaped URL
	 */
	public function get_paged_post_url( $i, $post_id = 0, $pos = '' ) {

		$from_option = false;

		if ( $i === 1 ) {
			$url = $this->the_url_from_cache( '', $post_id, true, $from_option );
		} else {
			$post = get_post( $post_id );

			$urlfromcache = $this->the_url_from_cache( '', $post_id, false, $from_option );

			/**
			 * Fix the url.
			 *
			 * @since 2.2.5
			 */
			if ( $i >= 2 ) {
				//* Fix adding pagination url.

				//* Parse query arg and put in var.
				$query_arg = parse_url( $urlfromcache, PHP_URL_QUERY );
				if ( isset( $query_arg ) )
					$urlfromcache = str_replace( '?' . $query_arg, '', $urlfromcache );

				// Calculate current page number.
				$int_current = 'next' === $pos ? ( $i - 1 ) : ( $i + 1 );
				$string_current = (string) $int_current;

				if ( $i !== 1 ) {
					//* We're adding a page.
					$last_occurence = strrpos( $urlfromcache, '/' . $string_current . '/' );

					if ( $last_occurence !== false )
						$urlfromcache = substr_replace( $urlfromcache, '/', $last_occurence, strlen( '/' . $string_current . '/' ) );
				}
			}

			if ( '' === $this->permalink_structure() || in_array( $post->post_status, array( 'draft', 'auto-draft', 'pending' ) ) ) {
				$url = add_query_arg( 'page', $i, $urlfromcache );
			} else if ( $this->is_static_frontpage( $post->ID ) ) {
				global $wp_rewrite;

				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			} else {
				$url = trailingslashit( $urlfromcache ) . user_trailingslashit( $i, 'single_paged' );

				//* Add back query arg if removed.
				if ( isset( $query_arg ) )
					$url = $url . '?' . $query_arg;
			}
		}

		return $url;
	}

	/**
	 * Cached WordPress permalink structure settings.
	 *
	 * @since 2.6.0
	 * @staticvar string $structure
	 *
	 * @return string permalink structure.
	 */
	public function permalink_structure() {

		static $structure = null;

		if ( isset( $structure ) )
			return $structure;

		return $structure = get_option( 'permalink_structure' );
	}

}
