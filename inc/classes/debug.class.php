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
 * Class AutoDescription_Debug
 *
 * Holds plugin debug functions.
 *
 * @since 2.6.0
 */
class AutoDescription_Debug extends AutoDescription_Init {

	/**
	 * Constructor, load parent constructor and add actions.
	 */
	public function __construct() {
		parent::__construct();

		/**
		 * @since 2.5.0
		 *
		 * PHP 5.2 compat
		 * @since 2.5.2
		 */
		add_action( 'admin_footer', array( $this, 'debug_screens' ) );
	}

	/**
	 * Echo debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @since 2.3.4
	 */
	public function echo_debug_information( $values ) {

		if ( $this->the_seo_framework_debug ) {
			echo "\r\n";

			if ( ! $this->the_seo_framework_debug_hidden ) {
				echo "<br>\r\n";
				echo '<span class="code highlight">';
			}

			if ( ! isset( $values ) ) {
				echo $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
				return;
			}

			if ( is_object( $values ) ) {
				// Ugh.
				$values = (array) $values;

				if ( is_array( $values ) ) {
					foreach ( $values as $key => $value ) {
						if ( is_object( $value ) ) {
							foreach ( $values as $key => $value ) {
								$values = $value;
								break;
							}
						}
						break;
					}
				}
			}

			if ( is_array( $values ) ) {
				foreach ( $values as $key => $value ) {
					if ( '' === $value ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( "''" );
						echo "\r\n";
					} else if ( is_string( $value ) || is_int( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ' . $this->debug_value_wrapper( $value );
						echo "\r\n";
					} else if ( is_bool( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( $value ? 'true' : 'false' );
						echo "\r\n";
					} else if ( is_array( $value ) ) {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo "Array[\r\n";
						foreach ( $value as $k => $v ) {
							if ( '' === $v ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( "''" );
								echo ',';
								echo "\r\n";
							} else if ( is_string( $v ) || is_int( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ' . $this->debug_value_wrapper( $v );
								echo ',';
								echo "\r\n";
							} else if ( is_bool( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( $v ? 'true' : 'false' );
								echo ',';
								echo "\r\n";
							} else if ( is_array( $v ) ) {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( 'Debug message: Three+ dimensional array.' );
								echo ',';
							} else {
								echo $this->debug_key_wrapper( $k ) . ' => ';
								echo $this->debug_value_wrapper( $v );
								echo ',';
								echo "\r\n";
							}
						}
						echo "]";
					} else {
						echo $this->debug_key_wrapper( $key ) . ' => ';
						echo $this->debug_value_wrapper( $value );
						echo "\r\n";
					}
				}
			} else if ( '' === $values ) {
				echo $this->debug_value_wrapper( "''" );
			} else if ( is_string( $values ) || is_int( $values ) ) {
				echo $this->debug_value_wrapper( $values );
			} else if ( is_bool( $values ) ) {
				echo $this->debug_value_wrapper( $values ? 'true' : 'false' );
			} else {
				echo $this->debug_value_wrapper( $values );
			}

			if ( ! $this->the_seo_framework_debug_hidden ) {
				echo '</span>';
			}
			echo "\r\n";
		}

	}

	/**
	 * Wrap debug key in a colored span.
	 *
	 * @param string $key The debug key.
	 *
	 * @since 2.3.9
	 *
	 * @return string
	 */
	public function debug_key_wrapper( $key ) {

		if ( ! $this->the_seo_framework_debug_hidden )
			return '<font color="chucknorris">' . esc_attr( (string) $key ) . '</font>';

		return esc_attr( (string) $key );
	}

	/**
	 * Wrap debug value in a colored span.
	 *
	 * @param string $value The debug value.
	 *
	 * @since 2.3.9
	 *
	 * @return string
	 */
	public function debug_value_wrapper( $value ) {

		if ( ! is_scalar( $value ) )
			return 'Debug message: not scalar';

		if ( ! $this->the_seo_framework_debug_hidden )
			return '<span class="wp-ui-notification">' . esc_attr( (string) trim( $value ) ) . '</span>';

		return esc_attr( (string) $value );
	}

	/**
	 * Echo found screens in the admin footer when debugging is enabled.
	 *
	 * @uses bool $this->the_seo_framework_debug
	 * @global array $current_screen
	 *
	 * @since 2.5.2
	 */
	public function debug_screens() {

		if ( $this->the_seo_framework_debug ) {
			global $current_screen;

			?><div style="float:right;margin:3em;padding:1em;border:1px solid;background:#fff;color:#000;"><?php

				foreach( $current_screen as $screen )
					echo "<p>$screen</p>";

			?></div><?php
		}

	}

}
