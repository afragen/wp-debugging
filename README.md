# WP Debugging

* Contributors: [Andy Fragen](https://github.com/afragen)
* Tags: debug, support
* Requires at least: 5.2
* Requires PHP: 5.6
* Stable tag: master
* Donate link: <https://thefragens.com/git-updater-donate>
* License: MIT

A debugging/support plugin for WordPress.

## Description

This plugin sets the following debug constants in `wp-config.php` on plugin activation and removes them on plugin deactivation. Any errors will result in a PHP Exception being thrown. Debug constants per [Debugging in WordPress](https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/).

Default settings:

```php
define( 'WP_DEBUG_LOG', true );
define( 'SCRIPT_DEBUG', true );
define( 'SAVEQUERIES', true );
```

`@ini_set( 'display_errors', 1 );` is set when the plugin is active. `WP_DEBUG` is set to true when the plugin is first run, thereafter it can be turned off in the Settings.

The Settings page allows the user to set the following.

```php
define( 'WP_DEBUG', true ); // Default on initial plugin installation.
define( 'WP_DEBUG_DISPLAY', false ); // Default when not declared is true.
define( 'WP_DISABLE_FATAL_ERROR_HANDLER', true ); // WordPress 5.2 WSOD Override.
```

When the plugin is deactivated best efforts are made to re-add pre-existing constants to their former state. When the plugin is activated the default settings and any saved settings are restored.

This plugin uses the [wp-cli/wp-config-transformer](https://github.com/wp-cli/wp-config-transformer) command for writing constants to `wp-config.php`.

[Debug Quick Look](https://github.com/norcross/debug-quick-look) from Andrew Norcross is included with this plugin to assist in reading the debug.log file. If you already have this plugin installed you should delete it when WP Debugging is not active.

[Query Monitor](https://wordpress.org/plugins/query-monitor/) and [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugins are optional dependencies to aid in debugging and troubleshooting. The notice for installation will recur 45 days after being dismissed.

If you have a non-standard location for your `wp-config.php` file you can use the filter `wp_debugging_config_path` to return the file path for your installation.

The filter `wp_debugging_add_constants` allows the user to add constants to `wp-config.php`.

The filter returns an array where the key is the name of the constant and the value is an array of data containing the value as a string and a boolean to indicate whether or not the value should be passed without quotes.

```php
$my_constants = [
    'my_test_constant' =>
    [
        'value' => 'abc123',
        'raw' => false,
    ],
    'another_test_constant' => [ 'value' => 'true' ],
];
```

The `value` option contains the constant's value as a string.

The `raw` option means that instead of placing the value inside the config as a string it will become unquoted. The default is `true`. Set as `false` for non-boolean values.

Example:

```php
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
```

This will create the following constants.

```php
define( 'MY_TEST_CONSTANT', '124xyz' );
define( 'ANOTHER_TEST_CONSTANT', true );
```

## Development

PRs are welcome against the `develop` branch.
