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

<div id="page" class="eminence-site-wrapper">

	<header id="masthead" class="eminence-site-header">
		<div class="eminence-header-inner">
			<a class="eminence-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php if ( has_custom_logo() ) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="eminence-logo-text"><?php bloginfo( 'name' ); ?></span>
				<?php endif; ?>
			</a>

			<?php get_template_part( 'template-parts/navigation' ); ?>
		</div>
	</header>

	<main id="eminence-main-content" class="eminence-site-main">
