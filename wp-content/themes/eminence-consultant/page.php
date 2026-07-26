<?php
/**
 * Generic content page template (FR-014 CMS editability).
 * Every visible piece of text/image comes from the_content()/the_post_thumbnail() —
 * nothing here is hardcoded, so an editor change in wp-admin is what appears live.
 * See contracts/theme-shell-contract.md: content pages must not duplicate header/footer.
 */

get_header();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'eminence-content-page' ); ?>>

	<?php if ( has_post_thumbnail() ) : ?>
		<div class="eminence-page-featured-image">
			<?php the_post_thumbnail( 'eminence-content' ); ?>
		</div>
	<?php endif; ?>

	<header class="eminence-page-header">
		<h1 class="eminence-page-title"><?php the_title(); ?></h1>
	</header>

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
