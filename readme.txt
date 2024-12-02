# WP Debugging

Contributors: afragen
Tags: debug, support, wp-config
Requires at least: 5.2
Requires PHP: 5.6
Tested up to: 6.8
Stable tag: 2.12.2
Donate link: https://thefragens.com/git-updater-donate
License: MIT

A support/troubleshooting plugin for WordPress.

## Description

This plugin sets the following debug constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Any errors will result in a PHP Exception being thrown. Debug constants per [Debugging in WordPress](https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/).

Default settings:

    define( 'WP_DEBUG_LOG', true );
    define( 'SCRIPT_DEBUG', true );
    define( 'SAVEQUERIES', true );

&nbsp;
`@ini_set( 'display_errors', 1 );` is set when the plugin is active. `WP_DEBUG` is set to true when the plugin is first run, thereafter it can be turned off in the Settings.

The Settings page allows the user to set the following.

    define( 'WP_DEBUG', true ); // Default on initial plugin installation.
    define( 'WP_DEBUG_DISPLAY', false ); // Default when not declared is true.
    define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true ); // WordPress 5.2 WSOD Override.

When the plugin is deactivated best efforts are made to re-add pre-existing constants to their former state. When the plugin is activated the default settings and any saved settings are restored.

This plugin uses the [wp-cli/wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) command for writing constants to `wp-config.php`.

