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
 * Class AutoDescription_Generate_Image
 *
 * Generates Image SEO data based on content.
 *
 * @since 2.6.0
 */
class AutoDescription_Generate_Image extends AutoDescription_Generate_Url {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Fetches og:image
	 *
	 * @uses get_header_image
	 *
	 * @param string $post_id the post ID
	 * @param string $image output url for image
	 * @param bool $escape Wether to escape the image url
	 *
	 * @since 2.2.1
	 *
	 * Applies filters string the_seo_framework_og_image_after_featured
	 * Applies filters string the_seo_framework_og_image_after_header
	 * @since 2.5.2
	 *
	 * @todo create options and upload area
	 */
	public function get_image( $post_id = '', $args = array(), $escape = true ) {

		if ( empty( $post_id ) )
			$post_id = $this->get_the_real_ID();

		if ( empty( $post_id ) )
			return '';

		$default_args = $this->parse_image_args( '', '', true );

		/**
		 * Parse args.
		 * @since 2.5.0
		 */
		if ( ! is_array( $args ) ) {
			//* Old style parameters are used. Doing it wrong.
			$this->_doing_it_wrong( __CLASS__ . '::' . __FUNCTION__, 'Use $args = array() for parameters.', '2.5.0' );
			$args = $default_args;
		} else if ( $args ) {
			$args = $this->parse_image_args( $args, $default_args );
		} else {
			$args = $default_args;
		}

		/**
		 * Backwards compat with parse args
		 * @since 2.5.0
		 */
		if ( ! isset( $args['post_id'] ) )
			$args['post_id'] = $post_id;

		//* 0. Image from argument.
		$image = $args['image'];

		//* Check if there are no disallowed arguments.
		$all_allowed = empty( $args['disallowed'] );

		//* 1. Fetch image from featured
		if ( empty( $image ) && ( $all_allowed || ! in_array( 'featured', $args['disallowed'] ) ) )
			$image = $this->get_image_from_post_thumbnail( $args );

		//* 2. Fetch image from fallback filter 1
		if ( empty( $image ) )
			$image = (string) apply_filters( 'the_seo_framework_og_image_after_featured', '', $args['post_id'] );

		//* 3. Fallback: Get header image if exists
		if ( empty( $image ) && ( $all_allowed || ! in_array( 'header', $args['disallowed'] ) ) )
			$image = get_header_image();

		//* 4. Fetch image from fallback filter 2
		if ( empty( $image ) )
			$image = (string) apply_filters( 'the_seo_framework_og_image_after_header', '', $args['post_id'] );

		//* 5. Get the WP 4.3.0 Site Icon
		if ( empty( $image ) && ( $all_allowed || ! in_array( 'icon', $args['disallowed'] ) ) )
			$image = $this->site_icon();

		/**
		 * Escape in Generation.
		 * @since 2.5.2
		 */
		if ( $escape && $image )
			return esc_url( $image );

		return $image;
	}

	/**
	 * Parse and sanitize image args.
	 *
	 * @param array $args required The passed arguments.
	 * @param array $defaults The default arguments.
	 * @param bool $get_defaults Return the default arguments. Ignoring $args.
	 *
	 * Applies filters the_seo_framework_og_image_args : {
	 *		@param string image The image url
	 *		@param mixed size The image size
	 *		@param bool icon Fetch Image icon
	 *		@param array attr Image attributes
	 *		@param array disallowed Disallowed image types : {
	 *			array (
	 * 				string 'featured'
	 * 				string 'header'
	 * 				string 'icon'
	 * 				string 'wpmudev-avatars'
	 *			)
	 * 		}
	 * }
	 * The image set in the filter will always be used as fallback
	 *
	 * @since 2.5.0
	 * @return array $args parsed args.
	 */
	public function parse_image_args( $args = array(), $defaults = array(), $get_defaults = false ) {

		//* Passing back the defaults reduces the memory usage.
		if ( empty( $defaults ) ) {
			$defaults = array(
				'post_id'	=> $this->get_the_real_ID(),
				'image'		=> '',
				'size'		=> 'full',
				'icon'		=> false,
				'attr'		=> array(),
				'disallowed' => array(),
			);

			//* @since 2.0.1
			$defaults = (array) apply_filters( 'the_seo_framework_og_image_args', $defaults, $args );
		}

		//* Return early if it's only a default args request.
		if ( $get_defaults )
			return $defaults;

		//* Array merge doesn't support sanitation. We're simply type casting here.
		$args['post_id'] 	= isset( $args['post_id'] ) 	? (int) $args['post_id'] 		: $defaults['post_id'];
		$args['image'] 		= isset( $args['image'] ) 		? (string) $args['image'] 		: $defaults['image'];
		$args['size'] 		= isset( $args['size'] ) 		? $args['size'] 				: $defaults['size']; // Mixed.
		$args['icon'] 		= isset( $args['icon'] ) 		? (bool) $args['icon'] 			: $defaults['icon'];
		$args['attr'] 		= isset( $args['attr'] ) 		? (array) $args['attr'] 		: $defaults['attr'];
		$args['disallowed'] = isset( $args['disallowed'] ) 	? (array) $args['disallowed'] 	: $defaults['disallowed'];

		return $args;
	}

