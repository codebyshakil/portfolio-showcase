<?php
/**
 * Registers and renders the [estel_portfolio] shortcode.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Shortcode
 */
class EPSW_Shortcode {

	/**
	 * Tracks whether the shortcode has been encountered on the current
	 * request so the frontend asset loader knows whether to enqueue.
	 *
	 * @var bool
	 */
	public static $shortcode_used = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'estel_portfolio', array( $this, 'render' ) );
	}

	/**
	 * Renders the shortcode output.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		self::$shortcode_used = true;

		$atts = shortcode_atts(
			array(
				'category'   => '',
				'technology' => '',
				'columns'    => 3,
				'per_page'   => 0,
			),
			$atts,
			'estel_portfolio'
		);

		$categories   = EPSW_Helpers::parse_slug_list( $atts['category'] );
		$technologies = EPSW_Helpers::parse_slug_list( $atts['technology'] );
		$columns      = max( 1, min( 4, (int) $atts['columns'] ) );

		$settings = get_option( 'epsw_settings', array() );
		$per_page = ! empty( $atts['per_page'] ) ? (int) $atts['per_page'] : (int) ( $settings['items_per_page'] ?? 9 );
		$per_page = max( 1, $per_page );

		$instance_id = 'epsw-' . wp_unique_id();

		ob_start();

		include EPSW_PLUGIN_DIR . 'frontend/templates/portfolio-grid.php';

		return ob_get_clean();
	}

	/**
	 * Builds the WP_Query args for a portfolio grid given filter slugs.
	 *
	 * @param string[] $categories   Category slugs to restrict to.
	 * @param string[] $technologies Technology slugs to restrict to.
	 * @param int      $per_page     Items per page.
	 * @param int      $paged        Page number.
	 * @return array
	 */
	public static function build_query_args( array $categories, array $technologies, $per_page, $paged = 1 ) {
		$tax_query = array( 'relation' => 'AND' );

		if ( ! empty( $categories ) ) {
			$tax_query[] = array(
				'taxonomy' => 'epsw_category',
				'field'    => 'slug',
				'terms'    => $categories,
			);
		}

		if ( ! empty( $technologies ) ) {
			$tax_query[] = array(
				'taxonomy' => 'epsw_technology',
				'field'    => 'slug',
				'terms'    => $technologies,
			);
		}

		$args = array(
			'post_type'      => 'epsw_project',
			'post_status'    => 'publish',
			'posts_per_page' => $per_page,
			'paged'          => max( 1, $paged ),
			'no_found_rows'  => false,
			// Featured projects first, then manual order, then newest
			// first. The OR/EXISTS + NOT EXISTS combo makes sure projects
			// that predate the "featured" field (or otherwise lack the
			// meta) are never filtered out of results, only sorted last.
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'relation'         => 'OR',
				'featured_clause'  => array(
					'key'     => '_epsw_featured',
					'compare' => 'EXISTS',
				),
				array(
					'key'     => '_epsw_featured',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'        => array(
				'featured_clause' => 'DESC',
				'menu_order'      => 'ASC',
				'date'            => 'DESC',
			),
		);

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		return $args;
	}
}
