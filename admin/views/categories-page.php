<?php
/**
 * View: Categories management screen.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$edit_id   = isset( $_GET['edit'] ) ? absint( $_GET['edit'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$edit_term = $edit_id ? get_term( $edit_id, 'epsw_category' ) : null;
$edit_icon_id  = $edit_id ? (int) get_term_meta( $edit_id, 'epsw_icon_id', true ) : 0;
$edit_icon_url = $edit_icon_id ? wp_get_attachment_url( $edit_icon_id ) : '';

$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$terms = EPSW_Helpers::get_ordered_terms(
	'epsw_category',
	array(
		'search' => $search,
	)
);
?>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-header.php'; ?>
	<div class="epsw-admin-header">
		<h1><?php esc_html_e( 'Categories', 'portfolio-showcase' ); ?></h1>
	</div>

	<div class="epsw-form-grid epsw-form-grid-narrow">
		<div class="epsw-admin-card">
			<form method="get">
				<input type="hidden" name="page" value="epsw-categories" />
				<p class="search-box">
					<input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" class="epsw-input" placeholder="<?php esc_attr_e( 'Search categories…', 'portfolio-showcase' ); ?>" />
					<button type="submit" class="epsw-btn-admin epsw-btn-admin-ghost"><?php esc_html_e( 'Search', 'portfolio-showcase' ); ?></button>
				</p>
			</form>

			<table class="epsw-admin-table widefat" data-taxonomy="epsw_category">
				<thead>
					<tr>
						<th class="epsw-drag-col"></th>
						<th><?php esc_html_e( 'Icon', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Name', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Slug', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Used Count', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'portfolio-showcase' ); ?></th>
					</tr>
				</thead>
				<tbody class="<?php echo empty( $search ) ? 'epsw-sortable-rows' : ''; ?>" id="epsw-sortable-categories">
					<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
						<?php foreach ( $terms as $term ) : ?>
							<?php
							$icon_id  = (int) get_term_meta( $term->term_id, 'epsw_icon_id', true );
							$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';

							$edit_url = add_query_arg(
								array(
									'page' => 'epsw-categories',
									'edit' => $term->term_id,
								),
								admin_url( 'admin.php' )
							);
							$delete_url = wp_nonce_url(
								add_query_arg(
									array(
										'action' => 'epsw_delete_category',
										'id'     => $term->term_id,
									),
									admin_url( 'admin-post.php' )
								),
								'epsw_delete_category_' . $term->term_id
							);
							?>
							<tr data-term-id="<?php echo esc_attr( $term->term_id ); ?>">
								<td class="epsw-drag-col"><span class="epsw-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'portfolio-showcase' ); ?>">&#8942;&#8942;</span></td>
								<td>
									<?php if ( $icon_url ) : ?>
										<img src="<?php echo esc_url( $icon_url ); ?>" alt="" class="epsw-admin-tech-icon" />
									<?php else : ?>
										&#8212;
									<?php endif; ?>
								</td>
								<td><?php echo esc_html( $term->name ); ?></td>
								<td><code><?php echo esc_html( $term->slug ); ?></code></td>
								<td><?php echo esc_html( $term->count ); ?></td>
								<td>
									<a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'portfolio-showcase' ); ?></a>
									|
									<a href="<?php echo esc_url( $delete_url ); ?>" class="epsw-row-delete" onclick="return confirm('<?php echo esc_js( __( 'Delete this category?', 'portfolio-showcase' ) ); ?>');"><?php esc_html_e( 'Delete', 'portfolio-showcase' ); ?></a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="6"><?php esc_html_e( 'No categories found.', 'portfolio-showcase' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
			<?php if ( empty( $search ) && ! is_wp_error( $terms ) && count( $terms ) > 1 ) : ?>
				<p class="epsw-field-hint"><?php esc_html_e( 'Drag rows by the ⋮⋮ handle to change display order on the frontend.', 'portfolio-showcase' ); ?></p>
			<?php endif; ?>
		</div>

		<div class="epsw-admin-card">
			<h2><?php echo $edit_term && ! is_wp_error( $edit_term ) ? esc_html__( 'Edit Category', 'portfolio-showcase' ) : esc_html__( 'Add New Category', 'portfolio-showcase' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="epsw_save_category" />
				<input type="hidden" name="term_id" value="<?php echo esc_attr( $edit_term && ! is_wp_error( $edit_term ) ? $edit_term->term_id : 0 ); ?>" />
				<?php wp_nonce_field( 'epsw_save_category' ); ?>

				<div class="epsw-field">
					<label for="epsw-cat-name"><?php esc_html_e( 'Category Name', 'portfolio-showcase' ); ?></label>
					<input type="text" id="epsw-cat-name" name="name" required class="epsw-input" value="<?php echo esc_attr( $edit_term && ! is_wp_error( $edit_term ) ? $edit_term->name : '' ); ?>" />
				</div>

				<div class="epsw-field">
					<label><?php esc_html_e( 'Category Icon', 'portfolio-showcase' ); ?></label>
					<p class="epsw-field-hint"><?php esc_html_e( 'Supported: SVG, PNG, JPG, JPEG, WEBP (max 2MB).', 'portfolio-showcase' ); ?></p>
					<div class="epsw-media-uploader">
						<input type="hidden" name="icon_id" id="epsw-cat-icon-id" value="<?php echo esc_attr( $edit_icon_id ); ?>" />
						<div class="epsw-icon-preview" id="epsw-cat-icon-preview">
							<?php if ( $edit_icon_url ) : ?>
								<img src="<?php echo esc_url( $edit_icon_url ); ?>" alt="" style="max-width: 100px; max-height: 100px; display: block; margin-bottom: 10px;" />
							<?php endif; ?>
						</div>
						<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-media-upload-btn" data-target-id="epsw-cat-icon-id" data-preview-id="epsw-cat-icon-preview">
							<?php echo $edit_icon_url ? esc_html__( 'Change Icon', 'portfolio-showcase' ) : esc_html__( 'Upload Icon', 'portfolio-showcase' ); ?>
						</button>
						<?php if ( $edit_icon_url ) : ?>
							<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-media-remove-btn" data-target-id="epsw-cat-icon-id" data-preview-id="epsw-cat-icon-preview" style="margin-left: 10px;">
								<?php esc_html_e( 'Remove Icon', 'portfolio-showcase' ); ?>
							</button>
						<?php endif; ?>
					</div>
				</div>

				<button type="submit" class="epsw-btn-admin epsw-btn-admin-primary epsw-btn-full">
					<?php echo $edit_term && ! is_wp_error( $edit_term ) ? esc_html__( 'Update Category', 'portfolio-showcase' ) : esc_html__( 'Add Category', 'portfolio-showcase' ); ?>
				</button>
			</form>
		</div>
	</div>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-footer.php'; ?>
