<?php
/**
 * Technote Traits Helper Custom Post
 *
 * @version 2.8.5
 * @author technote-space
 * @since 2.8.0
 * @since 2.8.3
 * @since 2.8.5 Fixed: hide unnecessary columns
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits\Helper;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Custom_Post
 * @package Technote\Traits\Helper
 * @property \Technote $app
 */
trait Custom_Post {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Presenter;

	/** @var string $_slug */
	private $_slug;

	/** @var array $_related_data */
	private $_related_data = [];

	/**
	 * @return mixed
	 */
	private function apply_custom_post_filters() {
		$args    = func_get_args();
		$key     = $args[0];
		$args[0] = $this->get_post_type_slug() . '-' . $key;

		return $this->apply_filters( ...$args );
	}

	/**
	 * @return string
	 */
	public function get_post_type_slug() {
		! isset( $this->_slug ) and $this->_slug = $this->get_file_slug();

		return $this->_slug;
	}

	/**
	 * @return string
	 */
	public function get_related_table_name() {
		return $this->apply_custom_post_filters( 'table_name', $this->get_post_type_slug() );
	}

	/**
	 * @return string
	 */
	public function get_post_type() {
		return $this->get_slug( 'post_type-' . $this->get_post_type_slug(), '-' . $this->get_post_type_slug() );
	}

	/**
	 * @return array
	 */
	protected abstract function get_capabilities();

	/**
	 * @return string|false
	 */
	protected abstract function get_post_type_parent();

	/**
	 * @param null|array $capabilities
	 *
	 * @return array
	 */
	public function get_post_type_args( $capabilities = null ) {
		if ( ! isset( $capabilities ) ) {
			$capabilities = $this->get_capabilities();
		}

		return $this->apply_custom_post_filters( 'args', [
			'labels'              => $this->get_post_type_labels(),
			'description'         => '',
			'public'              => false,
			'show_ui'             => true,
			'has_archive'         => false,
			'show_in_menu'        => $this->get_post_type_parent(),
			'exclude_from_search' => true,
			'capability_type'     => $this->get_post_type_capability_type(),
			'capabilities'        => $capabilities,
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => [ 'slug' => $this->get_post_type_slug(), 'with_front' => false ],
			'query_var'           => true,
			'menu_icon'           => $this->get_post_type_menu_icon(),
			'supports'            => $this->get_post_type_supports(),
			'menu_position'       => $this->get_post_type_position(),
		] );
	}

	/**
	 * @return array
	 */
	public function get_post_type_labels() {
		return $this->apply_custom_post_filters( 'labels', [
			'name'               => $this->app->translate( $this->get_post_type_plural_name() ),
			'singular_name'      => $this->app->translate( $this->get_post_type_single_name() ),
			'menu_name'          => $this->app->translate( $this->get_post_type_plural_name() ),
			'all_items'          => $this->app->translate( 'All ' . $this->get_post_type_plural_name() ),
			'add_new'            => $this->app->translate( 'Add ' . $this->get_post_type_single_name() ),
			'edit_item'          => $this->app->translate( 'Edit ' . $this->get_post_type_single_name() ),
			'search_items'       => $this->app->translate( 'Search ' . $this->get_post_type_plural_name() ),
			'not_found'          => $this->app->translate( 'No ' . $this->get_post_type_plural_name() . ' found.' ),
			'not_found_in_trash' => $this->app->translate( 'No ' . $this->get_post_type_plural_name() . ' found in Trash.' ),
		] );
	}

	/**
	 * @return string
	 */
	public function get_post_type_single_name() {
		return $this->apply_custom_post_filters( 'single_name', ucwords( str_replace( '_', ' ', $this->get_post_type_slug() ) ) );
	}

	/**
	 * @return string
	 */
	public function get_post_type_plural_name() {
		return $this->apply_custom_post_filters( 'plural_name', $this->get_post_type_single_name() . 's' );
	}

	/**
	 * @return string|array
	 */
	public function get_post_type_capability_type() {
		return $this->apply_custom_post_filters( 'capability_type', $this->get_post_type_slug() );
	}

