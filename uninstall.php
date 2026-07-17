<?php
/**
 * Fires when the plugin is deleted from the Plugins screen.
 * Removes all plugin data: projects, taxonomies/terms, term meta,
 * custom DB table, and plugin options.
 *
 * @package PortfolioShowcase
 */

// Block direct access - WordPress defines WP_UNINSTALL_PLUGIN when this
// file is legitimately invoked from the uninstall process.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

/**
 * Only remove data if the site owner opted in via the "Remove data on
 * uninstall" setting (stored in epsw_settings). Default is to keep data
 * safe unless explicitly requested, preventing accidental data loss.
 */
$settings    = get_option( 'epsw_settings', array() );
$remove_data = isset( $settings['remove_data_on_uninstall'] ) && $settings['remove_data_on_uninstall'];

if ( ! $remove_data ) {
	return;
}

// -----------------------------------------------------------------------
// Remove all projects (custom post type) and their meta/attachments refs.
// -----------------------------------------------------------------------
$project_ids = get_posts(
	array(
		'post_type'      => 'epsw_project',
		'post_status'    => 'any',
		'numberposts'    => -1,
		'fields'         => 'ids',
		'suppress_filters' => true,
	)
);

foreach ( $project_ids as $project_id ) {
	wp_delete_post( $project_id, true );
}

// -----------------------------------------------------------------------
// Remove taxonomy terms (categories + technologies) and their term meta.
// -----------------------------------------------------------------------
foreach ( array( 'epsw_category', 'epsw_technology' ) as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, $taxonomy );
		}
	}
}

// -----------------------------------------------------------------------
// Drop custom table used for saved/generated shortcodes.
// -----------------------------------------------------------------------
$table_name = $wpdb->prefix . 'epsw_shortcodes';
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

// -----------------------------------------------------------------------
// Remove options.
// -----------------------------------------------------------------------
delete_option( 'epsw_settings' );
delete_option( 'epsw_db_version' );

// Clear any cached data.
wp_cache_flush();
