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

<?php
// Transparent header overlaying the hero photo on Home, matching the reference design;
// a solid header on every other page (standard pattern — the hero is Home-only, per
// contracts/theme-shell-contract.md, so the shell only supplies the toggle, not the hero).
$eminence_header_class = is_front_page() ? 'eminence-site-header eminence-site-header--overlay' : 'eminence-site-header';
?>
<div id="page" class="eminence-site-wrapper">

	<header id="masthead" class="<?php echo esc_attr( $eminence_header_class ); ?>">
		<div class="eminence-header-inner">
			<?php if ( has_custom_logo() ) : ?>
				<div class="eminence-logo eminence-logo--custom">
					<?php // the_custom_logo() prints its own <a> — do not nest inside another one. ?>
					<?php the_custom_logo(); ?>
				</div>
			<?php else : ?>
				<a class="eminence-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
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
					<span class="eminence-logo-text"><?php bloginfo( 'name' ); ?></span>
				</a>
			<?php endif; ?>

			<?php get_template_part( 'template-parts/navigation' ); ?>
		</div>
	</header>

	<main id="eminence-main-content" class="eminence-site-main">
