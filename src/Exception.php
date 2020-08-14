<?php
/**
 * WP Debugging
 *
 * @package wp-debugging
 * @author Andy Fragen
 * @license MIT
 */

namespace Fragen\WP_Debugging;

use \Throwable;

// Exit if called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Class Exception
 */
class Exception {
	private $exception_handler = null;

	public function __construct() {
		$this->$exception_handler = \set_exception_handler( [ $this, 'exception_hander' ] );
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
		if ( 'Config file is empty.' === $e->getMessage() ) {
			error_log( 'Exception caught: ' . $e->getMessage() );
			exit( 1 );
		}

		// The exception must be re-thrown or passed to the previously registered exception handler so that the error
		// is logged appropriately instead of discarded silently.
		if ( $this->exception_handler ) {
			call_user_func( $this->exception_handler, $e );
		} else {
			throw $e;
		}

		exit( 1 );
	}
}
