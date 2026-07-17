<?php
/**
 * Template: portfolio grid + filter bar (matching filter-design-v2.html exactly)
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
	<div class="epsw-filter-desktop epsw-popular-collapsed" id="filterCard">
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
		</div>

		<div class="epsw-filter-content">
			<?php if ( ! empty( $all_categories ) ) : ?>
			<div class="epsw-filter-section">
				<label class="epsw-filter-label"><?php esc_html_e( 'Category', 'portfolio-showcase' ); ?></label>
				<div class="epsw-filter-dropdown" data-filter-type="category">
					<button type="button" class="epsw-filter-select">
						<span class="epsw-filter-select-icon">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
								<rect x="3" y="3" width="7" height="7" rx="1.5"/>
								<rect x="14" y="3" width="7" height="7" rx="1.5"/>
								<rect x="3" y="14" width="7" height="7" rx="1.5"/>
								<rect x="14" y="14" width="7" height="7" rx="1.5"/>
							</svg>
						</span>
						<span class="epsw-filter-select-text"><?php esc_html_e( 'All Categories', 'portfolio-showcase' ); ?></span>
						<svg class="epsw-filter-select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="6 9 12 15 18 9"/>
						</svg>
					</button>
					<div class="epsw-filter-menu">
						<div class="epsw-search-box">
							<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
								<circle cx="11" cy="11" r="7"/>
								<line x1="21" y1="21" x2="16.65" y2="16.65"/>
							</svg>
							<input type="text" placeholder="<?php esc_attr_e( 'Search categories...', 'portfolio-showcase' ); ?>" />
						</div>
						<div class="epsw-opt-list">
							<button type="button" class="epsw-filter-option is-active" data-slug="" data-name="<?php esc_attr_e( 'All Categories', 'portfolio-showcase' ); ?>">
								<span class="epsw-filter-option-icon">
									<svg viewBox="0 0 16 16" fill="none">
										<path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
								<?php esc_html_e( 'All Categories', 'portfolio-showcase' ); ?>
							</button>
							<?php foreach ( $all_categories as $term ) : ?>
								<?php
								$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
								$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
								?>
								<button
									type="button"
									class="epsw-filter-option<?php echo in_array( $term->slug, $categories, true ) ? ' is-active' : ''; ?>"
									data-slug="<?php echo esc_attr( $term->slug ); ?>"
									data-name="<?php echo esc_attr( $term->name ); ?>"
								>
									<span class="epsw-filter-option-icon">
										<?php if ( $icon_url ) : ?>
											<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
										<?php endif; ?>
										<svg viewBox="0 0 16 16" fill="none">
											<path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
									<?php echo esc_html( $term->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						<div class="epsw-panel-actions">
							<button type="button" class="epsw-mini-clear"><?php esc_html_e( 'Clear', 'portfolio-showcase' ); ?></button>
							<button type="button" class="epsw-mini-apply"><?php esc_html_e( 'Apply', 'portfolio-showcase' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $all_technologies ) ) : ?>
			<div class="epsw-filter-section">
				<label class="epsw-filter-label"><?php esc_html_e( 'Technology', 'portfolio-showcase' ); ?></label>
				<div class="epsw-filter-dropdown" data-filter-type="technology">
					<button type="button" class="epsw-filter-select">
						<span class="epsw-filter-select-icon">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
							</svg>
						</span>
						<span class="epsw-filter-select-text"><?php esc_html_e( 'All Technologies', 'portfolio-showcase' ); ?></span>
						<svg class="epsw-filter-select-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
							<polyline points="6 9 12 15 18 9"/>
						</svg>
					</button>
					<div class="epsw-filter-menu">
						<div class="epsw-search-box">
							<svg class="ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round">
								<circle cx="11" cy="11" r="7"/>
								<line x1="21" y1="21" x2="16.65" y2="16.65"/>
							</svg>
							<input type="text" placeholder="<?php esc_attr_e( 'Search technologies...', 'portfolio-showcase' ); ?>" />
						</div>
						<div class="epsw-opt-list">
							<button type="button" class="epsw-filter-option is-active" data-slug="" data-name="<?php esc_attr_e( 'All Technologies', 'portfolio-showcase' ); ?>">
								<span class="epsw-filter-option-icon">
									<svg viewBox="0 0 16 16" fill="none">
										<path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
									</svg>
								</span>
								<?php esc_html_e( 'All Technologies', 'portfolio-showcase' ); ?>
							</button>
							<?php foreach ( $all_technologies as $term ) : ?>
								<?php
								$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
								$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
								?>
								<button
									type="button"
									class="epsw-filter-option<?php echo in_array( $term->slug, $technologies, true ) ? ' is-active' : ''; ?>"
									data-slug="<?php echo esc_attr( $term->slug ); ?>"
									data-name="<?php echo esc_attr( $term->name ); ?>"
								>
									<span class="epsw-filter-option-icon">
										<?php if ( $icon_url ) : ?>
											<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
										<?php endif; ?>
										<svg viewBox="0 0 16 16" fill="none">
											<path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
										</svg>
									</span>
									<?php echo esc_html( $term->name ); ?>
								</button>
							<?php endforeach; ?>
						</div>
						<div class="epsw-panel-actions">
							<button type="button" class="epsw-mini-clear"><?php esc_html_e( 'Clear', 'portfolio-showcase' ); ?></button>
							<button type="button" class="epsw-mini-apply"><?php esc_html_e( 'Apply', 'portfolio-showcase' ); ?></button>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div class="epsw-filter-spacer"></div>

			<button type="button" class="epsw-popular-toggle" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle popular filters', 'portfolio-showcase' ); ?>">
				<svg class="epsw-popular-toggle-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="6 9 12 15 18 9"/>
				</svg>
			</button>

			<button type="button" class="epsw-filter-btn epsw-filter-btn-clear">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
					<polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
				</svg>
				<?php esc_html_e( 'Clear All', 'portfolio-showcase' ); ?>
			</button>

			<button type="button" class="epsw-filter-btn epsw-filter-btn-apply">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
					<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
				</svg>
				<?php esc_html_e( 'Apply Filters', 'portfolio-showcase' ); ?>
			</button>
		</div>

		<div class="epsw-popular-sections">
			<?php if ( ! empty( $all_categories ) && count( $all_categories ) > 0 ) : ?>
			<div class="epsw-popular-section">
				<div class="epsw-popular-header">
					<h3 class="epsw-popular-title"><?php esc_html_e( 'Popular Categories', 'portfolio-showcase' ); ?></h3>
					<button type="button" class="epsw-popular-view-all" data-type="category">
						<?php esc_html_e( 'View All', 'portfolio-showcase' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="9 18 15 12 9 6"/>
						</svg>
					</button>
				</div>
				<div class="epsw-popular-grid">
					<?php
					$popular_categories = array_slice( $all_categories, 0, 5 );
					foreach ( $popular_categories as $term ) :
						$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
						$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
						?>
						<button type="button" class="epsw-popular-chip" data-filter-type="category" data-slug="<?php echo esc_attr( $term->slug ); ?>">
							<?php if ( $icon_url ) : ?>
								<span class="epsw-popular-chip-icon">
									<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
								</span>
							<?php endif; ?>
							<span class="epsw-popular-chip-text"><?php echo esc_html( $term->name ); ?></span>
						</button>
					<?php endforeach; ?>
					<?php if ( count( $all_categories ) > 5 ) : ?>
						<button type="button" class="epsw-popular-chip epsw-popular-chip-more" data-more-type="category" aria-expanded="false">
							<span class="epsw-popular-chip-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
									<rect x="3" y="3" width="7" height="7" rx="1.5"/>
									<rect x="14" y="3" width="7" height="7" rx="1.5"/>
									<rect x="3" y="14" width="7" height="7" rx="1.5"/>
									<rect x="14" y="14" width="7" height="7" rx="1.5"/>
								</svg>
							</span>
							<span class="epsw-popular-chip-text"><?php esc_html_e( 'More', 'portfolio-showcase' ); ?></span>
							<svg class="epsw-more-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="6 9 12 15 18 9"/>
							</svg>
						</button>
						<?php foreach ( array_slice( $all_categories, 5 ) as $term ) : ?>
							<?php
							$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
							$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
							?>
							<button type="button" class="epsw-popular-chip epsw-popular-chip-extra is-hidden" data-extra-type="category" data-filter-type="category" data-slug="<?php echo esc_attr( $term->slug ); ?>">
								<?php if ( $icon_url ) : ?>
									<span class="epsw-popular-chip-icon">
										<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
									</span>
								<?php endif; ?>
								<span class="epsw-popular-chip-text"><?php echo esc_html( $term->name ); ?></span>
							</button>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $all_technologies ) && count( $all_technologies ) > 0 ) : ?>
			<div class="epsw-popular-section">
				<div class="epsw-popular-header">
					<h3 class="epsw-popular-title"><?php esc_html_e( 'Popular Technologies', 'portfolio-showcase' ); ?></h3>
					<button type="button" class="epsw-popular-view-all" data-type="technology">
						<?php esc_html_e( 'View All', 'portfolio-showcase' ); ?>
						<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<polyline points="9 18 15 12 9 6"/>
						</svg>
					</button>
				</div>
				<div class="epsw-popular-grid">
					<?php
					$popular_technologies = array_slice( $all_technologies, 0, 6 );
					foreach ( $popular_technologies as $term ) :
						$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
						$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
						?>
						<button type="button" class="epsw-popular-chip" data-filter-type="technology" data-slug="<?php echo esc_attr( $term->slug ); ?>">
							<?php if ( $icon_url ) : ?>
								<span class="epsw-popular-chip-icon">
									<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
								</span>
							<?php endif; ?>
							<span class="epsw-popular-chip-text"><?php echo esc_html( $term->name ); ?></span>
						</button>
					<?php endforeach; ?>
					<?php if ( count( $all_technologies ) > 6 ) : ?>
						<button type="button" class="epsw-popular-chip epsw-popular-chip-more" data-more-type="technology" aria-expanded="false">
							<span class="epsw-popular-chip-icon">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
									<rect x="3" y="3" width="7" height="7" rx="1.5"/>
									<rect x="14" y="3" width="7" height="7" rx="1.5"/>
									<rect x="3" y="14" width="7" height="7" rx="1.5"/>
									<rect x="14" y="14" width="7" height="7" rx="1.5"/>
								</svg>
							</span>
							<span class="epsw-popular-chip-text"><?php esc_html_e( 'More', 'portfolio-showcase' ); ?></span>
							<svg class="epsw-more-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
								<polyline points="6 9 12 15 18 9"/>
							</svg>
						</button>
						<?php foreach ( array_slice( $all_technologies, 6 ) as $term ) : ?>
							<?php
							$icon_id  = get_term_meta( $term->term_id, 'epsw_icon_id', true );
							$icon_url = $icon_id ? wp_get_attachment_url( $icon_id ) : '';
							?>
							<button type="button" class="epsw-popular-chip epsw-popular-chip-extra is-hidden" data-extra-type="technology" data-filter-type="technology" data-slug="<?php echo esc_attr( $term->slug ); ?>">
								<?php if ( $icon_url ) : ?>
									<span class="epsw-popular-chip-icon">
										<img src="<?php echo esc_url( $icon_url ); ?>" alt="" />
									</span>
								<?php endif; ?>
								<span class="epsw-popular-chip-text"><?php echo esc_html( $term->name ); ?></span>
							</button>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
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
<div class="epsw-backdrop" id="epsw-backdrop"></div>
