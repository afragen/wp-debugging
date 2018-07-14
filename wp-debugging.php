<?php
/**
 * Plugin Name:       WordPress Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           0.2.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.6
 */

register_activation_hook(
	__FILE__, function() {
		$orig_wp_config = file_get_contents( ABSPATH . 'wp-config.php' );
		file_put_contents( ABSPATH . 'wp-config-orig.php', $orig_wp_config );

		$new_wp_config = explode( "\n", $orig_wp_config );
		unset( $new_wp_config[0] );

		$wp_debugging_constants = array(
			'<?php',
			'// Add debugging constants',
			"define( 'WP_DEBUG', true );",
			"define( 'WP_DEBUG_LOG', true );",
			"define( 'WP_DEBUG_DISPLAY', true );",
			null,
		);
		$new_wp_config          = array_merge( $wp_debugging_constants, $new_wp_config );
		$new_wp_config          = implode( "\n", $new_wp_config );
		file_put_contents( ABSPATH . 'wp-config.php', $new_wp_config );
	}
);
register_deactivation_hook(
	__FILE__, function() {
		$orig_wp_config = file_get_contents( ABSPATH . 'wp-config-orig.php' );
		file_put_contents( ABSPATH . 'wp-config.php', $orig_wp_config );
	}
);

require_once __DIR__ . '/vendor/autoload.php';

WP_Dependency_Installer::instance()->run( __DIR__ );
