<?php
/**
 * Our actions functions.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\Actions;

// Set our alias items.
use DebugQuickLook as Core;
use DebugQuickLook\Helpers as Helpers;
use DebugQuickLook\Parser as Parser;
use DebugQuickLook\Formatting as Formatting;

/**
 * Start our engines.
 */
add_action( 'admin_init', __NAMESPACE__ . '\run_quicklook_action' );
add_filter( 'removable_query_args', __NAMESPACE__ . '\add_removable_arg' );
add_action( 'admin_notices', __NAMESPACE__ . '\display_purge_result' );

/**
 * Run the quicklook action if we've requested it.
 *
 * @return void
 */
function run_quicklook_action() {

	// Run my cap check.
	$hascap = Helpers\check_user_cap( 'quicklook-action' );

	// Bail without.
	if ( ! $hascap ) {
		return;
	}

	// Bail without the query strings or not on admin.
	if ( ! is_admin() || empty( $_GET['quicklook'] ) || empty( $_GET['debug'] ) ) {
		return;
	}

	// Set our debug key.
	$setkey = sanitize_text_field( $_GET['debug'] );

	// Check to see if our nonce was provided.
	if ( empty( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'debug_quicklook_' . $setkey . '_action' ) ) {
		return;
	}

	// Include our action to run beforehand.
	do_action( Core\HOOK_PREFIX . 'before_action', $setkey );

	// Switch through and return the item.
	switch ( esc_attr( $setkey ) ) {

		case 'view':
			Parser\run_parse();
			break;

		case 'raw':
			Parser\run_parse( false );
			break;

		case 'purge':
			Helpers\purge_debug_file();
			break;

		// End all case breaks.
	}

	// Include our action to run afterwards.
	do_action( Core\HOOK_PREFIX . 'after_action', $setkey );

	// And just be finished.
	return;
}

/**
 * Add our custom strings to the vars.
 *
 * @param  array $args  The existing array of args.
 *
 * @return array $args  The modified array of args.
 */
function add_removable_arg( $args ) {

	// Include my new args and return.
	return wp_parse_args( [ 'quicklook', 'quickpurge' ], $args );
}

/**
 * Echo out the notification.
 *
 * @return HTML
 */
function display_purge_result() {

	// Bail without the query strings or not on admin.
	if ( ! is_admin() || empty( $_GET['quicklook'] ) || empty( $_GET['quickpurge'] ) ) {
		return;
	}

	// Show the message.
	echo '<div class="notice notice-success is-dismissible">';
		echo '<p>' . esc_html__( 'Success! Your debug file has been purged.', 'debug-quick-look' ) . '</p>';
	echo '</div>';
}
