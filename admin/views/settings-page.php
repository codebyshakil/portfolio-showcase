<?php
/**
 * View: Settings + Import / Export screen.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = get_option(
	'epsw_settings',
	array(
		'items_per_page'           => 9,
		'load_more_enabled'        => 1,
		'remove_data_on_uninstall' => 0,
	)
);

$export_url = wp_nonce_url(
	add_query_arg( array( 'action' => 'epsw_export_data' ), admin_url( 'admin-post.php' ) ),
	'epsw_export_data'
);
?>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-header.php'; ?>
	<div class="epsw-admin-header">
		<h1><?php esc_html_e( 'Settings', 'portfolio-showcase' ); ?></h1>
	</div>

	<div class="epsw-form-grid epsw-form-grid-narrow">

		<div class="epsw-admin-card">
			<h2><?php esc_html_e( 'General', 'portfolio-showcase' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="epsw_save_settings" />
				<?php wp_nonce_field( 'epsw_save_settings' ); ?>

				<div class="epsw-field">
					<label for="epsw-items-per-page"><?php esc_html_e( 'Projects per page / Load More batch size', 'portfolio-showcase' ); ?></label>
					<input type="number" min="1" id="epsw-items-per-page" name="items_per_page" value="<?php echo esc_attr( $settings['items_per_page'] ?? 9 ); ?>" class="epsw-input" />
				</div>

				<div class="epsw-field">
					<label class="epsw-checkbox-item">
						<input type="checkbox" name="load_more_enabled" value="1" <?php checked( ! empty( $settings['load_more_enabled'] ) ); ?> />
						<?php esc_html_e( 'Enable "Load More" pagination on the frontend grid', 'portfolio-showcase' ); ?>
					</label>
				</div>

				<div class="epsw-field">
					<label class="epsw-checkbox-item epsw-danger-toggle">
						<input type="checkbox" name="remove_data_on_uninstall" value="1" <?php checked( ! empty( $settings['remove_data_on_uninstall'] ) ); ?> />
						<?php esc_html_e( 'Permanently delete all projects, categories, technologies and shortcodes when this plugin is deleted', 'portfolio-showcase' ); ?>
					</label>
					<p class="epsw-field-hint"><?php esc_html_e( 'Unchecked by default. Deactivating the plugin never removes data — this only applies when you delete it from the Plugins screen.', 'portfolio-showcase' ); ?></p>
				</div>

				<button type="submit" class="epsw-btn-admin epsw-btn-admin-primary">
					<?php esc_html_e( 'Save Settings', 'portfolio-showcase' ); ?>
				</button>
			</form>
		</div>

		<div class="epsw-admin-card">
			<h2><?php esc_html_e( 'Export Data', 'portfolio-showcase' ); ?></h2>
			<p class="epsw-field-hint"><?php esc_html_e( 'Downloads a single JSON file containing all Projects, Categories, Technologies, Settings and saved Shortcodes.', 'portfolio-showcase' ); ?></p>
			<a href="<?php echo esc_url( $export_url ); ?>" class="epsw-btn-admin epsw-btn-admin-primary">
				<?php esc_html_e( 'Download Export (.json)', 'portfolio-showcase' ); ?>
			</a>
		</div>
	</div>

	<div class="epsw-admin-card">
		<h2><?php esc_html_e( 'Import Data', 'portfolio-showcase' ); ?></h2>
		<p class="epsw-field-hint">
			<?php esc_html_e( 'Restores Projects, Categories, Technologies, Settings and Shortcodes from a previously exported JSON file. Existing categories/technologies with the same slug are reused rather than duplicated. Projects and shortcodes are added as new entries.', 'portfolio-showcase' ); ?>
		</p>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="epsw-import-form">
			<input type="hidden" name="action" value="epsw_import_data" />
			<?php wp_nonce_field( 'epsw_import_data' ); ?>

			<div class="epsw-field">
				<input type="file" name="import_file" accept="application/json,.json" required class="epsw-input" />
			</div>

			<button type="submit" class="epsw-btn-admin epsw-btn-admin-ghost" onclick="return confirm('<?php echo esc_js( __( 'Import this file into your portfolio? This cannot be automatically undone.', 'portfolio-showcase' ) ); ?>');">
				<?php esc_html_e( 'Upload & Import', 'portfolio-showcase' ); ?>
			</button>
		</form>
	</div>
<?php include EPSW_PLUGIN_DIR . 'admin/views/partials/shell-footer.php'; ?>
