<?php
/**
 * Our custom wp_die handler.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\Handler;

// Set our alias items.
use DebugQuickLook as Core;
use DebugQuickLook\Helpers as Helpers;
use DebugQuickLook\Formatting as Formatting;

/**
 * Output the custom wp_die display we built.
 *
 * @param  mixed  $message  The data to display
 * @param  string $title    Our file title.
 * @param  array  $args     Any additional args passed.
 *
 * @return void
 */
function build_handler( $message, $title = '', $args = array() ) {

	// Set an empty.
	$build  = '';

	// Set the doctype.
	$build .= '<!DOCTYPE html>';

	// Set the opening HTML tag.
	$build .= '<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">';

	// Output the head tag.
	$build .= handler_head_tag( $title, $args );

	// Output the body.
	$build .= handler_body_tag( $message, $args );

	// Close out the final HTML tag.
	$build .= '</html>';

	// Echo out the display.
	echo $build;

	// Then a regular die() to finish.
	die();
}

/**
 * Set up the <head> tag.
 *
 * @param  string  $title  The title to output.
 * @param  array   $args   The optional args that were passed.
 * @param  boolean $echo   Whether to echo or return.
 *
 * @return mixed
 */
function handler_head_tag( $title = '', $args = array(), $echo = false ) {

	// Determine the page title.
	$title  = ! empty( $title ) ? sanitize_text_field( $title ) : __( 'View Your File', 'debug-quick-look' );

	// Set an empty.
	$build  = '';

	// Set the opening head tag.
	$build .= '<head>' . "\n";

		// Include our action to run after we opened the head tag.
		$build .= do_action( Core\HOOK_PREFIX . 'after_head_tag_open', $args );

		// Include the basic meta tags.
		$build .= '<meta charset="utf-8">' . "\n";
		$build .= '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />' . "\n";
		$build .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>' . "\n";

		// Check the 'no robots', but output ourselves since the function only echos.
		if ( function_exists( 'wp_no_robots' ) ) {
			$build .= '<meta name="robots" content="noindex,follow" />' . "\n";
		}

		// Load our CSS.
		$build .= load_handler_css();

		// Output the title tag.
		$build .= '<title>' . esc_html( $title ) . '</title>' . "\n";

		// Include our action to run before we close the head tag.
		$build .= do_action( Core\HOOK_PREFIX . 'before_head_tag_close', $args );

	// Close out the head tag.
	$build .= '</head>';

	// Echo if requested.
	if ( $echo ) {
		echo $build;
	}

	// Just return the build.
	return $build;
}

/**
 * Set up the <body> tag.
 *
 * @param  string  $message  The total message output.
 * @param  array   $args     The optional args that were passed.
 * @param  boolean $echo     Whether to echo or return.
 *
 * @return mixed
 */
function handler_body_tag( $message = '', $args = array(), $echo = false ) {

	// Set an empty.
	$build  = '';

	// Set the opening body tag.
	$build .= '<body class="debug-quick-look">' . "\n";

		// Include our action to run after we opened the body tag.
		$build .= do_action( Core\HOOK_PREFIX . 'after_body_tag_open', $args );

		// Set the intro
		$build .= load_handler_intro( $args );

		// Output the message.
		$build .= load_handler_message( $message );

		// Include our action to run before we close the body tag.
		$build .= do_action( Core\HOOK_PREFIX . 'before_body_tag_close', $args );

	// Close out the body tag.
	$build .= '</body>';

	// Echo if requested.
	if ( $echo ) {
		echo $build;
	}

	// Just return the build.
	return $build;
}

/**
 * Load our CSS to display.
 *
 * @return mixed
 */
function load_handler_css() {

	// Set my stylesheet URL.
	$stylesheet = apply_filters( Core\HOOK_PREFIX . 'stylesheet', Core\ASSETS_URL . '/css/debug-quick-look.css' );

	// If we haven't already run the admin_head function, output the file.
	if ( ! did_action( 'admin_head' ) ) {

		// Set my stylesheet URL.
		$stylesheet = add_query_arg( array( 'ver' => time() ), $stylesheet );

		// And just return
		return '<link href="' . esc_url( $stylesheet ) . '" rel="stylesheet" type="text/css">' . "\n";
	}

	// Get my raw CSS.
	$style  = @file_get_contents( $stylesheet );

	// Set it displayed via filter.
	$display = apply_filters( Core\HOOK_PREFIX . 'raw_css', $style );

	// Wrap it in a style tag and return it.
	return ! $display ? false : '<style type="text/css">' . $display . '</style>' . "\n";
}

/**
 * Load our introduction to display.
 *
 * @param  array $args  The optional args that were passed.
 *
 * @return mixed
 */
function load_handler_intro( $args = array() ) {

	// Bail if we said to skip the intro.
	if ( ! empty( $args['skip-intro'] ) ) {
		return;
	}

	// Set my purge URL.
	$purge  = Helpers\build_quicklook_url( 'purge' );

	// Set the totals variable.
	$ttlnum = ! empty( $args['totals'] ) ? $args['totals'] : 0;

	// Set the totals display string.
	$totals = sprintf( __( 'Total log entries: %s', 'debug-quick-look' ), '<strong>' . absint( $ttlnum ) . '</strong>' );

	// Set an empty.
	$build  = '';

	// Set the opening div.
	$build .= '<div class="debug-quick-look-intro">' . "\n";

		// Output the paragraph wrapper.
		$build .= '<p>';

			// Handle the return link output.
			$build .= '<a class="debug-intro-link debug-intro-return-link" href="' . admin_url( '/' ) . '">&laquo; ' . esc_html__( 'Return To Admin Dashboard', 'debug-quick-look' ) . '</a>';

			// Output the entry count.
			$build .= '<span class="debug-quick-look-intro-count">' . $totals . '</span>';

			// Handle the purge links output.
			$build .= '<a class="debug-intro-link debug-intro-purge-link" href="' . esc_url( $purge ) . '">&times; ' . esc_html__( 'Purge File', 'debug-quick-look' ) . '</a>';

			// Display log file path.
			$build .= '<span class="debug-intro-error-file">' . Core\DEBUG_FILE . '</span>';

		// Close the paragraph
		$build .= '</p>' . "\n";

		// Include our action to run inside the div.
		$build .= do_action( Core\HOOK_PREFIX . 'inside_intro_block', $args );

	// Close out the div tag.
	$build .= '</div>' . "\n";

	// Include our action to run after the div.
	$build .= do_action( Core\HOOK_PREFIX . 'after_intro_block', $args );

	// Just return the build.
	return $build;
}

/**
 * Load our message to display.
 *
 * @param  string  $message  The total message output.
 *
 * @return mixed
 */
function load_handler_message( $message ) {

	// Set an empty.
	$build  = '';

	// Set the opening div.
	$build .= '<div class="debug-quick-look-block-list">' . "\n";

		// Output the actual link.
		$build .= $message . "\n";

	// Close out the div tag.
	$build .= '</div>' . "\n";

	// Just return the build.
	return $build;
}
