<?php
/**
 * Template Name: Employee Login (Placeholder)
 *
 * Static placeholder only (FR-005, constitution Principle VIII). No form, no login
 * handler, no session logic — real authentication ships in the Employee Portal module.
 * Assign this template to the "Employee Login" page in wp-admin.
 */

get_header();
?>

<article class="eminence-content-page eminence-employee-login-placeholder">
	<header class="eminence-page-header">
		<h1 class="eminence-page-title"><?php esc_html_e( 'Employee Login', 'eminence-consultant' ); ?></h1>
	</header>

	<div class="eminence-page-content">
		<p>
			<?php esc_html_e( 'The employee portal is coming soon.', 'eminence-consultant' ); ?>
		</p>
		<p>
			<a class="eminence-btn eminence-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'Back to Home', 'eminence-consultant' ); ?>
			</a>
		</p>
	</div>
</article>

<?php
get_footer();
