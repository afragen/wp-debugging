<?php
/**
 * Our helper functions to use across the plugin.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\Helpers;

// Set our alias items.
use DebugQuickLook as Core;

/**
 * Run a quick check to see if the debug log constant is set.
 *
 * @return boolean
 */
function maybe_constant_set() {
	return defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? true : false;
}

/**
 * Build and return the data for our admin nodes.
 *
 * @return array
 */
function get_admin_bar_nodes() {

	// Set the view args.
	$view_args  = array(
		'id'        => 'quick-look-view',
		'title'     => __( 'View File', 'debug-quick-look' ),
		'href'      => esc_url( build_quicklook_url( 'view' ) ),
		'position'  => 0,
		'parent'    => 'debug-quick-look',
		'meta'      => array(
			'title'     => __( 'View File', 'debug-quick-look' ),
			'target'    => '_blank',
			'rel'       => 'noopener',
		),
	);

	// Set the raw args.
	$raw_args   = array(
		'id'        => 'quick-look-raw',
		'title'     => __( 'View File (Raw)', 'debug-quick-look' ),
		'href'      => esc_url( build_quicklook_url( 'raw' ) ),
		'position'  => 0,
		'parent'    => 'debug-quick-look',
		'meta'      => array(
			'title'     => __( 'View File (Raw)', 'debug-quick-look' ),
			'target'    => '_blank',
			'rel'       => 'noopener',
		),
	);

	// Set the purge args.
	$purge_args = array(
		'id'        => 'quick-look-purge',
		'title'     => __( 'Purge File', 'debug-quick-look' ),
		'href'      => esc_url( build_quicklook_url( 'purge' ) ),
		'position'  => 0,
		'parent'    => 'debug-quick-look',
		'meta'      => array(
			'title'     => __( 'Purge File', 'debug-quick-look' ),
		),
	);

	// Add the text node with our warning.
	$error_args = array(
		'id'        => 'quick-look-error',
		'title'     => __( 'The <span>WP_DEBUG_LOG</span> constant is not defined!', 'debug-quick-look' ),
		'position'  => 0,
		'parent'    => 'debug-quick-look',
		'meta'      => array(
			'class' => 'debug-quick-look-missing',
		),
	);

	// Return the array of data.
	return array( 'view' => $view_args, 'raw' => $raw_args, 'purge' => $purge_args, 'error' => $error_args );
}

/**
 * Build and return the single URL for a quick action.
 *
 * @param  string $action  The action being added.
 *
 * @return string
 */
function build_quicklook_url( $action = '' ) {

	// Set my nonce name and key.
	$nonce  = 'debug_quicklook_' . sanitize_text_field( $action ) . '_action';

	// Set up my args.
	$setup  = array(
		'quicklook' => 1,
		'debug'     => sanitize_text_field( $action ),
		'nonce'     => wp_create_nonce( $nonce ),
	);

	// And return the URL.
	return add_query_arg( $setup, admin_url( '/' ) );
}

/**
 * Set each function we want to use during formatting.
 *
 * @return array
 */
function get_formatting_args() {

	// Set the args.
	$setup  = array(
		'\wrap_dateblock'     => 'native',
		'\wrap_stacktrace'    => 'native',
		'\wrap_warning_types' => 'native',
		'\wrap_json_bits'     => 'native',
	);

	// Return our args, filtered.
	return apply_filters( Core\HOOK_PREFIX . 'formatting_args', $setup );
}

/**
 * Get our debug log with the option to filter.
 *
 * @return string
 */
function get_debug_file() {
	return apply_filters( Core\HOOK_PREFIX . 'debug_file', Core\DEBUG_FILE );
}

/**
 * Run a quick check to see if the debug log file is empty.
 *
 * @return boolean
 */
function check_debug_file() {

	// Set our file.
	$debug  = get_debug_file();

	// If no file exists at all, create an empty one.
	if ( false === file_exists( $debug ) ) {
		file_put_contents( $debug, '' );
	}

	// If the file is empty, return that.
	return 0 === filesize( $debug ) ? false : true;
}

/**
 * Purge the existing log file.
 *
 * @return boolean
 */
function purge_debug_file() {

	// Purge the data file.
	file_put_contents( get_debug_file(), '' );

	// And redirect with a query string.
	$direct = add_query_arg( array( 'quicklook' => 1, 'quickpurge' => 1 ), admin_url( '/' ) );

	// Then redirect.
	wp_redirect( $direct );
	exit;
}

/**
 * Check the user capabilities at various points.
 *
 * @param  string $location  Where on the admin we're checking.
 *
 * @return boolean
 */
function check_user_cap( $location = '' ) {

	// Set our role check.
	$setcap = apply_filters( Core\HOOK_PREFIX . 'user_cap', 'manage_options', $location );

	// Return the result of checking.
	return current_user_can( $setcap );
}