	/**
	 * Fetches image from post thumbnail.
	 * Resizes the image between 1500px if bigger. Then it saves the image and
	 * Keeps dimensions relative.
	 *
	 * @param array $args Image arguments.
	 *
	 * @since 2.3.0
	 *
	 * @return string|null the image url.
	 */
	public function get_image_from_post_thumbnail( $args ) {

		if ( ! isset( $args['post_id'] ) )
			$args['post_id'] = $this->get_the_real_ID();

		$id = get_post_thumbnail_id( $args['post_id'] );

		$image = $id ? $this->parse_og_image( $id, $args ) : '';

		return $image;
	}

	/**
	 * Fetches images id's from WooCommerce gallery
	 *
	 * @staticvar array $ids The image ids
	 *
	 * @param array $args Image arguments.
	 *
	 * @since 2.5.0
	 *
	 * @return array The image URL's.
	 */
	public function get_image_from_woocommerce_gallery() {

		static $ids = null;

		if ( isset( $ids ) )
			return $ids;

		$attachment_ids = '';

		$post_id = $this->get_the_real_ID();

		if ( metadata_exists( 'post', $post_id, '_product_image_gallery' ) ) {
			$product_image_gallery = get_post_meta( $post_id, '_product_image_gallery', true );

			$attachment_ids = array_filter( explode( ',', $product_image_gallery ) );
		}

		return $ids = $attachment_ids;
	}

	/**
	 * Parses OG image to correct size
	 *
	 * @staticvar string $called Checks if image ID has already been fetched (to prevent duplicate output on WooCommerce).
	 *
	 * @param int $id The attachment ID.
	 * @param array $args The image args
	 *
	 * @since 2.5.0
	 *
	 * @todo create formula to fetch transient.
	 *
	 * @return string|empty Parsed image url or empty if already called
	 */
	public function parse_og_image( $id, $args = array() ) {

		//* Don't do anything if $id isn't given.
		if ( ! isset( $id ) || empty( $id ) )
			return;

		static $called = array();
		//* Don't parse image twice. Return empty on second run.
		if ( isset( $called[$id] ) )
			return '';

		if ( empty( $args ) )
			$args = $this->parse_image_args( '', '', true );

		$src = wp_get_attachment_image_src( $id, $args['size'], $args['icon'], $args['attr'] );

		$i = $src[0]; // Source URL
		$w = $src[1]; // Width
		$h = $src[2]; // Height

		//* Prefered 1500px, resize it
		if ( $w > 1500 || $h > 1500 ) {

			if ( $w === $h ) {
				//* Square
				$w = 1500;
				$h = 1500;
			} else if ( $w > $h ) {
				//* Landscape
				// var_dump( ) test this.
				$h = $this->proportionate_dimensions( $h, $w, $w = 1500 );
			} else if ( $h > $w ) {
				//* Portrait
				$w = $this->proportionate_dimensions( $w, $h, $h = 1500 );
			}

			//* Get path of image and load it into the wp_get_image_editor
			$i_file_path = get_attached_file( $id );

			$i_file_old_name	= basename( get_attached_file( $id ) );
			$i_file_ext			= pathinfo( $i_file_path, PATHINFO_EXTENSION );

			if ( $i_file_ext ) {
				$i_file_dir_name = pathinfo( $i_file_path, PATHINFO_DIRNAME );
				//* Add trailing slash.
				$i_file_dir_name = '/' === substr( $i_file_dir_name, -1 ) ? $i_file_dir_name : $i_file_dir_name . '/';

				$i_file_file_name = pathinfo( $i_file_path, PATHINFO_FILENAME );

				//* Yes I know, I should use generate_filename, but it's slower.
				//* Will look at that later. This is already 100 lines of correctly working code.
				$new_image_dirfile = $i_file_dir_name . $i_file_file_name . '-' . $w . 'x' . $h . '.' . $i_file_ext;

				/**
				 * Generate image URL.
				 */
				$upload_dir 	= wp_upload_dir();
				$upload_url 	= $upload_dir['baseurl'];
				$upload_basedir = $upload_dir['basedir'];
				$new_image_url = str_ireplace( $upload_basedir, '', $new_image_dirfile );
				$new_image_url = $upload_url . $new_image_url;

				//* We've got our image path.
				$i = $new_image_url;

				// Generate file if it doesn't exists yet.
				if ( ! file_exists( $new_image_dirfile ) ) {

					$image_editor = wp_get_image_editor( $i_file_path );

					if ( ! is_wp_error( $image_editor ) ) {
						$image_editor->resize( $w, $h, false );
						$image_editor->set_quality( 82 ); // Let's save some bandwidth, Facebook compresses it even further anyway.
						$image_editor->save( $new_image_dirfile );
					} else {
						//* Image has failed to create.
						$i = '';
					}
				}
			}
		}

		return $called[$id] = $i;
	}

	/**
	 * Fetches site icon brought in WordPress 4.3.0
	 *
	 * @param string $size The icon size, accepts 'full' and pixel values
	 * @since 2.2.1
	 *
	 * @return string url site icon, not escaped.
	 */
	public function site_icon( $size = 'full' ) {

		$icon = '';

		if ( 'full' === $size ) {
			$site_icon_id = get_option( 'site_icon' );

			if ( $site_icon_id ) {
				$url_data = '';
				$url_data = wp_get_attachment_image_src( $site_icon_id, $size );

				$icon = $url_data ? $url_data[0] : '';
			}

		} else if ( is_int( $size ) && function_exists( 'has_site_icon' ) && $this->wp_version( '4.3.0', '>=' ) ) {
			//* Also applies (MultiSite) filters.
			$icon = get_site_icon_url( $size );
		}

		return $icon;
	}

}
