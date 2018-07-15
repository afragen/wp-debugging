#### [unreleased]

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
