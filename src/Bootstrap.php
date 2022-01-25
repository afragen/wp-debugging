<?php
/**
 * WP Debugging
 *
 * @package wp-debugging
 * @author Andy Fragen
 * @license MIT
 */

namespace Fragen\WP_Debugging;

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

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
		@ini_set( 'display_errors', 1 ); // phpcs:ignore WordPress.PHP.IniSet.display_errors_Blacklisted
	}

	/**
	 * Test for writable wp-config.php, exit with notice if not available.
	 *
	 * @return bool|void
	 */
	public function init() {
		if ( ! is_writable( self::$config_path ) ) {
			echo '<div class="error notice is-dismissible"><p>';
			echo wp_kses_post( __( 'The <strong>WP Debugging</strong> plugin must have a <code>wp-config.php</code> file that is writable by the filesystem.', 'wp-debugging' ) );
			echo '</p></div>';

			return false;
		}

		$this->load_hooks();
		add_action(
			'plugins_loaded',
			function() {
				\WP_Dependency_Installer::instance()->run( $this->dir );
			}
		);
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
			'plugins_loaded',
			function() {
				( new Settings( self::$options, self::$config_path, $this->defined_constants ) )
					->load_hooks()
					->process_filter_constants();
			}
		);
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
		$this->set_pre_activation_constants();

		// Need to remove user defined constants from filter.
		$user_defined = apply_filters( 'wp_debugging_add_constants', [] );
		foreach ( array_keys( $user_defined ) as $defined ) {
			unset( self::$options[ $defined ] );
		}

		$constants = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants = array_flip( array_merge( array_keys( self::$options ), $constants ) );

		( new Settings( self::$options, self::$config_path, $this->defined_constants ) )->add_constants( $constants );
	}

	/**
	 * Run on deactivation.
	 * Removes all added constants from wp-config.php.
	 *
	 * @return void
	 */
	public function deactivate() {
		$restore_constants = get_site_option( 'wp_debugging_restore' );
		$remove_user_added = array_diff( self::$options, array_flip( $this->defined_constants ) );
		$remove_constants  = array_diff( array_flip( $this->defined_constants ), array_keys( $restore_constants ) );
		$remove_constants  = array_merge( $remove_constants, $remove_user_added );

		( new Settings( self::$options, self::$config_path, $this->defined_constants ) )->remove_constants( $remove_constants );

		$this->restore_pre_activation_constants();
	}

	/**
	 * Set pre-activation constant from `wp-config.php`.
	 *
	 * @return void
	 */
	private function set_pre_activation_constants() {
		$config_transformer   = new \WPConfigTransformer( self::$config_path );
		$predefined_constants = [];
		foreach ( $this->defined_constants as $defined_constant ) {
			if ( $config_transformer->exists( 'constant', strtoupper( $defined_constant ) ) ) {
				$value = $config_transformer->get_value( 'constant', strtoupper( $defined_constant ) );
				$predefined_constants[ $defined_constant ]['value'] = $value;
			}
		}
		update_site_option( 'wp_debugging_restore', $predefined_constants );
	}

	/**
	 * Restore pre-activation constants to `wp-config.php`.
	 *
	 * @return void
	 */
	private function restore_pre_activation_constants() {
		$restore_constants = get_site_option( 'wp_debugging_restore' );
		( new Settings( self::$options, self::$config_path, $this->defined_constants ) )->add_constants( $restore_constants );
	}
}
