# WordPress Debugging

* Contributors: [Andy Fragen](https://github.com/afragen)
* Tags: debug, support
* Requires at least: 4.6
* Requires PHP: 5.6
* Tested up to: 5.0
* Stable tag: master
* Donate link: <http://thefragens.com/github-updater-donate>
* License: MIT

A debugging/support plugin for WordPress.

## Description

This plugin sets the following debug constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Debug constants per [WordPress Debugging Tools](https://tommcfarlin.com/native-wordpress-debugging-tools/) by Tom McFarlin.

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
@ini_set( 'display_errors', 1 );
define( 'SCRIPT_DEBUG', true );
define( 'SAVEQUERIES', true );
```

Additionally the [Query Monitor](https://wordpress.org/plugins/query-monitor/) and [Debug Quick Look](https://github.com/norcross/debug-quick-look) plugins are installed and activated to aid in debugging and troubleshooting.

[GitHub Updater](https://github.com/afragen/github-updater) is optionally installed for plugin updates.

## Development

PRs are welcome against the `develop` branch.
