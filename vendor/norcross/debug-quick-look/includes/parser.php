<?php
/**
 * Our various parser functions.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\Parser;

// Set our alias items.
use DebugQuickLook as Core;
use DebugQuickLook\Helpers as Helpers;
use DebugQuickLook\Formatting as Formatting;

/**
 * Kick off our parsing action.
 *
 * @param  boolean $parse  Whether to actually parse the file.
 *
 * @return void
 */
function run_parse( $parse = true ) {

	// Check to see if we have our constant.
	$hascon = Helpers\maybe_constant_set();

	// If no constant is set, display that message.
	if ( ! $hascon ) {
		wp_die( __( 'You have not set the WP_DEBUG constant correctly.', 'debug-quick-look' ), __( 'Config Setup Error', 'debug-quick-look' ) );
	}

	// Add the new die handler.
	add_filter( 'wp_die_handler', __NAMESPACE__ . '\die_handler' );

	// Set our debug log file.
	Helpers\check_debug_file();
	$debug_file = Helpers\get_debug_file();

	// If we didn't wanna parse the file, do it raw.
	if ( ! $parse ) {

		// Get our raw file.
		$raw_debug = file_get_contents( $debug_file );

		// And die with the raw.
		wp_die( '<pre class="debug-quick-look-raw">' . $raw_debug . '</pre>', __( 'Viewing Raw Debug', 'debug-quick-look' ) );
	}

	// Parse it.
	$parsed = parse_debug_file( $debug_file );

	// And show the world.
	wp_die( $parsed['display'], __( 'View Your File', 'debug-quick-look' ), [ 'totals' => absint( $parsed['totals'] ) ] );
}

/**
 * Handle parsing our logfile.
 *
 * @param  string $logfile  Which log file we want to debug.
 * @param  string $order    What order to display the entries.
 *
 * @return mixed
 */
function parse_debug_file( $logfile = '', $order = 'asc' ) {

	// Fetch the full lines.
	$lines = file( $logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

	// Run a quick right trim on each line.
	$lines = array_map( 'rtrim', $lines );

	// Set our empty.
	$setup = [];

	// Set a marker for the proper lines.
	$index = 0;

	// Loop the lines.
	foreach ( $lines as $nm => $linestring ) {

		// Get our first character, which we test with.
		$first = substr( $linestring, 0, 1 );

		// Starting with the date bracket.
		if ( '[' === esc_attr( $first ) ) {

			// Set the line index, in case we need to append it.
			$index = absint( $nm );

			// Set the line as a new array element.
			$setup[ $nm ] = $linestring;
		}

		// Now handle the non-bracket lines.
		if ( '[' !== esc_attr( $first ) ) {

			// Get our current line data.
			$start = $setup[ $index ];

			// Merge our current string with whatever we already had.
			$merge = $start . PHP_EOL . $linestring;

			// Add it to the merged string.
			$setup[ $index ] = $merge;
		}

		// Should be done here.
	}

	// Reset the array keys.
	$setup = array_values( $setup );

	// Run our individual formatting.
	$setup = Formatting\format_parsed_lines( $setup );

	// If we wanted descending, swap.
	if ( 'desc' === sanitize_text_field( $order ) ) {
		$setup = array_reverse( $setup );
	}

	// Return an array of the markup and count.
	return [
		'totals'  => count( $setup ),
		'display' => implode( "\n", $setup ),
	];
}

/**
 * Return our custom wp_die handler.
 *
 * @param  string $die_handler  The current handler.
 *
 * @return string
 */
function die_handler( $die_handler ) {
	return apply_filters( Core\HOOK_PREFIX . 'die_handler', '\DebugQuickLook\Handler\build_handler', $die_handler );
}
