#### [unreleased]

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

#### 2.9.2 / 2021-03-05
* update dependencies
* update tested to

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
* test `wp-config.php` everywhere, still occasional WSOD reports

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
