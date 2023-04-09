<?php
/**
 * Our various formatting functions.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\Formatting;

// Set our alias items.
use DebugQuickLook as Core;
use DebugQuickLook\Helpers as Helpers;

/**
 * Format each line of our log data array.
 *
 * @param  array $lines  The log file lines.
 *
 * @return array
 */
function format_parsed_lines( $lines ) {

	// Filter the lines.
	$lines = apply_filters( Core\HOOK_PREFIX . 'before_line_parse', $lines );

	// Now return the lines.
	return array_map( __NAMESPACE__ . '\format_single_line', $lines );
}

/**
 * Format the single parsed line.
 *
 * @param  string $single  The single line.
 *
 * @return string          The formatted line.
 */
function format_single_line( $single ) {

	// Set our block class before we start manupulating.
	$div_class = set_parse_block_class( $single );

	// Get our formatting args.
	$formatting = Helpers\get_formatting_args();

	// Do something if we have no formatting.
	if ( ! $formatting ) {
		return wrap_final_return( $single, $div_class );
	}

	// Loop our formatting functions.
	foreach ( $formatting as $format => $source ) {

		// Check which formatting callback is setup.
		$format = 'native' !== sanitize_text_field( $source ) ? $format : __NAMESPACE__ . $format;

		// Run each function individually.
		$single = call_user_func( $format, $single );
	}

	// Now return the whole thing.
	return wrap_final_return( $single, $div_class );
}

/**
 * Create a class for log file block.
 *
 * @param  string $single  The single line from the log file.
 *
 * @return string $class   The resulting class.
 */
function set_parse_block_class( $single ) {

	// Set the notice types we want.
	$types = [
		'notice'       => 'PHP Notice:',
		'warning'      => 'PHP Warning:',
		'fatal'        => 'PHP Fatal error:',
		'wordpress-db' => 'WordPress database error',
		'stack-trace'  => 'Stack trace:',
		'wp-community' => 'WP_Community_Events',
	];

	// Filter the available types.
	$types = apply_filters( Core\HOOK_PREFIX . 'block_class_types', $types );

	// Set our default class.
	$data[] = 'log-entry-block';

	// Now loop them and check each one.
	foreach ( $types as $key => $text ) {

		// Bail if we don't have it.
		if ( strpos( $single, $text ) === false ) {
			continue;
		}

		// Add the key to our class data array.
		$data[] = 'log-entry-block-' . esc_attr( $key );
	}

	// Filter the array of classes.
	$items = apply_filters( Core\HOOK_PREFIX . 'block_classes', $data );

	// Make sure each one is sanitized.
	$setup = array_map( 'sanitize_html_class', $items );

	// Return the whole thing.
	return implode( ' ', $setup );
}

/**
 * Set up the date block.
 *
 * @param  string $single  The single line.
 *
 * @return string          The formatted line.
 */
function wrap_dateblock( $single ) {

	// Set up our formatting rules.
	$format = '~
		\[(              # open outer square brackets and capturing group
		(?:              # open subpattern for optional inner square brackets
		    [^[\]]*      # non-square-bracket characters
		    \[           # open inner square bracket
		    [^[\]]*      # non-square-bracket characters
		    ]            # close inner square bracket
		)*               # end subpattern and repeat it 0 or more times
		[^[\]]*          # non-square-bracket characters
		)]               # end capturing group and outer square brackets
		(?:              # open subpattern for optional parentheses
		    \((          # open parentheses and capturing group
		    [a-z]+       # letters
		    )\)          # close capturing group and parentheses
		)?               # end subpattern and make it optional
		~isx';

	// Run the big match for the date bracket.
	preg_match( $format, $single, $matches );

	// If we don't have the dateblock, return what we had.
	if ( empty( $matches[0] ) ) {
		return $single;
	}

	// Format our date.
	$fdate = date( 'c', strtotime( $matches[1] ) );

	// Set the markup.
	$markup = '<span class="log-entry-date"><time datetime="' . esc_attr( $fdate ) . '">' . $matches[0] . '</time></span>';

	// Wrap the dateblock itself in a time.
	$setup = str_replace( $matches[0], $markup, $single );

	// Return it.
	return apply_filters( Core\HOOK_PREFIX . 'dateblock_wrap', $setup, $single );
}

/**
 * Set up the stack trace list.
 *
 * @param  string $single  The single line.
 *
 * @return string          The formatted line.
 */
function wrap_stacktrace( $single ) {

	// Set up our formatting rules.
	$format = "/Stack trace:\n((?:#\d*.+\n*)+)/m";

	// Run the match check.
	preg_match( $format, $single, $matches );

	// If we don't have the list, return what we had.
	if ( empty( $matches[1] ) ) {
		return $single;
	}

	// Set our string to be modified.
	$setup = $single;

	// Create an array of the stack data.
	$items = explode( PHP_EOL, $matches[1] );

	// Set the empty list tagged items.
	$ltags = '';

	// Wrap each one with a list tag.
	foreach ( $items as $line_item ) {
		$ltags .= '<li class="log-entry-stack-trace-list-item">' . trim( $line_item ) . '</li>';
	}

	// Wrap the list with the ul tag.
	$ulwrap = '<ul class="log-entry-stack-trace-list-wrap">' . $ltags . '</ul>';

	// Now merge in the list.
	$merged = str_replace( $matches[1], $ulwrap, $setup );

	// Set my title.
	$twrap = '<p class="log-entry-stack-trace-title">Stack trace:</p>';

	// Wrap the stack trace word in a paragraph.
	$setup = str_replace( 'Stack trace:', $twrap, $merged );

	// Return it.
	return apply_filters( Core\HOOK_PREFIX . 'stacktrace_wrap', $setup, $single );
}

