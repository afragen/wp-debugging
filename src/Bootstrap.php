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
	 * @var string
	 */
	protected $file;

	/**
	 * Holds main plugin directory.
	 *
	 * @var string
	 */
	protected $dir;

	/**
	 * Holds plugin options.
	 *
	 * @var array
	 */
	protected static $options;

	/**
	 * Holds `wp-config.php` file path.
	 *
	 * @var string
	 */
	protected static $config_path;

	/**
	 * Holds pre-defined constants for `wp-config.php`.
	 *
	 * @var array
	 */
	protected $defined_constants = [ 'wp_debug_log', 'script_debug', 'savequeries', 'wp_debug', 'wp_debug_display', 'wp_disable_fatal_error_handler' ];

	/**
	 * Constructor.
	 *
	 * @param  string $file Main plugin file.
	 * @return void
	 */
	public function __construct( $file ) {
		$this->file        = $file;
		$this->dir         = dirname( $file );
		self::$options     = get_site_option( 'wp_debugging', [ 'wp_debug' => '1' ] );
		self::$config_path = $this->get_config_path();
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
		( new Settings( self::$options, self::$config_path ) )->load_hooks();
		\WP_Dependency_Installer::instance()->run( $this->dir );
	}

	/**
	 * Get the `wp-config.php` file path.
	 *
	 * The config file may reside one level above ABSPATH but is not part of another installation.
	 *
	 * @see wp-load.php#L26-L42
	 *
	 * @return string $config_path
	 */
	public function get_config_path() {
		$config_path = ABSPATH . 'wp-config.php';

		if ( ! file_exists( $config_path ) ) {
			if ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
				$config_path = dirname( ABSPATH ) . '/wp-config.php';
			}
		}

		/**
		 * Filter the config file path.
		 *
		 * @since 2.3.0
		 *
		 * @param string $config_path
		 */
		return apply_filters( 'wp_debugging_config_path', $config_path );
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
		$config_transformer   = new \WPConfigTransformer( self::$config_path );
		$predefined_constants = [];
		$constants            = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants            = array_merge( array_keys( self::$options ), $constants );
		$config_args          = [
			'raw'       => true,
			'normalize' => true,
		];
		foreach ( $this->defined_constants as $defined_constant ) {
			if ( $config_transformer->exists( 'constant', strtoupper( $defined_constant ) ) ) {
				$predefined_constants[ $defined_constant ] = $config_transformer->get_value( 'constant', strtoupper( $defined_constant ) );
			}
		}
		update_site_option( 'wp_debugging_restore', $predefined_constants );
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
		$restore_constants  = get_site_option( 'wp_debugging_restore' );
		$remove_constants   = array_diff( $this->defined_constants, array_keys( $restore_constants ) );
		$config_transformer = new \WPConfigTransformer( self::$config_path );
		$config_args        = [
			'raw'       => true,
			'normalize' => true,
		];

		foreach ( $remove_constants as $constant ) {
			$config_transformer->remove( 'constant', strtoupper( $constant ) );
		}
		foreach ( $restore_constants as $constant => $value ) {
			$config_transformer->update( 'constant', strtoupper( $constant ), $value, $config_args );
		}
	}
}
