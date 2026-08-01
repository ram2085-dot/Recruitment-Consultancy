<?php
/**
 * Site header: <head>, skip link, logo, primary navigation.
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
// Transparent header overlaying the hero photo on Home and any other page using a hero
// banner (see eminence_page_hero_templates()); a solid header everywhere else.
$eminence_header_class = eminence_current_page_has_hero() ? 'eminence-site-header eminence-site-header--overlay' : 'eminence-site-header';
?>
<div id="page" class="eminence-site-wrapper">

	<header id="masthead" class="<?php echo esc_attr( $eminence_header_class ); ?>">
		<div class="eminence-header-inner">
			<?php
			/**
			 * One unified, fully-clickable logo lockup — mark + stacked company
			 * name/tagline — regardless of whether the business owner has uploaded a
			 * real logo or the theme is still showing its inline SVG fallback.
			 * Previously the custom-logo branch rendered only the_custom_logo()'s bare
			 * image with no company name anywhere in the header; the fallback branch
			 * had the name but the custom-logo one (the one actually active once a
			 * real logo is uploaded) didn't. Rebuilt 2026-08-01 after feedback
			 * praising how a reference site (sapphirerecruitment.ae) pairs its mark
			 * with a clearly spelled-out name — same idea, our own mark/name/tagline.
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
