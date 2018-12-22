<?php
/**
 * Plugin Name: Debug Quick Look
 * Plugin URI: https://github.com/norcross/debug-quick-look
 * Description: Creates an admin bar link to view or purge the debug log file
 * Author: Andrew Norcross
 * Author URI: http://andrewnorcross.com/
 * Version: 0.0.3
 * Text Domain: debug-quick-look
 * Requires WP: 4.4
 * Domain Path: languages
 * GitHub Plugin URI: https://github.com/norcross/debug-quick-look
 * @package debug-quick-look
 */

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2017 Andrew Norcross
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

// Define our version.
if ( ! defined( 'DEBUG_QUICK_LOOK_VERS' ) ) {
	define( 'DEBUG_QUICK_LOOK_VERS', '0.0.3' );
}

/**
 * Call our class.
 */
class DebugQuickLook {

	/**
	 * Call our hooks.
	 *
	 * @return void
	 */
	public function init() {
		if ( ! $this->has_debug_constant() ) {
			add_action( 'wp_head',                      array( $this, 'add_warning_css'     )           );
			add_action( 'admin_head',                   array( $this, 'add_warning_css'     )           );
		}
		add_action( 'admin_init',                   array( $this, 'process_debug_type'  )           );
		add_action( 'admin_bar_menu',               array( $this, 'admin_bar_links'     ),  9999    );
	}

	/**
	 * Add the CSS to flag our warning message.
	 */
	public function add_warning_css() {

		// Bail if current user doesnt have cap or the constant is set.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Open the style tag.
		echo '<style>';

		// Output the actual CSS item.
		echo 'li#wp-admin-bar-debug-quick-look li.debug-quick-look-missing .ab-item span {';
			echo 'color: #ff0000;';
			echo 'font-weight: bold;';
			echo 'font-family: Consolas, Monaco, monospace;';
		echo '}';

		// Close the style tag.
		echo '</style>';
	}

	/**
	 * Run a quick check to see if the debug log constant is set.
	 *
	 * @return boolean
	 */
	public function has_debug_constant() {
		return defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? true : false;
	}

	/**
	 * Run a quick check to see if the debug log file is empty.
	 *
	 * @return boolean
	 */
	public function check_file_data() {

		// If the constant isn't set, return false right away.
		if ( ! $this->has_debug_constant() ) {
			return false;
		}

		// Set my path file.
		$pfile  = WP_CONTENT_DIR . '/debug.log';

		// If no file exists at all, create an empty one.
		if ( false === file_exists( $pfile ) ) {
			file_put_contents( $pfile, '' );
		}

		// If the file is empty, return that.
		return 0 === filesize( $pfile ) ? false : true;
	}

