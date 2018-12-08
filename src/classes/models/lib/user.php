<?php
/**
 * Technote Classes Models Lib User
 *
 * @version 2.0.0
 * @author technote-space
 * @since 1.0.0
 * @since 2.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Classes\Models\Lib;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class User
 * @package Technote\Classes\Models\Lib
 * @property int $user_id
 * @property \WP_User $user_data
 * @property int $user_level
 * @property bool $super_admin
 * @property string $user_name
 * @property string $display_name
 * @property string $user_email
 * @property bool $logged_in
 * @property string|false $user_role
 * @property array $user_roles
 * @property array $user_caps
 */
class User implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Uninstall;

	/** @var int $user_id */
	public $user_id;

	/** @var \WP_User $user_data */
	public $user_data;

	/** @var int $user_level */
	public $user_level;

	/** @var bool $super_admin */
	public $super_admin;

	/** @var string $user_name */
	public $user_name;

	/** @var string $display_name */
	public $display_name;

	/** @var string $user_email */
	public $user_email;

	/** @var bool $logged_in */
	public $logged_in;

	/** @var string|false $user_role */
	public $user_role;

	/** @var array $user_roles */
	public $user_roles;

	/** @var array $user_caps */
	public $user_caps;

	/**
	 * initialize
	 */
	protected function initialize() {
		$cache = $this->app->get_shared_object( 'user_info_cache', 'all' );
		if ( ! isset( $cache ) ) {
			$cache = $this->get_user_data();
			$this->app->set_shared_object( 'user_info_cache', $cache, 'all' );
		}
		foreach ( $cache as $k => $v ) {
			$this->$k = $v;
		}
	}

	/**
	 * @return array
	 */
	private function get_user_data() {
		global $user_ID;
		$current_user = wp_get_current_user();

		$data = [];
		if ( $user_ID ) {
			$data['user_data']   = get_userdata( $user_ID );
			$data['user_level']  = $data['user_data']->user_level;
			$data['super_admin'] = is_super_admin( $user_ID );
		} else {
			$data['user_data']   = $current_user;
			$data['user_level']  = 0;
			$data['super_admin'] = false;
		}
		$data['user_id']      = $data['user_data']->ID;
		$data['user_name']    = $data['user_data']->user_login;
		$data['display_name'] = $data['user_data']->display_name;
		$data['user_email']   = $data['user_data']->user_email;
		$data['logged_in']    = is_user_logged_in();
		if ( empty( $data['user_name'] ) ) {
			$data['user_name'] = $this->app->input->ip();
		}
		if ( $data['logged_in'] && ! empty( $data['user_data']->roles ) ) {
			$roles              = array_values( $data['user_data']->roles );
			$data['user_roles'] = $roles;
			$data['user_role']  = $roles[0];
		} else {
			$data['user_roles'] = [];
			$data['user_role']  = false;
		}
		$data['user_caps'] = [];
		foreach ( $data['user_roles'] as $r ) {
			$role = get_role( $r );
			if ( $role ) {
				$data['user_caps'] = array_merge( $data['user_caps'], $role->capabilities );
			}
		}

		return $data;
	}

	/**
	 * reset
	 */
	public function reset_user_data() {
		$data = $this->get_user_data();
		$this->app->set_shared_object( 'user_info_cache', $data, 'all' );
		foreach ( $data as $k => $v ) {
			$this->$k = $v;
		}
	}

	/**
	 * @return string
	 */
	private function get_user_prefix() {
		return $this->get_slug( 'user_prefix', '_user' ) . '-';
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_meta_key( $key ) {
		return $this->get_user_prefix() . $key;
	}

	/**
	 * @param string $key
	 * @param int|null $user_id
	 * @param bool $single
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key, $user_id = null, $single = true, $default = '' ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return $this->apply_filters( 'get_user_meta', $default, $key, $user_id, $single, $default );
		}

		return $this->apply_filters( 'get_user_meta', get_user_meta( $user_id, $this->get_meta_key( $key ), $single ), $key, $user_id, $single, $default, $this->get_user_prefix() );
	}

	/**
	 * @param $key
	 * @param $value
	 * @param int|null $user_id
	 *
	 * @return bool|int
	 */
	public function set( $key, $value, $user_id = null ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return false;
		}

		return update_user_meta( $user_id, $this->get_meta_key( $key ), $value );
	}

	/**
	 * @param string $key
	 * @param int|null $user_id
	 * @param mixed $meta_value
	 *
	 * @return bool
	 */
	public function delete( $key, $user_id = null, $meta_value = '' ) {
		if ( ! isset( $user_id ) ) {
			$user_id = $this->user_id;
		}
		if ( $user_id <= 0 ) {
			return false;
		}

		return delete_user_meta( $user_id, $this->get_meta_key( $key ), $meta_value );
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function set_all( $key, $value ) {
		global $wpdb;
		$query = $wpdb->prepare( "UPDATE $wpdb->usermeta SET meta_value = %s WHERE meta_key LIKE %s", $value, $this->get_meta_key( $key ) );
		$wpdb->query( $query );
	}

	/**
	 * @param string $key
	 */
	public function delete_all( $key ) {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s", $this->get_meta_key( $key ) );
		$wpdb->query( $query );
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return bool
	 */
	public function delete_matched( $key, $value ) {
		$user_ids = $this->find( $key, $value );
		if ( empty( $user_ids ) ) {
			return true;
		}
		foreach ( $user_ids as $user_id ) {
			$this->delete( $key, $user_id );
		}

		return true;
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return array
	 */
	public function find( $key, $value ) {
		global $wpdb;
		$query   = <<< SQL
			SELECT * FROM {$wpdb->usermeta}
			WHERE meta_key LIKE %s
			AND   meta_value LIKE %s
SQL;
		$results = $wpdb->get_results( $wpdb->prepare( $query, $this->get_meta_key( $key ), $value ) );

		return $this->apply_filters( 'find_user_meta', $this->app->utility->array_pluck( $results, 'user_id' ), $key, $value );
	}

	/**
	 * @param string $key
	 * @param string $value
	 *
	 * @return false|int
	 */
	public function first( $key, $value ) {
		$user_ids = $this->find( $key, $value );
		if ( empty( $user_ids ) ) {
			return false;
		}

		return reset( $user_ids );
	}

	/**
	 * @param string $key
	 *
	 * @return array
	 */
	public function get_meta_user_ids( $key ) {
		global $wpdb;
		$query   = <<< SQL
		SELECT user_id FROM {$wpdb->usermeta}
		WHERE meta_key LIKE %s
SQL;
		$results = $wpdb->get_results( $wpdb->prepare( $query, $this->get_meta_key( $key ) ) );

		return $this->apply_filters( 'get_meta_user_ids', $this->app->utility->array_pluck( $results, 'user_id' ), $key );
	}

	/**
	 * @param null|string|false $capability
	 *
	 * @return bool
	 */
	public function user_can( $capability = null ) {
		if ( ! isset( $capability ) ) {
			$capability = $this->app->get_config( 'capability', 'default_user', 'manage_options' );
		}
		if ( false === $capability ) {
			return true;
		}
		if ( '' === $capability ) {
			return false;
		}

		return $this->has_cap( $capability ) || in_array( $capability, $this->user_roles );
	}

	/**
	 * @param string $capability
	 *
	 * @return bool
	 */
	public function has_cap( $capability ) {
		return ! empty( $this->user_caps[ $capability ] );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		global $wpdb;
		$query = $wpdb->prepare( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE %s", $this->get_user_prefix() . '%' );
		$wpdb->query( $query );
	}
}
