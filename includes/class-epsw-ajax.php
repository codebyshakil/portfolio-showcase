<?php
/**
 * AJAX request handlers.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Ajax
 */
class EPSW_Ajax {

	/**
	 * Constructor. Hooks all AJAX actions (both logged-in and public
	 * where applicable).
	 */
	public function __construct() {
		// Public frontend filtering — available to logged-out visitors.
		add_action( 'wp_ajax_epsw_filter_projects', array( $this, 'filter_projects' ) );
		add_action( 'wp_ajax_nopriv_epsw_filter_projects', array( $this, 'filter_projects' ) );

		// Admin-only actions.
		add_action( 'wp_ajax_epsw_upload_technology_icon', array( $this, 'upload_technology_icon' ) );
		add_action( 'wp_ajax_epsw_preview_shortcode', array( $this, 'preview_shortcode' ) );
		add_action( 'wp_ajax_epsw_reorder_terms', array( $this, 'reorder_terms' ) );
	}

	/**
	 * Handles drag-and-drop reordering on the Categories / Technologies
	 * admin screens. Receives the taxonomy and the term IDs in their new
	 * visual order, then stores sequential `epsw_order` term meta.
	 */
	public function reorder_terms() {
		check_ajax_referer( 'epsw_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'portfolio-showcase' ) ), 403 );
		}

		$taxonomy = isset( $_POST['taxonomy'] ) ? sanitize_key( wp_unslash( $_POST['taxonomy'] ) ) : '';

		if ( ! in_array( $taxonomy, array( 'epsw_category', 'epsw_technology' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid taxonomy.', 'portfolio-showcase' ) ) );
		}

		$order = isset( $_POST['order'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['order'] ) ) : array();

		if ( empty( $order ) ) {
			wp_send_json_error( array( 'message' => __( 'Nothing to reorder.', 'portfolio-showcase' ) ) );
		}

		foreach ( $order as $index => $term_id ) {
			$term = get_term( $term_id, $taxonomy );
			if ( $term && ! is_wp_error( $term ) ) {
				update_term_meta( $term_id, 'epsw_order', $index );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Handles the public AJAX request that re-renders the project grid
	 * whenever a visitor changes a filter or requests another page via
	 * "Load More".
	 */
	public function filter_projects() {
		check_ajax_referer( 'epsw_frontend_nonce', 'nonce' );

		$categories   = isset( $_POST['categories'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['categories'] ) ) : array();
		$technologies = isset( $_POST['technologies'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['technologies'] ) ) : array();
		$per_page     = isset( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 9;
		$paged        = isset( $_POST['paged'] ) ? absint( $_POST['paged'] ) : 1;
		$columns      = isset( $_POST['columns'] ) ? max( 1, min( 4, absint( $_POST['columns'] ) ) ) : 3;

		$per_page = max( 1, $per_page );
		$paged    = max( 1, $paged );

		$args  = EPSW_Shortcode::build_query_args( $categories, $technologies, $per_page, $paged );
		$query = new WP_Query( $args );

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$project = EPSW_Helpers::get_project_data( get_the_ID() );
				include EPSW_PLUGIN_DIR . 'frontend/templates/project-card.php';
			}
			wp_reset_postdata();
		} else {
			echo '<p class="epsw-no-results">' . esc_html__( 'No projects found for the selected filters.', 'portfolio-showcase' ) . '</p>';
		}

		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'        => $html,
				'max_pages'   => (int) $query->max_num_pages,
				'found_posts' => (int) $query->found_posts,
				'paged'       => $paged,
			)
		);
	}

	/**
	 * Handles technology icon uploads from the admin Technologies page.
	 * Accepts SVG, PNG, JPG, JPEG and WEBP; SVGs are sanitized before
	 * being written to the uploads directory.
	 */
	public function upload_technology_icon() {
		check_ajax_referer( 'epsw_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'portfolio-showcase' ) ), 403 );
		}

		if ( empty( $_FILES['icon'] ) || ! isset( $_FILES['icon']['tmp_name'] ) ) {
			wp_send_json_error( array( 'message' => __( 'No file uploaded.', 'portfolio-showcase' ) ) );
		}

		$file      = $_FILES['icon'];
		$allowed   = EPSW_Helpers::allowed_icon_mimes();
		$ext       = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( ! array_key_exists( $ext, $allowed ) ) {
			wp_send_json_error( array( 'message' => __( 'Unsupported file type. Allowed: SVG, PNG, JPG, JPEG, WEBP.', 'portfolio-showcase' ) ) );
		}

		if ( $file['size'] > 2 * MB_IN_BYTES ) {
			wp_send_json_error( array( 'message' => __( 'File too large. Maximum size is 2MB.', 'portfolio-showcase' ) ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		if ( 'svg' === $ext ) {
			$content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$clean   = EPSW_Helpers::sanitize_svg( $content );

			if ( false === $clean ) {
				wp_send_json_error( array( 'message' => __( 'This SVG file could not be safely validated and was rejected.', 'portfolio-showcase' ) ) );
			}

			$upload_dir = wp_upload_dir();
			$filename   = wp_unique_filename( $upload_dir['path'], sanitize_file_name( $file['name'] ) );
			$new_path   = trailingslashit( $upload_dir['path'] ) . $filename;

			if ( false === file_put_contents( $new_path, $clean ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
				wp_send_json_error( array( 'message' => __( 'Could not write the icon file to disk.', 'portfolio-showcase' ) ) );
			}

			$attachment = array(
				'post_mime_type' => 'image/svg+xml',
				'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
				'guid'           => trailingslashit( $upload_dir['url'] ) . $filename,
			);

			$attachment_id = wp_insert_attachment( $attachment, $new_path );
		} else {
			add_filter( 'upload_mimes', array( $this, 'allow_icon_mimes' ) );
			$attachment_id = media_handle_upload( 'icon', 0 );
			remove_filter( 'upload_mimes', array( $this, 'allow_icon_mimes' ) );

			if ( is_wp_error( $attachment_id ) ) {
				wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ) );
			}
		}

		wp_send_json_success(
			array(
				'attachment_id' => $attachment_id,
				'url'           => wp_get_attachment_url( $attachment_id ),
			)
		);
	}

	/**
	 * Filter callback whitelisting icon mime types during upload.
	 *
	 * @param array $mimes Existing allowed mimes.
	 * @return array
	 */
	public function allow_icon_mimes( $mimes ) {
		return array_merge( $mimes, EPSW_Helpers::allowed_icon_mimes() );
	}

	/**
	 * Renders a live preview of a shortcode combination for the admin
	 * Shortcodes page "Preview" action.
	 */
	public function preview_shortcode() {
		check_ajax_referer( 'epsw_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to do this.', 'portfolio-showcase' ) ), 403 );
		}

		$categories   = isset( $_POST['categories'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['categories'] ) ) : array();
		$technologies = isset( $_POST['technologies'] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_POST['technologies'] ) ) : array();

		$shortcode_text = EPSW_Helpers::build_shortcode_text( $categories, $technologies );
		$html           = do_shortcode( $shortcode_text );

		wp_send_json_success(
			array(
				'shortcode' => $shortcode_text,
				'html'      => $html,
			)
		);
	}
}
