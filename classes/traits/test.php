<?php
/**
 * Technote Traits Test
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Test
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Test {

	use Singleton, Hook;

	/**
	 * Test constructor.
	 */
	public function __construct() {
		$args = func_get_args();
		if ( count( $args ) > 0 ) {
			$this->init( ...$args );
		}
	}

	/**
	 * @return string
	 */
	public function get_test_slug() {
		return $this->get_file_slug();
	}

}
