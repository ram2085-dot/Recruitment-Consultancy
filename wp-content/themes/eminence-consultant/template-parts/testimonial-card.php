<?php
/**
 * A single testimonial card. Included from page-community.php inside a slider slide.
 * Featured image (logo/photo) is rendered only when present (FR-003 / omit gracefully).
 *
 * Type badge added 2026-08-04: client and candidate testimonials used to sit under two
 * separately headed sections ("Client Testimonials" / "Candidate Testimonials"); now that
 * they're intermixed in one combined slider (page-community.php), each card needs its own
 * label so the distinction isn't lost.
 */

$eminence_testimonial_terms = get_the_terms( get_the_ID(), 'testimonial_type' );
$eminence_testimonial_type  = ( $eminence_testimonial_terms && ! is_wp_error( $eminence_testimonial_terms ) )
	? $eminence_testimonial_terms[0]->name
	: '';
?>
<div class="eminence-testimonial-card">
	<?php if ( $eminence_testimonial_type ) : ?>
		<span class="eminence-testimonial-type"><?php echo esc_html( $eminence_testimonial_type ); ?></span>
	<?php endif; ?>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="eminence-testimonial-logo">
			<?php the_post_thumbnail( 'thumbnail' ); ?>
		</div>
	<?php endif; ?>

	<blockquote class="eminence-testimonial-quote">
		<?php the_content(); ?>
	</blockquote>

	<p class="eminence-testimonial-author"><?php the_title(); ?></p>
</div>
