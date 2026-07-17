<?php
/**
 * Registers the Project custom post type and the Category / Technology
 * custom taxonomies used throughout the plugin.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Post_Types
 */
class EPSW_Post_Types {

	/**
	 * Constructor hooks registration into WordPress init.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
	}

	/**
	 * Registers the `epsw_project` custom post type.
	 *
	 * The post type is intentionally NOT publicly queryable and has no
	 * front-end single/archive templates of its own — projects are only
	 * ever displayed through the [estel_portfolio] shortcode. It is also
	 * not shown in the default WordPress "Add New" screens; all CRUD is
	 * handled by the plugin's own custom admin UI.
	 */
	public function register_post_type() {
		$labels = array(
			'name'          => __( 'Projects', 'portfolio-showcase' ),
			'singular_name' => __( 'Project', 'portfolio-showcase' ),
		);

		register_post_type(
			'epsw_project',
			array(
				'labels'              => $labels,
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => false,
				'exclude_from_search' => true,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => array( 'title', 'thumbnail', 'menu_order' ),
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'show_in_rest'        => false,
			)
		);
	}

	/**
	 * Registers the `epsw_category` and `epsw_technology` taxonomies.
	 * Both are non-public; management happens exclusively through the
	 * plugin's custom Categories / Technologies admin pages.
	 */
	public function register_taxonomies() {
		register_taxonomy(
			'epsw_category',
			array( 'epsw_project' ),
			array(
				'labels'            => array(
					'name'          => __( 'Categories', 'portfolio-showcase' ),
					'singular_name' => __( 'Category', 'portfolio-showcase' ),
				),
				'public'            => false,
				'show_ui'           => false,
				'show_admin_column' => false,
				'hierarchical'      => false,
				'query_var'         => false,
				'rewrite'           => false,
				'show_in_rest'      => false,
			)
		);

		register_taxonomy(
			'epsw_technology',
			array( 'epsw_project' ),
			array(
				'labels'            => array(
					'name'          => __( 'Technologies', 'portfolio-showcase' ),
					'singular_name' => __( 'Technology', 'portfolio-showcase' ),
				),
				'public'            => false,
				'show_ui'           => false,
				'show_admin_column' => false,
				'hierarchical'      => false,
				'query_var'         => false,
				'rewrite'           => false,
				'show_in_rest'      => false,
			)
		);
	}
}
