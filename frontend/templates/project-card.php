<?php
/**
 * Template: single project card.
 *
 * @var array $project Project data array from EPSW_Helpers::get_project_data().
 *
 * @package PortfolioShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $project ) ) {
	return;
}

$cat_slugs  = wp_list_pluck( $project['categories'], 'slug' );
$tech_slugs = wp_list_pluck( $project['technologies'], 'slug' );
?>
<article
	class="epsw-card"
	data-categories="<?php echo esc_attr( implode( ',', $cat_slugs ) ); ?>"
	data-technologies="<?php echo esc_attr( implode( ',', $tech_slugs ) ); ?>"
>
	<div class="epsw-card-media">
		<?php if ( ! empty( $project['image_url'] ) ) : ?>
			<img
				src="<?php echo esc_url( $project['image_url'] ); ?>"
				alt="<?php echo esc_attr( $project['title'] ); ?>"
				loading="lazy"
				decoding="async"
				class="epsw-card-image"
			/>
		<?php else : ?>
			<div class="epsw-card-image epsw-card-image-placeholder" aria-hidden="true"></div>
		<?php endif; ?>
		<div class="epsw-card-glow" aria-hidden="true"></div>

		<?php if ( ! empty( $project['featured'] ) ) : ?>
			<span class="epsw-card-featured" title="<?php esc_attr_e( 'Featured Project', 'portfolio-showcase' ); ?>">&#9733; <?php esc_html_e( 'Featured', 'portfolio-showcase' ); ?></span>
		<?php endif; ?>

		<?php if ( ! empty( $project['categories'] ) ) : ?>
			<span class="epsw-card-category"><?php echo esc_html( $project['categories'][0]['name'] ); ?></span>
		<?php endif; ?>
	</div>

	<div class="epsw-card-body">
		<h3 class="epsw-card-title"><?php echo esc_html( $project['title'] ); ?></h3>

		<?php if ( ! empty( $project['description'] ) ) : ?>
			<p class="epsw-card-desc"><?php echo esc_html( wp_trim_words( $project['description'], 18 ) ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $project['technologies'] ) ) : ?>
		<div class="epsw-card-tech" role="list" aria-label="<?php esc_attr_e( 'Technologies used', 'portfolio-showcase' ); ?>">
			<?php foreach ( $project['technologies'] as $tech ) : ?>
				<span class="epsw-tech-icon" role="listitem" title="<?php echo esc_attr( $tech['name'] ); ?>">
					<?php if ( ! empty( $tech['icon_url'] ) ) : ?>
						<img src="<?php echo esc_url( $tech['icon_url'] ); ?>" alt="<?php echo esc_attr( $tech['name'] ); ?>" loading="lazy" />
					<?php else : ?>
						<span class="epsw-tech-fallback"><?php echo esc_html( mb_substr( $tech['name'], 0, 1 ) ); ?></span>
					<?php endif; ?>
				</span>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>

		<div class="epsw-card-actions">
			<?php if ( ! empty( $project['details_url'] ) ) : ?>
				<a href="<?php echo esc_url( $project['details_url'] ); ?>" class="epsw-btn epsw-btn-primary" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View Project', 'portfolio-showcase' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( ! empty( $project['demo_url'] ) ) : ?>
				<a href="<?php echo esc_url( $project['demo_url'] ); ?>" class="epsw-btn epsw-btn-ghost" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Live Demo', 'portfolio-showcase' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</article>
