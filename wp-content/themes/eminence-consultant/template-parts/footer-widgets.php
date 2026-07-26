<?php
/**
 * Footer navigation + social links + Privacy Policy link (FR-002, FR-006).
 * Social icons only render for platforms with a confirmed URL (data-model.md
 * "Social Link" validation rule — omit gracefully, never a dead/incorrect link).
 */

$eminence_social_links = eminence_get_social_links();
// get_privacy_policy_url() (not get_page_by_path()) is the correct WordPress API here:
// WordPress core auto-creates its own draft "Privacy Policy" page on install and claims
// the privacy-policy slug, which caused get_page_by_path('privacy-policy') to resolve to
// that page instead of ours during live testing. Settings -> Privacy in wp-admin is where
// the site owner designates the real page; this function respects that setting.
$eminence_privacy_url = get_privacy_policy_url();
?>
<div class="eminence-footer-inner">

	<nav class="eminence-footer-nav" aria-label="<?php esc_attr_e( 'Footer', 'eminence-consultant' ); ?>">
		<?php
		if ( has_nav_menu( 'footer' ) ) {
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'eminence-footer-menu',
				)
			);
		}
		?>
	</nav>

	<?php if ( ! empty( $eminence_social_links ) ) : ?>
		<ul class="eminence-social-links">
			<?php foreach ( $eminence_social_links as $platform => $url ) : ?>
				<li class="eminence-social-link eminence-social-link--<?php echo esc_attr( $platform ); ?>">
					<a href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank">
						<span class="screen-reader-text"><?php echo esc_html( ucfirst( $platform ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="eminence-footer-legal">
		<?php if ( $eminence_privacy_url ) : ?>
			<a href="<?php echo esc_url( $eminence_privacy_url ); ?>">
				<?php esc_html_e( 'Privacy Policy', 'eminence-consultant' ); ?>
			</a>
		<?php endif; ?>
		<p class="eminence-footer-copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>

</div>
