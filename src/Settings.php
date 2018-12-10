<?php

namespace Fragen\WP_Debugging;

class Settings {

	/**
	 * Hold plugin options.
	 *
	 * @var $options
	 */
	protected static $options;

	/**
	 * Constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		self::$options = get_site_option( 'wp_debugging', [] );
	}

	/**
	 * Load hooks for settings.
	 *
	 * @return void
	 */
	public function load_hooks() {
		add_action( 'admin_init', array( $this, 'add_settings' ) );
		add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'add_plugin_menu' ) );
		add_action( 'network_admin_edit_wp_debugging', array( $this, 'update_settings' ) );
		add_action( 'admin_init', array( $this, 'update_settings' ) );
		add_filter(
			is_multisite()
			? 'network_admin_plugin_action_links_wp-debugging/wp-debugging.php'
			: 'plugin_action_links_wp-debugging/wp-debugging.php',
			[ $this, 'plugin_action_links' ]
		);
	}

	/**
	 * Add plugin menu.
	 *
	 * @return void
	 */
	public function add_plugin_menu() {
		$parent     = is_multisite() ? 'settings.php' : 'tools.php';
		$capability = is_multisite() ? 'manage_network' : 'manage_options';

		add_submenu_page(
			$parent,
			esc_html__( 'WP Debugging', 'wp-debugging' ),
			esc_html__( 'WP Debugging', 'wp-debugging' ),
			$capability,
			'wp-debugging',
			array( $this, 'create_settings_page' )
		);
	}

	/**
	 * Update settings on save.
	 *
	 * @return void
	 */
	public function update_settings() {
		if ( isset( $_POST['option_page'] ) &&
			'wp_debugging' === $_POST['option_page']
		) {
			$options = isset( $_POST['wp-debugging'] )
				? $_POST['wp-debugging']
				: array();
			$options = Settings::sanitize( $options );
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
	 * Update Feature Flag constants in wp-config.php.
	 *
	 * @uses https://github.com/wp-cli/wp-config-transformer
	 *
	 * @param array $old Current value of self::$options.
	 * @param mixed $new New value of $options.
	 * @return void
	 */
	private function update_constants( $old, $new ) {
		$remove = array_diff_assoc( $old, $new );
		$add    = array_diff_assoc( $new, $old );

		if ( ! empty( $add ) ) {
			// Use class WPConfigTransformer to add constant.
			$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
			foreach ( array_keys( $add ) as $constant ) {
				$value = 'wp_debug_display' === $constant ? 'false' : 'true';
				$config_transformer->update(
					'constant',
					strtoupper( $constant ),
					$value,
					array(
						'raw'       => true,
						'normalize' => true,
					)
				);
			}
		}
		if ( ! empty( $remove ) ) {
			// Use class WPConfigTransformer to remove constant.
			$config_transformer = new \WPConfigTransformer( ABSPATH . 'wp-config.php' );
			foreach ( array_keys( $remove ) as $constant ) {
				$config_transformer->remove( 'constant', strtoupper( $constant ) );
			}
		}
	}

	/**
	 * Redirect back to settings page on save.
	 *
	 * @return void
	 */
	private function redirect_on_save() {
		$update = false;
		if ( ( isset( $_POST['action'] ) && 'update' === $_POST['action'] ) &&
			( isset( $_POST['option_page'] ) && 'wp_debugging' === $_POST['option_page'] )
		) {
			$update = true;
		}

		$redirect_url = is_multisite() ? network_admin_url( 'settings.php' ) : admin_url( 'tools.php' );

		if ( $update ) {
			$query = isset( $_POST['_wp_http_referer'] ) ? parse_url( $_POST['_wp_http_referer'], PHP_URL_QUERY ) : null;

			$location = add_query_arg(
				array(
					'page'    => 'wp-debugging',
					'updated' => $update,
				),
				$redirect_url
			);
			wp_redirect( $location );
			exit;
		}
	}

	/**
	 * Add notice when settings are saved.
	 *
	 * @return void
	 */
	private function saved_settings_notice() {
		if ( ( isset( $_GET['updated'] ) && true == $_GET['updated'] ) ||
			( isset( $_GET['settings-updated'] ) && true == $_GET['settings-updated'] )
		) {
			echo '<div class="updated"><p>';
			esc_html_e( 'Saved.', 'wp-debugging' );
			echo '</p></div>';
		}
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
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'wp_debugging',
			esc_html__( 'Debugging Constants', 'wp-debugging' ),
			array( $this, 'print_settings_section' ),
			'wp_debugging'
		);

		add_settings_field(
			'wp_debug',
			null,
			array( $this, 'checkbox_setting' ),
			'wp_debugging',
			'wp_debugging',
			array(
				'id'    => 'wp_debug',
				'title' => esc_html__( 'Set WP_DEBUG to true.', 'wp-debugging' ),
			)
		);

		add_settings_field(
			'wp_debug_display',
			null,
			array( $this, 'checkbox_setting' ),
			'wp_debugging',
			'wp_debugging',
			array(
				'id'    => 'wp_debug_display',
				'title' => esc_html__( 'Set WP_DEBUG_DISPLAY to false, default is true.', 'wp-debugging' ),
			)
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
		$constants = [ 'wp_debug_log', 'script_debug', 'savequeries' ];
		$constants = array_merge( array_keys( self::$options ), $constants );
		echo '<pre>';
		foreach ( $constants as $constant ) {
			$value    = 'wp_debug_display' === $constant ? 'false' : 'true';
			$constant = strtoupper( $constant );
			echo "<code>define( '{$constant}', {$value} );</code><br>";
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
		$action = is_multisite() ? 'edit.php?action=wp-debugging' : 'options.php';

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'WP Debugging', 'wp-debugging' ); ?></h1>
			<div class="updated fade">
				<p><?php echo( wp_kses_post( __( '<strong>Please note:</strong> Your <code>wp-config.php</code> file must be writable by the filesystem.', 'wp-debugging' ) ) ); ?></p>
			</div>
			<div>
			<form method="post" action="<?php esc_attr_e( $action ); ?>">
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
	 * @param array $input
	 * @return array $new_input
	 */
	public function sanitize( $input ) {
		$new_input = array();
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
	 * @param array $args 'id' and 'title'
	 */
	public function checkbox_setting( $args ) {
		$checked = isset( self::$options[ $args['id'] ] ) ? self::$options[ $args['id'] ] : null;
		?>
		<style> .form-table th { display:none; } </style>
		<label for="<?php esc_attr_e( $args['id'] ); ?>">
			<input type="checkbox" name="wp-debugging[<?php esc_attr_e( $args['id'] ); ?>]" value="1" <?php checked( '1', $checked ); ?> >
			<?php echo $args['title']; ?>
		</label>
		<?php
	}

	/**
	 * Add setting link to plugin page.
	 * Applied to the list of links to display on the plugins page (beside the activate/deactivate links).
	 *
	 * @link http://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
	 *
	 * @param $links
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$settings_page = is_multisite() ? 'settings.php' : 'tools.php';
		$link          = [ '<a href="' . esc_url( network_admin_url( $settings_page ) ) . '?page=wp-debugging">' . esc_html__( 'Settings', 'wp-debugging' ) . '</a>' ];

		return array_merge( $links, $link );
	}

}
