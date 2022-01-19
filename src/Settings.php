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
 * Class Settings
 */
class Settings {
	/**
	 * Hold plugin options.
	 *
	 * @var array
	 */
	protected static $options;

	/**
	 * Hold `wp-config.php` file path.
	 *
	 * @var string
	 */
	protected static $config_path;

	/**
	 * Holds pre-defined constants for `wp-config.php`.
	 *
	 * @var array
	 */
	protected $defined_constants;

	/**
	 * Holds config args for WPConfigTransformer.
	 *
	 * @var array
	 */
	protected static $config_args;

	/**
	 * Constructor.
	 *
	 * @param  array  $options           Plugin options.
	 * @param  string $config_path       Path to config file.
	 * @param  array  $defined_constants Pre-defined constant group.
	 * @return void
	 */
	public function __construct( $options, $config_path, $defined_constants ) {
		self::$options           = $options;
		self::$config_path       = $config_path;
		$this->defined_constants = $defined_constants;
		self::$config_args       = [ 'normalize' => true ];

		if ( false === strpos( file_get_contents( self::$config_path ), "/* That's all, stop editing!" ) ) {
			if ( 1 === preg_match( '@\$table_prefix = (.*);@', file_get_contents( self::$config_path ), $matches ) ) {
				self::$config_args = array_merge(
					self::$config_args,
					[
						'anchor'    => "$matches[0]",
						'placement' => 'after',
					]
				);
			}
		}
	}

	/**
	 * Load hooks for settings.
	 *
	 * @return self
	 */
	public function load_hooks() {
		add_action( 'admin_init', [ $this, 'add_settings' ] );
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', [ $this, 'add_plugin_menu' ] );
		add_action( 'network_admin_edit_wp_debugging', [ $this, 'update_settings' ] );
		add_action( 'admin_init', [ $this, 'update_settings' ] );
		add_filter(
			is_multisite()
			? 'network_admin_plugin_action_links_wp-debugging/wp-debugging.php'
			: 'plugin_action_links_wp-debugging/wp-debugging.php',
			[ $this, 'plugin_action_links' ]
		);

		return $this;
	}

