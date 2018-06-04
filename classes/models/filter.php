<?php
/**
 * Technote Models Filter
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
 * Class Filter
 * @package Technote\Models
 */
class Filter implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/** @var array */
	private $filter = array();

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->filter = $this->apply_filters( 'filter', $this->app->config->load( 'filter' ) );
		foreach ( $this->filter as $class => $tags ) {
			$app = $class;
			if ( strpos( $class, '->' ) !== false ) {
				$app      = $this->app;
				$exploded = explode( '->', $class );
				foreach ( $exploded as $property ) {
					if ( property_exists( $app, $property ) ) {
						$app = $app->$property;
					} else {
						$app = false;
						break;
					}
				}
			} else {
				if ( property_exists( $this->app, $class ) ) {
					$app = $this->app->$class;
				}
			}
			if ( false !== $app && is_callable( array( $app, 'add_filter' ) ) ) {
				foreach ( $tags as $tag => $methods ) {
					foreach ( $methods as $method => $params ) {
						$this->call_add_filter( array( $app, 'add_filter' ), $tag, $method, $params );
					}
				}
			}
		}
	}

	/**
	 * @param mixed $var
	 * @param string $tag
	 * @param string $method
	 * @param array $params
	 */
	private function call_add_filter( $var, $tag, $method, $params ) {
		$priority      = 10;
		$accepted_args = 100;
		if ( is_array( $params ) ) {
			if ( count( $params ) >= 1 ) {
				$priority = $params[0];
			}
			if ( count( $params ) >= 2 ) {
				$accepted_args = $params[1];
			}
		}

		call_user_func( $var, $tag, $method, $priority, $accepted_args );
	}

}