	/**
	 * @return array
	 */
	public function get_post_type_supports() {
		return $this->apply_custom_post_filters( 'supports', [
			'title',
		] );
	}

	/**
	 * @return string
	 */
	public function get_post_type_menu_icon() {
		return $this->apply_custom_post_filters( 'menu_icon', 'dashicons-admin-plugins' );
	}

	/**
	 * @return int|null
	 */
	public function get_post_type_position() {
		return $this->apply_custom_post_filters( 'position', 5 );
	}

	/**
	 * @param string $search
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function posts_search(
		/** @noinspection PhpUnusedParameterInspection */
		$search, $wp_query
	) {
		if ( ! $wp_query->is_search() || ! $wp_query->is_main_query() || ! is_admin() || empty( $wp_query->query_vars['search_terms'] ) ) {
			return $search;
		}

		$fields = $this->get_search_fields();
		if ( empty( $fields ) ) {
			return $search;
		}

		global $wpdb;

		$exclusion_prefix = apply_filters( 'wp_query_search_exclusion_prefix', '-' );
		$search           = '';
		$q                = $wp_query->query_vars;
		$n                = ! empty( $q['exact'] ) ? '' : '%';
		$table            = $this->app->db->get_table( $this->get_related_table_name() );

		foreach ( $q['search_terms'] as $term ) {
			$exclude = $exclusion_prefix && ( $exclusion_prefix === substr( $term, 0, 1 ) );
			if ( $exclude ) {
				$like_op  = 'NOT LIKE';
				$andor_op = 'AND';
				$term     = substr( $term, 1 );
			} else {
				$like_op  = 'LIKE';
				$andor_op = 'OR';
			}

			$conditions = [];
			$fields     = array_map( function ( $field ) use ( $table ) {
				return "{$table}.{$field}";
			}, $fields );
			$fields[]   = "{$wpdb->posts}.post_title";
			$fields[]   = "{$wpdb->posts}.post_excerpt";
			$fields[]   = "{$wpdb->posts}.post_content";
			$like       = $n . $wpdb->esc_like( $term ) . $n;
			foreach ( $fields as $field ) {
				$conditions[] = $wpdb->prepare( "({$field} $like_op %s)", $like );
			}
			$conditions = implode( " {$andor_op} ", $conditions );
			$search     .= " AND ({$conditions})";
		}

		return $search;
	}

	/**
	 * @return array
	 */
	protected function get_search_fields() {
		return [];
	}

	/**
	 * @param string $join
	 * @param \WP_Query $wp_query
	 *
	 * @return string
	 */
	public function posts_join(
		/** @noinspection PhpUnusedParameterInspection */
		$join, $wp_query
	) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$table = $this->app->db->get_table( $this->get_related_table_name() );
		$join  .= " INNER JOIN {$table} ON {$table}.post_id = {$wpdb->posts}.ID ";

		return $join;
	}

	/**
	 * @param array $columns
	 * @param bool $sortable
	 *
	 * @return array
	 */
	public function manage_posts_columns( $columns, $sortable = false ) {
		if ( ! $sortable ) {
			$title = $this->get_post_column_title();
			! isset( $title ) and isset( $columns['title'] ) and $title = $columns['title'];
			$date = isset( $columns['date'] ) ? $columns['date'] : null;
			unset( $columns['date'] );
		}

		$columns = $this->pre_filter_posts_columns( $columns, $sortable );
		$columns = $this->post_filter_posts_columns( $columns, $sortable );

		if ( ! $sortable ) {
			isset( $title ) and $columns['title'] = $title;
			isset( $date ) and $columns['date'] = $date;
		}

		return $columns;
	}

	/**
	 * @return null|string
	 */
	protected function get_post_column_title() {
		return null;
	}

	/**
	 * @since 2.8.5 Fixed: hide unnecessary columns
	 *
	 * @param array $columns
	 * @param bool $sortable
	 *
	 * @return array
	 */
	protected function pre_filter_posts_columns( $columns, $sortable = false ) {
		// for cocoon
		unset( $columns['thumbnail'] );
		unset( $columns['memo'] );

		$_columns = $this->app->db->get_columns( $this->get_related_table_name() );
		foreach ( $this->get_manage_posts_columns() as $k => $v ) {
			if ( $sortable && ( ! is_array( $v ) || empty( $v['sortable'] ) ) ) {
				continue;
			}
			$key = $this->get_post_type() . '-' . $k;
			if ( $sortable ) {
				$columns[ $key ] = [ $key, ! empty( $v['desc'] ) ];
				continue;
			}

			$name = isset( $_columns[ $k ] ) ? $this->table_column_to_name( $k, $_columns ) : '';
			if ( is_array( $v ) ) {
				if ( isset( $v['name'] ) ) {
					$name = $v['name'];
				}
				$columns[ $key ] = $name;
			} elseif ( ! $sortable ) {
				$columns[ $key ] = empty( $name ) ? $k : $name;
			}
		}

		return $columns;
	}

	/**
	 * @param array $columns
	 * @param bool $sortable
	 *
	 * @return array
	 */
	protected function post_filter_posts_columns(
		/** @noinspection PhpUnusedParameterInspection */
		$columns, $sortable = false
	) {
		return $columns;
	}

	/**
	 * @return array
	 */
	protected function get_manage_posts_columns() {
		return [];
	}

	/**
	 * @param string $column_name
	 * @param \WP_Post $post
	 */
	public function manage_posts_custom_column(
		/** @noinspection PhpUnusedParameterInspection */
		$column_name, $post
	) {
		$data = $this->get_related_data( $post->ID );
		if ( empty( $data ) ) {
			return;
		}
		$this->manage_posts_column( $column_name, $post, $data );
	}

	/**
	 * @param string $column_name
	 * @param \WP_Post $post
	 * @param array $data
	 */
	protected function manage_posts_column(
		/** @noinspection PhpUnusedParameterInspection */
		$column_name, $post, $data
	) {
		foreach ( $this->get_manage_posts_columns() as $k => $v ) {
			$key = $this->get_post_type() . '-' . $k;
			if ( $column_name === $key ) {
				$value  = isset( $data[ $k ] ) ? $data[ $k ] : '';
				$escape = true;

				if ( is_array( $v ) ) {
					if ( isset( $v['callback'] ) && is_callable( $v['callback'] ) ) {
						$value = ( $v['callback'] )( $value, $data, $post );
					} elseif ( isset( $v['value'] ) ) {
						$value .= $v['value'];
					}
					if ( isset( $v['unescape'] ) ) {
						$escape = false;
					}
				} elseif ( is_callable( $v ) ) {
					$value = $v( $value, $data, $post );
				} else {
					$value .= $v;
				}
				$this->h( $value, false, true, $escape );

				break;
			}
		}
	}

	/**
	 * @param array $data
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	protected function set_post_data( $data, $post ) {
		$data['post_title'] = $post->post_title;
		$data['post']       = $post;
		if ( $this->app->user_can( 'read_' . $this->get_post_type_slug() ) ) {
			$data['edit_link'] = get_edit_post_link( $post->ID );
		}

		return $data;
	}

	/**
	 * @param int $post_id
	 *
	 * @return array|false
	 */
	public function get_related_data( $post_id ) {
		if ( ! isset( $this->_related_data[ $post_id ] ) ) {
			$table = $this->get_related_table_name();
			$data  = $this->app->db->select_row( $table, [
				'post_id' => $post_id,
			] );
			if ( empty( $data ) ) {
				$data = false;
			} else {
				$post = get_post( $post_id );
				if ( empty( $post ) || $post->post_type != $this->get_post_type() ) {
					$data = false;
				} else {
					$data = $this->filter_item( $this->set_post_data( $data, $post ) );
				}
			}

			$this->_related_data[ $post_id ] = $data;
		}

		return $this->_related_data[ $post_id ];
	}

	/**
	 * @param int $id
	 * @param bool $is_valid
	 *
	 * @return array|false
	 */
	public function get_data( $id, $is_valid = true ) {
		$table = $this->get_related_table_name();
		$data  = $this->app->db->select_row( $table, [
			'id' => $id,
		] );
		if ( empty( $data ) ) {
			return false;
		}
		$post_id = $data['post_id'];
		$post    = get_post( $post_id );
		if ( empty( $post ) || $post->post_type != $this->get_post_type() || ( $is_valid && $post->post_status !== 'publish' ) ) {
			return false;
		}

		return $this->filter_item( $this->set_post_data( $data, $post ) );
	}

	/**
	 * @param bool $is_valid
	 * @param int|null $per_page
	 * @param int $page
	 * @param array $where
	 * @param null|array $orderby
	 *
	 * @return array
	 */
	public function list_data( $is_valid = true, $per_page = null, $page = 1, $where = [], $orderby = null ) {
		/** @var \wpdb $wpdb */
		global $wpdb;
		$table      = $this->get_related_table_name();
		$limit      = $per_page;
		$page       = max( 1, $page );
		$table      = [
			[ $table, 't' ],
			[
				[ $wpdb->posts, 'p' ],
				'INNER JOIN',
				[
					't.post_id',
					'=',
					'p.ID',
				],
			],
		];
		$total      = $this->app->db->select_count( $table, null, $where );
		$total_page = isset( $per_page ) ? ceil( $total / $per_page ) : 1;
		$page       = min( $total_page, $page );
		$offset     = isset( $per_page ) && isset( $page ) ? $per_page * ( $page - 1 ) : null;
		if ( $is_valid ) {
			$where['p.post_status'] = 'publish';
		}

		$list = $this->app->db->select( $table, $where, null, $limit, $offset, $orderby );
		if ( empty( $list ) ) {
			return [
				'total'      => 0,
				'total_page' => 0,
				'page'       => $page,
				'data'       => [],
			];
		}

		$post_ids = $this->app->utility->array_pluck( $list, 'post_id' );
		$posts    = get_posts( [
			'include'   => $post_ids,
			'post_type' => $this->get_post_type(),
		] );
		$posts    = $this->app->utility->array_combine( $posts, 'ID' );
		$editable = $this->app->user_can( 'read_' . $this->get_post_type_slug() );

		return [
			'total'      => $total,
			'total_page' => $total_page,
			'page'       => $page,
			'data'       => array_map( function ( $d ) use ( $posts, $editable ) {
				return $this->filter_item( $this->set_post_data( $d, $posts[ $d['post_id'] ] ) );
			}, $list ),
		];
	}

	/**
	 * @param array $d
	 *
	 * @return array
	 */
	protected function filter_item( $d ) {
		return $d;
	}

	/**
	 * @param array $params
	 * @param array $where
	 * @param \WP_Post $post
	 * @param bool $update
	 *
	 * @return int
	 */
	public function update_data( $params, $where, $post, $update ) {
		$table  = $this->get_related_table_name();
		$params = array_merge( $params, $this->get_update_data_params( $post, $update ) );
		list( $params, $where ) = $this->update_misc( $params, $where, $post, $update );

		return $this->app->db->insert_or_update( $table, $params, $where );
	}

	/**
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param array $old
	 * @param array $new
	 */
	public function data_updated( $post_id, $post, $old, $new ) {

	}

	/**
	 * @param int $post_id
	 * @param \WP_Post $post
	 * @param array $data
	 */
	public function data_inserted( $post_id, $post, $data ) {

	}

	/**
	 * @param array $params
	 * @param array $where
	 * @param \WP_Post $post
	 * @param bool $update
	 *
	 * @return array
	 */
	protected function update_misc(
		/** @noinspection PhpUnusedParameterInspection */
		$params, $where, $post, $update
	) {
		return [ $params, $where ];
	}

	/**
	 * @param \WP_Post $post
	 * @param bool $update
	 *
	 * @return array
	 */
	public function get_update_data_params(
		/** @noinspection PhpUnusedParameterInspection */
		$post, $update
	) {
		$params = [];
		foreach ( $this->get_data_field_settings() as $k => $v ) {
			$params[ $k ] = $this->get_post_field( $k, $update ? null : $v['default'] );
			if ( ! isset( $params[ $k ] ) && ! empty( $v['unset_if_null'] ) ) {
				unset( $params[ $k ] );
				continue;
			}
			if ( isset( $v['type'] ) && isset( $params[ $k ] ) ) {
				$params[ $k ] = $this->sanitize_input( $params[ $k ], $v['type'] );
			}
		}

		return $params;
	}

	/**
	 * @param array $where
	 *
	 * @return bool|false|int
	 */
	public function delete_data( $where ) {
		$table = $this->get_related_table_name();
		$this->delete_misc( $where['post_id'] );

		return $this->app->db->delete( $table, $where );
	}

	/**
	 * @param int $post_id
	 */
	protected function delete_misc( $post_id ) {

	}

	/**
	 * @return string
	 */
	public function get_post_field_name_prefix() {
		return $this->apply_custom_post_filters( 'post_field_name_prefix', $this->get_slug( 'post_field_name_prefix' ) );
	}

	/**
	 * @param string $key
	 *
	 * @return string
	 */
	public function get_post_field_name( $key ) {
		return $this->get_post_field_name_prefix() . $key;
	}

	/**
	 * @param string $key
	 * @param array $post_array
	 *
	 * @return mixed
	 */
	protected function get_validation_var( $key, $post_array ) {
		return $this->app->utility->array_get( $post_array, $this->get_post_field_name( $key ) );
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * @param array|null $post_array
	 *
	 * @return mixed
	 */
	protected function get_post_field( $key, $default = null, $post_array = null ) {
		if ( isset( $post_array ) ) {
			return $this->app->utility->array_get( $post_array, $this->get_post_field_name( $key ), $default );
		}

		return $this->app->input->post( $this->get_post_field_name( $key ), $default );
	}

	/**
	 * @return array
	 */
	public function get_data_field_settings() {
		$columns = $this->app->db->get_columns( $this->get_related_table_name() );
		unset( $columns['id'] );
		$columns = $this->app->utility->array_combine( $columns, 'name' );
		unset( $columns['post_id'] );
		unset( $columns['created_at'] );
		unset( $columns['created_by'] );
		unset( $columns['updated_at'] );
		unset( $columns['updated_by'] );
		unset( $columns['deleted_at'] );
		unset( $columns['deleted_by'] );
		foreach ( $columns as $k => $v ) {
			$type          = isset( $v['type'] ) ? $v['type'] : 'string';
			$type          = $this->app->utility->parse_db_type( strtolower( trim( $type ) ) );
			$columns[ $k ] = [
				'default'       => isset( $v['default'] ) ? $v['default'] : ( $type === 'string' ? '' : 0 ),
				'type'          => $type,
				'unset_if_null' => true,
				'required'      => ! isset( $v['default'] ) && isset( $v['null'] ) && empty( $v['null'] ),
			];
		}

		return $this->filter_data_field_settings( $columns );
	}

	/**
	 * @param array $columns
	 *
	 * @return array
	 */
	protected function filter_data_field_settings( $columns ) {
		return $columns;
	}

	/**
	 * @since 2.8.3 Added: default form view
	 *
	 * @param \WP_Post $post
	 */
	public function output_edit_form( $post ) {
		$params = $this->get_edit_form_params( $post );
		$this->before_output_edit_form( $post, $params );
		$this->add_style_view( 'admin/style/custom_post', $params );
		$this->add_style_view( 'admin/style/custom_post/' . $this->get_post_type_slug(), $params );
		$this->add_script_view( 'admin/script/custom_post', $params );
		$this->add_script_view( 'admin/script/custom_post/' . $this->get_post_type_slug(), $params );
		if ( ! $this->get_view( 'admin/custom_post/' . $this->get_post_type_slug(), $params, true, false ) ) {
			$this->get_view( 'admin/custom_post', $params, true, false );
		}
		$this->after_output_edit_form( $post, $params );
	}

	/**
	 * @param \WP_Post $post
	 * @param array $params
	 */
	protected function before_output_edit_form( $post, $params ) {

	}

	/**
	 * @param \WP_Post $post
	 * @param array $params
	 */
	protected function after_output_edit_form( $post, $params ) {

	}

	/**
	 * @param \WP_Post $post
	 */
	public function output_after_editor( $post ) {

	}

	/**
	 * @since 2.8.3 Added: columns parameter
	 *
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	protected function get_edit_form_params( $post ) {
		return $this->filter_edit_form_params( [
			'post'    => $post,
			'data'    => $this->get_related_data( $post->ID ),
			'prefix'  => $this->get_post_field_name_prefix(),
			'columns' => $this->filter_table_columns( $this->get_table_columns() ),
		], $post );
	}

	/**
	 * @since 2.8.3
	 * @return array
	 */
	private function get_table_columns() {
		return $this->app->utility->array_map( $this->app->db->get_columns( $this->get_related_table_name() ), function ( $d ) {
			$d['form_type'] = $this->get_form_by_type( $d['type'] );

			return $d;
		} );
	}

	/**
	 * @since 2.8.3
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	protected function filter_table_columns( $columns ) {
		return $columns;
	}

	/**
	 * @param array $params
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	protected function filter_edit_form_params(
		/** @noinspection PhpUnusedParameterInspection */
		$params, $post
	) {
		return $params;
	}

	/**
	 * @param array|null $post_array
	 *
	 * @return array
	 */
	public function validate_input( $post_array = null ) {
		! isset( $post_array ) and $post_array = $this->app->input->post();
		$errors = [];
		foreach ( $this->get_data_field_settings() as $k => $v ) {
			$param = $this->get_post_field( $k, null, $post_array );
			if ( $v['required'] ) {
				$param = $this->sanitize_input( $param, $v['type'] );
				if ( (string) $param === '' ) {
					$errors[ $k ][] = $this->app->translate( 'Value is required.' );
				}
			}
		}

		return $this->filter_validate_input( $errors, $post_array );
	}

	/**
	 * @param array $errors
	 * @param array $post_array
	 *
	 * @return array
	 */
	protected function filter_validate_input(
		/** @noinspection PhpUnusedParameterInspection */
		$errors, $post_array
	) {
		return $errors;
	}

	/**
	 * @param array $data
	 * @param array $post_array
	 *
	 * @return array
	 */
	public function filter_post_data(
		/** @noinspection PhpUnusedParameterInspection */
		$data, $post_array
	) {
		return $data;
	}

	/**
	 * @param string $key
	 * @param array $errors
	 *
	 * @return array
	 */
	public function get_error_messages(
		/** @noinspection PhpUnusedParameterInspection */
		$key, $errors
	) {
		$columns = $this->app->db->get_columns( $this->get_related_table_name() );
		unset( $columns['id'] );
		$columns = $this->app->utility->array_combine( $columns, 'name' );

		return array_map( function ( $d ) use ( $key, $columns ) {
			$key = $this->table_column_to_name( $key, $columns );

			return "$d: [{$key}]";
		}, $errors );
	}

	/**
	 * @param string $key
	 * @param array $columns
	 *
	 * @return string
	 */
	protected function table_column_to_name( $key, $columns ) {
		$name = $this->get_table_column_name( $key );
		if ( isset( $name ) ) {
			return $name;
		}

		return isset( $columns[ $key ]['comment'] ) ? $columns[ $key ]['comment'] : $key;
	}

	/**
	 * @param string $key
	 *
	 * @return null|string
	 */
	protected function get_table_column_name(
		/** @noinspection PhpUnusedParameterInspection */
		$key
	) {
		return null;
	}

	/**
	 * @param string $str
	 *
	 * @return string
	 */
	protected function replace_set_param_variable( $str ) {
		return preg_replace( '#\$\{([\.\w]+)\}#', '<span class="set_param" data-set_param="$1"></span>', $str );
	}

	/**
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function get_edit_post_link( $post_id ) {
		if ( ! $post = get_post( $post_id ) ) {
			return admin_url( 'edit.php?post_type=' . $this->get_post_type() );
		}
		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return admin_url( 'edit.php?post_type=' . $this->get_post_type() );
		}
		$action = '&action=edit';

		return admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	}

	/**
	 * @return int
	 */
	public function get_load_priority() {
		return 10;
	}
}
