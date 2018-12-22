#### [unreleased]
* total re-write
* add settings page
* use `wp-cli/wp-config-transformer` to change `wp-config.php`
* include `norcross/debug-quick-look` as dependency

#### 1.2.5 - 1.2.7
* composer update

#### 1.2.4
* properly initialize `load_plugin_textdomain()`
* composer update

#### 1.2.3
* composer update, removing `composer/installers` dependency

#### 1.2.2
* make official name _WP Debugging_

#### 1.2.1
* composer update

#### 1.2.0
* use filter to set admin notice dismissal timeout
* update `vendor/afragen/wp-dependency-installer`

#### 1.1.0
* added filter `wp_debugging_constants` to filter array of added constants

#### 1.0.1
* fix regex to not replace double line feed with same

#### 1.0.0
* update composer dependencies

#### 0.9.0
* added function to normalize to unix line endings

#### 0.8.0
* refactor to put activation and deactivation function in class

#### 0.7.1
* fixed translatable string

#### 0.7.0
* use `wp_die()` to exit for non-privileged user, if a non-privileged user could ever get there
* add POT file

#### 0.6.0
* ensure only privileged user can write out new `wp-config.php`

#### 0.5.0
* add more debug constants per [WordPress Debugging Tools](https://tommcfarlin.com/native-wordpress-debugging-tools/) by [Tom McFarlin](https://github.com/tommcfarlin)
* add Debug Bar plugin as optional dependency

#### 0.4.0
* refactor to new `class AJF_WP_Debugging`
* use `array_splice` to add debug constants

#### 0.3.0
* use array functions to add and remove debug constants

#### 0.2.0
* added `register_activation_hook()` to add WP constants to `wp-config.php`
* added and `register_deactivation_hook()` to remove WP constants from `wp-config.php`

#### 0.1.0
* initial pass
