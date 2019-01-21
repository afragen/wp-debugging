# WP Debugging

Contributors: afragen
Tags: debug, support, wp-config
Requires at least: 4.6
Requires PHP: 5.4
Tested up to: 5.1
Stable tag: master
Donate link: https://thefragens.com/github-updater-donate
License: MIT

A support/troubleshooting plugin for WordPress.

## Description

This plugin sets the following debug constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Any errors will result in a PHP Exception being thrown. Debug constants per [Debugging in WordPress](https://codex.wordpress.org/Debugging_in_WordPress).

Default settings:

    define( 'WP_DEBUG_LOG', true );
    define( 'SCRIPT_DEBUG', true );
    define( 'SAVEQUERIES', true );

`@ini_set( 'display_errors', 1 );` is set when the plugin is active.

The Settings page allows the user to set the following.

    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_DISPLAY', false ); // Default when not declared is true.

When the plugin is deactivated all the constants are removed. When the plugin is activated the default settings and any saved settings are restored.

This plugin uses the [wp-cli/wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) command for writing constants to `wp-config.php`.

[Debug Quick Look](https://github.com/norcross/debug-quick-look) from Andrew Norcross is included with this plugin to assist in reading the debug.log file. If you already have this plugin installed you should delete it when WP Debugging is not active.

[Query Monitor](https://wordpress.org/plugins/query-monitor/) and [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugins are optional dependencies to aid in debugging and troubleshooting. The notice for installation will recur 45 days after being dismissed.

## Screenshots

1. Settings Screen

## Development

PRs are welcome against the [develop branch on GitHub](https://github.com/afragen/wp-debugging).
