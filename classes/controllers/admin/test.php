<?php
/**
 * Technote Controller Test
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Test
 * @package Technote\Controllers\Admin
 */
class Test extends Base {

	/**
	 * @return int
	 */
	public function get_priority() {
		return defined( 'WP_DEBUG' ) && WP_DEBUG && $this->app->loader->test->is_valid() ? 10 : - 1;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {
		return 'Test';
	}

	/**
	 * post
	 */
	public function post_action() {
		$action = $this->app->input->post( 'action' );
		if ( method_exists( $this, $action ) && is_callable( array( $this, $action ) ) ) {
			$this->$action();
		}
	}

	/**
	 * @return array
	 */
	public function get_view_args() {
		return array(
			'tests' => $this->app->loader->test->get_test_class_names()
		);
	}

	private function do_test() {
		$this->app->add_message( implode( "\n\n", $this->app->loader->test->do_tests() ) );
	}

}
