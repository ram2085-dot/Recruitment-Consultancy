<?php
/**
 * Site header: <head>, skip link, utility bar, logo, primary navigation.
 * Every content page template calls get_header() to render this — see
 * specs/001-site-shell-navigation/contracts/theme-shell-contract.md #1.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#eminence-main-content">
	<?php esc_html_e( 'Skip to content', 'eminence-consultant' ); ?>
</a>

<div class="eminence-top-accent" aria-hidden="true"></div>

<?php
$eminence_social_links  = eminence_get_social_links();
$eminence_contact_page  = get_page_by_path( 'contact-us' );
?>
<div id="page" class="eminence-site-wrapper">

	<?php
	/**
	 * Slim utility bar above the main header (2026-08-01, matching the two-tier header
	 * on sapphirerecruitment.ae). No phone number here — per explicit prior decision
	 * (2026-07-27) it stays on Contact Us only, not duplicated in the header; the
	 * reference's "Call Us" item is deliberately not carried over. No "Current
	 * Vacancies" link either — that needs a job-listing feature we don't have yet.
	 */
	?>
	<div class="eminence-utility-bar">
		<div class="eminence-utility-bar-inner">
			<?php if ( $eminence_contact_page ) : ?>
				<a class="eminence-utility-link" href="<?php echo esc_url( get_permalink( $eminence_contact_page ) ); ?>">
					<?php esc_html_e( 'Contact Us', 'eminence-consultant' ); ?>
				</a>
			<?php else : ?>
				<span></span>
			<?php endif; ?>

			<?php if ( ! empty( $eminence_social_links ) ) : ?>
				<div class="eminence-utility-social">
					<span class="eminence-utility-social-label"><?php esc_html_e( 'Get Social', 'eminence-consultant' ); ?></span>
					<ul class="eminence-social-links eminence-social-links--utility">
						<?php foreach ( $eminence_social_links as $platform => $url ) : ?>
							<li class="eminence-social-link eminence-social-link--<?php echo esc_attr( $platform ); ?>">
								<a href="<?php echo esc_url( $url ); ?>" rel="noopener noreferrer" target="_blank">
									<span class="screen-reader-text"><?php echo esc_html( ucfirst( $platform ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<header id="masthead" class="eminence-site-header">
		<div class="eminence-header-inner">
			<?php
			/**
			 * Vertically-stacked, centered logo lockup (mark above name above tagline) —
			 * changed 2026-08-01 from an earlier horizontal (icon-beside-text) layout to
			 * match the reference's composition. One unified, fully-clickable block
			 * regardless of whether the business owner has uploaded a real logo or the
			 * theme is still showing its inline SVG fallback.
			 */
			?>
			<a class="eminence-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php
					echo wp_get_attachment_image(
						get_theme_mod( 'custom_logo' ),
						'full',
						false,
						array(
							'class' => 'eminence-logo-mark-image',
							'alt'   => '',
						)
					);
					?>
				<?php else : ?>
					<span class="eminence-logo-mark" aria-hidden="true">
						<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="19" fill="none" stroke="url(#eminence-logo-gradient)" stroke-width="2.5"/>
							<circle cx="20" cy="12" r="4.5" fill="url(#eminence-logo-gradient)"/>
							<path d="M20 17 L14 34 L20 30 L26 34 Z" fill="url(#eminence-logo-gradient)"/>
							<defs>
								<linearGradient id="eminence-logo-gradient" x1="0" y1="0" x2="40" y2="40">
									<stop offset="0" stop-color="#781828"/>
									<stop offset="1" stop-color="#f8b000"/>
								</linearGradient>
							</defs>
						</svg>
					</span>
				<?php endif; ?>
				<span class="eminence-logo-wordmark">
					<span class="eminence-logo-name"><?php bloginfo( 'name' ); ?></span>
					<span class="eminence-logo-tagline"><?php esc_html_e( 'Recruitment Specialists', 'eminence-consultant' ); ?></span>
				</span>
			</a>

			<?php get_template_part( 'template-parts/navigation' ); ?>
		</div>
	</header>

	<main id="eminence-main-content" class="eminence-site-main">
