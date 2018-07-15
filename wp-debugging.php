<?php
/**
 * Plugin Name:       WordPress Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           0.7.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.4
 */

/**
 * Class AJF_WP_Debugging
 */
class AJF_WP_Debugging {

	/**
	 * Debugging constants.
	 *
	 * @var array
	 */
	public static $debugging_constants = array(
		"define( 'WP_DEBUG', true );",
		"define( 'WP_DEBUG_LOG', true );",
		"define( 'WP_DEBUG_DISPLAY', true );",
		"@ini_set( 'display_errors', 1 );",
		"define( 'SCRIPT_DEBUG', true );",
		"define( 'SAVEQUERIES', true );",
	);

	/**
	 * Return wp-config.php as array.
	 *
	 * @return array $wp_config wp-config.php as array.
	 */
	public function get_wp_config_as_array() {
		$wp_config = file_get_contents( ABSPATH . 'wp-config.php' );
		$wp_config = explode( "\n", $wp_config );

		return $wp_config;
	}

	/**
	 * Write out wp-config.php as string for privileged user.
	 *
	 * @param array $wp_config wp-config.php as array.
	 * @return void
	 */
	public function write_wp_config_as_string( array $wp_config ) {
		$is_user_privileged = is_multisite()
			? current_user_can( 'manage_network' )
			: current_user_can( 'manage_options' );
		if ( ! $is_user_privileged ) {
			wp_die( esc_html__( 'Keep playing, you do not have enough experience for this spell.' ) );
		}
		$wp_config = implode( "\n", $wp_config );
		file_put_contents( ABSPATH . 'wp-config.php', $wp_config );
	}
}

register_activation_hook(
	__FILE__, function() {
		$wp_config = ( new AJF_WP_Debugging() )->get_wp_config_as_array();
		array_splice( $wp_config, 1, 0, AJF_WP_Debugging::$debugging_constants );
		( new AJF_WP_Debugging() )->write_wp_config_as_string( $wp_config );
	}
);

register_deactivation_hook(
	__FILE__, function() {
		$wp_config = ( new AJF_WP_Debugging() )->get_wp_config_as_array();
		$wp_config = array_diff( $wp_config, AJF_WP_Debugging::$debugging_constants );
		( new AJF_WP_Debugging() )->write_wp_config_as_string( $wp_config );
	}
);

require_once __DIR__ . '/vendor/autoload.php';

WP_Dependency_Installer::instance()->run( __DIR__ );
