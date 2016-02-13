<?php

add_action( 'wp_head', 'the_seo_framework_php_benchmark', -1 );
//* Benchmark PHP.
function the_seo_framework_php_benchmark() {

	//* Boolean.
	$b = true;

	//* Iterations
	$it = 10000000;

	//* Start the engines.
	$i = 0;
	$t = microtime(true);
	while ( $i < 10 ) {
		if ( $b ) {
			$a = $b;
		}
		if ( empty( $b ) ) {
			$a = $b;
		}
		if ( ! $b ) {
			$a = $b;
		}
		if ( isset( $b ) ) {
			$a = $b;
		}
		$i++;
	}
	$starttime = microtime(true) - $t;

	//* Loose
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( $b ) {
			// valuated
		}
		++$i;
	}
	$loosetime = microtime(true) - $t;

	//* Strict
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( true === $b ) {
			// valuated
		}
		++$i;
	}
	$stricttime = microtime(true) - $t;

	//* Strict Neg
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( true !== $b ) {
			// valuated
		}
		++$i;
	}
	$strictnegtime = microtime(true) - $t;

	//* Empty
	$i = 0;
	$t = microtime(true);
	while ( $i < $it ) {
		if ( empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$emptytime = microtime(true) - $t;

	//* Neg Empty
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( ! empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$negemptytime = microtime(true) - $t;

	//* False Empty
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( false === empty( $b ) ) {
			// valuated
		}
		++$i;
	}
	$strictemptytime = microtime(true) - $t;

	//* Isset
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( isset( $b ) ) {
			// valuated
		}
		++$i;
	}
	$issettime = microtime(true) - $t;

	//* Isset Strict
	$i = 0;
	$t = microtime(true);
	while( $i < $it ) {
		if ( true === isset( $b ) ) {
			// valuated
		}
		++$i;
	}
	$issetstricttime = microtime(true) - $t;

	//* With PHP 7 results @ 10,000,000 iterations.
	echo 'Loose time: ' . $loosetime . " seconds\r\n"; 					// 0.1115360260009765625 seconds
	echo 'Strict time: ' . $stricttime . " seconds\r\n";				// 0.1202042102813720703125 seconds
	echo 'Strict Neg time: ' . $strictnegtime . " seconds\r\n";			// 0.1270349025726318359375 seconds
	echo 'Empty time: ' . $emptytime . " seconds\r\n";					// 0.1297409534454345703125 seconds
	echo 'Neg Empty time: ' . $negemptytime . " seconds\r\n";			// 0.20085906982421875 seconds
	echo 'Strict Neg Empty time: ' . $strictemptytime . " seconds\r\n"; // 0.18640804290771484375 seconds
	echo 'Isset time: ' . $issettime . " seconds\r\n"; 					// 0.115377902984619140625 seconds
	echo 'Strict Isset time: ' . $issetstricttime . " seconds\r\n"; 	// 0.17035007476806640625 seconds

}
