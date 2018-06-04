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
class Db implements \Technote\Interfaces\Singleton {

	use \Technote\Traits\Singleton;

	/** @var array */
	private $table_defines = null;

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

		return '%s';
	}

	/**
	 * load
	 */
	private function load_table_defines() {
		$this->table_defines = $this->app->config->load( 'db' );
		empty( $this->table_defines ) and $this->table_defines = array();


		foreach ( $this->table_defines as $table => $define ) {
			if ( empty( $define['columns'] ) ) {
				unset( $this->table_defines[ $table ] );
				continue;
			}

			if ( empty( $define['id'] ) ) {
				$this->table_defines[ $table ]['id'] = $table . '_id';
			}

			$columns       = array();
			$columns['id'] = array(
				'name'     => $this->table_defines[ $table ]['id'],
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
				unset( $this->table_defines[ $table ] );
				continue;
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

			$this->table_defines[ $table ]['columns'] = $columns;
		}
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
		require_once ABSPATH . "wp-admin" . DS . "includes" . DS . "upgrade.php";

		$char = defined( "DB_CHARSET" ) ? DB_CHARSET : "utf8";
		foreach ( $this->table_defines as $table => $define ) {
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

			$results = dbDelta( $sql );
			if ( $results ) {
				$tmp = '';
				foreach ( $results as $result ) {
					if ( ! $result ) {
						continue;
					}
					if ( $tmp ) {
						$tmp .= "<br>" . $result;
					} else {
						$tmp = $result;
					}
				}
				if ( $tmp ) {
					$this->app->add_message( $tmp );
				}
			}
		}
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
		return 'physical' !== Utility::array_get( $define, 'delete', 'logical' );
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
		$now  = date( 'Y-m-d H:i:s' );
		$user = $this->app->user->user_name;

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
	 * @param string $table
	 * @param array $where
	 * @param null|int $limit
	 * @param int $offset
	 *
	 * @return array|bool|null
	 */
	public function select( $table, $where = array(), $limit = null, $offset = 0 ) {
		if ( ! isset( $this->table_defines[ $table ] ) ) {
			return false;
		}

		/** @var \wpdb $wpdb */
		global $wpdb;
		$columns = $this->table_defines[ $table ]['columns'];

		if ( $this->is_logical( $this->table_defines[ $table ] ) ) {
			$where['deleted_at'] = null;
		}

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
		$table      = $this->get_table( $table );
		$sql        = "SELECT * FROM `$table`";
		if ( ! empty( $conditions ) ) {
			$sql .= "WHERE $conditions";
		}
		if ( isset( $limit ) && $limit > 1 ) {
			if ( $offset > 0 ) {
				$sql .= " LIMIT {$offset}, {$limit}";
			} else {
				$sql .= " LIMIT {$limit}";
			}
		}

		if ( isset( $limit ) && $limit == 1 ) {
			return $wpdb->get_row( empty( $values ) ? $sql : $wpdb->prepare( $sql, $values ), ARRAY_A );
		}

		return $wpdb->get_results( empty( $values ) ? $sql : $wpdb->prepare( $sql, $values ), ARRAY_A );
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


}
