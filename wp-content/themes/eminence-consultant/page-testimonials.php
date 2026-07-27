<?php
/**
 * Template Name: Testimonials
 *
 * Queries the eminence_testimonial CPT (007-testimonials-page data-model.md), split by
 * testimonial_type. Each section (client/candidate) is omitted entirely when it has no
 * published entries (FR-002) rather than rendering an empty heading. Assign this template
 * to the Testimonials page in wp-admin.
 */

get_header();

$eminence_client_testimonials    = eminence_get_testimonials( 'client' );
$eminence_candidate_testimonials = eminence_get_testimonials( 'candidate' );
?>

<article class="eminence-content-page eminence-testimonials-page">
	<header class="eminence-page-header">
		<h1 class="eminence-page-title"><?php the_title(); ?></h1>
	</header>

	<div class="eminence-page-content">

		<?php if ( $eminence_client_testimonials->have_posts() ) : ?>
			<section class="eminence-testimonial-section">
				<h2><?php esc_html_e( 'Client Testimonials', 'eminence-consultant' ); ?></h2>
				<div class="eminence-testimonial-grid">
					<?php
					while ( $eminence_client_testimonials->have_posts() ) :
						$eminence_client_testimonials->the_post();
						get_template_part( 'template-parts/testimonial-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $eminence_candidate_testimonials->have_posts() ) : ?>
			<section class="eminence-testimonial-section">
				<h2><?php esc_html_e( 'Candidate Testimonials', 'eminence-consultant' ); ?></h2>
				<div class="eminence-testimonial-grid">
					<?php
					while ( $eminence_candidate_testimonials->have_posts() ) :
						$eminence_candidate_testimonials->the_post();
						get_template_part( 'template-parts/testimonial-card' );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>

	</div>
</article>

<?php
get_footer();
