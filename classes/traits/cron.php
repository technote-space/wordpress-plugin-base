<?php
/**
 * Technote Traits Cron
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
 * Trait Cron
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Cron {

	use Singleton, Hook, Uninstall;

	/**
	 * initialize
	 */
	public final function initialize() {
		add_action( $this->get_hook_name(), function () {
			$this->run();
		} );
		$this->set_event();
	}

	/**
	 * set event
	 */
	private function set_event() {
		$interval = $this->get_interval();
		if ( $interval > 0 ) {
			if ( ! wp_next_scheduled( $this->get_hook_name() ) ) {
				if ( ! $this->check_cron_process() ) {
					return;
				}
				wp_schedule_single_event( time() + $interval, $this->get_hook_name() );
			}
		}
	}

	/**
	 * @return bool
	 */
	private function check_cron_process() {
		$expire = get_transient( $this->get_transient_key() );
		if ( false === $expire ) {
			$cron = $this->app->db->select( 'cron', array( 'name' => $this->get_hook_name() ), 'expire', 1, null, array( 'expire' => 'desc' ) );
			if ( ! empty( $cron ) ) {
				$expire = $cron['expire'];
			} else {
				$expire = 0;
			}
			if ( $expire <= time() ) {
				$expire = time() + $this->get_interval();
				set_transient( $this->get_transient_key(), $expire, 15 );

				return true;
			}
			set_transient( $this->get_transient_key(), $expire, 10 );
		}

		return $expire <= time();
	}

	/**
	 * @return int
	 */
	protected function get_interval() {
		return - 1;
	}

	/**
	 * @return int
	 */
	protected function get_expire() {
		return 10 * MINUTE_IN_SECONDS;
	}

	/**
	 * @return string
	 */
	protected function get_hook_prefix() {
		return $this->app->define->plugin_name . '-';
	}

	/**
	 * @return string
	 */
	protected function get_hook_name() {
		return $this->get_hook_prefix() . $this->get_file_slug();
	}

	/**
	 * @return string
	 */
	protected function get_transient_key() {
		return $this->get_hook_name() . '-transient';
	}

	/**
	 * clear event
	 */
	protected function clear_event() {
		wp_clear_scheduled_hook( $this->get_hook_name() );
	}

	/**
	 * run
	 */
	public final function run() {
		set_time_limit( 0 );
		$this->app->db->insert( 'cron', array(
			'name'   => $this->get_hook_name(),
			'expire' => time() + $this->get_expire()
		) );
		$this->do_action( 'before_cron_run', $this->get_hook_name() );
		$this->execute();
		$this->do_action( 'after_cron_run', $this->get_hook_name() );
		$this->set_event();
		$this->app->db->delete( 'cron', array( 'name' => $this->get_hook_name() ) );
	}

	/**
	 * run now
	 */
	public final function run_now() {
		$this->clear_event();
		$this->run();
	}

	/**
	 * execute
	 */
	protected function execute() {

	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		$this->clear_event();
		delete_transient( $this->get_transient_key() );
	}

}