	/**
	 * Add plugin menu.
	 *
	 * @return void
	 */
	public function add_plugin_menu() {
		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network_options' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html__( 'WP Debugging', 'wp-debugging' ),
			esc_html_x( 'WP Debugging', 'Menu item', 'wp-debugging' ),
			$capability,
			'wp-debugging',
			[ $this, 'create_settings_page' ]
		);
	}

	/**
	 * Update settings on save.
	 *
	 * @return void
	 */
	public function update_settings() {
		// Exit if improper privileges.
		if ( ! current_user_can( 'manage_options' )
			|| ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'wp_debugging-options' ) )
		) {
			return;
		}

		if ( isset( $_POST['option_page'] ) &&
			'wp_debugging' === $_POST['option_page']
		) {
			$options = isset( $_POST['wp-debugging'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['wp-debugging'] ) )
				: [];

			$options = $this->sanitize( $options );
			$this->update_constants( self::$options, $options );
			$filtered_options = array_filter(
				self::$options,
				function ( $e ) {
					return '1' !== $e;
				}
			);
			$options          = array_merge( $filtered_options, $options );
			update_site_option( 'wp_debugging', (array) $options );
			$this->redirect_on_save();
		}
	}

	/**
	 * Update constants in wp-config.php.
	 *
	 * @param  array $old Current value of self::$options.
	 * @param  mixed $new New value of $options.
	 * @return void
	 */
	private function update_constants( $old, $new ) {
		$remove = array_diff_assoc( $old, $new );
		$add    = array_diff_assoc( $new, $old );

		if ( ! empty( $add ) ) {
			$this->add_constants( $add );
		}
		if ( ! empty( $remove ) ) {
			$this->remove_constants( $remove );
		}
	}

	/**
	 * Add constants to wp-config.php file.
	 *
	 * @uses https://github.com/wp-cli/wp-config-transformer
	 *
	 * @param  array $add Constants to add to wp-config.php.
	 * @return array $added Array of added constants.
	 */
	public function add_constants( $add ) {
		$added = [];
		try {
			$config_transformer = new \WPConfigTransformer( self::$config_path );
			foreach ( $add as $constant => $config ) {
				$value             = 'wp_debug_display' === $constant ? 'false' : 'true';
				$value             = isset( $config['value'] ) ? $config['value'] : $value;
				$raw               = isset( $config['raw'] ) ? $config['raw'] : true;
				self::$config_args = array_merge( self::$config_args, [ 'raw' => $raw ] );
				$config_transformer->update( 'constant', strtoupper( $constant ), $value, self::$config_args );
				$added[ $constant ] = $value;
			}

			return $added;
		} catch ( \Exception $e ) {
			$messsage = 'Caught Exception: \Fragen\WP_Debugging\Settings::add_constants() - ' . $e->getMessage();
			// error_log( $messsage );
			wp_die( esc_html( $messsage ) );
		}
	}

	/**
	 * Process user defined constants added via filter.
	 *
	 * @return void
	 */
	public function process_filter_constants() {
		/**
		 * Filter to add user define constants.
		 *
		 * @since 2.5.0
		 *
		 * @param array Array of added constants.
		 *              The format for adding constants is to return an array of arrays.
		 *              Each array will have the constant as the key with an array of configuration data.
		 *              array(
		 *                  'my_constant' =>
		 *                  array(
		 *                      'value' => $value, @type string
		 *                      'raw' => $raw, // Optional. @type bool $raw is a boolean where false will return the value in quotes and true will return the raw value. Default is true.
		 *                  ),
		 *              )
		 *              Default is an empty array.
		 */
		$filter_constants    = apply_filters( 'wp_debugging_add_constants', [] );
		$remove_user_defined = array_diff( self::$options, array_flip( $this->defined_constants ) );

		// Remove and re-add user defined constants. Clean up for when filter removed or changed.
		if ( ! empty( $remove_user_defined ) ) {
			$this->remove_constants( $remove_user_defined );
		}
		$added_constants = $this->add_constants( $filter_constants );

		$options       = array_diff( self::$options, $remove_user_defined );
		self::$options = array_merge( $options, $added_constants );
		update_site_option( 'wp_debugging', (array) self::$options );
	}

	/**
	 * Remove constants from wp-config.php file.
	 *
	 * @uses https://github.com/wp-cli/wp-config-transformer
	 *
	 * @param  array $remove Constants to remove from wp-config.php.
	 * @return void
	 */
	public function remove_constants( $remove ) {
		try {
			$config_transformer = new \WPConfigTransformer( self::$config_path );
			foreach ( array_keys( $remove ) as $constant ) {
				$config_transformer->remove( 'constant', strtoupper( $constant ) );
			}
		} catch ( \Exception $e ) {
			$messsage = 'Caught Exception: \Fragen\WP_Debugging\Settings::remove_constants() - ' . $e->getMessage();
			// error_log( $messsage );
			wp_die( esc_html( $messsage ) );
		}
	}

	/**
	 * Redirect back to settings page on save.
	 *
	 * @return void
	 */
	private function redirect_on_save() {
		$update = false;

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ( isset( $_POST['action'] ) && 'update' === $_POST['action'] ) &&
			( isset( $_POST['option_page'] ) && 'wp_debugging' === $_POST['option_page'] )
		) {
			$update = true;
		}
		// phpcs:enable

		$redirect_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'tools.php' );

		if ( $update ) {
			$location = add_query_arg(
				[
					'page'    => 'wp-debugging',
					'updated' => $update,
				],
				$redirect_url
			);
			wp_safe_redirect( $location );
			exit;
		}
	}

	/**
	 * Add notice when settings are saved.
	 *
	 * @return void
	 */
	private function saved_settings_notice() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ( isset( $_GET['updated'] ) && '1' === $_GET['updated'] ) ||
		( isset( $_GET['settings-updated'] ) && '1' === $_GET['settings-updated'] )
		) {
			echo '<div class="updated"><p>';
			esc_html_e( 'Saved.', 'wp-debugging' );
			echo '</p></div>';
		}
		// phpcs:enable
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	public function add_settings() {
		register_setting(
			'wp_debugging',
			'wp_debugging',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			'wp_debugging',
			esc_html__( 'Debugging Constants', 'wp-debugging' ),
			[ $this, 'print_settings_section' ],
			'wp_debugging'
		);

		add_settings_field(
			'wp_debug',
			null,
			[ $this, 'checkbox_setting' ],
			'wp_debugging',
			'wp_debugging',
			[
				'id'    => 'wp_debug',
				'title' => esc_html__( 'Set WP_DEBUG to true.', 'wp-debugging' ),
			]
		);

		add_settings_field(
			'wp_debug_display',
			null,
			[ $this, 'checkbox_setting' ],
			'wp_debugging',
			'wp_debugging',
			[
				'id'    => 'wp_debug_display',
				'title' => esc_html__( 'Set WP_DEBUG_DISPLAY to false, default is true.', 'wp-debugging' ),
			]
		);

		add_settings_field(
			'wp_disable_fatal_error_handler',
			null,
			[ $this, 'checkbox_setting' ],
			'wp_debugging',
			'wp_debugging',
			[
				'id'    => 'wp_disable_fatal_error_handler',
				'title' => esc_html__( 'Set WP_DISABLE_FATAL_ERROR_HANDLER to true.', 'wp-debugging' ),
				'class' => version_compare( get_bloginfo( 'version' ), '5.2', '>=' ) ? '' : 'hidden',
			]
		);
	}

	/**
	 * Print settings section information.
	 *
	 * @return void
	 */
	public function print_settings_section() {
		esc_html_e( 'The following constants are set with plugin activation and removed with plugin deactivation.', 'wp-debugging' );
		$this->print_constants();
		esc_html_e( 'Select the debugging constants.', 'wp-debugging' );
	}

	/**
	 * Print current constants.
	 *
	 * @return void
	 */
	private function print_constants() {
		$added_constants      = apply_filters( 'wp_debugging_add_constants', [] );
		$additional_constants = [];
		if ( $added_constants ) {
			foreach ( $added_constants as $constant => $config ) {
				$additional_constants[ $constant ] = $config['value'];
			}
		}

		// Strip user defined constants from $constants array.
		$constants = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants = array_merge( array_keys( self::$options ), $constants );
		$constants = array_diff( $constants, array_keys( $additional_constants ) );

		echo '<pre>';
		foreach ( $constants as $constant ) {
			$value    = 'wp_debug_display' === $constant ? 'false' : 'true';
			$constant = strtoupper( $constant );
			echo wp_kses_post( "<code>define( '{$constant}', {$value} );</code><br>" );
		}
		foreach ( $additional_constants as $constant => $value ) {
			$value    = in_array( $value, [ 'true', 'false' ], true ) ? $value : "'$value'";
			$constant = strtoupper( $constant );
			echo wp_kses_post( "<code>define( '{$constant}', {$value} );</code><br>" );
		}
		echo '</pre>';
	}

	/**
	 * Create settings page.
	 *
	 * @return void
	 */
	public function create_settings_page() {
		$this->saved_settings_notice();
		$action = is_multisite() ? 'edit.php?action=wp-debugging' : 'options.php'; ?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Debugging', 'wp-debugging' ); ?></h1>
			<div class="updated fade">
				<p><?php echo wp_kses_post( __( '<strong>Please note:</strong> Your <code>wp-config.php</code> file must be writable by the filesystem. Any errors will result in a PHP Exception being thrown. Debug constants per <a href="https://codex.wordpress.org/Debugging_in_WordPress">Debugging in WordPress</a>.', 'wp-debugging' ) ); ?></p>
			</div>
			<div>
			<form method="post" action="<?php echo esc_attr( $action ); ?>">
				<?php settings_fields( 'wp_debugging' ); ?>
				<?php do_settings_sections( 'wp_debugging' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Sanitize save settings.
	 *
	 * @param  array $input Input.
	 * @return array $new_input Sanitized output.
	 */
	public function sanitize( $input ) {
		$new_input = [];
		if ( ! is_array( $input ) ) {
			$new_input = sanitize_text_field( $input );
		} else {
			foreach ( array_keys( (array) $input ) as $id ) {
				$new_input[ sanitize_text_field( $id ) ] = sanitize_text_field( $input[ $id ] );
			}
		}

		return $new_input;
	}

	/**
	 * Get the settings option array and print one of its values.
	 *
	 * @param array $args 'id' and 'title'.
	 */
	public function checkbox_setting( $args ) {
		$checked = isset( self::$options[ $args['id'] ] ) ? self::$options[ $args['id'] ] : null;
		?>
		<style> .form-table th { display:none; } </style>
		<label for="<?php echo esc_attr( $args['id'] ); ?>">
			<input type="checkbox" name="wp-debugging[<?php echo esc_attr( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php esc_html_e( $args['title'] ); ?>
		</label>
		<?php
	}

	/**
	 * Add setting link to plugin page.
	 * Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
	 *
	 * @param array $links Plugin links on plugins.php.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_page = is_multisite() ? 'settings.php' : 'tools.php';
		$link          = [ '<a href="' . esc_url( network_admin_url( $settings_page ) ) . '?page=wp-debugging">' . esc_html__( 'Settings', 'wp-debugging' ) . '</a>' ];

		return array_merge( $link, $links );
	}
}
