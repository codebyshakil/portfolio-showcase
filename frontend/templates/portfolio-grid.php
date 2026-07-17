<?php
/**
 * Template: portfolio grid + filter bar.
 *
 * Expected variables in scope (set by EPSW_Shortcode::render()):
 *
 * @var string[] $categories   Pre-selected category slugs from shortcode atts.
 * @var string[] $technologies Pre-selected technology slugs from shortcode atts.
 * @var int      $columns      Number of grid columns (1-4).
 * @var int      $per_page     Items per page / load-more batch size.
 * @var string   $instance_id  Unique DOM id for this shortcode instance.
 *
 * @package PortfolioShowcase
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
	<div class="epsw-filter-bar" role="region" aria-label="<?php esc_attr_e( 'Project filters', 'portfolio-showcase' ); ?>">

		<?php if ( ! empty( $all_categories ) ) : ?>
		<div class="epsw-filter-group" data-filter-type="category">
			<button type="button" class="epsw-filter-chip is-active" data-slug="">
				<?php esc_html_e( 'All Categories', 'portfolio-showcase' ); ?>
			</button>
			<?php foreach ( $all_categories as $term ) : ?>
				<button
					type="button"
					class="epsw-filter-chip<?php echo in_array( $term->slug, $categories, true ) ? ' is-active' : ''; ?>"
					data-slug="<?php echo esc_attr( $term->slug ); ?>"
				>
					<?php echo esc_html( $term->name ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<?php if ( ! empty( $all_technologies ) ) : ?>
		<div class="epsw-filter-group" data-filter-type="technology">
			<button type="button" class="epsw-filter-chip is-active" data-slug="">
				<?php esc_html_e( 'All Technologies', 'portfolio-showcase' ); ?>
			</button>
			<?php foreach ( $all_technologies as $term ) : ?>
				<button
					type="button"
					class="epsw-filter-chip<?php echo in_array( $term->slug, $technologies, true ) ? ' is-active' : ''; ?>"
					data-slug="<?php echo esc_attr( $term->slug ); ?>"
				>
					<?php echo esc_html( $term->name ); ?>
				</button>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

	</div>
	<?php endif; ?>

	<div class="epsw-grid-wrapper">
		<div class="epsw-grid" data-columns="<?php echo esc_attr( $columns ); ?>">
			<?php
			if ( $initial_query->have_posts() ) :
				while ( $initial_query->have_posts() ) :
					$initial_query->the_post();
					$project = EPSW_Helpers::get_project_data( get_the_ID() );
					include EPSW_PLUGIN_DIR . 'frontend/templates/project-card.php';
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<p class="epsw-no-results"><?php esc_html_e( 'No projects found.', 'portfolio-showcase' ); ?></p>
				<?php
			endif;
			?>
		</div>

		<?php if ( $initial_query->max_num_pages > 1 ) : ?>
		<div class="epsw-load-more-wrap">
			<button type="button" class="epsw-btn epsw-btn-primary epsw-load-more" data-page="1" data-max-pages="<?php echo esc_attr( $initial_query->max_num_pages ); ?>">
				<span class="epsw-btn-label"><?php esc_html_e( 'Load More', 'portfolio-showcase' ); ?></span>
				<span class="epsw-spinner" aria-hidden="true"></span>
			</button>
		</div>
		<?php endif; ?>
	</div>
</div>
