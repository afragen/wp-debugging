<?php
/**
 * WP Debugging
 *
 * @package wp-debugging
 * @author Andy Fragen
 * @license MIT
 */

namespace Fragen\WP_Debugging;

/**
 * Class Bootstrap
 */
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
	 * Holds plugin options.
	 *
	 * @var $options
	 */
	protected static $options;

	/**
	 * Constructor.
	 *
	 * @param  string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file    = $file;
		$this->dir     = dirname( $file );
		self::$options = get_site_option( 'wp_debugging', [] );
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
		( new Settings( self::$options ) )->load_hooks();
		\WP_Dependency_Installer::instance()->run( $this->dir );
	}

	/**
	 * Load hooks.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action(
			'init',
			function () {
				load_plugin_textdomain( 'wp-debugging' );
			}
		);
		add_filter(
			'wp_dependency_timeout',
			function ( $timeout, $source ) {
				$timeout = basename( $this->dir ) !== $source ? $timeout : 45;

				return $timeout;
			},
			10,
			2
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
		$constants          = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants          = array_merge( array_keys( self::$options ), $constants );
		$config_args        = [
			'raw'       => true,
			'normalize' => true,
		];
		foreach ( $constants as $constant ) {
			$value = 'wp_debug_display' === $constant ? 'false' : 'true';
			$config_transformer->update( 'constant', strtoupper( $constant ), $value, $config_args );
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
