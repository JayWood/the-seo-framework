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
class AutoDescription_Debug extends AutoDescription_Core {

	/**
	 * Enqueue the debug output.
	 *
	 * @since 2.6.0
	 *
	 * @var string The debug output.
	 */
	protected $debug_output = '';

	/**
	 * Constructor, load parent constructor and add actions.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'admin_footer', array( $this, 'debug_output' ) );
		add_action( 'admin_footer', array( $this, 'debug_screens' ) );
	}

	/**
	 * Echos debug output.
	 *
	 * @since 2.6.0
	 */
	public function debug_output() {

		if ( $this->the_seo_framework_debug && '' !== $this->debug_output ) {
			if ( $this->the_seo_framework_debug_hidden ) echo "<!--\r\n";

			?><div style="float:right;margin:3em;padding:1em;border:1px solid;background:#fff;color:#000;"><?php
				echo $this->debug_output;
			?></div><?php

			if ( $this->the_seo_framework_debug_hidden ) echo "\r\n-->";
		}

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

	/**
	 * Return debug values.
	 *
	 * @param mixed $values What to be output.
	 *
	 * @since 2.6.0
	 */
	public function get_debug_information( $values ) {

		$output = '';

		if ( $this->the_seo_framework_debug ) {

			$output .= "\r\n";
			$output .=  $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

			if ( false === isset( $values ) ) {
				$output .= $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';

				return $output;
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
				$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="margin:0;padding-left:12px">';
				foreach ( $values as $key => $value ) {
					if ( '' === $value ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( "''" );
						$output .= "\r\n";
					} else if ( is_string( $value ) || is_int( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ' . $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					} else if ( is_bool( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value ? 'true' : 'false' );
						$output .= "\r\n";
					} else if ( is_array( $value ) ) {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= "Array[\r\n";
						$output .= $this->the_seo_framework_debug_hidden ? '' : '<p style="margin:0;padding-left:12px">';
						foreach ( $value as $k => $v ) {
							if ( '' === $v ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( "''" );
								$output .= ',';
								$output .= "\r\n";
							} else if ( is_string( $v ) || is_int( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ' . $this->debug_value_wrapper( $v );
								$output .= ',';
								$output .= "\r\n";
							} else if ( is_bool( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v ? 'true' : 'false' );
								$output .= ',';
								$output .= "\r\n";
							} else if ( is_array( $v ) ) {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( 'Debug message: Three+ dimensional array.' );
								$output .= ',';
							} else {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v );
								$output .= ',';
								$output .= "\r\n";
							}
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<br />';
						}
						$output .= $this->the_seo_framework_debug_hidden ? '' : '</p>';
						$output .= "]";
					} else {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					}
					$output .= $this->the_seo_framework_debug_hidden ? '' : '<br />';
				}
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
			} else if ( '' === $values ) {
				$output .= $this->debug_value_wrapper( "''" );
			} else if ( is_string( $values ) || is_int( $values ) ) {
				$output .= $this->debug_value_wrapper( $values );
			} else if ( is_bool( $values ) ) {
				$output .= $this->debug_value_wrapper( $values ? 'true' : 'false' );
			} else {
				$output .= $this->debug_value_wrapper( $values );
			}

			$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';
			$output .= "\r\n";
		}

		return $output;
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

		if ( false === $this->the_seo_framework_debug_hidden )
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

		if ( false === is_scalar( $value ) )
			return 'Debug message: not scalar';

		if ( ! $this->the_seo_framework_debug_hidden )
			return '<span class="wp-ui-notification">' . esc_attr( (string) trim( $value ) ) . '</span>';

		return esc_attr( (string) $value );
	}

	/**
	 * Debug init. Simplified way of debugging a function.
	 *
	 * @since 2.6.0
	 *
	 * @param string $class The class name.
	 * @param string $method The function name.
	 * @param mixed function args.
	 *
	 * @return void early if debugging is disabled.
	 */
	public function debug_init( $class, $method ) {

		//* Something to consider.
		//

		if ( false === $this->the_seo_framework_debug )
			return;

		$output = '';

		if ( func_num_args() >= 3 ) {
			if ( $args = func_get_args() ) {

				var_dump( $args );

				$output = 'START: ' . $class . '::' . $method . "\r\n";

				foreach ( $args as $num => $a ) {

					if ( $num >= 2 ) {
						if ( is_array( $a ) ) {
							foreach ( $a as $k => $v ) {
								$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:6px">';
									$output .= (string) $k . ': ';
									$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
									$output .= gettype( $v ) . ': [';
									$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
										$output .= $this->get_debug_information( $v );
									$output .= $this->the_seo_framework_debug_hidden ? '' : '</div><br>';
									$output .= ']' . "\r\n";
								$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
							}
						} else {
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:6px">';
								$output .= (string) $num . ': ';
								$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
								$output .= gettype( $a ) . ': [';
								$output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="padding-left:12px">';
									$output .= $this->get_debug_information( $a );
								$output .= $this->the_seo_framework_debug_hidden ? '' : '</div><br>';
								$output .= ']' . "\r\n";
							$output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
						}
					}
				}
			}
		}

		if ( '' !== $output ) {
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="background:#dadada;margin-bottom:6px">';
			$this->debug_output .= $output;
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
		}
	}

	/**
	 * Reflect methods and their functions.
	 *
	 * @since ???
	 * @todo maybe.
	 */
	protected function reflection( $class, $method ) {
		// $ReflectionMethod = new ReflectionMethod( $class, $method );
	}

	/**
	 * Profile the plugin.
	 *
	 * @since 2.6.0
	 */
	public function profile() {

	}

}
