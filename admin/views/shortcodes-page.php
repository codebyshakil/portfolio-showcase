<?php
/**
 * View: Shortcode generator + saved shortcodes list.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_categories   = get_terms( array( 'taxonomy' => 'epsw_category', 'hide_empty' => false ) );
$all_technologies = get_terms( array( 'taxonomy' => 'epsw_technology', 'hide_empty' => false ) );
$saved_shortcodes = EPSW_Shortcode_Manager::get_all();
?>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-header.php'; ?>
	<div class="epsw-admin-header">
		<h1><?php esc_html_e( 'Shortcodes', 'portfolio-showcase' ); ?></h1>
	</div>

	<div class="epsw-form-grid epsw-form-grid-narrow">

		<div class="epsw-admin-card">
			<h2><?php esc_html_e( 'Saved Shortcodes', 'portfolio-showcase' ); ?></h2>
			<table class="epsw-admin-table widefat">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Label', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Shortcode', 'portfolio-showcase' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'portfolio-showcase' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $saved_shortcodes ) ) : ?>
						<?php foreach ( $saved_shortcodes as $row ) : ?>
							<?php
							$delete_url = wp_nonce_url(
								add_query_arg(
									array(
										'action' => 'epsw_delete_shortcode',
										'id'     => $row['id'],
									),
									admin_url( 'admin-post.php' )
								),
								'epsw_delete_shortcode_' . $row['id']
							);
							?>
							<tr>
								<td><?php echo esc_html( $row['label'] ); ?></td>
								<td><code class="epsw-shortcode-text"><?php echo esc_html( $row['shortcode_text'] ); ?></code></td>
								<td>
									<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-copy-shortcode" data-shortcode="<?php echo esc_attr( $row['shortcode_text'] ); ?>">
										<?php esc_html_e( 'Copy', 'portfolio-showcase' ); ?>
									</button>
									<?php if ( '1' === (string) $row['is_default'] ) : ?>
										<span class="epsw-badge epsw-badge-muted"><?php esc_html_e( 'Default (locked)', 'portfolio-showcase' ); ?></span>
									<?php else : ?>
										<a href="<?php echo esc_url( $delete_url ); ?>" class="epsw-row-delete" onclick="return confirm('<?php echo esc_js( __( 'Delete this shortcode?', 'portfolio-showcase' ) ); ?>');"><?php esc_html_e( 'Delete', 'portfolio-showcase' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr><td colspan="3"><?php esc_html_e( 'No shortcodes saved yet.', 'portfolio-showcase' ); ?></td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>

		<div class="epsw-admin-card">
			<h2><?php esc_html_e( 'Generate New Shortcode', 'portfolio-showcase' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="epsw-shortcode-form">
				<input type="hidden" name="action" value="epsw_save_shortcode" />
				<?php wp_nonce_field( 'epsw_save_shortcode' ); ?>

				<div class="epsw-field">
					<label for="epsw-shortcode-label"><?php esc_html_e( 'Label', 'portfolio-showcase' ); ?></label>
					<input type="text" id="epsw-shortcode-label" name="label" class="epsw-input" placeholder="<?php esc_attr_e( 'e.g. WordPress Projects', 'portfolio-showcase' ); ?>" />
				</div>

				<div class="epsw-field">
					<label><?php esc_html_e( 'Categories', 'portfolio-showcase' ); ?></label>
					<div class="epsw-checkbox-list">
						<?php if ( ! is_wp_error( $all_categories ) && ! empty( $all_categories ) ) : ?>
							<?php foreach ( $all_categories as $term ) : ?>
								<label class="epsw-checkbox-item">
									<input type="checkbox" class="epsw-sc-category" name="categories[]" value="<?php echo esc_attr( $term->slug ); ?>" />
									<?php echo esc_html( $term->name ); ?>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="epsw-empty-hint"><?php esc_html_e( 'No categories yet.', 'portfolio-showcase' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="epsw-field">
					<label><?php esc_html_e( 'Technologies', 'portfolio-showcase' ); ?></label>
					<div class="epsw-checkbox-list">
						<?php if ( ! is_wp_error( $all_technologies ) && ! empty( $all_technologies ) ) : ?>
							<?php foreach ( $all_technologies as $term ) : ?>
								<label class="epsw-checkbox-item">
									<input type="checkbox" class="epsw-sc-technology" name="technologies[]" value="<?php echo esc_attr( $term->slug ); ?>" />
									<?php echo esc_html( $term->name ); ?>
								</label>
							<?php endforeach; ?>
						<?php else : ?>
							<p class="epsw-empty-hint"><?php esc_html_e( 'No technologies yet.', 'portfolio-showcase' ); ?></p>
						<?php endif; ?>
					</div>
				</div>

				<div class="epsw-field">
					<label><?php esc_html_e( 'Live Preview', 'portfolio-showcase' ); ?></label>
					<code class="epsw-shortcode-text" id="epsw-live-shortcode">[estel_portfolio]</code>
				</div>

				<div class="epsw-field-row">
					<button type="button" class="epsw-btn-admin epsw-btn-admin-ghost epsw-btn-full" id="epsw-preview-shortcode-btn">
						<?php esc_html_e( 'Preview', 'portfolio-showcase' ); ?>
					</button>
					<button type="submit" class="epsw-btn-admin epsw-btn-admin-primary epsw-btn-full">
						<?php esc_html_e( 'Generate & Save', 'portfolio-showcase' ); ?>
					</button>
				</div>
			</form>

			<div id="epsw-shortcode-preview-result" class="epsw-shortcode-preview"></div>
		</div>
	</div>

	<div class="epsw-admin-card">
		<h2><?php esc_html_e( 'How to Use', 'portfolio-showcase' ); ?></h2>
		<ul class="epsw-help-list">
			<li><code>[estel_portfolio]</code> — <?php esc_html_e( 'shows every published project.', 'portfolio-showcase' ); ?></li>
			<li><code>[estel_portfolio category="wordpress"]</code> — <?php esc_html_e( 'filters by a single category.', 'portfolio-showcase' ); ?></li>
			<li><code>[estel_portfolio category="wordpress,laravel"]</code> — <?php esc_html_e( 'filters by multiple categories.', 'portfolio-showcase' ); ?></li>
			<li><code>[estel_portfolio technology="react,nodejs"]</code> — <?php esc_html_e( 'filters by technology.', 'portfolio-showcase' ); ?></li>
			<li><code>[estel_portfolio category="business" technology="laravel"]</code> — <?php esc_html_e( 'combines category and technology filters.', 'portfolio-showcase' ); ?></li>
		</ul>
	</div>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-footer.php'; ?>
