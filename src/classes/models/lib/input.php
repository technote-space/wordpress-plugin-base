<?php
/**
 * Technote Classes Models Lib Input
 *
 * @version 2.10.0
 * @author technote-space
 * @since 1.0.0
 * @since 2.0.0
 * @since 2.10.0 Changed: trivial change
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Classes\Models\Lib;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Input
 * @package Technote\Classes\Models\Lib
 */
class Input implements \Technote\Interfaces\Singleton {

	use \Technote\Traits\Singleton;

	/**
	 * @since 2.10.0 Changed: trivial change
	 * @var array $_input
	 */
	private static $_input = null;

	/**
	 * @since 2.10.0 Changed: trivial change
	 * @var string $_php_input
	 */
	private static $_php_input = null;

	/**
	 * @since 2.10.0
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @return array
	 */
	public function all() {
		if ( ! isset( self::$_input ) ) {
			self::$_input = array_merge( $_GET, $_POST );
		}

		return self::$_input;
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_GET : $this->app->utility->array_get( $_GET, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function post( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_POST : $this->app->utility->array_get( $_POST, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function request( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_REQUEST : $this->app->utility->array_get( $_REQUEST, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function file( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_FILES : $this->app->utility->array_get( $_FILES, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function cookie( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_COOKIE : $this->app->utility->array_get( $_COOKIE, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function server( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_SERVER : $this->app->utility->array_get( $_SERVER, strtoupper( $key ), $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function ip( $default = '0.0.0.0' ) {
		return $this->server( 'HTTP_X_FORWARDED_FOR', $this->server( 'REMOTE_ADDR', $default ) );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function user_agent( $default = '' ) {
		return $this->server( 'HTTP_USER_AGENT', $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function method( $default = 'GET' ) {
		return strtoupper( $this->server( 'REQUEST_METHOD', $this->request( '_method', $default ) ) );
	}

	/**
	 * @return bool
	 */
	public function is_post() {
		return ! in_array( $this->method(), [
			'GET',
			'HEAD',
		] );
	}

	/**
	 * @return bool|string
	 */
	public function php_input() {
		if ( ! isset( self::$_php_input ) ) {
			self::$_php_input = file_get_contents( 'php://input' );
		}

		return self::$_php_input;
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_current_url( $args = [] ) {
		$url = $this->get_current_host() . $this->get_current_path();
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_current_host() {
		return ( is_ssl() ? "https://" : "http://" ) . $this->server( 'HTTP_HOST' );
	}

	/**
	 * @return string
	 */
	public function get_current_path() {
		return $this->server( 'REQUEST_URI' );
	}
}
