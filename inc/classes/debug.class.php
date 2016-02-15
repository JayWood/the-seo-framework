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
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function		The function that was called.
	 * @param string $version		The version of WordPress that deprecated the function.
	 * @param string $replacement	Optional. The function that should have been called. Default null.
	 */
	public function _deprecated_function( $function, $version, $replacement = null ) {
		/**
		 * Fires when a deprecated function is called.
		 *
		 * @since WP Core 2.5.0
		 *
		 * @param string $function    The function that was called.
		 * @param string $replacement The function that should have been called.
		 * @param string $version     The version of WordPress that deprecated the function.
		 */
		do_action( 'deprecated_function_run', $function, $replacement, $version );

		/**
		 * Filter whether to trigger an error for deprecated functions.
		 *
		 * @since WP Core 2.5.0
		 *
		 * @param bool $trigger Whether to trigger the error for deprecated functions. Default true.
		 */
		if ( WP_DEBUG && apply_filters( 'deprecated_function_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler' ) );

			if ( function_exists( '__' ) ) {
				if ( isset( $replacement ) )
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework! Use %3$s instead.', 'autodescription' ), $function, $version, $replacement ) );
				else
					trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework with no alternative available.' ), $function, $version ) );
			} else {
				if ( isset( $replacement ) )
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework! Use %3$s instead.', $function, $version, $replacement ) );
				else
					trigger_error( sprintf( '%1$s is <strong>deprecated</strong> since version %2$s of The SEO Framework with no alternative available.', $function, $version ) );
			}

			restore_error_handler();
		}
	}

	/**
	 * Mark a function as deprecated and inform when it has been used.
	 *
	 * Taken from WordPress core, but added extra parameters and linguistic alterations.
	 *
	 * The current behavior is to trigger a user error if WP_DEBUG is true.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param string $function	The function that was called.
	 * @param string $message	A message explaining what has been done incorrectly.
	 * @param string $version	The version of WordPress where the message was added.
	 */
	public function _doing_it_wrong( $function, $message, $version ) {
		/**
		* Fires when the given function is being used incorrectly.
		*
		* @since WP Core 3.1.0
		*
		* @param string $function The function that was called.
		* @param string $message  A message explaining what has been done incorrectly.
		* @param string $version  The version of WordPress where the message was added.
		*/
		do_action( 'doing_it_wrong_run', $function, $message, $version );

		/**
		* Filter whether to trigger an error for _doing_it_wrong() calls.
		*
		* @since 3.1.0
		*
		* @param bool $trigger Whether to trigger the error for _doing_it_wrong() calls. Default true.
		*/
		if ( WP_DEBUG && apply_filters( 'doing_it_wrong_trigger_error', true ) ) {

			set_error_handler( array( $this, 'error_handler' ) );

			if ( function_exists( '__' ) ) {
				$version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s of The SEO Framework.)' ), $version );
				/* translators: %s: Codex URL */
				$message .= ' ' . sprintf( __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.', 'autodescription' ),
					__( 'https://codex.wordpress.org/Debugging_in_WordPress', 'autodescription' )
				);
				trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'autodescription' ), $function, $message, $version ) );
			} else {
				$version = is_null( $version ) ? '' : sprintf( '(This message was added in version %s of The SEO Framework.)', $version );
				$message .= ' ' . sprintf( 'Please see <a href="%s">Debugging in WordPress</a> for more information.',
					'https://codex.wordpress.org/Debugging_in_WordPress'
				);

				trigger_error( sprintf( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', $function, $message, $version ) );
			}

			restore_error_handler();
		}
	}

	/**
	 * The SEO Framework error handler.
	 *
	 * @access private
	 * Please don't use this error handler.
	 * Only handles notices.
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 *
	 * @return E_USER_NOTICE warning.
	 */
	protected function error_handler( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( $code >= 1024 ) {
			$backtrace = debug_backtrace();

			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger.
			 */
			$error = $backtrace[2];

			$file = $error['file'];
			$line = $error['line'];

			echo "\r\n" . '<strong>Notice:</strong> ' . $message . ' in ' . $file . ' on line ' . $line . ".\r\n";
		}

	}

	/**
	 * Echos debug output.
	 *
	 * @access private
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
	 * @access private
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
	 * @access private
	 * @since 2.6.0
	 */
	public function get_debug_information( $values ) {

		$output = '';

		if ( $this->the_seo_framework_debug ) {

			$output .= "\r\n";
			$output .=  $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

			if ( ! isset( $values ) ) {
				$output .= $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';

				return $output;
			}

			if ( is_object( $values ) ) {
				// Turn objects into values.
				$values = (array) $values;

				foreach ( $values as $key => $value ) {
					if ( is_object( $value ) ) {
						foreach ( (array) $value as $key => $v ) {
							$values = $v;
							break;
						}
					}
					break;
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
	 * @access private
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
	 * @access private
	 *
	 * @return string
	 */
	public function debug_value_wrapper( $value ) {

		if ( ! is_scalar( $value ) )
			return 'Debug message: not scalar';

		if ( false === $this->the_seo_framework_debug_hidden )
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
	 * @access private
	 *
	 * @return void early if debugging is disabled.
	 */
	public function debug_init( $class, $method ) {

		if ( false === $this->the_seo_framework_debug )
			return;

		$output = '';

		if ( func_num_args() >= 3 ) {
			if ( $args = func_get_args() ) {

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
			//* Store debug output.
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="background:#dadada;margin-bottom:6px">';
			$this->debug_output .= $output;
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
		}

	}


	/**
	 * Count the timings and memory usage. Dev only.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param bool $echo Wether to echo the total plugin time.
	 * @param bool $from_last Wether to echo the differences from the last timing.
	 *
	 * @staticvar bool $debug
	 *
	 * @return float The timer in seconds.
	 */
	public function profile( $echo = false, $from_last = false ) {

		static $debug = null;

		if ( false === isset( $debug ) )
			$debug = defined( 'THE_SEO_FRAMEWORK_DEBUG' ) && THE_SEO_FRAMEWORK_DEBUG ? true : false;

		if ( $debug ) {

			//* Get now.
			$time_now = microtime( true );
			$memory_usage_now = memory_get_usage();

			//* Calculate difference.
			$difference = $time_now - $this->timer_start;
			$difference_memory = $memory_usage_now - $this->memory_start;

			//* Add difference to total.
			$this->plugin_time = $this->plugin_time + $difference;
			$this->pugin_memory = $this->memory_usage + $difference;

			//* Reset timer and memory
			$this->timer_start = $time_now;
			$this->memory_start = $memory_usage_now;

			if ( false === $from_last ) {
				//* Return early if not allowed to echo.
				if ( false === $echo )
					return $this->plugin_time;

				//* Convert to string and echo if not returned yet.
				echo (string) "\r\n" . $this->plugin_time . "s\r\n";
				echo (string) ( $this->memory_usage / 1024 ) . "kiB\r\n";
			} else {
				//* Return early if not allowed to echo.
				if ( false === $echo )
					return $difference;

				//* Convert to string and echo if not returned yet.
				echo (string) "\r\n" . $difference . "s\r\n";
				echo (string) ( $difference_memory / 1024 ) . "kiB\r\n";
			}
		}
	}

}
