<?php
/**
 * Template Name: Industry Leaders
 *
 * Queries the eminence_gallery_photo CPT (008-industry-leaders-page data-model.md).
 * Shows a "coming soon" state when there are no published photos yet (spec Edge Case)
 * instead of an empty slider shell. Assign this template to the Industry Leaders page.
 *
 * Page hero added 2026-08-01 (same rotating slider as every other content page — see
 * eminence_page_hero_templates()); listed on that function directly since this template
 * also needs the CPT query logic below, which page-with-hero.php doesn't have. The
 * tagline moved from the old plain page-header into the hero as a subtitle line.
 */

get_header();

$eminence_gallery_photos = eminence_get_gallery_photos();
?>

<section class="eminence-hero eminence-hero--page">
	<?php get_template_part( 'template-parts/hero-slider' ); ?>
	<div class="eminence-hero-tint" aria-hidden="true"></div>
	<div class="eminence-hero-inner eminence-hero-inner--page">
		<h1 class="eminence-page-title"><?php the_title(); ?></h1>
		<p class="eminence-hero-subtitle eminence-industry-leaders-tagline">
			<?php esc_html_e( "Building relationships with the people shaping India's workforce", 'eminence-consultant' ); ?>
		</p>
	</div>
</section>

<article class="eminence-content-page eminence-after-hero eminence-industry-leaders-page">
	<div class="eminence-page-content">
		<?php if ( $eminence_gallery_photos->have_posts() ) : ?>
			<div class="eminence-slider">
				<button type="button" class="eminence-slider-btn eminence-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'eminence-consultant' ); ?>">&#8249;</button>

				<div class="eminence-slider-track">
					<?php
					while ( $eminence_gallery_photos->have_posts() ) :
						$eminence_gallery_photos->the_post();
						?>
						<figure class="eminence-slider-slide">
							<?php the_post_thumbnail( 'large' ); ?>
							<?php if ( get_the_title() ) : ?>
								<figcaption><?php the_title(); ?></figcaption>
							<?php endif; ?>
						</figure>
						<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>

				<button type="button" class="eminence-slider-btn eminence-slider-next" aria-label="<?php esc_attr_e( 'Next', 'eminence-consultant' ); ?>">&#8250;</button>
			</div>
		<?php else : ?>
			<p class="eminence-slider-empty">
				<?php esc_html_e( 'Photos from our industry events are coming soon.', 'eminence-consultant' ); ?>
			</p>
		<?php endif; ?>
	</div>
</article>

<?php
get_footer();
