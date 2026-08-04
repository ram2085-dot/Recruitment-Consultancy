<?php
/**
 * Template Name: Testimonials & Industry Leaders
 *
 * Merges what were two separate pages/nav items — Testimonials (007-testimonials-page)
 * and Industry Leaders We've Met (008-industry-leaders-page) — into one page, split into
 * two vertical halves, each its own sliding carousel (2026-08-04 request). Testimonials
 * moved from a static two-section grid (separately headed "Client"/"Candidate" blocks)
 * into a single combined slider — see eminence_get_all_testimonials() and the type badge
 * added to template-parts/testimonial-card.php, since the heading-based grouping no
 * longer exists to carry that distinction. The Industry Leaders gallery slider is
 * otherwise unchanged from its old standalone page.
 *
 * Supersedes page-testimonials.php and page-industry-leaders.php (both removed). Assign
 * this template to the surviving page (formerly "Testimonials") in wp-admin; the old
 * "Industry Leaders We've Met" page and its nav entry are retired — its gallery photos
 * (eminence_gallery CPT) are untouched by that, they're queried independently of any page.
 */

get_header();

$eminence_testimonials    = eminence_get_all_testimonials();
$eminence_gallery_photos  = eminence_get_gallery_photos();
?>

<section class="eminence-hero eminence-hero--page">
	<?php get_template_part( 'template-parts/hero-slider' ); ?>
	<div class="eminence-hero-tint" aria-hidden="true"></div>
	<div class="eminence-hero-inner eminence-hero-inner--page">
		<h1 class="eminence-page-title"><?php the_title(); ?></h1>
	</div>
</section>

<article class="eminence-content-page eminence-after-hero eminence-community-page">
	<div class="eminence-page-content">
		<div class="eminence-community-grid">

			<div class="eminence-community-half">
				<h2><?php esc_html_e( 'What People Say', 'eminence-consultant' ); ?></h2>

				<?php if ( $eminence_testimonials->have_posts() ) : ?>
					<div class="eminence-slider">
						<button type="button" class="eminence-slider-btn eminence-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'eminence-consultant' ); ?>">&#8249;</button>

						<div class="eminence-slider-track">
							<?php
							while ( $eminence_testimonials->have_posts() ) :
								$eminence_testimonials->the_post();
								?>
								<div class="eminence-slider-slide eminence-slider-slide--card">
									<?php get_template_part( 'template-parts/testimonial-card' ); ?>
								</div>
								<?php
							endwhile;
							wp_reset_postdata();
							?>
						</div>

						<button type="button" class="eminence-slider-btn eminence-slider-next" aria-label="<?php esc_attr_e( 'Next', 'eminence-consultant' ); ?>">&#8250;</button>
					</div>
				<?php else : ?>
					<p class="eminence-slider-empty">
						<?php esc_html_e( 'Client and candidate testimonials are coming soon.', 'eminence-consultant' ); ?>
					</p>
				<?php endif; ?>
			</div>

			<div class="eminence-community-half">
				<h2><?php esc_html_e( "Industry Leaders We've Met", 'eminence-consultant' ); ?></h2>
				<p class="eminence-community-tagline">
					<?php esc_html_e( "Building relationships with the people shaping India's workforce", 'eminence-consultant' ); ?>
				</p>

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

		</div>
	</div>
</article>

<?php
get_footer();
