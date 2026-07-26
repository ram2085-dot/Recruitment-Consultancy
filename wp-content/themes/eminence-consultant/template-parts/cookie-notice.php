<?php
/**
 * Cookie / privacy consent banner (FR-013). Hidden by default in CSS; shown by
 * consent.js only when no consent decision is recorded yet (first-time visitors).
 */

$eminence_privacy_page = get_page_by_path( 'privacy-policy' );
$eminence_privacy_url  = $eminence_privacy_page ? get_permalink( $eminence_privacy_page ) : '';
?>
<div id="eminence-cookie-notice" class="eminence-cookie-notice" hidden>
	<p class="eminence-cookie-notice-text">
		<?php esc_html_e( 'We use cookies for basic site analytics. You can accept or decline non-essential cookies.', 'eminence-consultant' ); ?>
		<?php if ( $eminence_privacy_url ) : ?>
			<a href="<?php echo esc_url( $eminence_privacy_url ); ?>">
				<?php esc_html_e( 'Read our Privacy Policy', 'eminence-consultant' ); ?>
			</a>
		<?php endif; ?>
	</p>
	<div class="eminence-cookie-notice-actions">
		<button type="button" id="eminence-cookie-decline" class="eminence-btn eminence-btn--secondary">
			<?php esc_html_e( 'Decline', 'eminence-consultant' ); ?>
		</button>
		<button type="button" id="eminence-cookie-accept" class="eminence-btn eminence-btn--primary">
			<?php esc_html_e( 'Accept', 'eminence-consultant' ); ?>
		</button>
	</div>
</div>
