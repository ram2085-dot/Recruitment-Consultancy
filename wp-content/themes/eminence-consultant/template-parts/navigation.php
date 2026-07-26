<?php
/**
 * Primary navigation (FR-001, FR-004). Included from header.php.
 * Collapses into a mobile menu via assets/js/mobile-nav.js + assets/css/theme.css.
 */
?>
<button
	type="button"
	class="eminence-nav-toggle"
	aria-controls="eminence-primary-menu"
	aria-expanded="false"
>
	<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'eminence-consultant' ); ?></span>
	<span class="eminence-nav-toggle-bar" aria-hidden="true"></span>
	<span class="eminence-nav-toggle-bar" aria-hidden="true"></span>
	<span class="eminence-nav-toggle-bar" aria-hidden="true"></span>
</button>

<nav id="eminence-primary-nav" class="eminence-primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'eminence-consultant' ); ?>">
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_id'        => 'eminence-primary-menu',
				'menu_class'     => 'eminence-primary-menu',
			)
		);
	} else {
		// Editorial fallback so navigation is never empty before the menu is configured
		// in Appearance -> Menus (quickstart.md Setup step 2).
		?>
		<ul id="eminence-primary-menu" class="eminence-primary-menu">
			<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'eminence-consultant' ); ?></a></li>
		</ul>
		<?php
	}
	?>
</nav>
