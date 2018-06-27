<?php
/**
 * Technote Models Db
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
 * Class Db
 * @package Technote\Models
 */
class Db implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Uninstall;

	/** @var array */
	protected $table_defines = null;

	/**
	 * initialize
	 */
	protected function initialize() {
		$this->load_table_defines();
		$this->db_update();
	}

	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function type2format( $type ) {
		if ( stristr( $type, 'INT' ) !== false ) {
			return '%d';
		}
		if ( stristr( $type, 'BIT' ) !== false ) {
			return '%d';
		}
		if ( stristr( $type, 'BOOLEAN' ) !== false ) {
			return '%d';
		}
		if ( stristr( $type, 'DECIMAL' ) !== false ) {
			return '%f';
		}
		if ( stristr( $type, 'FLOAT' ) !== false ) {
			return '%f';
		}
		if ( stristr( $type, 'DOUBLE' ) !== false ) {
			return '%f';
		}
		if ( stristr( $type, 'REAL' ) !== false ) {
			return '%f';
		}

		return $this->apply_filters( 'type2format', '%s', $type );
	}

	/**
	 * load
	 */
	private function load_table_defines() {
		$this->table_defines = $this->app->config->load( 'db' );
		empty( $this->table_defines ) and $this->table_defines = array();

		foreach ( $this->table_defines as $table => $define ) {

			list( $id, $columns ) = $this->setup_table_columns( $table, $define );
			if ( empty( $id ) ) {
				continue;
			}
			$this->table_defines[ $table ]['id']      = $id;
			$this->table_defines[ $table ]['columns'] = $columns;
		}
	}

	/**
	 * @param string $table
	 * @param array $define
	 *
	 * @return array
	 */
	protected function setup_table_columns( $table, $define ) {
		if ( empty( $define['columns'] ) ) {
			return array( false, false );
		}

		$id = $table . '_id';
		if ( ! empty( $define['id'] ) ) {
			$id = $define['id'];
		}

		$columns       = array();
		$columns['id'] = array(
			'name'     => $id,
			'type'     => 'bigint(20)',
			'unsigned' => true,
			'null'     => false,
			'format'   => '%d',
		);

		$check = true;
		foreach ( $define['columns'] as $key => $column ) {
			if ( ! is_array( $column ) ) {
				$check = false;
				break;
			}
			$type = Utility::array_get( $column, 'type' );
			if ( empty( $type ) ) {
				$check = false;
				break;
			}

			$column['name']   = Utility::array_get( $column, 'name', $key );
			$column['format'] = Utility::array_get( $column, 'format', $this->type2format( $type ) );
			$columns[ $key ]  = $column;
		}
		if ( ! $check ) {
			return array( false, false );
		}

		$columns['created_at'] = array(
			'name'   => 'created_at',
			'type'   => 'datetime',
			'null'   => false,
			'format' => '%s'
		);
		$columns['created_by'] = array(
			'name'   => 'created_by',
			'type'   => 'varchar(32)',
			'null'   => false,
			'format' => '%s'
		);
		$columns['updated_at'] = array(
			'name'   => 'updated_at',
			'type'   => 'datetime',
			'null'   => false,
			'format' => '%s'
		);
		$columns['updated_by'] = array(
			'name'   => 'updated_by',
			'type'   => 'varchar(32)',
			'null'   => false,
			'format' => '%s'
		);

		if ( $this->is_logical( $define ) ) {
			$columns['deleted_at'] = array(
				'name'   => 'deleted_at',
				'type'   => 'datetime',
				'format' => '%s'
			);
			$columns['deleted_by'] = array(
				'name'   => 'deleted_by',
				'type'   => 'varchar(32)',
				'format' => '%s'
			);
		}

		return $this->apply_filters( 'setup_table_columns', array( $id, $columns ), $table, $define, $id, $columns );
	}

	/**
	 * @return string
	 */
	private function get_table_prefix() {
		global $table_prefix;

		return $table_prefix . $this->get_slug( 'table_prefix', '_' );
	}

	/**
	 * @param $table
	 *
	 * @return string
	 */
	public function get_table( $table ) {
		return $this->get_table_prefix() . $table;
	}

	/**
	 * @param $table
	 *
	 * @return array
	 */
	public function get_columns( $table ) {
		if ( ! isset( $this->table_defines[ $table ]['columns'] ) ) {
			return array();
		}

		return $this->table_defines[ $table ]['columns'];
	}

	/**
	 * db update
	 */
	private function db_update() {
		if ( ! $this->need_to_update() ) {
			return;
		}
		$this->update_db_version();

		if ( empty( $this->table_defines ) ) {
			return;
		}

		set_time_limit( 60 * 5 );

		foreach ( $this->table_defines as $table => $define ) {
			$results = $this->table_update( $table, $define );
			if ( $results ) {
				$message = implode( '<br>', array_filter( $results, function ( $d ) {
					return ! empty( $d );
				} ) );
				if ( $message ) {
					$this->app->add_message( $message, 'db' );
				}
			}
		}

		$this->do_action( 'db_updated' );
	}

	/**
	 * @param string $table
	 * @param array $define
	 *
	 * @return array
	 */
	protected function table_update( $table, $define ) {
		require_once ABSPATH . "wp-admin" . DS . "includes" . DS . "upgrade.php";
		$char = defined( "DB_CHARSET" ) ? DB_CHARSET : "utf8";
		if ( empty( $define['id'] ) ) {
			$define['id'] = $table . '_id';
		}

		$table = $this->get_table( $table );
		$sql   = "CREATE TABLE {$table} (\n";
		foreach ( $define['columns'] as $key => $column ) {
			$name     = Utility::array_get( $column, 'name' );
			$type     = Utility::array_get( $column, 'type' );
			$unsigned = Utility::array_get( $column, 'unsigned', false );
			$null     = Utility::array_get( $column, 'null', true );
			$default  = Utility::array_get( $column, 'default', null );
			$comment  = Utility::array_get( $column, 'comment', '' );

			$sql .= $name . ' ' . strtolower( $type );
			if ( $unsigned ) {
				$sql .= ' unsigned';
			}
			if ( $null ) {
				$sql .= ' NULL';
			} else {
				$sql .= ' NOT NULL';
			}
			if ( $key === 'id' ) {
				$sql .= ' AUTO_INCREMENT';
			} elseif ( isset( $default ) ) {
				$default = str_replace( '\'', '\\\'', $default );
				$sql     .= " DEFAULT '{$default}'";
			}
			if ( ! empty( $comment ) ) {
				$comment = str_replace( '\'', '\\\'', $comment );
				$sql     .= " COMMENT '{$comment}'";
			}
			$sql .= ",\n";
		}

		$index   = array();
		$index[] = "PRIMARY KEY  ({$define['columns']['id']['name']})";
		if ( ! empty( $define['index']['key'] ) ) {
			foreach ( $define['index']['key'] as $name => $columns ) {
				if ( ! array( $columns ) ) {
					$columns = array( $columns );
				}
				$columns = implode( ', ', $columns );
				$index[] = "INDEX {$name} ({$columns})";
			}
		}
		if ( ! empty( $define['index']['unique'] ) ) {
			foreach ( $define['index']['unique'] as $name => $columns ) {
				if ( ! array( $columns ) ) {
					$columns = array( $columns );
				}
				$columns = implode( ', ', $columns );
				$index[] = "UNIQUE KEY {$name} ({$columns})";
			}
		}
		$sql .= implode( ",\n", $index );
		$sql .= "\n) ENGINE = InnoDB DEFAULT CHARSET = {$char};";

		return dbDelta( $sql );
	}

	/**
	 * @return bool
	 */
	private function need_to_update() {
		return version_compare( $this->get_version(), $this->get_db_version() ) > 0;
	}

	/**
	 * @return string
	 */
	private function get_version() {
		return $this->app->get_config( 'config', 'db_version', '0.0.0.0.0' );
	}

	/**
	 * @return string
	 */
	private function get_db_version() {
		return $this->app->get_option( 'db_version', '0.0.0.0.0' );
	}

	/**
	 * @return bool
	 */
	private function update_db_version() {
		return $this->app->option->set( 'db_version', $this->get_version() );
	}


	/**
	 * @param array $define
	 *
	 * @return bool
	 */
	private function is_logical( $define ) {
		return $this->apply_filters( 'is_logical', 'physical' !== Utility::array_get( $define, 'delete', 'logical' ), $define );
	}

	/**
	 * @param $data
	 * @param $columns
	 *
	 * @return array
	 */
	private function filter( $data, $columns ) {
		$_format = array();
		$_data   = array();
		foreach ( $data as $k => $v ) {
			if ( isset( $columns[ $k ] ) ) {
				$_format[]                       = $columns[ $k ]['format'];
				$_data[ $columns[ $k ]['name'] ] = $v;
			}
		}

		return array( $_data, $_format );
	}

	/**
	 * @param $data
	 * @param $create
	 * @param $update
	 * @param $delete
	 */
	private function set_update_params( &$data, $create, $update, $delete ) {
		$now  = $this->apply_filters( 'set_update_params_date', date( 'Y-m-d H:i:s' ) );
		$user = $this->apply_filters( 'set_update_params_user', $this->app->user->user_name );

		if ( $create ) {
			$data['created_at'] = $now;
			$data['created_by'] = $user;
		}
		if ( $update ) {
			$data['updated_at'] = $now;
			$data['updated_by'] = $user;
		}
		if ( $delete ) {
			$data['deleted_at'] = $now;
			$data['deleted_by'] = $user;
		}
	}

	/**
	 * @param array|string $fields
	 * @param array $columns
	 *
	 * @return array
	 */
	private function build_fields( $fields, $columns ) {
		if ( is_string( $fields ) ) {
			$fields = array( $fields );
		}
		foreach ( $fields as $k => $option ) {
			$key = $k;
			if ( is_int( $key ) ) {
				$key    = $option;
				$option = null;
			}
			if ( $key === '*' ) {
				$name = '*';
			} elseif ( isset( $columns[ $key ] ) ) {
				$name = $columns[ $key ]['name'];
			} else {
				$name = $key;
			}
			if ( is_array( $option ) ) {
				$group_func = $option[0];
				if ( strtoupper( $group_func ) == 'AS' ) {
					$fields[ $k ] = $name;
					if ( count( $option ) >= 2 ) {
						$fields[ $k ] .= ' AS ' . $option[1];
					}
				} else {
					$fields[ $k ] = "$group_func( $name )";
					if ( count( $option ) >= 2 ) {
						$fields[ $k ] .= ' AS ' . $option[1];
					}
				}
			} else {
				$fields[ $k ] = $name;
			}
		}
		if ( empty( $fields ) ) {
			$fields = array();
			foreach ( $columns as $key => $column ) {
				$name     = Utility::array_get( $column, 'name' );
				$fields[] = $name === $key ? $name : $name . ' AS ' . $key;
			}
		}
		empty( $fields ) and $fields = array( '*' );
		$fields = implode( ', ', $fields );

		return $fields;
	}

	/**
	 * @param array $where
	 * @param array $columns
	 *
	 * @return array
	 */
	private function build_conditions( $where, $columns ) {
		list ( $_where, $_where_format ) = $this->filter( $where, $columns );
		$conditions = $values = array();
		$index      = 0;
		foreach ( $_where as $field => $value ) {
			$format = $_where_format[ $index ++ ];
			if ( is_null( $value ) ) {
				$conditions[] = "`$field` IS NULL";
				continue;
			}

			$op = '=';
			if ( is_array( $value ) ) {
				if ( count( $value ) > 1 ) {
					$op  = $value[0];
					$val = $value[1];
					if ( is_array( $val ) ) {
						foreach ( $val as $v ) {
							$values[] = $v;
						}
						$conditions[] = "`$field` $op (" . str_repeat( $format . ',', count( $val ) - 1 ) . $format . ')';
						continue;
					}
				} else {
					continue;
				}
			} else {
				$val = $value;
			}

			$conditions[] = "`$field` $op " . $format;
			$values[]     = $val;
		}
		$conditions = implode( ' AND ', $conditions );

		return array( $conditions, $values );
	}

	/**
	 * @param null|array $group_by
	 * @param array $columns
	 *
	 * @return string
	 */
	private function build_group_by( $group_by, $columns ) {
		$sql = '';
		if ( ! empty( $group_by ) ) {
			$items = array();
			foreach ( $group_by as $k ) {
				if ( ! isset( $columns[ $k ] ) ) {
					continue;
				}
				$k       = $columns[ $k ]['name'];
				$items[] = $k;
			}
			if ( ! empty( $items ) ) {
				$sql .= ' GROUP BY ' . implode( ', ', $items );
			}
		}

		return $sql;
	}

	/**
	 * @param null|array $order_by
	 * @param array $columns
	 *
	 * @return string
	 */
	private function build_order_by( $order_by, $columns ) {
		$sql = '';
		if ( ! empty( $order_by ) ) {
			$items = array();
			foreach ( $order_by as $k => $order ) {
				if ( is_int( $k ) ) {
					$k     = $order;
					$order = 'ASC';
				} else {
					$order = trim( strtoupper( $order ) );
				}
				if ( ! isset( $columns[ $k ] ) || ( $order !== 'DESC' && $order !== 'ASC' ) ) {
					continue;
				}
				$k       = $columns[ $k ]['name'];
				$items[] = "$k $order";
			}
			if ( ! empty( $items ) ) {
				$sql .= ' ORDER BY ' . implode( ', ', $items );
			}
		}

		return $sql;
	}

	/**
	 * @param null|int $limit
	 * @param null|int $offset
	 *
	 * @return string
	 */
	private function build_limit( $limit, $offset ) {
		$sql = '';
		if ( isset( $limit ) && $limit > 0 ) {
			if ( isset( $offset ) && $offset > 0 ) {
				$sql .= " LIMIT {$offset}, {$limit}";
			} else {
				$sql .= " LIMIT {$limit}";
			}
		}

		return $sql;
	}

	/**
	 * @param string $table
	 * @param array $where
	 * @param array|string $fields
	 * @param null|int $limit
	 * @param null|int $offset
	 * @param null|array $order_by
	 * @param null|array $group_by
	 * @param bool $for_update
	 *
	 * @return string|false
	 */
	public function get_select_sql( $table, $where = array(), $fields = array( '*' ), $limit = null, $offset = null, $order_by = null, $group_by = null, $for_update = false ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		$columns = $this->table_defines[ $table ]['columns'];

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			$where['deleted_at'] = null;
		}

		list( $conditions, $values ) = $this->build_conditions( $where, $columns );
		$table  = $this->get_table( $table );
		$fields = $this->build_fields( $fields, $columns );
		$sql    = "SELECT {$fields} FROM `$table`";
		if ( ! empty( $conditions ) ) {
			$sql .= " WHERE $conditions";
		}
		$sql .= $this->build_group_by( $group_by, $columns );
		$sql .= $this->build_order_by( $order_by, $columns );
		$sql .= $this->build_limit( $limit, $offset );
		if ( $for_update ) {
			$sql .= ' FOR UPDATE';
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		return empty( $values ) ? $sql : $wpdb->prepare( $sql, $values );
	}

	/**
	 * @param string $table
	 * @param array $where
	 * @param array|string $fields
	 * @param null|int $limit
	 * @param null|int $offset
	 * @param null|array $order_by
	 * @param null|array $group_by
	 * @param bool $for_update
	 *
	 * @return array|bool|null
	 */
	public function select( $table, $where = array(), $fields = array( '*' ), $limit = null, $offset = null, $order_by = null, $group_by = null, $for_update = false ) {
		$sql = $this->get_select_sql( $table, $where, $fields, $limit, $offset, $order_by, $group_by, $for_update );
		if ( false === $sql ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		if ( isset( $limit ) && $limit == 1 ) {
			return $wpdb->get_row( $sql, ARRAY_A );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	/**
	 * @param $table
	 * @param string $field
	 * @param array $where
	 * @param null $limit
	 * @param int $offset
	 * @param array $order_by
	 * @param array $group_by
	 * @param bool $for_update
	 *
	 * @return int
	 */
	public function select_count( $table, $field = '*', $where = array(), $limit = null, $offset = 0, $order_by = array(), $group_by = array(), $for_update = false ) {
		empty( $field ) and $field = '*';
		$result = $this->select( $table, $where, array(
			$field => array(
				'COUNT',
				'num'
			)
		), $limit, $offset, $order_by, $group_by, $for_update );
		if ( empty( $result ) ) {
			return 0;
		}
		if ( $limit == 1 ) {
			return isset( $result['num'] ) ? $result['num'] - 0 : 0;
		}

		return isset( $result[0]['num'] ) ? $result[0]['num'] - 0 : 0;
	}

	/**
	 * @param string $table
	 * @param array $data
	 * @param string $method
	 *
	 * @return bool|false|int
	 */
	private function _insert_replace( $table, $data, $method ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}
		if ( $method !== 'insert' && $method !== 'replace' ) {
			return false;
		}
		if ( $method === 'replace' && ! isset( $data['id'] ) ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$columns = $this->table_defines[ $table ]['columns'];

		$this->set_update_params( $data, $method === 'insert', true, false );
		list ( $_data, $_format ) = $this->filter( $data, $columns );

		return $wpdb->$method( $this->get_table( $table ), $_data, $_format );
	}

	/**
	 * @return int
	 */
	public function get_insert_id() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->insert_id;
	}

	/**
	 * @param string $table
	 * @param array $data
	 *
	 * @return bool|false|int
	 */
	public function insert( $table, $data ) {
		return $this->_insert_replace( $table, $data, 'insert' );
	}

	/**
	 * @param string $table
	 * @param array $data
	 *
	 * @return bool|false|int
	 */
	public function replace( $table, $data ) {
		return $this->_insert_replace( $table, $data, 'replace' );
	}

	/**
	 * @param string $table
	 * @param array $data
	 * @param array $where
	 *
	 * @return bool|false|int
	 */
	public function update( $table, $data, $where ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$columns = $this->table_defines[ $table ]['columns'];

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			$where['deleted_at'] = null;
		}

		$this->set_update_params( $data, false, true, false );
		list ( $_data, $_format ) = $this->filter( $data, $columns );
		list ( $_where, $_where_format ) = $this->filter( $where, $columns );

		return $wpdb->update( $this->get_table( $table ), $_data, $_where, $_format, $_where_format );
	}

	/**
	 * @param $table
	 * @param $data
	 * @param $where
	 *
	 * @return int
	 */
	public function insert_or_update( $table, $data, $where ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			$where['deleted_at'] = null;
		}

		$row = $this->select( $table, $where, 'id', 1 );
		if ( empty( $row ) ) {
			$this->insert( $table, $data );

			return $this->get_insert_id();
		}
		$where = array( 'id' => $row['id'] );
		$this->update( $table, $data, $where );

		return $row['id'];
	}

	/**
	 * @param string $table
	 * @param array $where
	 *
	 * @return bool|false|int
	 */
	public function delete( $table, $where ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			$data = array();
			$this->set_update_params( $data, false, false, true );

			return $this->update( $table, $data, $where );
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$columns = $this->table_defines[ $table ]['columns'];

		list ( $_where, $_where_format ) = $this->filter( $where, $columns );

		return $wpdb->delete( $this->get_table( $table ), $_where, $_where_format );
	}

	/**
	 * @param $table
	 *
	 * @return bool|false|int
	 */
	public function truncate( $table ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			return $this->delete( $table, array() );
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$sql = 'TRUNCATE TABLE `' . $this->get_table( $table ) . '`';

		return $wpdb->query( $sql );
	}

	/**
	 * @return false|int
	 */
	public function begin() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->query( 'START TRANSACTION' );
	}

	/**
	 * @param string $table
	 * @param bool $write
	 *
	 * @return bool|string
	 */
	public function lock( $table, $write ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->query( 'LOCK TABLES `' . $this->get_table( $table ) ) . '` ' . ( $write ? 'WRITE' : 'READ' );
	}

	/**
	 * @return false|int
	 */
	public function unlock() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->query( 'UNLOCK TABLES' );
	}

	/**
	 * @return false|int
	 */
	public function commit() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->query( 'COMMIT' );
	}

	/**
	 * @return false|int
	 */
	public function rollback() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		return $wpdb->query( 'ROLLBACK' );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		/** @var \wpdb $wpdb */
		global $wpdb;

		foreach ( $this->table_defines as $table => $define ) {
			$sql = 'DROP TABLE IF EXISTS `' . $this->get_table( $table ) . '`';
			$wpdb->query( $sql );
		}
	}

}