	/**
	 * Handle our debug file actions based on query strings.
	 *
	 * @return HTML
	 */
	public function process_debug_type() {

		// Bail if current user doesnt have cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Bail without the query strings or not on admin.
		if ( ! is_admin() || empty( $_GET['quicklook'] ) || empty( $_GET['quickaction'] ) ) {
			return;
		}

		// Bail if we didn't pass the correct action type.
		if ( ! in_array( sanitize_key( $_GET['quickaction'] ), array( 'view', 'purge' ) ) ) {
			return;
		}

		// Create some basic CSS.
		$style  = '
		p.returnlink { text-align: center; font-size: 14px; line-height: 22px; }
		p.nofile { text-align: center; font-size: 14px; line-height: 22px; font-style: italic; }
		p.codeblock { background-color: #fff; color: #000; font-size: 14px; line-height: 22px; padding: 5px 15px; }
		p.codeblock .empty-space { display: block; width: 100%; height: 0; margin: 10px 0 -10px; padding: 0; border-top: 1px dotted #ccc; }
		p strong { font-weight: bold; }	p em { font-style: italic; }
		code, pre { white-space: pre-wrap; white-space: -moz-pre-wrap; white-space: -pre-wrap; white-space: -o-pre-wrap; word-wrap: break-word; }
		code pre, span.prewrap { color: #ff0000; }
		';

		// Filter it.
		$style  = apply_filters( 'debug_quick_look_css', $style );

		// Set my empty.
		$build  = '';

		// Include a "back to admin" link.
		$build .= '<p class="returnlink"><a href="' . admin_url( '/' ) . '">' . esc_html__( 'Return To Admin Dashboard', 'debug-quick-look' ) . '</a></p>';

		// Check to make sure we have a file.
		if ( ! $this->check_file_data() ) {
			$build .= '<p class="nofile">' . esc_html__( 'Your debug file is empty.', 'debug-quick-look' ) . '</p>';

		// We have a file. So start the additional checks.
		} else {

			// Set our file.
			$file   = WP_CONTENT_DIR . '/debug.log';

			// We requested a viewing.
			if ( 'view' === sanitize_key( $_GET['quickaction'] ) ) {
				$build .= $this->view_log( $file );
			}

			// We requested a purging.
			if ( 'purge' === sanitize_key( $_GET['quickaction'] ) ) {
				$build .= $this->purge_log( $file );
			}
		}

		// If we have CSS values, echo them.
		if ( ! empty( $style ) ) {
			echo '<style>' . esc_attr( $style ) . '</style>';
		}

		// Echo out the build.
		echo wp_kses_post( $build );

		// And die.
		die();
	}

	/**
	 * Our abstracted function for viewing the log file.
	 *
	 * @param  string $file  The filepath we are working with.
	 *
	 * @return string
	 */
	public function view_log( $file = '' ) {

		// Parse out the data.
		$data   = $this->parse_log( $file );

		// Trim and break it up.
		$data   = nl2br( trim( $data ) );

		// Now convert the line break markup to an empty div.
		$data   = str_replace( array( '<br>', '<br />' ), '<span class="empty-space">&nbsp;</span>', $data );

		// Convert my pre tags to spans so we can style them.
		$data   = str_replace( array( '<pre>', '</pre>' ), array( '<span class="prewrap">', '</span>' ), $data );

		// Generate and return the actual output.
		return '<p class="codeblock"><code>' . $data . '</code></p>';
	}

	/**
	 * Our abstracted function for purging the log file.
	 *
	 * @param  string $file  The filepath we are working with.
	 *
	 * @return string
	 */
	public function purge_log( $file = '' ) {

		// Purge the data file.
		$purge  = file_put_contents( $file, '' );

		// Generate and return the message.
		return '<p class="nofile">' . esc_html__( 'The log file has been purged.', 'debug-quick-look' ) . '</p>';
	}

	/**
	 * Parse my log file from the end in case it's too big.
	 *
	 * @link http://stackoverflow.com/questions/6451232/php-reading-large-files-from-end/6451391#6451391
	 *
	 * @param  string  $file   The filepath we are working with.
	 * @param  integer $count  Our line count that we're working with.
	 * @param  integer $size   How many bytes we safely wanna check.
	 *
	 * @return string
	 */
	public function parse_log( $file = '', $count = 100, $size = 512 ) {

		// Set my empty.
		$lines  = array();

		// We will always have a fragment of a non-complete line, so keep this in here till we have our next entire line.
		$left   = '';

		// Open our file.
		$readf  = fopen( $file, 'r' );

		// Go to the end of the file.
		fseek( $readf, 0, SEEK_END );

		do {

			// Confirm we can actually go back $size bytes
			$check  = $size;

			if ( ftell( $readf ) <= $size ) {
				$check = ftell( $readf );
			}

			// Bail on an empty file.
			if ( empty( $check ) ) {
				break;
			}

			// Go back as many bytes as we can and read them to $data,
			// and then move the file pointer back to where we were.
			fseek( $readf, - $check, SEEK_CUR );

			// Set the data.
			$data  = fread( $readf, $check );

			// Include the "leftovers".
			$data .= $left;

			// Seek back into it.
			fseek( $readf, - $check, SEEK_CUR );

			// Split lines by \n. Then reverse them, now the last line is most likely
			// not a complete line which is why we do not directly add it, but
			// append it to the data read the next time.
			$split  = array_reverse( explode( "\n", $data ) );
			$newls  = array_slice( $split, 0, - 1 );
			$lines  = array_merge( $lines, $newls );
			$left   = $split[ count( $split ) - 1 ];

		} while ( count( $lines ) < $count && ftell( $readf ) != 0 );

		// Check and add the extra line.
		if ( ftell( $readf ) == 0 ) {
			$lines[] = $left;
		}

		// Close the file we just dealt with.
		fclose( $readf );

		// Usually, we will read too many lines, correct that here.
		$array  = array_slice( $lines, 0, $count );
		$array  = array_reverse( array_filter( $array, 'strlen' ) );

		// Convert my array to a large string.
		return implode( "\n", $array );
	}

	/**
	 * Add the links for the debug log file.
	 *
	 * @param  WP_Admin_Bar $wp_admin_bar  The global WP_Admin_Bar object.
	 *
	 * @return void.
	 */
	public function admin_bar_links( WP_Admin_Bar $wp_admin_bar ) {

		// Bail if current user doesnt have cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Add a parent item.
		$wp_admin_bar->add_node(
			array(
				'id'    => 'debug-quick-look',
				'title' => __( 'Debug Quick Look', 'debug-quick-look' ),
			)
		);

		// Load the two links if we have the logging constant defined.
		if ( $this->has_debug_constant() ) {

			// Make my links.
			$view   = add_query_arg( array( 'quicklook' => 1, 'quickaction' => 'view' ), admin_url( '/' ) );
			$purge  = add_query_arg( array( 'quicklook' => 1, 'quickaction' => 'purge' ), admin_url( '/' ) );

			// Add the "view" link.
			$wp_admin_bar->add_node(
				array(
					'id'        => 'quick-look-view',
					'title'     => __( 'View Log', 'debug-quick-look' ),
					'href'      => esc_url( $view ),
					'position'  => 0,
					'parent'    => 'debug-quick-look',
					'meta'      => array(
						'title'     => __( 'View Log', 'debug-quick-look' ),
						'target'    => '_blank',
					),
				)
			);

			// Add the "purge" link.
			$wp_admin_bar->add_node(
				array(
					'id'        => 'quick-look-purge',
					'title'     => __( 'Purge Log', 'debug-quick-look' ),
					'href'      => esc_url( $purge ),
					'position'  => 0,
					'parent'    => 'debug-quick-look',
					'meta'      => array(
						'title'     => __( 'Purge Log', 'debug-quick-look' ),
						'target'    => '_blank',
					),
				)
			);

		// Load a warning message if we haven't defined it.
		} else {

			// Add the text node with our warning.
			$wp_admin_bar->add_node(
				array(
					'id'        => 'quick-look-error',
					'title'     => __( 'The <span>WP_DEBUG_LOG</span> constant is not defined!', 'debug-quick-look' ),
					'position'  => 0,
					'parent'    => 'debug-quick-look',
					'meta'      => array(
						'class' => 'debug-quick-look-missing',
					),
				)
			);
		}
	}

	// End our class.
}

// Call our class.
$DebugQuickLook = new DebugQuickLook();
$DebugQuickLook->init();
