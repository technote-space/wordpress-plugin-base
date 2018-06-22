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

	/** @var bool $is_valid */
	private $is_valid = false;

	/** @var array */
	protected $tests = null;

	/**
	 * initialize
	 */
	protected function initialize() {
		if ( ! class_exists( '\PHPUnit_TextUI_Command' ) ) {
			$autoload = $this->app->define->lib_vendor_dir . DS . 'autoload.php';
			if ( ! file_exists( $autoload ) ) {
				return;
			}

			if ( ! defined( 'PHPUNIT_TESTSUITE' ) ) {
				define( 'PHPUNIT_TESTSUITE', 'PHPUNIT_TESTSUITE' );
			}
			require_once $this->app->define->lib_vendor_dir . DS . 'autoload.php';

			if ( ! class_exists( '\PHPUnit_TextUI_Command' ) ) {
				return;
			}
		}

		$this->is_valid = true;
		\Technote\Tests\Base::set_app( $this->app );
	}

	/**
	 * @return bool
	 */
	public function is_valid() {
		return $this->is_valid;
	}

	/**
	 * @return array
	 */
	private function get_tests() {
		if ( ! $this->is_valid ) {
			return array();
		}

		if ( ! isset( $this->tests ) ) {
			$this->tests = array();
			/** @var \Technote\Tests\Base $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'tests', '\Technote\Tests\Base' ) as $class ) {
				$slug = $class->class_name;
				if ( ! isset( $this->tests[ $slug ] ) ) {
					$this->tests[ $slug ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'tests', '\Technote\Tests\Base' ) as $class ) {
				$slug = $class->class_name;
				if ( ! isset( $this->tests[ $slug ] ) ) {
					$this->tests[ $slug ] = $class;
				}
			}
		}

		return $this->tests;
	}

	/**
	 * @return array
	 */
	public function get_test_class_names() {
		return \Technote\Models\Utility::array_pluck( $this->get_tests(), 'class_name' );
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
		if ( ! $this->is_valid ) {
			return array();
		}

		$command = new \PHPUnit_TextUI_Command();

		$results = array();
		foreach ( $this->get_tests() as $slug => $class ) {
			$results[ $slug ] = $slug . "\n\n" . $this->do_test( $class, $command );
		}

		return $results;
	}

	/**
	 * @param \Technote\Tests\Base $class
	 * @param \PHPUnit_TextUI_Command $command
	 *
	 * @return string
	 */
	private function do_test( $class, $command ) {
		ob_start();
		$command->run( array(
			"--no-globals-backup",
			$class->class_name,
			$class->reflection->getFileName()
		), false );
		$buffer = ob_get_contents();
		ob_end_clean();

		return $buffer;
	}
}
