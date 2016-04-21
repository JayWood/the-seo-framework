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
 * Class AutoDescription_Search
 *
 * Generates sitemap and outputs it.
 *
 * @since 2.2.9
 */
class AutoDescription_Sitemaps extends AutoDescription_Metaboxes {

	/**
	 * Maximum number of posts that show up in the sitemap.xml page.
	 *
	 * @since 2.2.9
	 *
	 * @var int Max Posts in Sitemap
	 */
	protected $max_posts;

	/**
	 * Checks for pretty permalinks.
	 *
	 * @since 2.2.9
	 *
	 * @var bool true if pretty
	 */
	protected $pretty_permalinks;

	/**
	 * Checks if sitemap is being output.
	 *
	 * @since 2.5.2
	 *
	 * @var bool true if sitemap is being output.
	 */
	protected $doing_sitemap = false;

	/**
	 * Constructor, load parent constructor and set up caches.
	 */
	public function __construct() {
		parent::__construct();

		$this->max_posts = (int) apply_filters( 'the_seo_framework_sitemap_post_limit', 700 );

		// I'm not going to initialize my own rewrite engine. Causes too many problems.
		$this->pretty_permalinks = ( '' !== get_option( 'permalink_structure' ) ) ? true : false;

		/**
		 * Add query strings to rewrite
		 */
		add_action( 'init', array( $this, 'rewrite_rule_sitemap' ), 1 );
		add_filter( 'query_vars', array( $this, 'enqueue_sitemap_query_vars' ), 1 );

		/**
		 * Adding a higher priority will cause a trailing slash to be added.
		 * We need to be in front of the queue to prevent this from happening.
		 */
		add_action( 'template_redirect', array( $this, 'maybe_output_sitemap' ), 1 );

		//* Edit the robots.txt file
		add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );

