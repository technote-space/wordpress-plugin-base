<?php
/**
 * Technote Traits Loader
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
 * Trait Loader
 * @package Technote\Traits\Controller
 * @property \Technote $app
 */
trait Loader {

	use Singleton, Hook, Presenter;

	/** @var array */
	private $cache = array();

	/**
	 * @return string
	 */
	public function get_loader_name() {
		return $this->get_file_slug();
	}

	/**
	 * @param string $dir
	 * @param string $instanceof
	 *
	 * @return \Generator
	 */
	protected function get_classes( $dir, $instanceof ) {
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( preg_match( "/^[^\\.].*\\.php$/", $file ) ) {
					$instance = $this->get_class_instance( $this->get_class_string( $this->app->get_page_slug( $file ) ), $instanceof );
					if ( false !== $instance ) {
						yield $instance;
					}
				}
			}
		}
	}

	/**
	 * @param string $page
	 *
	 * @return string
	 */
	private function get_class_name( $page ) {
		return $this->apply_filters( 'get_class_name', ucfirst( str_replace( DS, '\\', $page ) ), $page );
	}

	/**
	 * @param string $page
	 *
	 * @return false|string
	 */
	protected function get_class_string( $page ) {
		if ( 'base' === $page ) {
			return false;
		}
		if ( isset( $this->cache[ $page ] ) ) {
			return $this->cache[ $page ];
		}
		$namespaces = $this->get_namespaces( $page );
		if ( ! empty( $namespaces ) ) {
			foreach ( $namespaces as $namespace ) {
				$class = rtrim( $namespace, '\\' ) . '\\' . $this->get_class_name( $page );
				if ( class_exists( $class ) ) {
					$this->cache[ $page ] = $class;

					return $class;
				}
			}
		}

		return false;
	}

	/**
	 * @param $class
	 * @param $instanceof
	 *
	 * @return bool|Singleton
	 */
	protected function get_class_instance( $class, $instanceof ) {
		if ( false !== $class && class_exists( $class ) ) {
			try {
				/** @var Singleton $class */
				$instance = $class::get_instance( $this->app );
				if ( $instance instanceof $instanceof ) {
					return $instance;
				}
			} catch ( \Exception $e ) {
			}
		}

		return false;
	}

	/**
	 * @param string $page
	 *
	 * @return array
	 */
	protected abstract function get_namespaces( $page );

}
