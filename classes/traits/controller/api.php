<?php
/**
 * Technote Traits Controller Api
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits\Controller;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Api
 * @package Technote\Traits\Controller
 * @property \Technote\Technote $app
 */
trait Api {

	use \Technote\Traits\Controller;

	/**
	 * @return string
	 */
	public abstract function get_endpoint();

	/**
	 * @return string
	 */
	public abstract function get_call_function_name();

	/**
	 * @return string
	 */
	public abstract function get_method();

	/**
	 * @return array
	 */
	public function get_args_setting() {
		return array();
	}

	/**
	 * @param \WP_REST_Request $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( \WP_REST_Request $params ) {
		return new \WP_REST_Response( null, 404 );
	}

}
