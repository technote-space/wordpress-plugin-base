<?php
/**
 * Technote Models Device
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
 * Class Device
 * @package Technote\Models
 */
class Device implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook;

	/** @var bool */
	private $is_bot = null;

	protected function initialize() {

	}

	/**
	 * @return bool
	 */
	public function is_bot() {
		if ( isset( $this->is_bot ) ) {
			return $this->is_bot;
		}

		$this->is_bot = $this->apply_filters( "pre_check_bot", null );
		if ( is_bool( $this->is_bot ) ) {
			return $this->is_bot;
		}

		$bot_list = explode( ',', $this->apply_filters( "bot_list", implode( ',', array(
			"facebookexternalhit",
			"Googlebot",
			"Baiduspider",
			"bingbot",
			"Yeti",
			"NaverBot",
			"Yahoo! Slurp",
			"Y!J-BRI",
			"Y!J-BRJ\\/YATS crawler",
			"Tumblr",
			//		"livedoor",
			//		"Hatena",
			"Twitterbot",
			"Page Speed",
			"Google Web Preview",
		) ) ) );

		$this->is_bot = false;
		$ua           = $this->app->input->user_agent();
		foreach ( $bot_list as $value ) {
			$value = trim( $value );
			if ( preg_match( '/' . $value . '/i', $ua ) ) {
				$this->is_bot = true;
				break;
			}
		}

		return $this->is_bot;
	}

}
