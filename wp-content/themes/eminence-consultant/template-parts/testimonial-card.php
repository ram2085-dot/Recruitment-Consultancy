<?php
/**
 * A single testimonial card. Included from page-testimonials.php inside the loop.
 * Featured image (logo/photo) is rendered only when present (FR-003 / omit gracefully).
 */
?>
<div class="eminence-testimonial-card">
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
