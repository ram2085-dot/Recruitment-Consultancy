<?php
/**
 * Home page shell wrapper (T033).
 *
 * Deliberately minimal: this feature (001-site-shell-navigation) only guarantees Home
 * renders inside the shell without erroring. The hero banner / tagline / CTA layout that
 * spec 002-home-page requires is that feature's implementation, not this one's — see
 * contracts/theme-shell-contract.md. Do not add hero-specific markup here; extend this
 * file (or replace it) when implementing 002-home-page instead.
 */

get_header();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'eminence-content-page eminence-home-page' ); ?>>
	<div class="eminence-page-content">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</article>

<?php
get_footer();
