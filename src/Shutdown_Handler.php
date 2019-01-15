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
 * Class Shutdown_Handler
 *
 * @link https://gist.github.com/westonruter/583a42392a0b8684dc268b40d44eb7f1
 */
class Shutdown_Handler extends \WP_Shutdown_Handler {
	/**
	 * handle
	 *
	 * @return void
	 */
	public function handle() {
		// No-op.
	}
}
