<?php
/**
 * View: Projects screen. Routes between the list table and the
 * add/edit form based on the `action` query var.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$action = isset( $_GET['action'] ) ? sanitize_key( wp_unslash( $_GET['action'] ) ) : 'list'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

if ( in_array( $action, array( 'add', 'edit' ), true ) ) {
	include EPSW_PLUGIN_DIR . 'admin/views/project-form.php';
	return;
}

$list_table = new EPSW_Projects_List_Table();
$list_table->prepare_items();

$add_new_url = add_query_arg(
	array(
		'page'   => 'epsw-projects',
		'action' => 'add',
	),
	admin_url( 'admin.php' )
);
?>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-header.php'; ?>
	<div class="epsw-admin-header">
		<h1><?php esc_html_e( 'Projects', 'portfolio-showcase' ); ?></h1>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="epsw-btn-admin epsw-btn-admin-primary">
			<?php esc_html_e( '+ Add Project', 'portfolio-showcase' ); ?>
		</a>
	</div>

	<div class="epsw-admin-card">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=epsw-projects' ) ); ?>">
			<input type="hidden" name="page" value="epsw-projects" />
			<?php $list_table->search_box( __( 'Search Projects', 'portfolio-showcase' ), 'epsw-project-search' ); ?>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="epsw_bulk_projects" />
			<?php wp_nonce_field( 'epsw_bulk_projects' ); ?>
			<input type="hidden" name="bulk_action" id="epsw-bulk-action-field" value="" />
			<?php $list_table->display(); ?>
		</form>
	</div>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-footer.php'; ?>
<script>
	// Wires the native WP_List_Table bulk action dropdowns to our
	// admin-post.php form, since the table itself submits to admin.php.
	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.querySelector( 'form[action*="admin-post.php"]' );
		if ( ! form ) {
			return;
		}
		form.addEventListener( 'submit', function ( e ) {
			var selects = form.querySelectorAll( 'select[name="action"], select[name="action2"]' );
			var chosen = '';
			selects.forEach( function ( sel ) {
				if ( sel.value && '-1' !== sel.value ) {
					chosen = sel.value;
				}
			} );
			if ( ! chosen ) {
				e.preventDefault();
				return;
			}
			document.getElementById( 'epsw-bulk-action-field' ).value = chosen;
		} );
	} );
</script>
