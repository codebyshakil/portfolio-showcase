<?php
/**
 * Fired during plugin activation.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Activator
 */
class EPSW_Activator {

	/**
	 * Current DB schema version. Bump this when the table structure changes
	 * so activate() can run the necessary dbDelta migration.
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Runs on plugin activation.
	 */
	public static function activate() {
		self::create_tables();
		self::seed_default_shortcode();
		self::maybe_set_default_settings();

		// Register CPT + taxonomies before flushing so rewrite rules exist.
		require_once EPSW_PLUGIN_DIR . 'includes/class-epsw-post-types.php';
		$post_types = new EPSW_Post_Types();
		$post_types->register_post_type();
		$post_types->register_taxonomies();

		flush_rewrite_rules();
	}

	/**
	 * Creates the custom database table used to store generated shortcodes.
	 */
	private static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name       = $wpdb->prefix . 'epsw_shortcodes';

		$sql = "CREATE TABLE {$table_name} (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			label VARCHAR(191) NOT NULL,
			shortcode_text VARCHAR(500) NOT NULL,
			categories VARCHAR(500) DEFAULT '' NOT NULL,
			technologies VARCHAR(500) DEFAULT '' NOT NULL,
			is_default TINYINT(1) DEFAULT 0 NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY  (id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'epsw_db_version', self::DB_VERSION );
	}

	/**
	 * Inserts the default, non-deletable [estel_portfolio] shortcode entry
	 * so it always shows up in the admin Shortcodes list. Only runs once.
	 */
	private static function seed_default_shortcode() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'epsw_shortcodes';

		$existing = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table_name} WHERE is_default = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				1
			)
		);

		if ( ! empty( $existing ) ) {
			return;
		}

		$wpdb->insert(
			$table_name,
			array(
				'label'          => __( 'Default (All Projects)', 'portfolio-showcase' ),
				'shortcode_text' => '[estel_portfolio]',
				'categories'     => '',
				'technologies'   => '',
				'is_default'     => 1,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s' )
		);
	}

	/**
	 * Ensures default plugin settings exist without overwriting existing ones.
	 */
	private static function maybe_set_default_settings() {
		if ( false === get_option( 'epsw_settings', false ) ) {
			add_option(
				'epsw_settings',
				array(
					'items_per_page'            => 9,
					'load_more_enabled'         => 1,
					'remove_data_on_uninstall'  => 0,
				)
			);
		}
	}
}
