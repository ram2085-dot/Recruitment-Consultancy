<?php
/**
 * Template Name: Employee Login
 *
 * Real authentication now (011-employee-login) — was a static "coming soon" placeholder
 * built in 001-site-shell-navigation. All of the actual logic (login form, session
 * handling, role gating) lives in the eminence-portal plugin, not here; this template's
 * only job is to render the [eminence_employee_login] shortcode inside the normal site
 * chrome (header/footer/nav), matching the theme<->plugin split required by constitution
 * Principle I. See specs/011-employee-login/contracts/portal-auth-contract.md — this file
 * is the entire integration surface between the plugin and the theme.
 */

get_header();
?>

<article class="eminence-content-page eminence-employee-login">
	<header class="eminence-page-header">
		<h1 class="eminence-page-title"><?php esc_html_e( 'Employee Login', 'eminence-consultant' ); ?></h1>
	</header>

	<div class="eminence-page-content">
		<?php if ( shortcode_exists( 'eminence_employee_login' ) ) : ?>
			<?php echo do_shortcode( '[eminence_employee_login]' ); ?>
		<?php else : ?>
			<p>
				<?php esc_html_e( 'Employee login is temporarily unavailable. Please try again shortly.', 'eminence-consultant' ); ?>
			</p>
		<?php endif; ?>
	</div>
</article>

<?php
get_footer();
