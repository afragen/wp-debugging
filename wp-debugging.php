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
 * Version:           2.12.2
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * Text Domain:       wp-debugging
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires at least: 5.2
 * Requires PHP:      5.6
 */

namespace Fragen\WP_Debugging;

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';
( new Bootstrap( __FILE__ ) )->init();
