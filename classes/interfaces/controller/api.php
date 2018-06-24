<?php
/**
 * Technote Interfaces Controller Api
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Interfaces\Controller;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Interface Api
 * @package Technote\Interfaces\Controller
 */
interface Api {

	/**
	 * @return string
	 */
	public function get_endpoint();

	/**
	 * @return string
	 */
	public function get_call_function_name();

	/**
	 * @return string
	 */
	public function get_method();

	/**
	 * @return array
	 */
	public function get_args_setting();

	/**
	 * @return bool
	 */
	public function is_valid();

	/**
	 * @return bool
	 */
	public function is_only_admin();

	/**
	 * @return bool
	 */
	public function is_only_front();

	/**
	 * @param string $class
	 *
	 * @return false|string
	 */
	public function common_script( $class );

	/**
	 * @param string $class
	 *
	 * @return false|string
	 */
	public function admin_script( $class );

	/**
	 * @param string $class
	 *
	 * @return false|string
	 */
	public function front_script( $class );

	/**
	 * @param \WP_REST_Request $params
	 *
	 * @return int|\WP_Error|\WP_REST_Response
	 */
	public function callback( \WP_REST_Request $params );

}