[Debug Quick Look](https://github.com/norcross/debug-quick-look) from Andrew Norcross is included with this plugin to assist in reading the debug.log file. If you already have this plugin installed you should delete it when WP Debugging is not active.

[Query Monitor](https://wordpress.org/plugins/query-monitor/) and [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugins are optional dependencies to aid in debugging and troubleshooting. The notice for installation will recur 45 days after being dismissed.

If you have a non-standard location for your `wp-config.php` file you can use the filter `wp_debugging_config_path` to return the file path for your installation.

The filter `wp_debugging_add_constants` allows the user to add constants to `wp-config.php`.

The filter returns an array where the key is the name of the constant and the value is an array of data containing the value as a string and a boolean to indicate whether or not the value should be passed without quotes.

    $my_constants = [
        'my_test_constant' =>
        [
            'value' => 'abc123',
            'raw' => false,
        ],
        'another_test_constant' => [ 'value' => 'true' ],
    ];

The `value` option contains the constant's value as a string.

The `raw` option means that instead of placing the value inside the config as a string it will become unquoted. The default is `true`. Set as `false` for non-boolean values.

Example:

    add_filter(
	    'wp_debugging_add_constants',
	    function( $added_constants ) {
	    	$my_constants = [
	    		'my_test_constant'      => [
	    			'value' => '124xyz',
	    			'raw'   => false,
	    		],
	    		'another_test_constant' => [ 'value' => 'true' ],
	    	];
	    	return array_merge( $added_constants, $my_constants );
	    },
	    10,
	    1
    );

This will create the following constants.

    define( 'MY_TEST_CONSTANT', '124xyz' );
    define( 'ANOTHER_TEST_CONSTANT', true );

## Screenshots

1. Settings Screen

## Development

PRs are welcome against the [develop branch on GitHub](https://github.com/afragen/wp-debugging).

## Changelog

#### 2.12.2 / 2024-12-02
* add GA to generate POT
* update for PCP

#### 2.12.1 / 2024-11-08
* move `register_activation_hook` and `register_deactivation_hook` up the chain

#### 2.12.0 / 2024-11-01
* exit early(ish) if not on WP Debugging settings page
* composer update
* remove `load_plugin_textdomain()`

#### 2.11.24 / 2024-04-29
* update link to Debugging in WordPress, thanks @tobifjellner
* cast variable to array in `Bootstrap::deactivate()` fix for PHP 8.3

#### 2.11.23 / 2024-03-04
* composer update
* WPCS updates

#### 2.11.22 / 2023-05-31 🎂
* update `afragen/wp-dismiss-notice`

#### 2.11.21 / 2023-04-29
* update `afragen/wp-dismiss-notice`
* update Debug Quick Look

#### 2.11.18, 2.11.19, 2.11.20 / 2023-02-07
* composer update

#### 2.11.17 / 2023-01-27
* updated link to _Debugging in WordPress_ and improved text for translators
* composer update

#### 2.11.16 / 2023-01-19
* composer update

#### 2.11.15 / 2023-01-02
* composer update

#### 2.11.14 / 2022-07-15
* composer update

#### 2.11.13 / 2022-06-23
* make anchor `preg_match` more greedy

#### 2.11.12 / 2022-05-29
* update to latest `afragen/debug-quick-look`

#### 2.11.11 / 2022--5-16
* update `wp-dismiss-notice` with transient and only poll `wp_remote_get()` weekly

#### 2.11.10 / 2022-05-10
* use `sanitize_key()` for nonces
* composer update

#### 2.11.9 / 2022-02-05
* composer update

#### 2.11.8 / 2022-01-24
* load call to `WP_Dependency_Installer()` in `plugins_loaded` hook to avoid loading `pluggable.php`.
* update `WP_Dependency_Installer`

#### 2.11.7 / 2022-01-18
* fix logic in verify nonce conditional

#### 2.11.6 /2022-01-18
* proper nonce verification
* composer updates

#### 2.11.5 / 2022-01-11
* I messed up the release 🤦‍♂️

#### 2.11.4 / 2022-01-10
* composer updates

#### 2.11.3 / 2021-12-19
* more fixes via composer update

#### 2.11.2 / 2021-09-24
* composer update, cause of course I needed to fix something

#### 2.11.1 / 2021-09-24
* init in `plugins_loaded` hook
* composer update

#### 2.11.0 / 2021-09-23
* fix security issue for capabilities check, possible CSRF, and nonce checks.

#### 2.10.2 / 2021-09-04
* only use `esc_attr_e` for translating strings

#### 2.10.1 / 2021-07-23
* fix PHP Notice, `Settings:line 68`
* update Debug Quick Look admin bar menu for mobile

#### 2.10.0 / 2021-06-30
* update Debug Quick Look to show menu on mobile

#### 2.9.3 / 2021-06-22
* update WPConfigTransformer to use alternate anchor if default not present
* add @10up GitHub Actions for WordPress svn integration

#### 2.9.1 / 2020-11-17
* update `wp-dependency-installer` library
* update `wp-cli/wp-config-transformer`
* comment out quote normalization in `set_pre_activation_constants()`, not sure why I did that but it can cause problems [#10](https://github.com/afragen/wp-debugging/issues/10)

#### 2.9.0 / 2020-08-15
* use try/catch around use of `WPConfigTransformer` object

#### 2.8.0 / 2020-08-01
* exit if called directly
* NB: I have seen the `WPConfigTransformer` Exception error live. The issue seems to be that a `file_get_contents()` on the `wp-config.php` file path, at random times, returns an empty value. I'm done chasing this random error in `wp-cli/wp-config-transformer`. Modified version of `wp-cli/wp-config-transformer` present

#### 2.7.2 / 2020-06-01
* test `wp-config.php` everywhere, still occasional WSOD reports.

#### 2.7.1 / 2020-5-15
* return early if `wp-config.php` is empty before calling `WPConfigTransformer`

#### 2.7.0 / 2020-04-30
* start loading in `init` hook
* run `process_filter_constants()` as chained method in `Bootstrap`

#### 2.6.1 / 2020-03-28
* move `Settings` action link to front
* change test for `wp-config.php` file empty

#### 2.6.0 / 2020-02-28
* load autoloader in main file
* update composer dependencies

#### 2.5.8 / 2019-12-23
* badly messed up check for empty `wp-config.php`

#### 2.5.7 / 2019-12-20
* check and exit early if `wp-config.php` is empty
* return empty array for the above exit

#### 2.5.6 / 2019-11-02
* early exit if `wp-config.php` not set in specific functions

#### 2.5.5 / 2019-09-17
* update composer.json for wp-dependency-installer update, now requires at least PHP 5.6 for spread operator
* composer update

#### 2.5.4 / 2019-04-25
* added check for writable `wp-config.php`, exit with notice if not found

#### 2.5.3 / 2019-04-01
* update `Debug Quick Look` to display error log file path

#### 2.5.1 / 2019-04-01
* updated version of wp-cli/wp-config-transformer

#### 2.5.0 / 2019-03-25
* added `wp_debugging_add_constants` filter for users to add their own constants

#### 2.4.3 / 2019-03-09
* missed an output escape

#### 2.4.2 / 2019-02-26
* add `Domain Path` header

#### 2.4.1 / 2019-02-10
* refactor set/restore pre-activation constants

#### 2.4.0 / 2019-02-06
* save pre-activation constants for re-installation on deactivation ( say that 5x fast )

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
