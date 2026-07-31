<?php
/**
 * Footer navigation + social links + Privacy Policy link (FR-002, FR-006).
 * Social icons only render for platforms with a confirmed URL (data-model.md
 * "Social Link" validation rule — omit gracefully, never a dead/incorrect link).
 *
 * Restructured 2026-07-31 into a multi-column layout (brand / quick links / connect) as
 * part of the "make it feel rich" visual pass — richer footers read as more established
 * than a single flat row, and it gives the footer nav menu (currently just Contact Us)
 * somewhere to sit alongside more than a copyright line.
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

	<div class="eminence-footer-grid">

		<div class="eminence-footer-brand">
			<?php if ( has_custom_logo() ) : ?>
				<div class="eminence-footer-brand-mark">
					<?php the_custom_logo(); ?>
				</div>
			<?php else : ?>
				<span class="eminence-footer-brand-name"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
			<p class="eminence-footer-tagline">
				<?php esc_html_e( 'Connecting skilled professionals with the businesses that need them — with deep expertise in the Transformer industry.', 'eminence-consultant' ); ?>
			</p>
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
		</div>

		<nav class="eminence-footer-col" aria-label="<?php esc_attr_e( 'Footer', 'eminence-consultant' ); ?>">
			<h2 class="eminence-footer-heading"><?php esc_html_e( 'Quick Links', 'eminence-consultant' ); ?></h2>
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

		<div class="eminence-footer-col">
			<h2 class="eminence-footer-heading"><?php esc_html_e( 'Legal', 'eminence-consultant' ); ?></h2>
			<ul class="eminence-footer-menu">
				<?php if ( $eminence_privacy_url ) : ?>
					<li><a href="<?php echo esc_url( $eminence_privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'eminence-consultant' ); ?></a></li>
				<?php endif; ?>
			</ul>
		</div>

	</div>

	<div class="eminence-footer-bottom">
		<p class="eminence-footer-copyright">
			&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
		</p>
	</div>

</div>
