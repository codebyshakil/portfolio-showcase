<?php
/**
 * Fired during plugin deactivation.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class EPSW_Deactivator
 */
class EPSW_Deactivator {

	/**
	 * Runs on plugin deactivation. Data is intentionally preserved;
	 * only rewrite rules are flushed.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}
}