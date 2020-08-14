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
 * Class Exception
 */
class Exception extends \Exception {

	public function __construct() {
		\set_exception_handler( [ $this, 'exception_hander' ] );
	}

	/**
	 * Exception handler.
	 *
	 * In PHP >= 7 this will receive a Throwable object.
	 * In PHP < 7 it will receive an Exception object.
	 *
	 * @param Throwable|Exception $e The error or exception.
	 *
	 * @return void
	 */
	public function exception_handler( $e ) {
		error_log( 'Exception caught: ' . $e->getMessage() );
		if ( 'Config file is empty.' === $e->getMessage() ) {
			error_log( 'Exception caught: ' . $e->getMessage() );
			exit( 1 );
		}

		throw $e;
	}
}
