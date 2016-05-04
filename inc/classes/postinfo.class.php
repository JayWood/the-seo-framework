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
 * Class AutoDescription_PostInfo
 *
 * Renders post/page states.
 *
 * @since 2.6.0
 */
class AutoDescription_PostInfo extends AutoDescription_PostData {

	/**
	 * Constructor, load parent constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Fetches Post content.
	 *
	 * @since 2.6.0
	 *
	 * @param int $id.
	 *
	 * @return string The post content.
	 */
	public function get_post_content( $id = 0 ) {

		if ( empty( $id ) ) {
			global $wp_query;

			if ( isset( $wp_query->post->post_content ) )
				return $wp_query->post->post_content;
		} else {
			$content = get_post_field( 'post_content', $id );

			if ( is_string( $content ) )
				return $content;
		}

		return '';
	}

}
