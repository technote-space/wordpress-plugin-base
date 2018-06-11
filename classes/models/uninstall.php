<?php
/**
 * Technote Models Uninstall
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Uninstall
 * @package Technote\Models
 */
class Uninstall implements \Technote\Interfaces\Singleton {

	use \Technote\Traits\Singleton;

	/** @var array $uninstall */
	private $uninstall = array();

	/**
	 * initialize
	 */
	protected function initialize() {
		register_uninstall_hook( $this->app->define->plugin_base_name, array(
			"\Technote",
			"register_uninstall_" . $this->app->define->plugin_base_name,
		) );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		foreach ( $this->uninstall as $item ) {
			if ( is_callable( $item ) ) {
				call_user_func( $item );
			}
		}
		$this->uninstall = array();
	}

	/**
	 * @param $callback
	 */
	public function add_uninstall( $callback ) {
		$this->uninstall[] = $callback;
	}

}
