<?php
/**
 * Plugin Name:       Portfolio Showcase
 * Plugin URI:        https://wordpress.org/plugins/portfolio-showcase/
 * Description:       A lightweight, modern, dark-glassmorphism project portfolio showcase with category/technology filtering, AJAX filtering, unlimited shortcodes and a full admin management panel.
 * Version:           1.0.17
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       portfolio-showcase
 * Domain Path:       /languages
 *
 * @package PortfolioShowcase
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Core plugin constants.
// ---------------------------------------------------------------------------
define( 'EPSW_VERSION', '1.0.21' );
define( 'EPSW_PLUGIN_FILE', __FILE__ );
define( 'EPSW_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'EPSW_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'EPSW_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'EPSW_MIN_PHP', '8.2' );

/**
 * PHP version guard. Bail out cleanly if the host is running an
 * unsupported PHP version instead of causing a fatal parse error.
 */
if ( version_compare( PHP_VERSION, EPSW_MIN_PHP, '<' ) ) {
	add_action(
		'admin_notices',
		function () {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Portfolio Showcase requires PHP 8.2 or higher. Please upgrade PHP to activate this plugin.', 'portfolio-showcase' );
			echo '</p></div>';
		}
	);
	return;
}

/**
 * Simple PSR-4-ish autoloader for the plugin's own classes.
 * Maps class name prefixes to file locations following the
 * `class-epsw-xxx.php` naming convention used across the plugin.
 */
spl_autoload_register(
	function ( $class ) {
		if ( ! str_starts_with( $class, 'EPSW_' ) ) {
			return;
		}

		$relative = strtolower( str_replace( '_', '-', substr( $class, 5 ) ) );
		$filename = 'class-epsw-' . $relative . '.php';

		$locations = array(
			EPSW_PLUGIN_DIR . 'includes/' . $filename,
			EPSW_PLUGIN_DIR . 'admin/' . $filename,
			EPSW_PLUGIN_DIR . 'frontend/' . $filename,
		);

		foreach ( $locations as $location ) {
			if ( file_exists( $location ) ) {
				require_once $location;
				return;
			}
		}
	}
);

/**
 * Activation hook: creates DB tables, registers CPT/taxonomies so rewrite
 * rules exist, seeds the default shortcode row, and flushes rewrite rules.
 */
function epsw_activate_plugin() {
	require_once EPSW_PLUGIN_DIR . 'includes/class-epsw-activator.php';
	EPSW_Activator::activate();
}
register_activation_hook( __FILE__, 'epsw_activate_plugin' );

/**
 * Deactivation hook: flush rewrite rules only. Data is preserved.
 */
function epsw_deactivate_plugin() {
	require_once EPSW_PLUGIN_DIR . 'includes/class-epsw-deactivator.php';
	EPSW_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'epsw_deactivate_plugin' );

/**
 * Boots the plugin once all plugins are loaded.
 */
function epsw_init_plugin() {
	load_plugin_textdomain( 'portfolio-showcase', false, dirname( EPSW_PLUGIN_BASENAME ) . '/languages' );

	// Core registrations (custom post type + taxonomies).
	new EPSW_Post_Types();

	// Shortcode engine.
	new EPSW_Shortcode();

	// AJAX handlers (frontend filtering + admin icon uploads etc).
	new EPSW_Ajax();

	// Admin area.
	if ( is_admin() ) {
		new EPSW_Admin();
	}

	// Public facing.
	new EPSW_Frontend();
}
add_action( 'plugins_loaded', 'epsw_init_plugin' );