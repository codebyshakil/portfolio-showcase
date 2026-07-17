<?php
/**
 * View: Project add/edit form.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$project_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$is_edit    = 'edit' === $action && $project_id > 0;

$title        = '';
$description  = '';
$details_url  = '';
$demo_url     = '';
$status       = 'publish';
$order        = 0;
$featured     = false;
$image_id     = 0;
$image_url    = '';
$selected_cats  = array();
$selected_techs = array();

if ( $is_edit ) {
	$post = get_post( $project_id );

	if ( ! $post || 'epsw_project' !== $post->post_type ) {
		echo '<div class="wrap"><p>' . esc_html__( 'Project not found.', 'portfolio-showcase' ) . '</p></div>';
		return;
	}

	$title       = get_the_title( $post );
	$description = get_post_meta( $post->ID, '_epsw_description', true );
	$details_url = get_post_meta( $post->ID, '_epsw_details_url', true );
	$demo_url    = get_post_meta( $post->ID, '_epsw_demo_url', true );
	$status      = $post->post_status;
	$order       = (int) $post->menu_order;
	$featured    = '1' === get_post_meta( $post->ID, '_epsw_featured', true );
	$image_id    = get_post_thumbnail_id( $post );
	$image_url   = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';

	$selected_cats  = wp_list_pluck( wp_get_post_terms( $post->ID, 'epsw_category' ), 'term_id' );
	$selected_techs = wp_list_pluck( wp_get_post_terms( $post->ID, 'epsw_technology' ), 'term_id' );
}

$all_categories   = get_terms( array( 'taxonomy' => 'epsw_category', 'hide_empty' => false ) );
$all_technologies = get_terms( array( 'taxonomy' => 'epsw_technology', 'hide_empty' => false ) );

$back_url = admin_url( 'admin.php?page=epsw-projects' );
?>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-header.php'; ?>
	<div class="epsw-admin-header">
		<h1><?php echo $is_edit ? esc_html__( 'Edit Project', 'portfolio-showcase' ) : esc_html__( 'Add Project', 'portfolio-showcase' ); ?></h1>
		<a href="<?php echo esc_url( $back_url ); ?>" class="epsw-btn-admin epsw-btn-admin-ghost"><?php esc_html_e( '← Back to Projects', 'portfolio-showcase' ); ?></a>
	</div>

	<div class="epsw-admin-card">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="action" value="epsw_save_project" />
			<input type="hidden" name="project_id" value="<?php echo esc_attr( $project_id ); ?>" />
			<?php wp_nonce_field( 'epsw_save_project' ); ?>

			<div class="epsw-form-grid">
				<div class="epsw-form-main">

					<div class="epsw-field">
						<label for="epsw-title"><?php esc_html_e( 'Project Title', 'portfolio-showcase' ); ?> <span class="epsw-required">*</span></label>
						<input type="text" id="epsw-title" name="title" value="<?php echo esc_attr( $title ); ?>" required class="epsw-input" />
					</div>

					<div class="epsw-field">
						<label for="epsw-description"><?php esc_html_e( 'Short Description', 'portfolio-showcase' ); ?></label>
						<textarea id="epsw-description" name="description" rows="4" class="epsw-input"><?php echo esc_textarea( $description ); ?></textarea>
					</div>

					<div class="epsw-field-row">
						<div class="epsw-field">
							<label for="epsw-details-url"><?php esc_html_e( 'Details / View Project URL', 'portfolio-showcase' ); ?></label>
							<input type="url" id="epsw-details-url" name="details_url" value="<?php echo esc_attr( $details_url ); ?>" class="epsw-input" placeholder="https://" />
						</div>
						<div class="epsw-field">
							<label for="epsw-demo-url"><?php esc_html_e( 'Live Demo URL', 'portfolio-showcase' ); ?></label>
							<input type="url" id="epsw-demo-url" name="demo_url" value="<?php echo esc_attr( $demo_url ); ?>" class="epsw-input" placeholder="https://" />
						</div>
					</div>

					<div class="epsw-field-row">
						<div class="epsw-field">
							<label for="epsw-status"><?php esc_html_e( 'Status', 'portfolio-showcase' ); ?></label>
							<select id="epsw-status" name="status" class="epsw-input">
								<option value="publish" <?php selected( $status, 'publish' ); ?>><?php esc_html_e( 'Published', 'portfolio-showcase' ); ?></option>
								<option value="draft" <?php selected( $status, 'draft' ); ?>><?php esc_html_e( 'Hidden', 'portfolio-showcase' ); ?></option>
							</select>
						</div>
						<div class="epsw-field">
							<label for="epsw-order"><?php esc_html_e( 'Order Number', 'portfolio-showcase' ); ?></label>
							<input type="number" id="epsw-order" name="order" value="<?php echo esc_attr( $order ); ?>" class="epsw-input" />
						</div>
					</div>

					<div class="epsw-field">
						<label class="epsw-checkbox-item epsw-featured-toggle">
							<input type="checkbox" name="featured" value="1" <?php checked( $featured ); ?> />
							<?php esc_html_e( 'Featured project — show this first in the grid', 'portfolio-showcase' ); ?>
						</label>
					</div>

					<div class="epsw-field-row">
						<div class="epsw-field">
							<label><?php esc_html_e( 'Categories', 'portfolio-showcase' ); ?></label>
							<div class="epsw-checkbox-list">
								<?php if ( ! is_wp_error( $all_categories ) ) : ?>
									<?php foreach ( $all_categories as $term ) : ?>
										<label class="epsw-checkbox-item">
											<input type="checkbox" name="categories[]" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( $term->term_id, $selected_cats, true ) ); ?> />
											<?php echo esc_html( $term->name ); ?>
										</label>
									<?php endforeach; ?>
								<?php endif; ?>
								<?php if ( is_wp_error( $all_categories ) || empty( $all_categories ) ) : ?>
									<p class="epsw-empty-hint"><?php esc_html_e( 'No categories yet.', 'portfolio-showcase' ); ?></p>
								<?php endif; ?>
							</div>
						</div>
						<div class="epsw-field">
							<label><?php esc_html_e( 'Technologies', 'portfolio-showcase' ); ?></label>
							<div class="epsw-checkbox-list">
								<?php if ( ! is_wp_error( $all_technologies ) ) : ?>
									<?php foreach ( $all_technologies as $term ) : ?>
										<label class="epsw-checkbox-item">
											<input type="checkbox" name="technologies[]" value="<?php echo esc_attr( $term->term_id ); ?>" <?php checked( in_array( $term->term_id, $selected_techs, true ) ); ?> />
											<?php echo esc_html( $term->name ); ?>
										</label>
									<?php endforeach; ?>
								<?php endif; ?>
								<?php if ( is_wp_error( $all_technologies ) || empty( $all_technologies ) ) : ?>
									<p class="epsw-empty-hint"><?php esc_html_e( 'No technologies yet.', 'portfolio-showcase' ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					</div>

				</div>

				<div class="epsw-form-side">
					<div class="epsw-field">
						<label><?php esc_html_e( 'Project Image', 'portfolio-showcase' ); ?></label>
						<div class="epsw-media-picker" id="epsw-project-image-picker">
							<input type="hidden" name="image_id" id="epsw-image-id" value="<?php echo esc_attr( $image_id ); ?>" />
							<div class="epsw-media-preview" id="epsw-image-preview">
								<?php if ( $image_url ) : ?>
									<img src="<?php echo esc_url( $image_url ); ?>" alt="" />
								<?php endif; ?>
							</div>
							<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-select-image-btn">
								<?php echo $image_url ? esc_html__( 'Change Image', 'portfolio-showcase' ) : esc_html__( 'Select Image', 'portfolio-showcase' ); ?>
							</button>
							<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-remove-image-btn" <?php echo $image_url ? '' : 'style="display:none;"'; ?>>
								<?php esc_html_e( 'Remove', 'portfolio-showcase' ); ?>
							</button>
						</div>
					</div>

					<button type="submit" class="epsw-btn-admin epsw-btn-admin-primary epsw-btn-full">
						<?php echo $is_edit ? esc_html__( 'Update Project', 'portfolio-showcase' ) : esc_html__( 'Create Project', 'portfolio-showcase' ); ?>
					</button>
				</div>
			</div>
		</form>
	</div>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-footer.php'; ?>
