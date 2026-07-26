<?php
/**
 * Site footer: closes <main>/#page, renders footer nav + social links + cookie notice,
 * then conditionally outputs the gated GA4 tag (FR-012, FR-013).
 * See specs/001-site-shell-navigation/contracts/theme-shell-contract.md #1, #5.
 */
?>
	</main><!-- #eminence-main-content -->

	<footer id="colophon" class="eminence-site-footer">
		<?php get_template_part( 'template-parts/footer-widgets' ); ?>
	</footer>

</div><!-- #page -->

<?php get_template_part( 'template-parts/cookie-notice' ); ?>

<?php if ( eminence_has_analytics_consent() && EMINENCE_GA4_ID && 'G-XXXXXXXXXX' !== EMINENCE_GA4_ID ) : ?>
	<!--
		Server-side gate (research.md #3): this tag is only ever printed when the
		eminence_consent cookie already says "accepted" — a visitor who has never
		answered, or who declined, gets no GA4 request on page load at all (FR-013).
		For the current session's Accept click, consent.js injects the same tag
		client-side without requiring a reload.
	-->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( EMINENCE_GA4_ID ); ?>"></script>
	<script>
		window.dataLayer = window.dataLayer || [];
		function gtag(){dataLayer.push(arguments);}
		gtag('js', new Date());
		gtag('config', '<?php echo esc_js( EMINENCE_GA4_ID ); ?>');
	</script>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
