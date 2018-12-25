<?php
/**
 * Technote Traits Helper Data Helper
 *
 * @version 2.8.3
 * @author technote-space
 * @since 2.8.0
 * @since 2.8.3 Changed: move parse_db_type to utility
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits\Helper;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Data_Helper
 * @package Technote\Traits\Helper
 * @property \Technote $app
 */
trait Data_Helper {

	use \Technote\Traits\Singleton;

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	protected function filter_key( $key ) {
		return $key;
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return bool
	 */
	protected function validate_kana( $key, $post_array ) {
		$key = $this->filter_key( $key );

		return empty( $post_array[ $key ] ) || preg_match( '#^[ァ-ヴ][ァ-ヴー・]*$#u', $post_array[ $key ] ) > 0;
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return bool
	 */
	protected function validate_date( $key, $post_array ) {
		$key = $this->filter_key( $key );

		return ! empty( $post_array[ $key ] ) && preg_match( '#^\d{4}[/-]\d{1,2}[/-]\d{1,2}$#', $post_array[ $key ] ) > 0;
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return bool
	 */
	protected function validate_time( $key, $post_array ) {
		$key = $this->filter_key( $key );

		return ! empty( $post_array[ $key ] ) && preg_match( '#^\d{1,2}:\d{1,2}$#', $post_array[ $key ] ) > 0;
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return bool
	 */
	protected function validate_email( $key, $post_array ) {
		$key = $this->filter_key( $key );

		return ! empty( $post_array[ $key ] ) && is_email( $post_array[ $key ] );
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return bool
	 */
	protected function validate_phone( $key, $post_array ) {
		$key = $this->filter_key( $key );

		return ! empty( $post_array[ $key ] ) && preg_match( '#^\d{2,3}-?\d{3,4}-?\d{4,5}$#', $post_array[ $key ] ) > 0;
	}

	/**
	 * @param array $data
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function convert_to_bool( $data, $key ) {
		return ! empty( $data[ $key ] ) && $data[ $key ] !== '0' && $data[ $key ] !== 'false';
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function the_content( $str ) {
		return apply_filters( 'the_content', $str );
	}

	/**
	 * @param mixed $param
	 * @param string $type
	 *
	 * @return mixed
	 */
	protected function sanitize_input( $param, $type ) {
		switch ( strtolower( trim( $type ) ) ) {
			case 'int':
				if ( ! is_int( $param ) && ! ctype_digit( ltrim( $param, '-' ) ) ) {
					return null;
				}
				$param -= 0;
				$param = (int) $param;
				break;
			case 'float':
			case 'number':
				if ( ! is_numeric( $param ) && ! ctype_alpha( $param ) ) {
					return null;
				}
				$param -= 0;
				break;
			case 'bool':
			case 'boolean':
				if ( is_string( $param ) ) {
					$param = strtolower( trim( $param ) );
					if ( $param === 'true' ) {
						$param = 1;
					} elseif ( $param === 'false' ) {
						$param = 0;
					} elseif ( $param === '0' ) {
						$param = 0;
					} else {
						$param = ! empty( $param );
					}
				} else {
					$param = ! empty( $param );
				}
				break;
			default:
				break;
		}

		return $param;
	}
}
