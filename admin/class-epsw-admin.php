<?php
/**
 * Admin area: menu registration, asset loading, and all CRUD form
 * handlers for Projects, Categories, Technologies and Shortcodes.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Admin
 */
class EPSW_Admin {

	/**
	 * Capability required to manage the plugin.
	 *
	 * @var string
	 */
	const CAP = 'manage_options';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );

		// Project handlers.
		add_action( 'admin_post_epsw_save_project', array( $this, 'handle_save_project' ) );
		add_action( 'admin_post_epsw_delete_project', array( $this, 'handle_delete_project' ) );
		add_action( 'admin_post_epsw_toggle_status', array( $this, 'handle_toggle_project_status' ) );
		add_action( 'admin_post_epsw_bulk_projects', array( $this, 'handle_bulk_projects' ) );
		add_action( 'admin_post_epsw_duplicate_project', array( $this, 'handle_duplicate_project' ) );

		// Category handlers.
		add_action( 'admin_post_epsw_save_category', array( $this, 'handle_save_category' ) );
		add_action( 'admin_post_epsw_delete_category', array( $this, 'handle_delete_category' ) );

		// Technology handlers.
		add_action( 'admin_post_epsw_save_technology', array( $this, 'handle_save_technology' ) );
		add_action( 'admin_post_epsw_delete_technology', array( $this, 'handle_delete_technology' ) );

		// Shortcode manager handlers.
		add_action( 'admin_post_epsw_save_shortcode', array( $this, 'handle_save_shortcode' ) );
		add_action( 'admin_post_epsw_delete_shortcode', array( $this, 'handle_delete_shortcode' ) );

		// Settings / Import / Export handlers.
		add_action( 'admin_post_epsw_save_settings', array( $this, 'handle_save_settings' ) );
		add_action( 'admin_post_epsw_export_data', array( $this, 'handle_export_data' ) );
		add_action( 'admin_post_epsw_import_data', array( $this, 'handle_import_data' ) );

		// List table bulk action dispatch (Projects screen top/bottom forms
		// submit to admin.php?page=epsw-projects, not admin-post.php, so we
		// intercept them early here).
		add_action( 'admin_init', array( $this, 'maybe_handle_list_table_actions' ) );
	}

	/**
	 * Registers the top level menu and its five submenus.
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Portfolio Showcase', 'portfolio-showcase' ),
			__( 'Portfolio Showcase', 'portfolio-showcase' ),
			self::CAP,
			'epsw-projects',
			array( $this, 'render_projects_page' ),
			'dashicons-grid-view',
			26
		);

		add_submenu_page(
			'epsw-projects',
			__( 'Projects', 'portfolio-showcase' ),
			__( 'Projects', 'portfolio-showcase' ),
			self::CAP,
			'epsw-projects',
			array( $this, 'render_projects_page' )
		);

		add_submenu_page(
			'epsw-projects',
			__( 'Categories', 'portfolio-showcase' ),
			__( 'Categories', 'portfolio-showcase' ),
			self::CAP,
			'epsw-categories',
			array( $this, 'render_categories_page' )
		);

		add_submenu_page(
			'epsw-projects',
			__( 'Technologies', 'portfolio-showcase' ),
			__( 'Technologies', 'portfolio-showcase' ),
			self::CAP,
			'epsw-technologies',
			array( $this, 'render_technologies_page' )
		);

		add_submenu_page(
			'epsw-projects',
			__( 'Shortcodes', 'portfolio-showcase' ),
			__( 'Shortcodes', 'portfolio-showcase' ),
			self::CAP,
			'epsw-shortcodes',
			array( $this, 'render_shortcodes_page' )
		);

		add_submenu_page(
			'epsw-projects',
			__( 'Settings', 'portfolio-showcase' ),
			__( 'Settings', 'portfolio-showcase' ),
			self::CAP,
			'epsw-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Returns true if the current admin screen belongs to this plugin.
	 *
	 * @return bool
	 */
	private function is_plugin_screen() {
		if ( ! isset( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}
		$page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return in_array( $page, array( 'epsw-projects', 'epsw-categories', 'epsw-technologies', 'epsw-shortcodes', 'epsw-settings' ), true );
	}

	/**
	 * Enqueues admin CSS/JS only on the plugin's own screens.
	 */
	public function maybe_enqueue_assets() {
		if ( ! $this->is_plugin_screen() ) {
			return;
		}

		wp_enqueue_media();

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_style(
			'epsw-admin',
			EPSW_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css',
			array(),
			EPSW_VERSION
		);

		wp_enqueue_script(
			'epsw-admin',
			EPSW_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			EPSW_VERSION,
			true
		);

		wp_localize_script(
			'epsw-admin',
			'EPSW_Admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'epsw_admin_nonce' ),
				'i18n'     => array(
					'confirmDelete' => __( 'Are you sure you want to delete this? This action cannot be undone.', 'portfolio-showcase' ),
					'uploading'     => __( 'Uploading…', 'portfolio-showcase' ),
					'uploadIcon'    => __( 'Upload Icon', 'portfolio-showcase' ),
					'copy'          => __( 'Copy', 'portfolio-showcase' ),
					'copied'        => __( 'Copied!', 'portfolio-showcase' ),
				),
			)
		);
	}

	/**
	 * Renders any transient admin notice set by a previous form submission.
	 */
	public function render_notices() {
		if ( ! $this->is_plugin_screen() ) {
			return;
		}

		$notice = get_transient( 'epsw_admin_notice_' . get_current_user_id() );

		if ( ! $notice ) {
			return;
		}

		delete_transient( 'epsw_admin_notice_' . get_current_user_id() );

		$type = 'error' === $notice['type'] ? 'notice-error' : 'notice-success';

		printf(
			'<div class="notice %1$s is-dismissible"><p>%2$s</p></div>',
			esc_attr( $type ),
			esc_html( $notice['message'] )
		);
	}

	/**
	 * Stores a one-time admin notice to display after a redirect.
	 *
	 * @param string $message Notice text.
	 * @param string $type    'success' or 'error'.
	 */
	private function set_notice( $message, $type = 'success' ) {
		set_transient(
			'epsw_admin_notice_' . get_current_user_id(),
			array(
				'message' => $message,
				'type'    => $type,
			),
			60
		);
	}

	/**
	 * Verifies capability + nonce, dying with an error if either fails.
	 *
	 * @param string $nonce_action Nonce action name.
	 * @param string $nonce_field  Request field holding the nonce.
	 */
	private function guard( $nonce_action, $nonce_field = '_wpnonce' ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'portfolio-showcase' ), 403 );
		}

		$nonce = isset( $_REQUEST[ $nonce_field ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_field ] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
			wp_die( esc_html__( 'Security check failed. Please go back and try again.', 'portfolio-showcase' ), 403 );
		}
	}

	// ---------------------------------------------------------------
	// Screen renderers.
	// ---------------------------------------------------------------

	/**
	 * Renders the Projects list / add / edit screen.
	 */
	public function render_projects_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'portfolio-showcase' ) );
		}
		include EPSW_PLUGIN_DIR . 'admin/views/projects-page.php';
	}

	/**
	 * Renders the Categories screen.
	 */
	public function render_categories_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'portfolio-showcase' ) );
		}
		include EPSW_PLUGIN_DIR . 'admin/views/categories-page.php';
	}

	/**
	 * Renders the Technologies screen.
	 */
	public function render_technologies_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'portfolio-showcase' ) );
		}
		include EPSW_PLUGIN_DIR . 'admin/views/technologies-page.php';
	}

	/**
	 * Renders the Shortcodes screen.
	 */
	public function render_shortcodes_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'portfolio-showcase' ) );
		}
		include EPSW_PLUGIN_DIR . 'admin/views/shortcodes-page.php';
	}

	/**
	 * Renders the Settings / Import / Export screen.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'portfolio-showcase' ) );
		}
		include EPSW_PLUGIN_DIR . 'admin/views/settings-page.php';
	}

	// ---------------------------------------------------------------
	// Projects screen: row-level actions submitted via admin.php (not
	// admin-post.php) need to be intercepted before headers are sent.
	// ---------------------------------------------------------------

	/**
	 * Handles the "toggle status" / "delete" row links and the bulk
	 * action form on the Projects list screen, all of which submit back
	 * to admin.php?page=epsw-projects rather than admin-post.php.
	 */
	public function maybe_handle_list_table_actions() {
		if ( ! is_admin() || ! $this->is_plugin_screen() ) {
			return;
		}

		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( 'epsw-projects' !== $page ) {
			return;
		}

		$action = isset( $_GET['epsw_action'] ) ? sanitize_key( wp_unslash( $_GET['epsw_action'] ) ) : '';

		if ( 'toggle_status' === $action && isset( $_GET['id'] ) ) {
			$id = absint( $_GET['id'] );
			$this->guard( 'epsw_toggle_project_' . $id );

			$post = get_post( $id );
			if ( $post && 'epsw_project' === $post->post_type ) {
				$new_status = 'publish' === $post->post_status ? 'draft' : 'publish';
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => $new_status,
					)
				);
				$this->set_notice( __( 'Project status updated.', 'portfolio-showcase' ) );
			}

			wp_safe_redirect( remove_query_arg( array( 'epsw_action', 'id', '_wpnonce' ) ) );
			exit;
		}

		if ( 'delete' === $action && isset( $_GET['id'] ) ) {
			$id = absint( $_GET['id'] );
			$this->guard( 'epsw_delete_project_' . $id );

			$post = get_post( $id );
			if ( $post && 'epsw_project' === $post->post_type ) {
				wp_delete_post( $id, true );
				$this->set_notice( __( 'Project deleted.', 'portfolio-showcase' ) );
			}

			wp_safe_redirect( remove_query_arg( array( 'epsw_action', 'id', '_wpnonce' ) ) );
			exit;
		}

		if ( 'toggle_featured' === $action && isset( $_GET['id'] ) ) {
			$id = absint( $_GET['id'] );
			$this->guard( 'epsw_toggle_featured_' . $id );

			$post = get_post( $id );
			if ( $post && 'epsw_project' === $post->post_type ) {
				$is_featured = '1' === get_post_meta( $id, '_epsw_featured', true );
				update_post_meta( $id, '_epsw_featured', $is_featured ? '0' : '1' );
				$this->set_notice( $is_featured ? __( 'Project removed from featured.', 'portfolio-showcase' ) : __( 'Project marked as featured.', 'portfolio-showcase' ) );
			}

			wp_safe_redirect( remove_query_arg( array( 'epsw_action', 'id', '_wpnonce' ) ) );
			exit;
		}
	}

	// ---------------------------------------------------------------
	// Project CRUD.
	// ---------------------------------------------------------------

	/**
	 * Saves (creates or updates) a project from the admin form.
	 */
	public function handle_save_project() {
		$this->guard( 'epsw_save_project' );

		$id          = isset( $_POST['project_id'] ) ? absint( $_POST['project_id'] ) : 0;
		$title       = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$details_url = isset( $_POST['details_url'] ) ? esc_url_raw( wp_unslash( $_POST['details_url'] ) ) : '';
		$demo_url    = isset( $_POST['demo_url'] ) ? esc_url_raw( wp_unslash( $_POST['demo_url'] ) ) : '';
		$status      = isset( $_POST['status'] ) && 'publish' === $_POST['status'] ? 'publish' : 'draft';
		$order       = isset( $_POST['order'] ) ? intval( $_POST['order'] ) : 0;
		$featured    = ! empty( $_POST['featured'] ) ? '1' : '0';
		$image_id    = isset( $_POST['image_id'] ) ? absint( $_POST['image_id'] ) : 0;
		$categories  = isset( $_POST['categories'] ) ? array_map( 'absint', (array) $_POST['categories'] ) : array();
		$technologies = isset( $_POST['technologies'] ) ? array_map( 'absint', (array) $_POST['technologies'] ) : array();

		if ( empty( $title ) ) {
			$this->set_notice( __( 'Project title is required.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=epsw-projects' ) );
			exit;
		}

		$postarr = array(
			'post_type'   => 'epsw_project',
			'post_title'  => $title,
			'post_status' => $status,
			'menu_order'  => $order,
		);

		if ( $id ) {
			$postarr['ID'] = $id;
			$post_id       = wp_update_post( $postarr, true );
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $post_id ) ) {
			$this->set_notice( $post_id->get_error_message(), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
			exit;
		}

		update_post_meta( $post_id, '_epsw_description', $description );
		update_post_meta( $post_id, '_epsw_details_url', $details_url );
		update_post_meta( $post_id, '_epsw_demo_url', $demo_url );
		update_post_meta( $post_id, '_epsw_featured', $featured );

		if ( $image_id ) {
			set_post_thumbnail( $post_id, $image_id );
		} else {
			delete_post_thumbnail( $post_id );
		}

		wp_set_object_terms( $post_id, $categories, 'epsw_category', false );
		wp_set_object_terms( $post_id, $technologies, 'epsw_technology', false );

		$this->set_notice( $id ? __( 'Project updated.', 'portfolio-showcase' ) : __( 'Project created.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
		exit;
	}

	/**
	 * Deletes a single project (fallback handler for admin-post.php link).
	 */
	public function handle_delete_project() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_delete_project_' . $id );

		$post = get_post( $id );
		if ( $post && 'epsw_project' === $post->post_type ) {
			wp_delete_post( $id, true );
			$this->set_notice( __( 'Project deleted.', 'portfolio-showcase' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
		exit;
	}

	/**
	 * Toggles a single project's publish/hidden status (fallback handler).
	 */
	public function handle_toggle_project_status() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_toggle_project_' . $id );

		$post = get_post( $id );
		if ( $post && 'epsw_project' === $post->post_type ) {
			$new_status = 'publish' === $post->post_status ? 'draft' : 'publish';
			wp_update_post(
				array(
					'ID'          => $id,
					'post_status' => $new_status,
				)
			);
			$this->set_notice( __( 'Project status updated.', 'portfolio-showcase' ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
		exit;
	}

	/**
	 * Handles bulk Show / Hide / Delete on the Projects list table.
	 */
	public function handle_bulk_projects() {
		$this->guard( 'epsw_bulk_projects' );

		$bulk_action = isset( $_POST['bulk_action'] ) ? sanitize_key( wp_unslash( $_POST['bulk_action'] ) ) : '';
		$ids         = isset( $_POST['project_ids'] ) ? array_map( 'absint', (array) $_POST['project_ids'] ) : array();

		if ( empty( $ids ) || empty( $bulk_action ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
			exit;
		}

		foreach ( $ids as $id ) {
			$post = get_post( $id );
			if ( ! $post || 'epsw_project' !== $post->post_type ) {
				continue;
			}

			switch ( $bulk_action ) {
				case 'bulk_show':
					wp_update_post( array( 'ID' => $id, 'post_status' => 'publish' ) );
					break;
				case 'bulk_hide':
					wp_update_post( array( 'ID' => $id, 'post_status' => 'draft' ) );
					break;
				case 'bulk_delete':
					wp_delete_post( $id, true );
					break;
			}
		}

		$this->set_notice( __( 'Bulk action completed.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
		exit;
	}

	// ---------------------------------------------------------------
	// Category CRUD.
	// ---------------------------------------------------------------

	/**
	 * Creates or updates a category term, including its icon term meta.
	 */
	public function handle_save_category() {
		$this->guard( 'epsw_save_category' );

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$icon_id = isset( $_POST['icon_id'] ) ? absint( $_POST['icon_id'] ) : 0;

		if ( empty( $name ) ) {
			$this->set_notice( __( 'Category name is required.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-categories' ) );
			exit;
		}

		if ( $term_id ) {
			$result = wp_update_term( $term_id, 'epsw_category', array( 'name' => $name ) );
		} else {
			$result = wp_insert_term( $name, 'epsw_category' );
		}

		if ( is_wp_error( $result ) ) {
			$this->set_notice( $result->get_error_message(), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-categories' ) );
			exit;
		}

		$final_term_id = $term_id ? $term_id : $result['term_id'];

		if ( $icon_id ) {
			update_term_meta( $final_term_id, 'epsw_icon_id', $icon_id );
		} else {
			delete_term_meta( $final_term_id, 'epsw_icon_id' );
		}

		$this->set_notice( $term_id ? __( 'Category updated.', 'portfolio-showcase' ) : __( 'Category created.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-categories' ) );
		exit;
	}

	/**
	 * Deletes a category term.
	 */
	public function handle_delete_category() {
		$term_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_delete_category_' . $term_id );

		wp_delete_term( $term_id, 'epsw_category' );
		$this->set_notice( __( 'Category deleted.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-categories' ) );
		exit;
	}

	// ---------------------------------------------------------------
	// Technology CRUD.
	// ---------------------------------------------------------------

	/**
	 * Creates or updates a technology term, including its icon term meta.
	 */
	public function handle_save_technology() {
		$this->guard( 'epsw_save_technology' );

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$icon_id = isset( $_POST['icon_id'] ) ? absint( $_POST['icon_id'] ) : 0;

		if ( empty( $name ) ) {
			$this->set_notice( __( 'Technology name is required.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-technologies' ) );
			exit;
		}

		if ( $term_id ) {
			$result = wp_update_term( $term_id, 'epsw_technology', array( 'name' => $name ) );
		} else {
			$result = wp_insert_term( $name, 'epsw_technology' );
		}

		if ( is_wp_error( $result ) ) {
			$this->set_notice( $result->get_error_message(), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-technologies' ) );
			exit;
		}

		$final_term_id = $term_id ? $term_id : $result['term_id'];

		if ( $icon_id ) {
			update_term_meta( $final_term_id, 'epsw_icon_id', $icon_id );
		}

		$this->set_notice( $term_id ? __( 'Technology updated.', 'portfolio-showcase' ) : __( 'Technology created.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-technologies' ) );
		exit;
	}

	/**
	 * Deletes a technology term and its icon attachment reference.
	 */
	public function handle_delete_technology() {
		$term_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_delete_technology_' . $term_id );

		wp_delete_term( $term_id, 'epsw_technology' );
		$this->set_notice( __( 'Technology deleted.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-technologies' ) );
		exit;
	}

	// ---------------------------------------------------------------
	// Shortcode manager CRUD.
	// ---------------------------------------------------------------

	/**
	 * Generates and saves a new shortcode combination.
	 */
	public function handle_save_shortcode() {
		$this->guard( 'epsw_save_shortcode' );

		$label        = isset( $_POST['label'] ) ? sanitize_text_field( wp_unslash( $_POST['label'] ) ) : __( 'Untitled Shortcode', 'portfolio-showcase' );
		$categories   = isset( $_POST['categories'] ) ? array_map( 'sanitize_title', (array) $_POST['categories'] ) : array();
		$technologies = isset( $_POST['technologies'] ) ? array_map( 'sanitize_title', (array) $_POST['technologies'] ) : array();

		EPSW_Shortcode_Manager::create( $label, $categories, $technologies );

		$this->set_notice( __( 'Shortcode generated successfully.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-shortcodes' ) );
		exit;
	}

	/**
	 * Deletes a saved shortcode (never the protected default row).
	 */
	public function handle_delete_shortcode() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_delete_shortcode_' . $id );

		$deleted = EPSW_Shortcode_Manager::delete( $id );

		if ( $deleted ) {
			$this->set_notice( __( 'Shortcode deleted.', 'portfolio-showcase' ) );
		} else {
			$this->set_notice( __( 'This shortcode cannot be deleted.', 'portfolio-showcase' ), 'error' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-shortcodes' ) );
		exit;
	}

	/**
	 * Duplicates an existing project: title, meta, featured image and
	 * taxonomy terms are copied. The clone is always created as Hidden
	 * so two identical published cards never appear on the frontend by
	 * accident; the admin can publish it once they've made their edits.
	 */
	public function handle_duplicate_project() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$this->guard( 'epsw_duplicate_project_' . $id );

		$source = get_post( $id );

		if ( ! $source || 'epsw_project' !== $source->post_type ) {
			$this->set_notice( __( 'Project not found.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
			exit;
		}

		$new_id = wp_insert_post(
			array(
				'post_type'   => 'epsw_project',
				/* translators: %s: original project title. */
				'post_title'  => sprintf( __( '%s (Copy)', 'portfolio-showcase' ), $source->post_title ),
				'post_status' => 'draft',
				'menu_order'  => $source->menu_order,
			),
			true
		);

		if ( is_wp_error( $new_id ) ) {
			$this->set_notice( $new_id->get_error_message(), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects' ) );
			exit;
		}

		foreach ( array( '_epsw_description', '_epsw_details_url', '_epsw_demo_url', '_epsw_featured' ) as $meta_key ) {
			update_post_meta( $new_id, $meta_key, get_post_meta( $id, $meta_key, true ) );
		}
		// A duplicate should never inherit the "featured" spotlight silently.
		update_post_meta( $new_id, '_epsw_featured', '0' );

		$thumbnail_id = get_post_thumbnail_id( $id );
		if ( $thumbnail_id ) {
			set_post_thumbnail( $new_id, $thumbnail_id );
		}

		$categories   = wp_list_pluck( wp_get_post_terms( $id, 'epsw_category' ), 'term_id' );
		$technologies = wp_list_pluck( wp_get_post_terms( $id, 'epsw_technology' ), 'term_id' );
		wp_set_object_terms( $new_id, $categories, 'epsw_category', false );
		wp_set_object_terms( $new_id, $technologies, 'epsw_technology', false );

		$this->set_notice( __( 'Project duplicated. The copy is hidden — edit and publish it when ready.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-projects&action=edit&id=' . $new_id ) );
		exit;
	}

	// ---------------------------------------------------------------
	// Settings + Import / Export.
	// ---------------------------------------------------------------

	/**
	 * Saves the General Settings form (items per page, load more toggle,
	 * and whether uninstalling the plugin should erase all its data).
	 */
	public function handle_save_settings() {
		$this->guard( 'epsw_save_settings' );

		$settings = array(
			'items_per_page'           => isset( $_POST['items_per_page'] ) ? max( 1, absint( $_POST['items_per_page'] ) ) : 9,
			'load_more_enabled'        => ! empty( $_POST['load_more_enabled'] ) ? 1 : 0,
			'remove_data_on_uninstall' => ! empty( $_POST['remove_data_on_uninstall'] ) ? 1 : 0,
		);

		update_option( 'epsw_settings', $settings );

		$this->set_notice( __( 'Settings saved.', 'portfolio-showcase' ) );

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
		exit;
	}

	/**
	 * Streams the full data export as a downloadable JSON file.
	 */
	public function handle_export_data() {
		$this->guard( 'epsw_export_data' );

		$data = EPSW_Import_Export::export_array();
		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );

		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="portfolio-export-' . gmdate( 'Y-m-d' ) . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Handles the JSON import upload: validates the file, decodes it,
	 * and delegates the actual data restoration to EPSW_Import_Export.
	 */
	public function handle_import_data() {
		$this->guard( 'epsw_import_data' );

		if ( empty( $_FILES['import_file'] ) || empty( $_FILES['import_file']['tmp_name'] ) || UPLOAD_ERR_OK !== $_FILES['import_file']['error'] ) {
			$this->set_notice( __( 'Please choose a valid JSON export file to import.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		$file = $_FILES['import_file'];
		$ext  = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( 'json' !== $ext ) {
			$this->set_notice( __( 'The import file must be a .json file.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		if ( $file['size'] > 10 * MB_IN_BYTES ) {
			$this->set_notice( __( 'Import file is too large (max 10MB).', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		$raw = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

		if ( false === $raw ) {
			$this->set_notice( __( 'Could not read the uploaded file.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		$data = json_decode( $raw, true );

		if ( null === $data || ! is_array( $data ) ) {
			$this->set_notice( __( 'This file is not valid JSON.', 'portfolio-showcase' ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		$report = EPSW_Import_Export::import_array( $data );

		if ( ! empty( $report['errors'] ) ) {
			$this->set_notice( implode( ' ', $report['errors'] ), 'error' );
			wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
			exit;
		}

		$this->set_notice(
			sprintf(
				/* translators: 1: categories imported, 2: technologies imported, 3: projects imported, 4: shortcodes imported */
				__( 'Import complete — %1$d categories, %2$d technologies, %3$d projects, %4$d shortcodes imported.', 'portfolio-showcase' ),
				$report['categories'],
				$report['technologies'],
				$report['projects'],
				$report['shortcodes']
			)
		);

		wp_safe_redirect( admin_url( 'admin.php?page=epsw-settings' ) );
		exit;
	}
}