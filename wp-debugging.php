<?php
/**
 * Plugin Name:       WP Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           1.2.7.4
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Text Domain:       wp-debugging
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.4
 */

require_once __DIR__ . '/src/Bootstrap.php';
( new Fragen\WP_Debugging\Bootstrap( __FILE__, __DIR__ ) )->run();

WP_Dependency_Installer::instance()->run( __DIR__ );
add_filter(
	'wp_dependency_timeout',
	function( $timeout, $source ) {
		$timeout = $source !== basename( __DIR__ ) ? $timeout : 30;
		return $timeout;
	},
	10,
	2
);
