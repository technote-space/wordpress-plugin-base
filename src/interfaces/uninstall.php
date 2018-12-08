<?php
/**
 * Technote Interfaces Uninstall
 *
 * @version 2.0.0
 * @author technote-space
 * @since 1.0.0
 * @since 2.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Interfaces;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Interface Uninstall
 * @package Technote\Interfaces
 */
interface Uninstall {

	/**
	 * uninstall
	 */
	public function uninstall();

	/**
	 * @return int
	 */
	public function get_uninstall_priority();

}