		//* Enqueue rewrite flush
		add_action( 'shutdown', array( $this, 'maybe_flush_rewrite' ), 999 );
	}

	/**
	 * Whether we can output sitemap or not based on options.
	 *
	 * @staticvar bool $cache
	 * @since 2.6.0
	 *
	 * @return bool
	 */
	public function can_run_sitemap() {

		static $cache = null;

		if ( isset( $cache ) )
			return $cache;

		/**
		 * Don't do anything on a deleted or spam blog.
		 * There's nothing to find anyway. Multisite Only.
		 */
		return $cache = $this->pretty_permalinks && $this->is_option_checked( 'sitemaps_output' ) && false === $this->current_blog_is_spam_or_deleted() ? true : false;
	}

	/**
	 * Adds rewrite rule to WordPress
	 * This rule defines the sitemap.xml output
	 *
	 * @param bool $override add the rule anyway, regardless of setting.
	 *
	 * @since 2.2.9
	 */
	public function rewrite_rule_sitemap( $run = false ) {

		//* Adding rewrite rules only has effect when permalink structures are active.
		if ( $this->can_run_sitemap() || $run ) {

			/**
			 * Don't do anything if a sitemap plugin is active.
			 * On sitemap plugin activation, the sitemap plugin should flush the
			 * rewrite rules. If it doesn't, then this plugin's sitemap will be called.
			 */
			if ( $this->has_sitemap_plugin() )
				return;

			add_rewrite_rule( 'sitemap\.xml$', 'index.php?the_seo_framework_sitemap=xml', 'top' );

			$this->wpmudev_domainmap_flush_fix( false );
		}

	}

	/**
	 * Register the_seo_framework_sitemap to wp_query
	 *
	 * @param array vars The WP_Query vars
	 *
	 * @since 2.2.9
	 */
	public function enqueue_sitemap_query_vars( $vars ) {

		if ( $this->can_run_sitemap() )
			$vars[] = 'the_seo_framework_sitemap';

		return $vars;
	}

	/**
	 * Maybe Output sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 *
	 * @return void|header+string SiteMap XML file.
	 */
	public function maybe_output_sitemap() {

		if ( $this->can_run_sitemap() ) {
			global $wp_query;

			if ( isset( $wp_query->query_vars['the_seo_framework_sitemap'] ) && 'xml' === $wp_query->query_vars['the_seo_framework_sitemap'] ) {
				// Don't let WordPress think this is 404.
				$wp_query->is_404 = false;

				$this->doing_sitemap = true;

				/**
				 * Set at least 2000 variables free.
				 * Freeing 0.15MB on a clean WordPress installation.
				 */
				$this->clean_up_globals();

				$this->output_sitemap();
			}
		}

	}

	/**
	 * Destroy unused $GLOBALS.
	 *
	 * @since 2.6.0
	 */
	protected function clean_up_globals() {

		$remove = array(
			'wp_filter' => array(
				'wp_head',
				'the_content',
				'the_content_feed',
				'the_excerpt_rss',
			),
			'wp_registered_widgets',
			'wp_registered_sidebars',
			'wp_registered_widget_updates',
			'wp_registered_widget_controls',
			'_wp_deprecated_widgets_callbacks',
			'posts',
			'shortcode_tags',
		);

		foreach ( $remove as $key => $value ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $v )
					unset( $GLOBALS[$key][$v] );
			} else {
				unset( $GLOBALS[$value] );
			}
		}

	}

	/**
	 * Output sitemap.xml 'file' and header.
	 *
	 * @since 2.2.9
	 */
	protected function output_sitemap() {

		if ( ! headers_sent() )
			header( 'Content-type: text/xml; charset=utf-8' );

		//* Fetch sitemap content.
		$xml_content = $this->get_sitemap_content();

		//* Echo and add trailing line.
		echo $xml_content . "\r\n";

		// We're done now.
		die();
	}

	/**
	 * Output sitemap.xml content from transient.
	 *
	 * @since 2.2.9
	 *
	 * @return string Sitemap XML contents.
	 */
	protected function get_sitemap_content() {

		if ( $this->the_seo_framework_debug ) $timer_start = microtime( true );

		/**
		 * Re-use the variable, eliminating database requests
		 * @since 2.4.0
		 */
		$sitemap_content = $this->get_transient( $this->sitemap_transient );

		if ( false === $sitemap_content ) {
			$cached_content = "\r\n<!-- " . __( 'Sitemap is generated for this view', 'autodescription' ) . " -->";
		} else {
			$cached_content = "\r\n<!-- " . __( 'Sitemap is served from cache', 'autodescription' ) . " -->";
		}

		$content  = '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n";
		$content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\r\n";
		$content .= $this->setup_sitemap( $sitemap_content );
		$content .= '</urlset>';

		$content .= $cached_content;

		/**
		 * Output debug info.
		 * @since 2.3.7
		 */
		if ( $this->the_seo_framework_debug ) {
			$content .= "\r\n<!-- Site current usage: " . ( memory_get_usage() / 1024 / 1024 ) . " MB -->";
			$content .= "\r\n<!-- System current usage: " . ( memory_get_usage( true ) / 1024 / 1024 ) . " MB -->";
			$content .= "\r\n<!-- Sitemap generation time: " . ( number_format( microtime( true ) - $timer_start, 6 ) ) . " seconds -->";
		}

		return $content;
	}

	/**
	 * Create sitemap.xml content transient.
	 *
	 * @param string|bool $content required The sitemap transient content.
	 *
	 * @since 2.6.0
	 */
	public function setup_sitemap( $sitemap_content ) {

		if ( false === $sitemap_content ) {
			//* Transient doesn't exist yet.
			$sitemap_content = $this->generate_sitemap();

			/**
			 * Transient expiration: 1 week.
			 * Keep the sitemap for at most 1 week.
			 *
			 * 60s * 60m * 24h * 7d
			 */
			$expiration = 60 * 60 * 24 * 7;

			$this->set_transient( $this->sitemap_transient, $sitemap_content, $expiration );
		}

		return $sitemap_content;
	}

	/**
	 * Generate sitemap.xml content.
	 *
	 * @param bool $secure Only process when param is given.
	 *
	 * @since 2.2.9
	 */
	protected function generate_sitemap() {

		$content = '';

		/**
		 * Applies filters the_seo_framework_sitemap_exclude_ids : array of id's
		 *
		 * @since 2.5.2
		 */
		$excluded = (array) apply_filters( 'the_seo_framework_sitemap_exclude_ids', array() );

		if ( empty( $excluded ) ) {
			$excluded = '';
		} else {
			$excluded = array_flip( $excluded );
		}

		/**
		 * Maximum pages and posts to fetch.
		 * A total of 2100, consisting of 3 times $max_posts
		 *
		 * Applies filters the_seo_framework_sitemap_pages_count : int max pages
		 * Applies filters the_seo_framework_sitemap_posts_count : int max posts
		 * Applies filters the_seo_framework_sitemap_custom_posts_count : int max posts
		 */
		$totalpages = (int) apply_filters( 'the_seo_framework_sitemap_pages_count', $this->max_posts );
		$totalposts = (int) apply_filters( 'the_seo_framework_sitemap_posts_count', $this->max_posts );
		$total_cpt_posts = (int) apply_filters( 'the_seo_framework_sitemap_custom_posts_count', $this->max_posts );
		$total_cpt_posts_bool = $total_cpt_posts ? true : false;

		$latest_pages = array();
		$latest_posts = array();
		$latest_cpt_posts = array();
		$cpt = array();

		//* Sets timezone according to WordPress settings.
		$this->set_timezone();
		$timestamp_format = $this->get_option( 'sitemap_timestamps' );
		$timestamp_format = '1' === $timestamp_format ? 'Y-m-d\TH:iP' : 'Y-m-d';

		if ( $totalpages ) {
			//* Ascend by the date for normal pages. Older pages get to the top of the list.
			$args = array(
				'numberposts' 		=> $totalpages,
				'posts_per_page' 	=> $totalpages,
				'post_type' 		=> 'page',
				'orderby' 			=> 'date',
				'order' 			=> 'ASC',
				'post_status' 		=> 'publish',
				'cache_results' 	=> false,
			);
			$latest_pages = get_posts( $args );
		}

		if ( $totalposts ) {
			//* Descend by the date for posts. The latest posts get to the top of the list after pages.
			$args = array(
				'numberposts' 		=> $totalposts,
				'posts_per_page' 	=> $totalposts,
				'post_type' 		=> 'post',
				'orderby' 			=> 'date',
				'order' 			=> 'DESC',
				'post_status' 		=> 'publish',
				'cache_results' 	=> false,
			);
			$latest_posts = get_posts( $args );
		}

		if ( $total_cpt_posts_bool ) {
			$post_page = (array) get_post_types( array( 'public' => true ) );

			/**
			 * Applies filters Array the_seo_framework_sitemap_exclude_cpt : Excludes these CPT
			 * @since 2.5.0
			 */
			$excluded_cpt = (array) apply_filters( 'the_seo_framework_sitemap_exclude_cpt', array() );

			$notcpt = array( 'post', 'page', 'attachment' );

			foreach ( $post_page as $post_type ) {
				if ( ! in_array( $post_type, $notcpt ) ) {
					if ( empty( $excluded_cpt ) || ! in_array( $post_type, $excluded_cpt ) ) {
						if ( $this->post_type_supports_custom_seo( $post_type ) ) {
							$cpt[] = $post_type;
						}
					}
				}
			}
		}

		if ( $total_cpt_posts_bool && $cpt ) {
			//* Descend by the date for CPTs. The latest posts get to the top of the list after pages.
			$args = array(
				'numberposts' 		=> $total_cpt_posts,
				'posts_per_page' 	=> $total_cpt_posts,
				'post_type' 		=> $cpt,
				'orderby' 			=> 'date',
				'order' 			=> 'DESC',
				'post_status' 		=> 'publish',
				'cache_results' 	=> false,
			);
			$latest_cpt_posts = get_posts( $args );
		}

		/**
		 * Fetch the page/post modified options.
		 * We can't get specific on the home page, unfortunately.
		 */
		$sitemaps_modified = $this->is_option_checked( 'sitemaps_modified' );
		if ( $sitemaps_modified ) {
			$page_lastmod = true;
			$post_lastmod = true;
			$home_lastmod = true;
		} else {
			$page_lastmod = $this->is_option_checked( 'page_modify_time' );
			$post_lastmod = $this->is_option_checked( 'post_modify_time' );
			$home_lastmod = $page_lastmod ? $page_lastmod : $this->is_option_checked( 'home_modify_time' );
		}

		/**
		 * Generation time output
		 *
		 * Applies filter the_seo_framework_sitemap_timestamp : bool
		 */
		$timestamp = (bool) apply_filters( 'the_seo_framework_sitemap_timestamp', true );

		if ( $timestamp )
			$content .= '<!-- ' . __( 'Sitemap is generated on', 'autodescription' ) . ' ' . current_time( "Y-m-d H:i:s" ) . ' -->' . "\r\n";

		$latest_pages_amount = (int) count( $latest_pages );

		if ( $latest_pages_amount > 0 ) {

			$id_on_front = $this->has_page_on_front() ? (int) get_option( 'page_on_front' ) : (int) get_option( 'page_for_posts' );

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_pages as $page ) {
				if ( isset( $page->ID ) ) {
					$page_id = $page->ID;

					if ( '' === $excluded || ! isset( $excluded[$post_id] ) ) {
						//* Is this the front page?
						$page_is_front = ( $page_id === $id_on_front ) ? true : false;

						//* Fetch the noindex option, per page.
						$noindex = (bool) $this->get_custom_field( '_genesis_noindex', $page_id );

						//* Continue if indexed.
						if ( false === $noindex ) {
							$content .= "	<url>\r\n";
							if ( $page_is_front ) {
								$content .= '		<loc>' . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'home' => true ) ) . "</loc>\r\n";
							} else {
								$content .= '		<loc>' . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $page, 'id' => $page_id ) ) . "</loc>\r\n";
							}

							// Keep it consistent. Only parse if page_lastmod is true.
							if ( $page_lastmod || ( $page_is_front && $home_lastmod ) ) {
								$page_modified_gmt = $page->post_modified_gmt;

								if ( $page_modified_gmt !== '0000-00-00 00:00:00' )
									$content .= '		<lastmod>' . mysql2date( $timestamp_format, $page_modified_gmt, false ) . "</lastmod>\r\n";
							}

							// Give higher priority to the home page.
							$priority_page = $page_is_front ? 1 : 0.9;

							$content .= '		<priority>' . number_format( $priority_page, 1 ) . "</priority>\r\n";
							$content .= "	</url>\r\n";
						}
					}
				}
			}
		}

		$latest_posts_amount = (int) count( $latest_posts );

		if ( $latest_posts_amount > 0 ) {

			/**
			 * Setting up priorities, with pages always being important.
			 *
			 * From there, older posts get a gradually lower priority. Down to 0.
			 * Differentiate with 1 / max posts (0 to $this->max_posts). With a 1 dot decimal.
			 */
			$priority = 0.9;

			/**
			 * Infinity is abstract. But what is it when it's both positive and negative?
			 * Undefined. Bugfix.
			 *
			 * @since 2.3.2
			 * @thanks Schlock | https://wordpress.org/support/topic/sitemap-xml-parsing-error
			 */
			$prioritydiff = 0;

			if ( $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.9 / $latest_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_posts_amount <= (int) 9 && $latest_posts_amount > (int) 1 )
				$prioritydiff = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_posts as $post ) {
				if ( isset( $post->ID ) ) {
					$post_id = $post->ID;

					if ( '' === $excluded || ! isset( $excluded[$post_id] ) ) {

						//* Fetch the noindex option, per page.
						$noindex = (bool) $this->get_custom_field( '_genesis_noindex', $post_id );

						//* Continue if indexed
						if ( ! $noindex ) {

							$content .= "	<url>\r\n";
							// No need to use static vars
							$content .= '		<loc>' . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $post, 'id' => $post_id ) ) . "</loc>\r\n";

							// Keep it consistent. Only parse if page_lastmod is true.
							if ( $post_lastmod ) {
								$post_modified_gmt = $post->post_modified_gmt;

								if ( $post_modified_gmt !== '0000-00-00 00:00:00' )
									$content .= '		<lastmod>' . mysql2date( $timestamp_format, $post_modified_gmt, false ) . "</lastmod>\r\n";
							}

							$content .= '		<priority>' . number_format( $priority, 1 ) . "</priority>\r\n";
							$content .= "	</url>\r\n";

							// Lower the priority for the next pass.
							$priority = $priority - $prioritydiff;

							// Cast away negative numbers.
							$priority = $priority <= (int) 0 ? (int) 0 : (float) $priority;
						}
					}
				}
			}
		}

		$latest_cpt_posts_amount = (int) count( $latest_cpt_posts );

		if ( $latest_cpt_posts_amount > 0 ) {

			/**
			 * Setting up priorities, with pages always being important.
			 *
			 * From there, older posts get a gradually lower priority. Down to 0.
			 * Differentiate with 1 / max posts (0 to $this->max_posts). With a 1 dot decimal.
			 */
			$priority_cpt = 0.9;

			$prioritydiff_cpt = 0;

			if ( $latest_cpt_posts_amount > (int) 1 )
				$prioritydiff_cpt = 0.9 / $latest_cpt_posts_amount;

			// Keep it consistent. Only remove 0.1 when we only have a few posts.
			if ( $latest_cpt_posts_amount <= (int) 9 && $latest_cpt_posts_amount > (int) 1 )
				$prioritydiff_cpt = 0.1;

			/**
			 * This can be heavy.
			 */
			foreach ( $latest_cpt_posts as $ctp_post ) {
				if ( isset( $ctp_post->ID ) ) {
					$post_id = $ctp_post->ID;

					if ( '' === $excluded || ! isset( $excluded[$post_id] ) ) {

						//* Fetch the noindex option, per page.
						$noindex = (bool) $this->get_custom_field( '_genesis_noindex', $post_id );

						//* Continue if indexed
						if ( ! $noindex ) {

							$content .= "	<url>\r\n";
							//* No need to use static vars
							$content .= '		<loc>' . $this->the_url( '', array( 'get_custom_field' => false, 'external' => true, 'post' => $ctp_post, 'id' => $post_id ) ) . "</loc>\r\n";

							//* Keep it consistent. Only parse if page_lastmod is true.
							if ( $post_lastmod ) {
								$post_modified_gmt = $ctp_post->post_modified_gmt;

								//* Some CPT don't set modified time.
								if ( $post_modified_gmt !== '0000-00-00 00:00:00' )
									$content .= '		<lastmod>' . mysql2date( $timestamp_format, $post_modified_gmt, false ) . "</lastmod>\r\n";
							}

							$content .= '		<priority>' . number_format( $priority_cpt, 1 ) . "</priority>\r\n";
							$content .= "	</url>\r\n";

							// Lower the priority for the next pass.
							$priority_cpt = $priority_cpt - $prioritydiff_cpt;

							// Cast away negative numbers.
							$priority_cpt = $priority_cpt <= (int) 0 ? (int) 0 : (float) $priority_cpt;
						}
					}
				}
			}
		}

		/**
		 * Applies filters the_seo_framework_sitemap_additional_urls : {
		 * 		@param string url The absolute url to the page. : {
		 * 			@param string lastmod UNIXTIME Last modified date, e.g. "2016-01-26 13:04:55"
		 * 			@param float|int|string priority URL Priority
		 *		}
		 * }
		 *
		 * @since 2.5.2
		 */
		$custom_urls = (array) apply_filters( 'the_seo_framework_sitemap_additional_urls', array() );

		if ( $custom_urls ) {
			foreach ( $custom_urls as $url => $args ) {

				if ( ! is_array( $args ) ) {
					//* If there are no args, it's assigned as URL (per example)
					$url = $args;
				}

				$content .= "	<url>\r\n";
				//* No need to use static vars
				$content .= '		<loc>' . esc_url_raw( $url ) . "</loc>\r\n";

				if ( isset( $args['lastmod'] ) && $args['lastmod'] ) {
					$content .= '		<lastmod>' . mysql2date( $timestamp_format, $args['lastmod'], false ) . "</lastmod>\r\n";
				}

				if ( isset( $args['priority'] ) && $args['priority'] ) {
					$priority = $args['priority'];
				} else {
					$priority = 0.9;
				}

				$content .= '		<priority>' . number_format( $priority, 1 ) . "</priority>\r\n";
				$content .= "	</url>\r\n";
			}
		}

		/**
		 * Applies filters the_seo_framework_sitemap_extend
		 *
		 * @since 2.5.2
		 */
		$extend = (string) apply_filters( 'the_seo_framework_sitemap_extend', '' );

		if ( '' !== $extend )
			$content .= "	" . $extend . "\r\n";

		//* Reset timezone to default.
		$this->reset_timezone();

		return $content;
	}

	/**
	 * Ping search engines on post publish.
	 *
	 * @since 2.2.9
	 */
	public function ping_searchengines() {

		/**
		 * Don't ping if the blog isn't public.
		 *
		 * @since 2.3.1
		 */
		if ( false === $this->is_option_checked( 'site_noindex' ) && $this->is_blog_public() ) {
			global $blog_id;

			$blog_id = (string) $blog_id;

			$transient = 'tsf_throttle_ping_' . $blog_id;

			//* NOTE: Use legacy get_transient to prevent ping spam.
			if ( false === get_transient( $transient ) ) {
				//* Transient doesn't exist yet.

				if ( $this->is_option_checked( 'ping_google' ) )
					$this->ping_google();

				if ( $this->is_option_checked( 'ping_bing' ) )
					$this->ping_bing();

				if ( $this->is_option_checked( 'ping_yandex' ) )
					$this->ping_yandex();

				// Sorry I couldn't help myself.
				$throttle = 'Bert and Ernie are weird.';

				/**
				 * Limit the pinging to a maximum of 1 per hour.
				 * Transient expiration. 1 hour.
				 *
				 * 60s * 60m
				 *
				 * Applies filters the_seo_framework_sitemap_throttle_s
				 * @since 2.5.1
				 */
				$expiration = (int) apply_filters( 'the_seo_framework_sitemap_throttle_s', 60 * 60 );

				//* @NOTE: Using legacy set_transient to prevent ping spam.
				set_transient( $transient, $throttle, $expiration );
			}
		}

	}

	/**
	 * Ping Google
	 *
	 * @since 2.2.9
	 */
	public function ping_google() {

		$pingurl = 'http://www.google.com/webmasters/sitemaps/ping?sitemap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		wp_remote_get( $pingurl, array( 'timeout' => 3 ) );

	}

	/**
	 * Ping Bing
	 *
	 * @since 2.2.9
	 */
	public function ping_bing() {

		$pingurl = 'http://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		wp_remote_get( $pingurl, array( 'timeout' => 3 ) );

	}

	/**
	 * Ping Yandex
	 *
	 * @since 2.6.0
	 */
	public function ping_yandex() {

		$pingurl = 'http://blogs.yandex.ru/pings/?status=success&url=' . urlencode( $this->the_home_url_from_cache( true ) . 'sitemap.xml' );
		wp_remote_get( $pingurl, array( 'timeout' => 3 ) );

	}

	/**
	 * Edits the robots.txt output
	 *
	 * Requires not to have a robots.txt file in the root directory
	 *
	 * @uses robots_txt filter located at WP core
	 *
	 * @since 2.2.9
	 *
	 * @global int $blog_id;
	 *
	 * @todo maybe combine with noindex/noarchive/(nofollow) -> only when object caching?
	 */
	public function robots_txt( $robots_txt = '', $public = '' ) {
		global $blog_id;

		/**
		 * Don't do anything if the blog isn't public
		 */
		if ( '0' === $public )
			return $robots_txt;

		$revision = '1';

		$cache_key = 'robots_txt_output_' . $revision . $blog_id;

		$output = $this->object_cache_get( $cache_key );
		if ( false === $output ) {
			$output = '';

			/**
			 * Apply filters the_seo_framework_robots_txt_pre & the_seo_framework_robots_txt_pro
			 * 		: Add custom cacheable lines.
			 *		: Don't forget to add line breaks ( "\r\n" | PHP_EOL )
			 *
			 * @since 2.5.0
			 */
			$pre = (string) apply_filters( 'the_seo_framework_robots_txt_pre', '' );
			$pro = (string) apply_filters( 'the_seo_framework_robots_txt_pro', '' );

			$home_url = $this->the_home_url_from_cache();
			$parse_url = parse_url( $home_url );
			$path = $parse_url['path'] ? ltrim( $parse_url['path'], ' /' ) : '';

			$output .= $pre;
			//* Output defaults
			$output .= "User-agent: *\r\n";
			$output .= "Disallow: $path/wp-admin/\r\n";
			$output .= "Allow: $path/wp-admin/admin-ajax.php\r\n";

			/**
			 * Prevents query indexing
			 * @since 2.2.9
			 *
			 * Applies filters the_seo_framework_robots_disallow_queries : Whether to allow queries for robots.
			 * @since 2.5.0
			 */
			if ( (bool) apply_filters( 'the_seo_framework_robots_disallow_queries', false ) )
				$output .= "Disallow: $path/*?*\r\n";

			$output .= $pro;

			if ( $this->get_option( 'sitemaps_output') && (bool) $this->get_option( 'sitemaps_robots' ) ) {
				//* Add whitespace before sitemap.
				$output .= "\r\n";

				//* Add sitemap full url
				$output .= 'Sitemap: ' . trailingslashit( $home_url ) . "sitemap.xml\r\n";
			}

			$this->object_cache_set( $cache_key, $output, 86400 );
		}

		/**
		 * Completely override robots with output.
		 * @since 2.5.0
		 */
		$robots_txt = $output;

		return $robots_txt;
	}

	/**
	 * Add and Flush rewrite rules on plugin activation.
	 *
	 * @since 2.2.9
	 * Do not return anything. Just be here. Thanks.
	 */
	public static function flush_rewrite_rules_activation() {
		global $wp_rewrite;

		// This function is called statically.
		$the_seo_framework = the_seo_framework();

		$the_seo_framework->rewrite_rule_sitemap( true );

		$wp_rewrite->init();
		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Flush rewrite rules on plugin deactivation.
	 *
	 * @since 2.2.9
	 * Do not return anything. Just be here. Thanks.
	 */
	public static function flush_rewrite_rules_deactivation() {
		global $wp_rewrite;

		$wp_rewrite->init();

		unset( $wp_rewrite->extra_rules_top['sitemap\.xml$'] );

		$wp_rewrite->flush_rules( true );
	}

	/**
	 * Enqueue rewrite flush for activation.
	 *
	 * @staticvar bool $flush Only true
	 *
	 * @since 2.3.0
	 */
	public function enqueue_rewrite_activate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return (bool) $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
	}

	/**
	 * Enqueue rewrite flush for deactivation.
	 *
	 * @staticvar bool $flush Only true
	 *
	 * @since 2.3.0
	 */
	public function enqueue_rewrite_deactivate( $enqueue = false ) {

		static $flush = null;

		if ( isset( $flush ) )
			return (bool) $flush;

		if ( $enqueue )
			return $flush = true;

		return false;
	}

	/**
	 * Flush rewrite rules based on static variables.
	 *
	 * @since 2.3.0
	 */
	public function maybe_flush_rewrite() {

		if ( $this->enqueue_rewrite_activate() )
			$this->flush_rewrite_rules_activation();

		if ( $this->enqueue_rewrite_deactivate() )
			$this->flush_rewrite_rules_deactivation();

	}

	/**
	 * Add the WPMUdev Domain Mapping rules again. And flush them on init.
	 * Domain Mapping bugfix.
	 *
	 * @param bool $options_saved : If we're in admin and the sanitiation function runs.
	 *
	 * Runs a flush and updates the site option to "true".
	 * When the site option is set to true, it not flush again on init.
	 *
	 * If $options_saved is true, it will not check for the init action hook and continue,
	 * So it will flush the next time on init.
	 *
	 * @since 2.3.0
	 */
	public function wpmudev_domainmap_flush_fix( $options_saved = false ) {

		if ( $this->pretty_permalinks && $this->is_domainmapping_active() ) {
			if ( 'init' === current_action() || $options_saved ) {
				if ( class_exists( 'Domainmap_Module_Cdsso' ) && defined( 'Domainmap_Module_Cdsso::SSO_ENDPOINT' ) ) {
					add_rewrite_endpoint( Domainmap_Module_Cdsso::SSO_ENDPOINT, EP_ALL );

					//* Force extra flush on init.
					if ( class_exists( 'domain_map' ) ) {
						$key = 'the_seo_framework_wpmdev_dm' . get_current_blog_id() . '_extra_flush';

						if ( $options_saved ) {
							//* Reset the flush on option change.
							if ( get_site_option( $key ) ) {
								update_site_option( $key, false );
							}
						} else {
							if ( false === get_site_option( $key ) ) {
								//* Prevent flushing multiple times.
								update_site_option( $key, true );

								//* Now flush
								flush_rewrite_rules();
							}
						}
					}
				}
			}
		}

	}

}
