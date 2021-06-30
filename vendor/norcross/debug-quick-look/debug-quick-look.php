<?php
/**
 * Plugin Name:         Debug Quick Look
 * Plugin URI:          https://github.com/norcross/debug-quick-look
 * Description:         Creates an admin bar link to view or purge the debug log file.
 * Author:              Andrew Norcross
 * Author URI:          http://andrewnorcross.com
 * Text Domain:         debug-quick-look
 * Domain Path:         /languages
 * Version:             0.1.6
 * License:             MIT
 * License URI:         https://opensource.org/licenses/MIT
 * GitHub Plugin URI:   https://github.com/norcross/debug-quick-look
 * Requires PHP:        5.3
 *
 * @package             DebugQuickLook
 */

// Call our namepsace.
namespace DebugQuickLook;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Exit if already using this plugin.
if ( defined( __NAMESPACE__ . '\VERS' ) ) return;

// Define our version.
define( __NAMESPACE__ . '\VERS', '0.1.6' );

// Plugin Folder URL.
define( __NAMESPACE__ . '\URL', plugin_dir_url( __FILE__ ) );

// Plugin root file.
define( __NAMESPACE__ . '\FILE', __FILE__ );

// Set our assets directory constant.
define( __NAMESPACE__ . '\ASSETS_URL', URL . 'assets' );

// Set the prefix for our actions and filters.
define( __NAMESPACE__ . '\HOOK_PREFIX', 'debug_quick_look_' );

// Set the debug file we wanna use.
define( __NAMESPACE__ . '\DEBUG_FILE', ini_get( 'error_log' ) );

// Go and load our files.
require_once __DIR__ . '/includes/activate.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/admin-bar.php';
require_once __DIR__ . '/includes/actions.php';
require_once __DIR__ . '/includes/parser.php';
require_once __DIR__ . '/includes/handler.php';
require_once __DIR__ . '/includes/formatting.php';
