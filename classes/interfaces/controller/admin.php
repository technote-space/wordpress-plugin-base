<?php
/**
 * Technote Interfaces Controller Admin
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
 * Interface Controller Admin
 * @package Technote\Interfaces\Controller
 */
interface Admin extends \Technote\Interfaces\Controller, \Technote\Interfaces\Nonce {

	/**
	 * @return int
	 */
	public function get_priority();

	/**
	 * @return string
	 */
	public function get_page_title();

	/**
	 * @return string
	 */
	public function get_menu_name();

	/**
	 * get
	 */
	public function get_action();

	/**
	 * post
	 */
	public function post_action();

	/**
	 * @return string
	 */
	public function presenter();

}
