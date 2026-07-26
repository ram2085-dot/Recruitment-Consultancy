<?php
/**
 * Fallback template, required by WordPress's template hierarchy even though
 * page.php/front-page.php/404.php cover every URL this theme is expected to serve.
 * Without this file WordPress refuses to activate the theme at all ("Template is
 * missing") — found via live testing in the local preview, not anticipated in tasks.md.
 */

get_header();
?>

<article class="eminence-content-page">
	<div class="eminence-page-content">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				the_title( '<h1 class="eminence-page-title">', '</h1>' );
				the_content();
			endwhile;
		else :
			?>
			<p><?php esc_html_e( 'Nothing found.', 'eminence-consultant' ); ?></p>
			<?php
		endif;
		?>
	</div>
</article>

<?php
get_footer();
