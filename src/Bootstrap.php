<?php

namespace Fragen\WP_Debugging;

class Bootstrap {

	/**
	 * Holds main plugin file.
	 *
	 * @var $file
	 */
	protected $file;

	/**
	 * Holds main plugin directory.
	 *
	 * @var $dir
	 */
	protected $dir;

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 * @param string $dir Main plugin directory.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file = $file;
		$this->dir  = dirname( $file );
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
		( new \DebugQuickLook() )->init();
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

		register_activation_hook( $this->file, [ $this, 'activate' ] );
		register_deactivation_hook( $this->file, [ $this, 'deactivate' ] );
	}

	/**
	 * Run on activation.
	 * Reloads constants to wp-config.php, including saved options.
	 *
	 * @return void
	 */
	public function activate() {
		$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
		$options            = get_site_option( 'wp_debugging', [] );
		$constants          = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants          = array_merge( array_keys( $options ), $constants );
		$config_arg         = [
			'raw'       => true,
			'normalize' => true,
		];
		foreach ( $constants as $constant ) {
			$value = 'wp_debug_display' === $constant ? 'false' : 'true';
			$config_transformer->update( 'constant', strtoupper( $constant ), $value, $config_arg );
		}
	}

	/**
	 * Run on deactivation.
	 * Removes all added constants from wp-config.php.
	 *
	 * @return void
	 */
	public function deactivate() {
		$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
		$constants          = [ 'wp_debug_log', 'script_debug', 'savequeries', 'wp_debug', 'wp_debug_display' ];
		foreach ( $constants as $constant ) {
			$config_transformer->remove( 'constant', strtoupper( $constant ) );
		}
	}

}
