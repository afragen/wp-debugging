Debug Quick Look
========================

## Contributors
* [Andrew Norcross](https://github.com/norcross)

## About
Allows users with admin access to view and purge the `debug.log` file kept inside the `wp_content` folder. The `WP_DEBUG_LOG` constant *must* be set to `true`.

## Current Actions
* allows for loading the debug file in a new browser window
* allows for purging the debug file

# Roadmap
* fancier formatting for log file
* collapse / expand stack traces

#### [Pull requests](https://github.com/norcross/debug-quick-look/pulls) are very much welcome and encouraged.

## Changelog

#### Version 0.0.3 - 2017/07/27
* Simplify reasoning a bit. Props [@jtsternberg](https://github.com/jtsternberg)

#### Version 0.0.2 - 2017/07/12
* Changed method for pulling and display log file to check for memory overload. Props [@rarst](https://github.com/Rarst)

#### Version 0.0.1 - 2017/07/12
* Initial release!
