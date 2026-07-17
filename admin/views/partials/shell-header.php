<?php
/**
 * Partial: opens the shared dark-sidebar admin shell used by every
 * Portfolio Showcase screen. Include this at the top of a view, then
 * include shell-footer.php at the end.
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$epsw_current_page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : 'epsw-projects'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$epsw_nav_items = array(
	'epsw-projects'     => array(
		'label' => __( 'Projects', 'portfolio-showcase' ),
		'icon'  => '&#9635;',
	),
	'epsw-categories'   => array(
		'label' => __( 'Categories', 'portfolio-showcase' ),
		'icon'  => '&#9776;',
	),
	'epsw-technologies' => array(
		'label' => __( 'Technologies', 'portfolio-showcase' ),
		'icon'  => '&#9881;',
	),
	'epsw-shortcodes'   => array(
		'label' => __( 'Shortcodes', 'portfolio-showcase' ),
		'icon'  => '&#60;/&#62;',
	),
	'epsw-settings'     => array(
		'label' => __( 'Settings', 'portfolio-showcase' ),
		'icon'  => '&#9881;&#65039;',
	),
);
?>
<div class="wrap epsw-admin-wrap">
	<div class="epsw-shell">

		<aside class="epsw-sidebar">
			<div class="epsw-sidebar-brand">
				<span class="epsw-sidebar-logo">PS</span>
				<span class="epsw-sidebar-title"><?php esc_html_e( 'Portfolio Showcase', 'portfolio-showcase' ); ?></span>
			</div>

			<nav class="epsw-sidebar-nav">
				<?php foreach ( $epsw_nav_items as $slug => $item ) : ?>
					<a
						href="<?php echo esc_url( admin_url( 'admin.php?page=' . $slug ) ); ?>"
						class="epsw-sidebar-link<?php echo $epsw_current_page === $slug ? ' is-active' : ''; ?>"
					>
						<span class="epsw-sidebar-icon" aria-hidden="true"><?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span><?php echo esc_html( $item['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>

			<div class="epsw-sidebar-footer">
				<?php esc_html_e( 'Portfolio Showcase', 'portfolio-showcase' ); ?> v<?php echo esc_html( EPSW_VERSION ); ?>
			</div>
		</aside>

		<main class="epsw-content">
