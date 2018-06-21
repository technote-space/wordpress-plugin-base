<?php
/**
 * Technote Models Loader Test
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models\Loader;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Test
 * @package Technote\Models\Loader
 */
class Test implements \Technote\Interfaces\Loader {

	use \Technote\Traits\Loader;

	/** @var array */
	protected $tests = null;

	/**
	 * initialize
	 */
	protected function initialize() {

	}

	/**
	 * @return array
	 */
	private function get_tests() {
		if ( ! isset( $this->tests ) ) {
			$this->tests = array();
			/** @var \Technote\Tests\Base $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'tests', '\Technote\Tests\Base' ) as $class ) {
				$slug = $class->get_file_slug();
				if ( ! isset( $this->tests[ $slug ] ) ) {
					$this->tests[ $slug ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'tests', '\Technote\Tests\Base' ) as $class ) {
				$slug = $class->get_file_slug();
				if ( ! isset( $this->tests[ $slug ] ) ) {
					$this->tests[ $slug ] = $class;
				}
			}
		}

		return $this->tests;
	}

	/**
	 * @param $page
	 *
	 * @return array
	 */
	protected function get_namespaces( $page ) {
		return array(
			$this->app->define->plugin_namespace . '\\Tests',
			$this->app->define->lib_namespace . '\\Tests',
		);
	}

	/**
	 * @return array
	 */
	public function do_tests() {
		$results = array();
		foreach ( $this->get_tests() as $slug => $class ) {
			$results[ $slug ] = $this->do_test( $class );
		}

		return $results;
	}

	/**
	 * @param string $class
	 *
	 * @return string
	 */
	private function do_test( $class ) {

		return "";
	}
}
