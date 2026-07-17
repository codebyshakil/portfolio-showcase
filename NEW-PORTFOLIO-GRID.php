<?php
/**
 * Template: portfolio grid + filter bar - EXACT MATCH to filter-design-v2.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$all_categories   = get_terms( array( 'taxonomy' => 'epsw_category', 'hide_empty' => true ) );
$all_technologies = get_terms( array( 'taxonomy' => 'epsw_technology', 'hide_empty' => true ) );

$initial_args  = EPSW_Shortcode::build_query_args( $categories, $technologies, $per_page, 1 );
$initial_query = new WP_Query( $initial_args );
?>
<div
	id="<?php echo esc_attr( $instance_id ); ?>"
	class="epsw-portfolio epsw-columns-<?php echo esc_attr( $columns ); ?>"
	data-columns="<?php echo esc_attr( $columns ); ?>"
	data-per-page="<?php echo esc_attr( $per_page ); ?>"
	data-preset-categories="<?php echo esc_attr( implode( ',', $categories ) ); ?>"
	data-preset-technologies="<?php echo esc_attr( implode( ',', $technologies ) ); ?>"
>

	<?php if ( ! is_wp_error( $all_categories ) && ! is_wp_error( $all_technologies ) && ( ! empty( $all_categories ) || ! empty( $all_technologies ) ) ) : ?>
	<div class="epsw-filter-desktop" id="filterCard">
		<div class="epsw-filter-header">
			<div class="epsw-filter-title">
				<svg class="epsw-filter-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
					<line x1="4" y1="6" x2="20" y2="6"/>
					<circle cx="9" cy="6" r="2" fill="currentColor" stroke="none"/>
					<line x1="4" y1="12" x2="20" y2="12"/>
					<circle cx="16" cy="12" r="2" fill="currentColor" stroke="none"/>
					<line x1="4" y1="18" x2="20" y2="18"/>
					<circle cx="11" cy="18" r="2" fill="currentColor" stroke="none"/>
				</svg>
				<span><?php esc_html_e( 'Filters', 'portfolio-showcase' ); ?></span>
			</div>
			<button class="epsw-filter-mobile-toggle" id="collapseBtnFilter">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="6 9 12 15 18 9"/>
				</svg>
			</button>

			<?php if ( ! empty( $all_categories ) ) : ?>
			<div class="epsw-filter-section">
				<label class="epsw-filter-label"><?php esc_html_e( 'Category', 'portfolio-showcase' ); ?></label>
				<div class="epsw-filter-dropdown" data-filter-type="category">
					<button type="button" class="epsw-filter-select">
