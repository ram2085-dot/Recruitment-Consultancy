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
$eminence_phone = get_theme_mod( 'eminence_phone_number', '' );
// Transparent header overlaying the hero photo on Home, matching the reference design;
// a solid header on every other page (standard pattern — the hero is Home-only, per
// contracts/theme-shell-contract.md, so the shell only supplies the toggle, not the hero).
$eminence_header_class = is_front_page() ? 'eminence-site-header eminence-site-header--overlay' : 'eminence-site-header';
?>
<div id="page" class="eminence-site-wrapper">

	<header id="masthead" class="<?php echo esc_attr( $eminence_header_class ); ?>">
		<div class="eminence-header-inner">
			<a class="eminence-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="eminence-logo-mark" aria-hidden="true">
						<svg viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
							<circle cx="20" cy="20" r="19" fill="none" stroke="url(#eminence-logo-gradient)" stroke-width="2.5"/>
							<circle cx="20" cy="12" r="4.5" fill="url(#eminence-logo-gradient)"/>
							<path d="M20 17 L14 34 L20 30 L26 34 Z" fill="url(#eminence-logo-gradient)"/>
							<defs>
								<linearGradient id="eminence-logo-gradient" x1="0" y1="0" x2="40" y2="40">
									<stop offset="0" stop-color="#14b8a6"/>
									<stop offset="1" stop-color="#f59e0b"/>
								</linearGradient>
							</defs>
						</svg>
					</span>
					<span class="eminence-logo-text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>

			<?php get_template_part( 'template-parts/navigation' ); ?>

			<?php if ( $eminence_phone ) : ?>
				<a class="eminence-header-phone" href="<?php echo esc_attr( 'tel:' . preg_replace( '/[^0-9+]/', '', $eminence_phone ) ); ?>">
					<svg class="eminence-header-phone-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.9 21 3 13.1 3 3.9c0-.6.4-1 1-1h3.4c.6 0 1 .4 1 1 0 1.2.2 2.4.6 3.6.1.4 0 .8-.3 1z" fill="currentColor"/></svg>
					<span><?php echo esc_html( $eminence_phone ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</header>

	<main id="eminence-main-content" class="eminence-site-main">
