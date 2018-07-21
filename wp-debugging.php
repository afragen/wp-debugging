<?php
/**
 * Plugin Name:       WordPress Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           1.0.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Text Domain:       wp-debugging
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
	private static $debugging_constants = array(
		"define( 'WP_DEBUG', true );",
		"define( 'WP_DEBUG_LOG', true );",
		"define( 'WP_DEBUG_DISPLAY', true );",
		"@ini_set( 'display_errors', 1 );",
		"define( 'SCRIPT_DEBUG', true );",
		"define( 'SAVEQUERIES', true );",
	);

	/**
	 * Return `wp-config.php` as array.
	 *
	 * @return array $wp_config `wp-config.php` as array.
	 */
	private function get_wp_config_as_array() {
		$wp_config = file_get_contents( ABSPATH . 'wp-config.php' );
		$wp_config = $this->normalize_line_endings( $wp_config );
		$wp_config = explode( "\n", $wp_config );

		return $wp_config;
	}

	/**
	 * Write out wp-config.php as string for privileged user.
	 *
	 * @param  array $wp_config `wp-config.php` as array.
	 * @return void
	 */
	private function write_wp_config_as_string( array $wp_config ) {
		$is_user_privileged = is_multisite()
			? current_user_can( 'manage_network' )
			: current_user_can( 'manage_options' );
		if ( ! $is_user_privileged ) {
			wp_die( esc_html__( 'Keep playing, you do not have enough experience for this spell.', 'wp-debugging' ) );
		}
		$wp_config = implode( "\n", $wp_config );
		file_put_contents( ABSPATH . 'wp-config.php', $wp_config );
	}

	/**
	 * Normalize to unix line endings.
	 *
	 * @param  string $str
	 * @return string $str
	 */
	private function normalize_line_endings( $str ) {
		$str = str_replace( "\r\n", "\n", $str );
		$str = str_replace( "\r", "\n", $str );
		$str = preg_replace( "/\n{2,}/", "\n\n", $str );

		return $str;
	}

	/**
	 * Activation function to add debug constants to `wp-config.php`.
	 *
	 * @return void
	 */
	public function activate() {
		$wp_config = $this->get_wp_config_as_array();
		array_splice( $wp_config, 1, 0, self::$debugging_constants );
		$this->write_wp_config_as_string( $wp_config );
	}

	/**
	 * Deactivation function to remove debug constants from `wp-config.php`.
	 *
	 * @return void
	 */
	public function deactivate() {
		$wp_config = $this->get_wp_config_as_array();
		$wp_config = array_diff( $wp_config, self::$debugging_constants );
		$this->write_wp_config_as_string( $wp_config );
	}
}

register_activation_hook(
	__FILE__,
	function () {
		( new AJF_WP_Debugging() )->activate();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		( new AJF_WP_Debugging() )->deactivate();
	}
);

load_plugin_textdomain( 'wp-debugging' );
require_once __DIR__ . '/vendor/autoload.php';
WP_Dependency_Installer::instance()->run( __DIR__ );
