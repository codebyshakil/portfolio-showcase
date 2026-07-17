<?php
/**
 * Handles public-facing behaviour: conditional asset loading so nothing
 * is enqueued on pages that do not use the [estel_portfolio] shortcode.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Frontend
 */
class EPSW_Frontend {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
		add_action( 'wp_footer', array( $this, 'late_enqueue_fallback' ), 5 );
	}

	/**
	 * Registers assets and enqueues them only when the current request is
	 * highly likely to contain the shortcode (checked against post content
	 * up front to avoid a render-time flash of unstyled content).
	 */
	public function maybe_enqueue_assets() {
		$this->register_assets();

		if ( $this->content_has_shortcode() ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Fallback for cases where the shortcode is injected via a widget,
	 * template part, or other mechanism not visible in post_content at
	 * the time wp_enqueue_scripts fires. Runs late in the footer and
	 * only prints assets if the shortcode actually rendered and the
	 * assets are not already enqueued.
	 */
	public function late_enqueue_fallback() {
		if ( ! EPSW_Shortcode::$shortcode_used ) {
			return;
		}

		if ( wp_style_is( 'epsw-frontend', 'enqueued' ) ) {
			return;
		}

		$this->register_assets();
		$this->enqueue_assets();

		wp_print_styles( array( 'epsw-frontend' ) );
		wp_print_scripts( array( 'epsw-frontend' ) );
	}

	/**
	 * Registers (but does not enqueue) the frontend CSS/JS.
	 */
	private function register_assets() {
		if ( wp_style_is( 'epsw-frontend', 'registered' ) ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_style(
			'epsw-frontend',
			EPSW_PLUGIN_URL . 'assets/css/frontend' . $suffix . '.css',
			array(),
			EPSW_VERSION
		);

		wp_register_script(
			'epsw-frontend',
			EPSW_PLUGIN_URL . 'assets/js/frontend' . $suffix . '.js',
			array(),
			EPSW_VERSION,
			true
		);

		wp_localize_script(
			'epsw-frontend',
			'EPSW_Frontend',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'epsw_frontend_nonce' ),
				'i18n'     => array(
					'loading'    => __( 'Loading projects…', 'portfolio-showcase' ),
					'loadMore'   => __( 'Load More', 'portfolio-showcase' ),
					'noResults'  => __( 'No projects found for the selected filters.', 'portfolio-showcase' ),
				),
			)
		);
	}

	/**
	 * Enqueues the frontend CSS/JS.
	 */
	private function enqueue_assets() {
		wp_enqueue_style( 'epsw-frontend' );
		wp_enqueue_script( 'epsw-frontend' );
	}

	/**
	 * Checks the current queried post(s) content for the shortcode tag.
	 *
	 * @return bool
	 */
	private function content_has_shortcode() {
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && has_shortcode( $post->post_content, 'estel_portfolio' ) ) {
				return true;
			}
		}

		global $wp_query;
		if ( ! empty( $wp_query->posts ) && is_array( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $post ) {
				if ( isset( $post->post_content ) && has_shortcode( $post->post_content, 'estel_portfolio' ) ) {
					return true;
				}
			}
		}

		/**
		 * Allows themes/other plugins to force-load portfolio assets when
		 * the shortcode is rendered from a widget, template part, or PHP
		 * (e.g. echo do_shortcode(...)) rather than post content.
		 */
		return (bool) apply_filters( 'epsw_force_load_assets', false );
	}
}
