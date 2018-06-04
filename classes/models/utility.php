<?php
/**
 * Technote Models Utility
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
 * Class Utility
 * @package Technote\Models
 */
class Utility {

	/** @var array */
	private static $time = null;

	/**
	 * @param array $array
	 * @param bool $preserve_keys
	 *
	 * @return array
	 */
	public static function flatten( array $array, $preserve_keys = false ) {
		$return = array();
		array_walk_recursive( $array, function ( $v, $k ) use ( &$return, $preserve_keys ) {
			if ( $preserve_keys ) {
				$return[ $k ] = $v;
			} else {
				$return[] = $v;
			}
		} );

		return $return;
	}

	/**
	 * @return string
	 */
	public static function uuid() {
		$pid  = getmypid();
		$node = $_SERVER['SERVER_ADDR'];
		list( $timeMid, $timeLow ) = explode( ' ', microtime() );

		return sprintf( "%08x%04x%04x%02x%02x%04x%08x", (int) $timeLow, (int) substr( $timeMid, 2 ) & 0xffff,
			mt_rand( 0, 0xfff ) | 0x4000, mt_rand( 0, 0x3f ) | 0x80, mt_rand( 0, 0xff ), $pid & 0xffff, $node );
	}

	/**
	 * @param $c
	 *
	 * @return bool
	 */
	public static function defined( $c ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );
			if ( $const ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $c
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public static function definedv( $c, $default = null ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );

			return $const;
		}

		return $default;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public static function array_get( array $array, $key, $default = null ) {
		if ( ! is_array( $array ) ) {
			return $default;
		}
		if ( array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}

		return $default;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public static function array_set( array &$array, $key, $value ) {
		if ( ! is_array( $array ) ) {
			$array = array();
		}
		$array[ $key ] = $value;
	}

	/**
	 * @param string $string
	 * @param array $data
	 *
	 * @return string
	 */
	public static function replace( $string, $data ) {
		foreach ( $data as $k => $v ) {
			$string = str_replace( '${' . $k . '}', $v, $string );
		}

		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public static function replace_time( $string ) {
		if ( ! isset( self::$time ) ) {
			self::$time = array();
			foreach (
				array(
					'Y',
					'y',
					'M',
					'm',
					'D',
					'd',
					'H',
					'h',
					'i',
					's',
				) as $t
			) {
				self::$time[ $t ] = date( $t );
			}
		}

		return static::replace( $string, self::$time );
	}
}
