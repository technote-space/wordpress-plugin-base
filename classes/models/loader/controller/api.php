<?php
/**
 * Technote Models Loader Controller Api
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Models\Loader\Controller;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Admin
 * @package Technote\Models\Loader\Controller
 */
class Api implements \Technote\Interfaces\Loader, \Technote\Interfaces\Nonce {

	use \Technote\Traits\Loader, \Technote\Traits\Nonce;

	/** @var array */
	private $api_controllers = null;

	/**
	 * initialize
	 */
	protected function initialize() {
		$apis = $this->get_api_controllers();
		if ( ! empty( $apis ) ) {
			wp_enqueue_script( 'wp-api' );
		}
	}

	/**
	 * @return string
	 */
	public function get_nonce_slug() {
		return 'wp_rest';
	}

	/**
	 * @return string
	 */
	private function get_js_class() {
		return $this->get_slug( 'js_class', '_rest_api' );
	}

	/**
	 * register script
	 */
	private function register_script() {
		$functions = array();
		/** @var \Technote\Traits\Controller\Api $api */
		foreach ( $this->get_api_controllers() as $api ) {
			$name               = $api->get_call_function_name();
			$functions[ $name ] = array(
				'method'   => $api->get_method(),
				'endpoint' => $api->get_endpoint(),
			);
		}
		if ( ! empty( $functions ) ) {
			$this->add_script_view( 'include/api', array(
				'endpoint'  => rest_url(),
				'namespace' => $this->get_api_namespace(),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'functions' => $functions,
				'class'     => $this->get_js_class(),
			), 9 );
		}
	}

	/**
	 * @return string
	 */
	private function get_api_namespace() {
		return $this->get_slug( 'api_namespace', '' ) . '/' . $this->app->get_config( 'config', 'api_version' );
	}

	/**
	 * register api
	 */
	private function register_api() {
		foreach ( $this->get_api_controllers() as $api ) {
			/** @var \Technote\Controllers\Api\Base $api */
			register_rest_route( $this->get_api_namespace(), $api->get_endpoint(), array(
				'methods'             => strtoupper( $api->get_method() ),
				'permission_callback' => function () use ( $api ) {
					return current_user_can( $api->get_capability() );
				},
				'args'                => $api->get_args_setting(),
				'callback'            => array( $api, 'callback' ),
			) );
		}
	}

	/**
	 * @param string $page
	 *
	 * @return array
	 */
	protected function get_namespaces( $page ) {
		return array(
			$this->app->define->plugin_namespace . '\\Controllers\\Api\\',
			$this->app->define->lib_namespace . '\\Controllers\\Api\\',
		);
	}

	/**
	 * @return array
	 */
	private function get_api_controllers() {
		if ( ! isset( $this->api_controllers ) ) {
			$this->api_controllers = array();
			/** @var \Technote\Traits\Controller\Api $class */
			foreach ( $this->get_classes( $this->app->define->lib_classes_dir . DS . 'controllers' . DS . 'api', '\Technote\Controllers\Api\Base' ) as $class ) {
				$name = $class->get_call_function_name();
				if ( ! isset( $this->api_controllers[ $name ] ) ) {
					$this->api_controllers[ $name ] = $class;
				}
			}

			foreach ( $this->get_classes( $this->app->define->plugin_classes_dir . DS . 'controllers' . DS . 'api', '\Technote\Controllers\Api\Base' ) as $class ) {
				$name = $class->get_call_function_name();
				if ( ! isset( $this->api_controllers[ $name ] ) ) {
					$this->api_controllers[ $name ] = $class;
				}
			}
		}

		return $this->api_controllers;
	}


}