/**
 * Wrap any warning types with markup.
 *
 * @param  string $single  The single line from the log file.
 *
 * @return string $single  The formatted line from the log file.
 */
function wrap_warning_types( $single ) {

	// Set the notice types we want.
	$types = [
		'notice'       => 'PHP Notice:  ',
		'warning'      => 'PHP Warning:  ',
		'fatal'        => 'PHP Fatal error:  ',
		'wordpress-db' => 'WordPress database error ',
		'wp-community' => 'WP_Community_Events::maybe_log_events_response: ',
	];

	// Filter the available types.
	$types = apply_filters( Core\HOOK_PREFIX . 'warning_types', $types );

	// Set our string to be modified.
	$setup = $single;

	// Now loop them and check each one.
	foreach ( $types as $key => $text ) {

		// Bail if we don't have it.
		if ( strpos( $single, $text ) === false ) {
			continue;
		}

		// Set the notice class.
		$nclass = 'log-entry-error-label log-entry-error-' . esc_attr( $key ) . '-label';

		// Set up the wrapped item.
		$markup = '<span class="' . esc_attr( $nclass ) . '">' . esc_html( rtrim( $text, ': ' ) ) . '</span>' . PHP_EOL;

		// Now wrap it in some markup.
		$setup = str_replace( $text, $markup, $setup );
	}

	// Return it with our filter.
	return apply_filters( Core\HOOK_PREFIX . 'warning_types_wrap', $setup, $single );
}

/**
 * Wrap any JSON that may exist.
 *
 * @param  string $single  The single line from the log file.
 *
 * @return string $single  The formatted line from the log file.
 */
function wrap_json_bits( $single ) {

	// Set my format for finding JSON.
	$format = '/\{(?:[^{}]|(?R))*\}/x';

	// Attempt the preg_match.
	preg_match_all( $format, $single, $matches );

	// Bail if we have none.
	if ( empty( $matches[0] ) ) {
		return $single;
	}

	// Set our string to be modified.
	$setup = $single;

	// Loop each bit of JSON and attempt to format it.
	foreach ( $matches[0] as $found_json ) {

		// Now attempt to decode it.
		$maybe_json = json_decode( $found_json, true );

		// If we threw an error, return the single line.
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			continue;
		}

		// Get my markup and wrap it in a div.
		$markup = '<div class="log-entry-json-array-section">' . format_json_array( $maybe_json ) . '</div>';

		// Now wrap it in some markup.
		$setup = str_replace( $found_json, $markup, $setup );
	}

	// Return it with our filter.
	return apply_filters( Core\HOOK_PREFIX . 'json_bits_wrap', $setup, $single );
}

/**************************************
  Set the various callback formatting.
 ***************************************/

/**
 * Take the JSON array and make it fancy.
 *
 * @param  array $maybe_json  The array parsed from the JSON.
 *
 * @return HTML
 */
function format_json_array( $maybe_json ) {

	// Set my empty build.
	$build = '';

	// Wrap the whole thing in a list.
	$build .= '<ul class="log-entry-json-array-wrap">';

	// Loop my array and start checking.
	foreach ( $maybe_json as $key => $value ) {

		// Open it as a list.
		$build .= '<li class="log-entry-json-array-item">';

			// Set the key as our first label.
			$build .= '<span class="log-entry-json-array-piece">' . esc_html( $key ) . '</span>';

			// Add our splitter.
			$build .= '<span class="log-entry-json-array-piece log-entry-json-array-splitter">&nbsp;&equals;&gt;&nbsp;</span>';

			// If the value isn't an array, make a basic list item.
		if ( ! is_array( $value ) ) {

			// Make boolean a string for display.
			$value = is_bool( $value ) ? var_export( $value, true ) : $value;

			// Just wrap the piece as per usual.
			$build .= '<span class="log-entry-json-array-piece">' . esc_html( $value ) . '</span>';

		} else {

			// Set the "array" holder text.
			$build .= '<span class="log-entry-json-array-piece"><em>' . esc_html__( '(array)', 'debug-quick-look' ) . '</em></span>';

			// And get recursive with it.
			$build .= format_json_array( $value );
		}

		// Close the item inside.
		$build .= '</li>';
	}

	// Close my list.
	$build .= '</ul>';

	// Return the entire thing.
	return $build;
}

/**
 * Handle the final markup adding.
 *
 * @param  string $single  The formatted line from the log file.
 * @param  string $class   What class to include on the div.
 *
 * @return string $build   The line wrapped in some divs.
 */
function wrap_final_return( $single, $class = '' ) {

	// Now set our display.
	$build = '';

	// Set the div wrapper.
	$build .= '<div class="' . esc_attr( $class ) . '">';

		// Add a second div wrapper to mimic the <pre> tag stuff.
		$build .= '<div class="log-entry-block-pre-wrap">';

			// Output to handle the text remaining.
			$build .= wpautop( $single, false ); // . "\n";

		// Close the div wrapper.
		$build .= '</div>';

	// Close the div wrapper.
	$build .= '</div>';

	// Now return the whole thing.
	return $build;
}
