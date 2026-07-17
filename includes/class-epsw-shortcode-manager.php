<?php
/**
 * Manages the custom database table that stores generated/saved
 * shortcode combinations shown on the admin Shortcodes page.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Shortcode_Manager
 */
class EPSW_Shortcode_Manager {

	/**
	 * Returns the fully prefixed table name.
	 *
	 * @return string
	 */
	private static function table() {
		global $wpdb;
		return $wpdb->prefix . 'epsw_shortcodes';
	}

	/**
	 * Fetches all saved shortcodes, default entry first.
	 *
	 * @return array[]
	 */
	public static function get_all() {
		global $wpdb;
		$table = self::table();

		return $wpdb->get_results(
			"SELECT * FROM {$table} ORDER BY is_default DESC, created_at DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);
	}

	/**
	 * Fetches a single saved shortcode by ID.
	 *
	 * @param int $id Row ID.
	 * @return array|null
	 */
	public static function get( $id ) {
		global $wpdb;
		$table = self::table();

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			ARRAY_A
		);

		return $row ? $row : null;
	}

	/**
	 * Inserts a new generated shortcode row.
	 *
	 * @param string   $label        Human readable label.
	 * @param string[] $categories   Category slugs.
	 * @param string[] $technologies Technology slugs.
	 * @return int|false Insert ID or false on failure.
	 */
	public static function create( $label, array $categories, array $technologies ) {
		global $wpdb;
		$table = self::table();

		$shortcode_text = EPSW_Helpers::build_shortcode_text( $categories, $technologies );

		$inserted = $wpdb->insert(
			$table,
			array(
				'label'          => sanitize_text_field( $label ),
				'shortcode_text' => $shortcode_text,
				'categories'     => implode( ',', $categories ),
				'technologies'   => implode( ',', $technologies ),
				'is_default'     => 0,
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Deletes a saved shortcode row. The default row (is_default = 1)
	 * can never be deleted, per the plugin specification.
	 *
	 * @param int $id Row ID.
	 * @return bool
	 */
	public static function delete( $id ) {
		global $wpdb;
		$table = self::table();

		$row = self::get( $id );

		if ( ! $row || (int) $row['is_default'] === 1 ) {
			return false;
		}

		return (bool) $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
	}
}
