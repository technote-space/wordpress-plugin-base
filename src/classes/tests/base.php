<?php
/**
 * Technote Tests Base
 *
 * @version 2.3.2
 * @author technote-space
 * @since 1.0.0
 * @since 2.0.0
 * @since 2.3.2 Fixed: prevent class not found fatal exception
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Classes\Tests;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Base
 * @package Technote\Classes\Tests
 */
abstract class Base extends \Technote\Classes\Models\Lib\Test\Base implements \Technote\Interfaces\Test {

	use \Technote\Traits\Test;

	/** @var \Technote */
	protected static $test_app;

	/**
	 * @param \Technote $app
	 */
	public static function set_app( $app ) {
		static::$test_app = $app;
	}

	/**
	 * @throws \ReflectionException
	 */
	public final function setUp() {
		$class = get_called_class();
		if ( false === $class ) {
			$class = get_class();
		}
		$reflection = new \ReflectionClass( $class );
		$this->init( static::$test_app, $reflection );
		$this->_setup();
	}

	/**
	 * setup
	 */
	public function _setup() {

	}
}
