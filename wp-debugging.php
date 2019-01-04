<?php
/**
 * WP Debugging
 *
 * @package wp-debugging
 * @author Andy Fragen
 * @license MIT
 */

/**
 * Plugin Name:       WP Debugging
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           1.2.7.8
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Text Domain:       wp-debugging
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.4
 */

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/src/Bootstrap.php';
( new Fragen\WP_Debugging\Bootstrap( __FILE__ ) )->run();
