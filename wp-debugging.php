<?php
/**
 * Plugin Name:       WordPress Debuggin
 * Plugin URI:        https://github.com/afragen/wp-debugging
 * Description:       A support/troubleshooting plugin for WordPress.
 * Version:           0.1.0
 * Author:            Andy Fragen
 * License:           MIT
 * Network:           true
 * GitHub Plugin URI: https://github.com/afragen/wp-debugging
 * Requires WP:       4.6
 * Requires PHP:      5.6
 */

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

require_once __DIR__ . '/vendor/autoload.php';

WP_Dependency_Installer::instance()->run( __DIR__ );
