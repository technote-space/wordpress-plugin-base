<?php
/**
 * Technote Classes Controller Admin Logs
 *
 * @version 2.9.0
 * @author technote-space
 * @since 1.0.0
 * @since 2.0.0
 * @since 2.7.0 Changed: save log to db
 * @since 2.9.0 Changed: is_valid_log > is_valid
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Classes\Controllers\Admin;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class Logs
 * @package Technote\Classes\Controllers\Admin
 */
class Logs extends Base {

	/**
	 * @return int
	 */
	public function get_load_priority() {
		return $this->app->log->is_valid() ? $this->apply_filters( 'logs_page_priority', 999 ) : - 1;
	}

	/**
	 * @return string
	 */
	public function get_page_title() {
		return $this->apply_filters( 'logs_page_title', 'Logs' );
	}

	/**
	 * @return array
	 */
	protected function get_view_args() {
		$p     = $this->apply_filters( 'log_page_query_name', 'p' );
		$total = $this->app->db->select_count( '__log' );
		$limit = $this->apply_filters( 'get___log_limit', 20 );
		if ( $limit < 1 ) {
			$limit = 1;
		}
		$total_page = max( 1, ceil( $total / $limit ) );
		$page       = max( 1, min( $total_page, $this->app->input->get( $p, 1 ) ) );
		$offset     = ( $page - 1 ) * $limit;
		$start      = $total > 0 ? $offset + 1 : 0;
		$end        = $total > 0 ? min( $offset + $limit, $total ) : 0;

		return [
			'logs'       => $this->app->db->select( '__log', [], null, $limit, $offset, [
				'created_at' => 'DESC',
			] ),
			'total'      => $total,
			'total_page' => $total_page,
			'page'       => $page,
			'offset'     => $offset,
			'p'          => $p,
			'start'      => $start,
			'end'        => $end,
		];
	}
}
