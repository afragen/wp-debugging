<?php

namespace Fragen\WP_Debugging;

class Bootstrap {

	/**
	 * Holds main plugin directory.
	 *
	 * @var $dir
	 */
	protected $dir;

	/**
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	protected $file;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 * @param string $dir Main plugin directory.
	 * @return void
	 */
	public function __construct( $file, $dir ) {
		$this->file = $file;
		$this->dir  = $dir;
		@ini_set( 'display_errors', 1 );
	}

	/**
	 * Let's get going.
	 *
	 * @return void
	 */
	public function run() {
		require_once $this->dir . '/vendor/autoload.php';
		$this->load_hooks();
		( new Settings() )->load_hooks();
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action(
			'init',
			function() {
				load_plugin_textdomain( 'wp-debugging' );
			}
		);

		// On activation also re-add saved options.
		register_activation_hook(
			$this->file,
			function () {
				$options            = get_site_option( 'wp_debugging', [] );
				$config_arg         = [
					'raw'       => true,
					'normalize' => true,
				];
				$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
				$config_transformer->update( 'constant', 'WP_DEBUG_LOG', 'true', $config_arg );
				$config_transformer->update( 'constant', 'SCRIPT_DEBUG', 'true', $config_arg );
				$config_transformer->update( 'constant', 'SAVEQUERIES', 'true', $config_arg );
				foreach ( array_keys( $options ) as $option ) {
					$value = 'wp_debug_display' === $option ? 'false' : 'true';
					$config_transformer->update( 'constant', strtoupper( $option ), $value, $config_arg );
				}
			}
		);

		// Remove all constants on deactivation.
		register_deactivation_hook(
			$this->file,
			function () {
				$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
				$config_transformer->remove( 'constant', 'WP_DEBUG_LOG' );
				$config_transformer->remove( 'constant', 'SCRIPT_DEBUG' );
				$config_transformer->remove( 'constant', 'SAVEQUERIES' );
				$config_transformer->remove( 'constant', 'WP_DEBUG' );
				$config_transformer->remove( 'constant', 'WP_DEBUG_DISPLAY' );
			}
		);
	}

}
