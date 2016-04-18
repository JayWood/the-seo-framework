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
 * Class AutoDescription_Transients
 *
 * Generates, stores and deletes common transients.
 *
 * @since 2.3.3
 */
class AutoDescription_Transients extends AutoDescription_Sitemaps {

	/**
	 * The sitemap transient name.
	 *
	 * @since 2.2.9
	 *
	 * @var string The Sitemap Transient Name.
	 */
	protected $sitemap_transient;

	/**
	 * The Automatic Description transient name.
	 *
	 * @since 2.3.3
	 *
	 * @var string The Automatic Description Transient Name.
	 */
	protected $auto_description_transient;

	/**
	 * The LD+Json script transient name.
	 *
	 * @since 2.3.3
	 *
	 * @var string The LD+Json Script Transient Name.
	 */
	protected $ld_json_transient;

	/**
	 * The Theme is doing the Title right transient name
	 *
	 * @since 2.5.2
	 *
	 * @var string The Theme Doing It Right Transient Name.
	 */
	protected $theme_doing_it_right_transient;

	/**
	 * Constructor, load parent constructor and set up caches.
	 */
	public function __construct() {
		parent::__construct();

		// Setup Transient names
		add_action( 'plugins_loaded', array( $this, 'setup_transient_names' ), 10 );

		//* Delete Sitemap and Description transients on post publish/delete.
		add_action( 'publish_post', array( $this, 'delete_transients_post' ) );
		add_action( 'delete_post', array( $this, 'delete_transients_post' ) );
		add_action( 'post_updated', array( $this, 'delete_transients_post' ) );

		add_action( 'edit_term', array( $this, 'delete_auto_description_transients_term' ), 10, 3 );
		add_action( 'delete_term', array( $this, 'delete_auto_description_transients_term' ), 10, 4 );

		//* Delete Sitemap transient on permalink structure change.
		add_action( 'load-options-permalink.php', array( $this, 'delete_sitemap_transient_permalink_updated' ), 20 );

		add_action( 'update_option_blogdescription', array( $this, 'delete_auto_description_blog_transient' ), 10, 1 );

		//* Delete doing it wrong transient after theme switch.
		add_action( 'after_switch_theme', array( $this, 'delete_theme_dir_transient' ), 10 );

	}

	/**
	 * Get the value of the transient.
	 *
	 * If the transient does not exists, does not have a value or has expired,
	 * or transients have been disabled through a constant, then the transient
	 * will be false.
	 * @see $this->the_seo_framework_use_transients
	 *
	 * @since 2.6.0
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 *
	 * @return mixed|bool Value of the transient. False on failure or non existing transient.
	 */
	public function get_transient( $transient ) {

		if ( $this->the_seo_framework_use_transients )
			return get_transient( $transient );

		return false;
	}

	/**
	 * Set the value of the transient..
	 *
	 * Prevents setting of transients when they're disabled.
	 * @see $this->the_seo_framework_use_transients
	 *
	 * @since 2.6.0
	 *
	 * @param string $transient Transient name. Expected to not be SQL-escaped.
	 * @param string $value Transient value. Expected to not be SQL-escaped.
	 * @param int $expiration Optional Transient expiration date, optional. Expected to not be SQL-escaped.
	 */
	public function set_transient( $transient, $value, $expiration = '' ) {

		if ( $this->the_seo_framework_use_transients )
			set_transient( $transient, $value, $expiration );

	}

	/**
	 * Setup vars for transients.
	 *
	 * @since 2.3.3
	 */
	public function setup_transient_names() {
		global $blog_id;

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 */
		$revision = '3';

		$this->sitemap_transient = 'the_seo_framework_sitemap_' . (string) $revision . '_' . (string) $blog_id;
		$this->theme_doing_it_right_transient = 'the_seo_framework_tdir_' . (string) $revision . '_' . (string) $blog_id;
	}

	/**
	 * Setup vars for transients which require $page_id.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @since 2.3.3
	 */
	public function setup_auto_description_transient( $page_id, $taxonomy = '' ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy );

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 *
		 * @since 2.3.4
		 */
		$revision = '0';

