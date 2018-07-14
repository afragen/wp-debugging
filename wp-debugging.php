<?php
/**
 * Plugin Name:       WordPress Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           0.3.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.6
 */

register_activation_hook(
	__FILE__, function() {
		$debugging_constants = array(
			"define( 'WP_DEBUG', true );",
			"define( 'WP_DEBUG_LOG', true );",
			"define( 'WP_DEBUG_DISPLAY', true );",
		);
		$wp_config           = file_get_contents( ABSPATH . 'wp-config.php' );
		$wp_config           = explode( "\n", $wp_config );
		unset( $wp_config[0] );

		$wp_config = array_merge( [ '<?php' ], $debugging_constants, $wp_config );
		$wp_config = implode( "\n", $wp_config );
		file_put_contents( ABSPATH . 'wp-config.php', $wp_config );
	}
);
register_deactivation_hook(
	__FILE__, function() {
		$debugging_constants = array(
			"define( 'WP_DEBUG', true );",
			"define( 'WP_DEBUG_LOG', true );",
			"define( 'WP_DEBUG_DISPLAY', true );",
		);
		$wp_config           = file_get_contents( ABSPATH . 'wp-config.php' );
		$wp_config           = explode( "\n", $wp_config );
		$wp_config           = array_diff( $wp_config, $debugging_constants );
		$wp_config           = implode( "\n", $wp_config );
		file_put_contents( ABSPATH . 'wp-config.php', $wp_config );
	}
);

require_once __DIR__ . '/vendor/autoload.php';

WP_Dependency_Installer::instance()->run( __DIR__ );
