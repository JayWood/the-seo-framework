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

		add_action( 'admin_footer', array( $this, 'debug_screens' ) );
		add_action( 'admin_footer', array( $this, 'debug_output' ) );

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

			set_error_handler( array( $this, 'error_handler_deprecated' ) );

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

			set_error_handler( array( $this, 'error_handler_doing_it_wrong' ) );

			if ( function_exists( '__' ) ) {
				$version = is_null( $version ) ? '' : sprintf( __( '(This message was added in version %s of The SEO Framework.)' ), $version );
				/* translators: %s: Codex URL */
				$message .= ' ' . sprintf( __( 'Please see <a href="%s">Debugging in WordPress</a> for more information.', 'autodescription' ),
					__( 'https://codex.wordpress.org/Debugging_in_WordPress', 'autodescription' )
				);
				/* translators: 1: Function name, 2: Message, 3: Plugin Version notification */
				trigger_error( sprintf( __( '%1$s was called <strong>incorrectly</strong>. %2$s %3$s', 'autodescription' ), $function, $message, $version ) );
			} else {
				$version = is_null( $version ) ? '' : sprintf( '(This message was added in version %s of The SEO Framework.)', $version );
				$message .= ' ' . sprintf( 'Please see <a href="%s">Debugging in WordPress</a> for more information.',
					'https://codex.wordpress.org/Debugging_in_WordPress'
				);

				/* translators: 1: Function name, 2: Message, 3: Plugin Version notification */
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
	 * @see E_USER_NOTICE
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 */
	protected function error_handler_deprecated( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( $code >= 1024 && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger. 3 = Deprecated call.
			 */
			$error = $backtrace[3];

			$this->error_handler( $error, $message );
		}

	}

	/**
	 * The SEO Framework error handler.
	 *
	 * @access private
	 * Please don't use this error handler.
	 * Only handles notices.
	 * @see E_USER_NOTICE
	 *
	 * @since 2.6.0
	 *
	 * @param int Error handling code.
	 * @param string The error message.
	 */
	protected function error_handler_doing_it_wrong( $code, $message ) {

		//* Only do so if E_USER_NOTICE is pased.
		if ( $code >= 1024 && isset( $message ) ) {

			$backtrace = debug_backtrace();
			/**
			 * 0 = This function. 1 = Debug function. 2 = Error trigger.
			 */
			$error = $backtrace[2];

			$this->error_handler( $error, $message );
		}

	}

	/**
	 * Echo's error.
	 *
	 * @access private
	 * Please don't use this error handler.
	 *
	 * @since 2.6.0
	 *
	 * @param array $error The Error location and file.
	 * @param string $message The error message.
	 */
	protected function error_handler( $error, $message ) {

		$file = isset( $error['file'] ) ? $error['file'] : '';
		$line = isset( $error['line'] ) ? $error['line'] : '';

		if ( isset( $message ) ) {
			echo "\r\n" . '<strong>Notice:</strong> ' . $message;
			echo $file ? ' In ' . $file : '';
			echo $line ? ' on line ' . $line : '';
			echo ".<br>\r\n";
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

			$this->debug_init( __CLASS__, __FUNCTION__, false, get_defined_vars() );
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

			?><div style="float:right;margin:3em;padding:1em;border:1px solid;background:#fff;color:#000;max-width:80%;max-width:calc( 100% - 280px )"><?php
				echo $this->debug_output;
			?></div><?php

			if ( $this->the_seo_framework_debug_hidden ) echo "\r\n-->";
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
	public function get_debug_information( $values = null ) {

		$output = '';

		if ( $this->the_seo_framework_debug ) {

			$output .= "\r\n";
			$output .=  $this->the_seo_framework_debug_hidden ? '' : '<span class="code highlight">';

			if ( is_null( $values ) ) {
				$output .= $this->debug_value_wrapper( "Debug message: Value isn't set." ) . "\r\n";
				$output .= $this->the_seo_framework_debug_hidden ? '' : '</span>';

				return $output;
			}

			if ( is_object( $values ) ) {
				//* Turn objects into arrays.
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
								$output .= $this->debug_value_wrapper( 'Debug message: Three+ dimensional array' );
								$output .= ',';
							} else {
								$output .= $this->debug_key_wrapper( $k ) . ' => ';
								$output .= $this->debug_value_wrapper( $v );
								$output .= ',';
								$output .= "\r\n";
							}
							$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
						}
						$output .= $this->the_seo_framework_debug_hidden ? '' : '</p>';
						$output .= "]";
					} else {
						$output .= $this->debug_key_wrapper( $key ) . ' => ';
						$output .= $this->debug_value_wrapper( $value );
						$output .= "\r\n";
					}
					$output .= $this->the_seo_framework_debug_hidden ? '' : '<br>';
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
	 * @param bool $ignore Ignore the hidden output.
	 *
	 * @since 2.3.9
	 * @access private
	 *
	 * @return string
	 */
	public function debug_key_wrapper( $key, $ignore = false ) {

		if ( $ignore || false === $this->the_seo_framework_debug_hidden )
			return '<font color="chucknorris">' . esc_attr( (string) $key ) . '</font>';

		return esc_attr( (string) $key );
	}

	/**
	 * Wrap debug value in a colored span.
	 *
	 * @param string $value The debug value.
	 * @param bool $ignore Ignore the hidden output.
	 *
	 * @since 2.3.9
	 * @access private
	 *
	 * @return string
	 */
	public function debug_value_wrapper( $value, $ignore = false ) {

		if ( ! is_scalar( $value ) )
			return 'Debug message: not scalar';

		if ( $ignore || false === $this->the_seo_framework_debug_hidden )
			return '<span class="wp-ui-notification">' . esc_attr( (string) trim( $value ) ) . '</span>';

		return esc_attr( (string) $value );
	}

	/**
	 * Debug init. Simplified way of debugging a function, only works in admin.
	 *
	 * @since 2.6.0
	 *
	 * @param string $class The class name.
	 * @param string $method The function name.
	 * @param bool $store Whether to store the output in cache for next run to pick up on.
	 *
	 * @param mixed function args.
	 *
	 * @access private
	 *
	 * @return void early if debugging is disabled.
	 */
	protected function debug_init( $class, $method, $store = false ) {

		if ( false === $this->the_seo_framework_debug || false === $this->is_admin() )
			return;

		$output = '';

		if ( func_num_args() >= 4 ) {

			//* Cache the args for $store.
			static $cached_args = array();

			$args = array_slice( func_get_args(), 3 );
			$key = $class . '_' . $method;

			if ( $store ) {
				$this->profile( false, false, 'time', $key ) . ' seconds';
				$this->profile( false, false, 'memory', $key ) . ' bytes';

				$cached_args[$class][$method] = $args;
				return;
			} else {

				/**
				 * Generate human-readable debug keys and echo it when it's called.
				 * Matched value is found within the $output.
				 *
				 * @staticvar int $loop
				 */
				static $loop = 0;
				$loop++;
				$debug_key = '<p>[Debug key: ' . $loop . ' - ' . $method . ']</p>';

				echo $debug_key;
				$output .= $debug_key;

				if ( isset( $cached_args[$class][$method] ) ) {
					$args[] = array(
						'profile' => array(
							'time' => $this->profile( false, true, 'time', $key ) . ' seconds',
							'memory' => $this->profile( false, true, 'memory', $key ) . ' bytes'
						)
					);

					$args = array_merge( $cached_args[$class][$method], $args );
					$cached_args[$class][$method] = null;
				}
			}

			if ( $args ) {

				$output .= $class . '::' . $method . "\r\n";

				foreach ( $args as $num => $a ) {
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

		if ( '' !== $output ) {
			//* Store debug output.
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '<div style="background:#dadada;margin-bottom:6px">';
			$this->debug_output .= $output;
			$this->debug_output .= $this->the_seo_framework_debug_hidden ? '' : '</div>';
		}

	}

	/**
	 * Count the timings and memory usage.
	 * Memory usage fetching is unreliable, i.e. Opcode.
	 *
	 * @since 2.6.0
	 * @access private
	 *
	 * @param bool $echo Whether to echo the total plugin time.
	 * @param bool $from_last Whether to echo the differences from the last timing.
	 * @param string $what Whether to return the time or memory.
	 * @param string $key When used, it will detach the profiling separately.
	 *
	 * @staticvar bool $debug
	 *
	 * @return float The timer in seconds. Or memory in Bytes when $what is 'memory'.
	 */
	public function profile( $echo = false, $from_last = false, $what = 'time', $key = '' ) {

		if ( $this->the_seo_framework_profile ) {

			static $timer_start = array();
			static $memory_start = array();
			static $plugin_time = array();
			static $plugin_memory = array();

			$timer_start[$key] = isset( $timer_start[$key] ) ? $timer_start[$key] : 0;
			$memory_start[$key] = isset( $memory_start[$key] ) ? $memory_start[$key] : 0;
			$plugin_time[$key] = isset( $plugin_time[$key] ) ? $plugin_time[$key] : 0;
			$plugin_memory[$key] = isset( $plugin_memory[$key] ) ? $plugin_memory[$key] : 0;

			//* Get now.
			$time_now = microtime( true );
			$memory_usage_now = memory_get_usage();

			//* Calculate difference.
			$difference_time = $time_now - $timer_start[$key];
			$difference_memory = $memory_usage_now - $memory_start[$key];

			//* Add difference to total.
			$plugin_time[$key] = $plugin_time[$key] + $difference_time;
			$plugin_memory[$key] = $plugin_memory[$key] + $difference_memory;

			//* Reset timer and memory
			$timer_start[$key] = $time_now;
			$memory_start[$key] = $memory_usage_now;

			if ( false === $from_last ) {
				//* Return early if not allowed to echo.
				if ( false === $echo ) {
					if ( 'time' === $what )
						return number_format( $plugin_time[$key], 5 );

					return $plugin_memory[$key];
				}

				//* Convert to string and echo if not returned yet.
				echo (string) "\r\n" . $plugin_time[$key] . "s\r\n";
				echo (string) ( $plugin_memory[$key] / 1024 ) . "kiB\r\n";
			} else {
				//* Return early if not allowed to echo.
				if ( false === $echo ) {
					if ( 'time' === $what )
						return number_format( $difference_time, 5 );

					return $difference_memory;
				}

				//* Convert to string and echo if not returned yet.
				echo (string) "\r\n" . $difference_time . "s\r\n";
				echo (string) ( $difference_memory / 1024 ) . "kiB\r\n";
			}

		}

	}

}