		$additions = $this->add_description_additions( $page_id, $taxonomy );

		if ( $additions ) {
			/**
			 * Two different cache keys for two different settings.
			 *
			 * @since 2.3.4
			 */
			if ( $this->get_option( 'description_blogname' ) ) {
				$this->auto_description_transient = 'tsf_descall_' . $revision . '_' . $cache_key;
			} else {
				$this->auto_description_transient = 'tsf_descnob_' . $revision . '_' . $cache_key;
			}
		} else {
			$this->auto_description_transient = 'tsf_descnoa_' . $revision . '_' . $cache_key;
		}

	}

	/**
	 * Setup vars for transients which require $page_id.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID. If false it will generate for the blog page.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @since 2.3.3
	 */
	public function setup_ld_json_transient( $page_id, $taxonomy = '' ) {

		$cache_key = $this->generate_cache_key( $page_id, $taxonomy );

		/**
		 * When the caching mechanism changes. Change this value.
		 *
		 * Use hex. e.g. 0, 1, 2, 9, a, b
		 */
		$revision = '4';

		$this->ld_json_transient = 'the_seo_f' . $revision . '_ldjs_' . $cache_key;
	}

	/**
	 * Generate transient key based on query vars.
	 *
	 * @param int|string|bool $page_id the Taxonomy or Post ID.
	 * @param string $taxonomy The Taxonomy name.
	 *
	 * @staticvar array $cached_id : contains cache strings.
	 *
	 * @global $blog_id;
	 *
	 * @since 2.3.3
	 *
	 * @return string The generated page id key.
	 */
	public function generate_cache_key( $page_id, $taxonomy = '' ) {

		$page_id = $page_id ? $page_id : $this->get_the_real_ID();

		static $cached_id = array();

		if ( isset( $cached_id[$page_id][$taxonomy] ) )
			return $cached_id[$page_id][$taxonomy];

		global $blog_id;

		$locale = '_' . strtolower( get_locale() );

		//* Placeholder ID.
		$the_id = '';

		if ( $this->is_404() ) {
			//* 404.
			$the_id = '_404_';
		} else if ( $this->is_singular() ) {
			if ( $this->is_page( $page_id ) ) {
				//* Page.
				$the_id = 'page_' . $page_id;
			} else if ( $this->is_single( $page_id ) ) {
				//* Post.
				$the_id = 'post_' . $page_id;
			} else if ( $this->is_attachment( $page_id ) ) {
				//* Attachment.
				$the_id = 'attach_' . $page_id;
			} else {
				//* Other.
				$the_id = 'singular_' . $page_id;
			}
		} else if ( $this->is_search() ) {
			//* Search query.
			$query = '';

			if ( function_exists( 'get_search_query' ) ) {
				$search_query = get_search_query();

				if ( $search_query )
					$query = str_replace( ' ', '', $search_query );

				//* Limit to 10 chars.
				if ( mb_strlen( $query ) > 10 )
					$query = mb_substr( $query, 0, 10 );

				$query = esc_sql( $query );
			}

			$the_id = $page_id . '_s_' . $query;
		} else if ( $this->is_archive() ) {
			if ( $this->is_category() || $this->is_tag() || $this->is_tax() ) {
				//* Term.

				if ( empty( $taxonomy ) )
					$taxonomy = get_query_var( 'taxonomy' );

				$the_id = $this->generate_taxonomial_cache_key( $page_id, $taxonomy );

				if ( $this->is_tax() )
					$the_id = 'archives_' . $the_id;

			} else if ( $this->is_author() ) {
				//* Author page.
				$the_id = 'author_' . $page_id;
			} else if ( $this->is_date() ) {
				//* Dates.
				$post = get_post();

				if ( $post && isset( $post->post_date ) ) {
					$date = $post->post_date;

					if ( $this->is_year() ) {
						//* Year.
						$the_id .= 'year_' . mysql2date( 'y', $date, false );
					} else if ( $this->is_month() ) {
						//* Month.
						$the_id .= 'month_' . mysql2date( 'm_y', $date, false );
					} else if ( $this->is_day() ) {
						//* Day. The correct notation.
						$the_id .= 'day_' . mysql2date( 'd_m_y', $date, false );
					}
				} else {
					//* Get seconds since UNIX Epoch. This is a failsafe.

					/**
					 * @staticvar string $unix : Used to maintain a static timestamp for this query.
					 */
					static $unix = null;

					if ( ! isset( $unix ) )
						$unix = date( 'U' );

					//* Temporarily disable transients to prevent database spam.
					$this->the_seo_framework_use_transients = false;

					$the_id = 'unix_' . $unix;
				}
			} else {
				//* Other taxonomial archives.

				if ( empty( $taxonomy ) ) {
					$post_type = get_query_var( 'post_type' );

					if ( is_array( $post_type ) )
						reset( $post_type );

					if ( $post_type )
						$post_type_obj = get_post_type_object( $post_type );

					if ( isset( $post_type_obj->labels->name ) )
						$taxonomy = $post_type_obj->labels->name;
				}

				//* Still empty? Try this.
				if ( empty( $taxonomy ) )
					$taxonomy = get_query_var( 'taxonomy' );

				$the_id = $this->generate_taxonomial_cache_key( $page_id, $taxonomy );
				$the_id = 'archives_' . $the_id;
			}
		} else if ( ( $this->is_front_page( $page_id ) ) || ( $this->is_admin() && $this->is_menu_page( $this->pagehook ) ) ) {
			//* Fetch Home key.
			if ( $this->has_page_on_front() ) {
				//* Home is page.
				$the_id = 'hpage_' . $this->get_the_front_page_ID();
			} else {
				//* Home is blog.
				$the_id = 'hblog_' . $this->get_the_front_page_ID();
			}
		}

		/**
		 * Static Front page isn't set or something else is happening. Causes all kinds of problems :(
		 * Noob. :D
		 */
		if ( empty( $the_id ) ) {
			$the_id = 'noob_' . $page_id . '_' . $taxonomy;
		}

		/**
		 * This should be at most 25 chars. Unless the $blog_id is higher than 99,999,999.
		 * Then some cache keys will conflict on every 10th blog ID from eachother which post something on the same day..
		 * On the day archive. With the same description setting (short).
		 */
		return $cached_id[$page_id][$taxonomy] = $the_id . '_' . $blog_id . $locale;
	}

	/**
	 * Generates Cache key for taxonomial archives.
	 *
	 * @since 2.6.0
	 *
	 * @param int $page_id The taxonomy or page ID.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return string The Taxonomial Archive cache key.
	 */
	protected function generate_taxonomial_cache_key( $page_id = '', $taxonomy = '' ) {

		$the_id = '';

		$taxonomy_name = explode( '_', $taxonomy );
		if ( is_array( $taxonomy_name ) ) {
			foreach ( $taxonomy_name as $name )
				$the_id .= mb_substr( $name, 0, 3 ) . '_';
		}

		//* Put it all together.
		return rtrim( $the_id, '_' ) . '_' . $page_id;
	}

	/**
	 * Delete transient on post save.
	 *
	 * @since 2.2.9
	 *
	 * @param int $post_id The Post ID that has been updated.
	 *
	 * @return bool|null True when sitemap is flushed. False on revision. Null
	 * when sitemaps are deactivated.
	 */
	public function delete_transients_post( $post_id ) {

		$this->delete_auto_description_transient( $post_id );
		$this->delete_ld_json_transient( $post_id );

		if ( (bool) $this->get_option( 'sitemaps_output' ) !== false ) {

			//* Don't flush sitemap on revision.
			if ( wp_is_post_revision( $post_id ) )
				return false;

			$this->delete_sitemap_transient();

			return true;
		}
	}

	/**
	 * Delete transient on term save/deletion.
	 *
	 * @param int $term_id The Term ID
	 * @param int $tt_id The Term Taxonomy ID.
	 * @param string $taxonomy The Taxonomy type.
	 * @param mixed $deleted_term Copy of the already-deleted term. Unused.
	 *
	 * @since 2.3.3
	 */
	public function delete_auto_description_transients_term( $term_id, $tt_id, $taxonomy, $deleted_term = '' ) {

		$term_id = $term_id ? $term_id : $tt_id;

		$this->delete_auto_description_transient( $term_id, $taxonomy );
	}

	/**
	 * Checks wether the permalink structure is updated.
	 *
	 * @since 2.3.0
	 *
	 * @return bool Wether if sitemap transient is deleted.
	 */
	public function delete_sitemap_transient_permalink_updated() {

		if ( isset( $_POST['permalink_structure'] ) || isset( $_POST['category_base'] ) )
			return $this->delete_sitemap_transient();

		return false;
	}

	/**
	 * Delete transient for sitemap on requests.
	 * Also ping search engines.
	 *
	 * @since 2.2.9
	 *
	 * @return bool true
	 */
	public function delete_sitemap_transient() {

		delete_transient( $this->sitemap_transient );

		$this->ping_searchengines();

		return true;
	}

	/**
	 * Delete transient for the automatic description for blog on save request.
	 * Returns old option, since that's passed for sanitation within WP Core.
	 *
	 * @param string $old_option The previous blog description option.
	 *
	 * @since 2.3.3
	 *
	 * @return string Previous option.
	 */
	public function delete_auto_description_blog_transient( $old_option ) {

		$this->setup_auto_description_transient( (int) get_option( 'page_for_posts' ) );

		delete_transient( $this->auto_description_transient );

		return $old_option;
	}

	/**
	 * Delete transient for the automatic description on requests.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 *
	 * @since 2.3.3
	 *
	 * @return bool true
	 */
	public function delete_auto_description_transient( $page_id, $taxonomy = '' ) {

		$this->setup_auto_description_transient( $page_id, $taxonomy );

		delete_transient( $this->auto_description_transient );

		return true;
	}

	/**
	 * Delete transient for the LD+Json scripts on requests.
	 *
	 * @param mixed $page_id The page ID or identifier.
	 * @param string $taxonomy The tt name.
	 *
	 * @since 2.4.2
	 *
	 * @return bool true
	 */
	public function delete_ld_json_transient( $page_id, $taxonomy = '' ) {

		$flushed = null;

		if ( ! isset( $flushed ) ) {
			$this->setup_ld_json_transient( $page_id, $taxonomy );

			delete_transient( $this->ld_json_transient );

			$flushed = 'Oh behave!';

			return true;
		}

		return false;
	}

	/**
	 * Delete transient for the Theme doing it Right bool on special requests.
	 *
	 * @since 2.5.2
	 *
	 * @return bool true
	 */
	public function delete_theme_dir_transient() {

		delete_transient( $this->theme_doing_it_right_transient );

		return true;
	}

	/**
	 * Sets transient for Theme doing it Right
	 *
	 * @since 2.5.2
	 *
	 * @param bool $doing_it_right
	 */
	public function set_theme_dir_transient( $dir = '' ) {

		if ( is_bool( $dir ) && false === get_transient( $this->theme_doing_it_right_transient ) ) {

			//* Convert $dir to string 1 or 0 as transients can be false on failure.
			$dir = $dir ? '1' : '0';

			/**
			 * Expiration time, 3 days.
			 * 60s * 60m * 24d * 3d
			 */
			$expiration = 60 * 60 * 24 * 3;

			set_transient( $this->theme_doing_it_right_transient, $dir, $expiration );
		}

	}

	/**
	 * Flushes the home page LD+Json transient.
	 *
	 * @staticvar bool $flushed
	 * @since 2.6.0
	 */
	public function delete_front_ld_json_transient() {

		static $flushed = null;

		if ( isset( $flushed ) )
			return;

		$front_id = $this->get_the_front_page_ID();

		if ( 0 === $front_id )
			$front_id = 'hblog_' . (string) get_option( 'page_on_front' );

		$this->delete_ld_json_transient( $front_id );

		return $flushed = true;
	}

}
