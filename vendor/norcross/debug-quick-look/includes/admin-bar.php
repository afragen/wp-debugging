<?php
/**
 * Handle the admin bar setup.
 *
 * @package DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook\AdminBar;

// Set our alias items.
use DebugQuickLook as Core;
use DebugQuickLook\Helpers as Helpers;

/**
 * Start our engines.
 */
add_action( 'wp_head', __NAMESPACE__ . '\add_warning_css' );
add_action( 'admin_head', __NAMESPACE__ . '\add_warning_css' );
add_action( 'wp_head', __NAMESPACE__ . '\add_mobile_css' );
add_action( 'admin_head', __NAMESPACE__ . '\add_mobile_css' );
add_action( 'admin_bar_menu', __NAMESPACE__ . '\admin_bar_links', 9999 );

/**
 * Add the CSS to flag our warning message.
 */
function add_warning_css() {

	// Run my cap check.
	$hascap = Helpers\check_user_cap( 'warning-css' );

	// Bail without.
	if ( ! $hascap ) {
		return;
	}

	// Open the style tag.
	echo '<style>';

	// Output the actual CSS item.
	echo 'li#wp-admin-bar-debug-quick-look li.debug-quick-look-missing .ab-item span {';
		echo 'color: #ff0000;';
		echo 'font-weight: bold;';
		echo 'font-family: Consolas, Monaco, monospace;';
	echo '}';

	// Close the style tag.
	echo '</style>';
}

/**
 * Add the links for the debug log file.
 *
 * @param  WP_Admin_Bar $wp_admin_bar  The global WP_Admin_Bar object.
 *
 * @return void.
 */
function admin_bar_links( $wp_admin_bar ) {

	// Run my cap check.
	$hascap = Helpers\check_user_cap( 'admin-bar' );

	// Bail without.
	if ( ! $hascap ) {
		return;
	}

	// Fetch my nodes.
	$nodes = Helpers\get_admin_bar_nodes();

	// Bail without nodes.
	if ( ! $nodes ) {
		return;
	}

	// Add a parent item.
	$wp_admin_bar->add_node(
		[
			'id'    => 'debug-quick-look',
			'title' => __( 'Debug Quick Look', 'debug-quick-look' ),
		]
	);

	// Check to see if we have our constant.
	$hascon = Helpers\maybe_constant_set();

	// If no constant is set, show the error.
	if ( ! $hascon ) {

		// Show the error node.
		$wp_admin_bar->add_node( $nodes['error'] );

		// And be done.
		return;
	}

	// We have the constant, so unset the error.
	unset( $nodes['error'] );

	// Loop my node data.
	foreach ( $nodes as $node_name => $node_data ) {
		$wp_admin_bar->add_node( $node_data );
	}

	// And be done.
	return;
}

/**
 * Add the CSS to add admin bar menu on mobile.
 */
function add_mobile_css() {

	// Run my cap check.
	$hascap = Helpers\check_user_cap( 'warning-css' );

	// Bail without.
	if ( ! $hascap ) {
		return;
	}

	// Open the style tag.
	echo '<style>';

	// Output the actual CSS item.
	echo '@media screen and (max-width: 782px) {';
		echo '#wp-toolbar > ul > li#wp-admin-bar-debug-quick-look {';
			// Style similar to Query Monitor.
			echo 'display: list-item;';
			echo 'font: 18px/44px -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif !important;';
			echo 'padding: 0 10px !important;';
			echo 'width: auto !important';
		echo '}';
		echo '#wp-toolbar > ul > li#wp-admin-bar-debug-quick-look:before {';
			echo 'content: "DQL";';
		echo '}';
		echo '#wp-toolbar > ul > li#wp-admin-bar-debug-quick-look div.ab-item.ab-empty-item {';
			echo 'display: none;';
		echo '}';
	echo '}';

	// Close the style tag.
	echo '</style>';
}
