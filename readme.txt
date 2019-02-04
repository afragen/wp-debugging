# WP Debugging

Contributors: afragen
Tags: debug, support, wp-config
Requires at least: 4.6
Requires PHP: 5.4
Tested up to: 5.1
Stable tag: 2.3.0
Donate link: https://thefragens.com/github-updater-donate
License: MIT

A support/troubleshooting plugin for WordPress.

## Description

This plugin sets the following debug constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Any errors will result in a PHP Exception being thrown. Debug constants per [Debugging in WordPress](https://codex.wordpress.org/Debugging_in_WordPress).

Default settings:

    define( 'WP_DEBUG_LOG', true );
    define( 'SCRIPT_DEBUG', true );
    define( 'SAVEQUERIES', true );

`@ini_set( 'display_errors', 1 );` is set when the plugin is active. `WP_DEBUG` is set to true when the plugin is first run, thereafter it can be turned off in the Settings.

The Settings page allows the user to set the following.

    define( 'WP_DEBUG', true ); // Default on initial plugin installation.
    define( 'WP_DEBUG_DISPLAY', false ); // Default when not declared is true.
    define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true ); // WordPress 5.2 WSOD Override.

When the plugin is deactivated all the constants are removed. When the plugin is activated the default settings and any saved settings are restored.

This plugin uses the [wp-cli/wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) command for writing constants to `wp-config.php`.

[Debug Quick Look](https://github.com/norcross/debug-quick-look) from Andrew Norcross is included with this plugin to assist in reading the debug.log file. If you already have this plugin installed you should delete it when WP Debugging is not active.

[Query Monitor](https://wordpress.org/plugins/query-monitor/) and [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugins are optional dependencies to aid in debugging and troubleshooting. The notice for installation will recur 45 days after being dismissed.

If you have a non-standard location for your `wp-config.php` file you can use the filter `wp_debugging_config_path` to return the file path for your installation.

## Screenshots

1. Settings Screen

## Development

PRs are welcome against the [develop branch on GitHub](https://github.com/afragen/wp-debugging).

## Changelog

#### 2.3.0 / 2019-02-04
* look for `wp-config.php` in directory above `ABSPATH`
* add filter `wp_debugging_config_path` to set non-standard path to `wp-config.php`

#### 2.2.0 / 2019-02-02 🏈
* initial release on dot org

#### 2.1.1 / 2019-02-01
* only show WSOD bypass when appropriate
* update dependencies

#### 2.1.0 / 2019-01-26
* update Debug Quick Look, minor CSS changes
* Improve messaging
* add setting for WP_DISABLE_FATAL_ERROR_HANDLER constant (WSOD)
* add default setting of WP_DEBUG to true, can be changed

#### 2.0.0 / 2019-01-18
* total re-write
* add settings page
* use `wp-cli/wp-config-transformer` to change `wp-config.php`
* include `norcross/debug-quick-look` as dependency via composer but use my fork
* update POT via `composer.json` and wp-cli
* add image assets
