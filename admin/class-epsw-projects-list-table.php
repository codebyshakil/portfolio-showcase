<?php
/**
 * WP_List_Table implementation for the Projects admin screen.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class EPSW_Projects_List_Table
 */
class EPSW_Projects_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'project',
				'plural'   => 'projects',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Defines the columns shown in the table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'           => '<input type="checkbox" />',
			'image'        => __( 'Image', 'portfolio-showcase' ),
			'title'        => __( 'Title', 'portfolio-showcase' ),
			'categories'   => __( 'Categories', 'portfolio-showcase' ),
			'technologies' => __( 'Technologies', 'portfolio-showcase' ),
			'featured'     => __( 'Featured', 'portfolio-showcase' ),
			'status'       => __( 'Status', 'portfolio-showcase' ),
			'date'         => __( 'Date', 'portfolio-showcase' ),
		);
	}

	/**
	 * Defines sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'title'  => array( 'title', false ),
			'status' => array( 'status', false ),
			'date'   => array( 'date', true ),
		);
	}

	/**
	 * Defines bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'bulk_show'   => __( 'Show', 'portfolio-showcase' ),
			'bulk_hide'   => __( 'Hide', 'portfolio-showcase' ),
			'bulk_delete' => __( 'Delete', 'portfolio-showcase' ),
		);
	}

	/**
	 * Checkbox column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="project_ids[]" value="%d" />', (int) $item['id'] );
	}

	/**
	 * Image column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_image( $item ) {
		if ( empty( $item['image_url'] ) ) {
			return '<div class="epsw-admin-thumb epsw-admin-thumb-empty"></div>';
		}

		return sprintf(
			'<img src="%s" alt="" class="epsw-admin-thumb" />',
			esc_url( $item['image_url'] )
		);
	}

	/**
	 * Title column with row actions.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_title( $item ) {
		$edit_url = add_query_arg(
			array(
				'page'   => 'epsw-projects',
				'action' => 'edit',
				'id'     => $item['id'],
			),
			admin_url( 'admin.php' )
		);

		$delete_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'       => 'epsw-projects',
					'epsw_action' => 'delete',
					'id'         => $item['id'],
				),
				admin_url( 'admin-post.php' )
			),
			'epsw_delete_project_' . $item['id']
		);

		$toggle_label = 'publish' === $item['status'] ? __( 'Hide', 'portfolio-showcase' ) : __( 'Show', 'portfolio-showcase' );
		$toggle_url   = wp_nonce_url(
			add_query_arg(
				array(
					'page'        => 'epsw-projects',
					'epsw_action' => 'toggle_status',
					'id'          => $item['id'],
				),
				admin_url( 'admin-post.php' )
			),
			'epsw_toggle_project_' . $item['id']
		);

		$duplicate_url = wp_nonce_url(
			add_query_arg(
				array(
					'action' => 'epsw_duplicate_project',
					'id'     => $item['id'],
				),
				admin_url( 'admin-post.php' )
			),
			'epsw_duplicate_project_' . $item['id']
		);

		$actions = array(
			'edit'      => sprintf( '<a href="%s">%s</a>', esc_url( $edit_url ), esc_html__( 'Edit', 'portfolio-showcase' ) ),
			'duplicate' => sprintf( '<a href="%s">%s</a>', esc_url( $duplicate_url ), esc_html__( 'Duplicate', 'portfolio-showcase' ) ),
			'toggle'    => sprintf( '<a href="%s">%s</a>', esc_url( $toggle_url ), esc_html( $toggle_label ) ),
			'delete'    => sprintf(
				'<a href="%s" class="epsw-row-delete" onclick="return confirm(\'%s\');">%s</a>',
				esc_url( $delete_url ),
				esc_js( __( 'Delete this project permanently?', 'portfolio-showcase' ) ),
				esc_html__( 'Delete', 'portfolio-showcase' )
			),
		);

		return sprintf(
			'<strong><a href="%s">%s</a></strong>%s',
			esc_url( $edit_url ),
			esc_html( $item['title'] ),
			$this->row_actions( $actions )
		);
	}

	/**
	 * Categories column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_categories( $item ) {
		if ( empty( $item['categories'] ) ) {
			return '&#8212;';
		}
		return esc_html( implode( ', ', wp_list_pluck( $item['categories'], 'name' ) ) );
	}

	/**
	 * Technologies column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_technologies( $item ) {
		if ( empty( $item['technologies'] ) ) {
			return '&#8212;';
		}
		return esc_html( implode( ', ', wp_list_pluck( $item['technologies'], 'name' ) ) );
	}

	/**
	 * Featured column: a clickable star that toggles featured state.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_featured( $item ) {
		$toggle_url = wp_nonce_url(
			add_query_arg(
				array(
					'page'        => 'epsw-projects',
					'epsw_action' => 'toggle_featured',
					'id'          => $item['id'],
				),
				admin_url( 'admin.php' )
			),
			'epsw_toggle_featured_' . $item['id']
		);

		$class = $item['featured'] ? 'epsw-star is-active' : 'epsw-star';
		$label = $item['featured'] ? __( 'Featured — click to unfeature', 'portfolio-showcase' ) : __( 'Click to mark as featured', 'portfolio-showcase' );

		return sprintf(
			'<a href="%s" class="%s" title="%s">%s</a>',
			esc_url( $toggle_url ),
			esc_attr( $class ),
			esc_attr( $label ),
			$item['featured'] ? '&#9733;' : '&#9734;'
		);
	}

	/**
	 * Status column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_status( $item ) {
		if ( 'publish' === $item['status'] ) {
			return '<span class="epsw-badge epsw-badge-success">' . esc_html__( 'Published', 'portfolio-showcase' ) . '</span>';
		}
		return '<span class="epsw-badge epsw-badge-muted">' . esc_html__( 'Hidden', 'portfolio-showcase' ) . '</span>';
	}

	/**
	 * Date column.
	 *
	 * @param array $item Row item.
	 * @return string
	 */
	public function column_date( $item ) {
		return esc_html( $item['date'] );
	}

	/**
	 * Default column fallback.
	 *
	 * @param array  $item        Row item.
	 * @param string $column_name Column key.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '';
	}

	/**
	 * Prepares table items: query, search, sort, pagination.
	 */
	public function prepare_items() {
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$search       = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_key( wp_unslash( $_REQUEST['orderby'] ) ) : 'menu_order'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order   = isset( $_REQUEST['order'] ) && 'asc' === strtolower( wp_unslash( $_REQUEST['order'] ) ) ? 'ASC' : 'DESC'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$orderby_map = array(
			'title'  => 'title',
			'status' => 'status',
			'date'   => 'date',
		);
		$wp_orderby = $orderby_map[ $orderby ] ?? 'menu_order';

		$args = array(
			'post_type'      => 'epsw_project',
			'post_status'    => array( 'publish', 'draft' ),
			'posts_per_page' => $per_page,
			'paged'          => $current_page,
			's'              => $search,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation'        => 'OR',
				'featured_clause' => array(
					'key'     => '_epsw_featured',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_epsw_featured',
					'compare' => 'NOT EXISTS',
				),
			),
		);

		if ( 'menu_order' === $wp_orderby ) {
			$args['orderby'] = array(
				'featured_clause' => 'DESC',
				'menu_order'      => 'ASC',
				'date'            => 'DESC',
			);
		} else {
			$args['orderby'] = $wp_orderby;
			$args['order']   = $order;
		}

		$query = new WP_Query( $args );

		$items = array();
		foreach ( $query->posts as $post ) {
			$items[] = EPSW_Helpers::get_project_data( $post );
		}

		$this->items = $items;

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->set_pagination_args(
			array(
				'total_items' => $query->found_posts,
				'per_page'    => $per_page,
				'total_pages' => $query->max_num_pages,
			)
		);
	}
}
