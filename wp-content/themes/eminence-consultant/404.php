<?php
/**
 * Styled 404 template (FR-008). Rendered automatically by WordPress for any URL that
 * doesn't resolve to a published page/post.
 */

get_header();
?>

<article class="eminence-content-page eminence-404-page">
	<header class="eminence-page-header">
		<h1 class="eminence-page-title"><?php esc_html_e( 'Page Not Found', 'eminence-consultant' ); ?></h1>
	</header>

	<div class="eminence-page-content">
		<p>
			<?php esc_html_e( "Sorry, the page you're looking for doesn't exist or may have moved.", 'eminence-consultant' ); ?>
		</p>
		<p>
			<a class="eminence-btn eminence-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to Home', 'eminence-consultant' ); ?>
			</a>
		</p>
	</div>
</article>

<?php
get_footer();
