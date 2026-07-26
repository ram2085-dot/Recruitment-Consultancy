<?php
/**
 * Home page shell wrapper (T033), extended with a hero section matching the
 * reference design (2026-07-26): full-bleed gradient banner behind a transparent
 * header, bold headline, CTA buttons.
 *
 * The hero's visual system (colors, type, layout) belongs here because it's shared
 * theme styling. The hero's actual COPY (tagline, services summary) is
 * 002-home-page's job per contracts/theme-shell-contract.md — everything below is
 * placeholder text standing in for that, not final content. A real photo replaces
 * the gradient once the business owner supplies one (BRD Section 9).
 */

get_header();

// Slug lookups, not hardcoded IDs — IDs are specific to this preview install and would be
// wrong (or point at an unrelated page) on any other WordPress install.
$eminence_employers_page  = get_page_by_path( 'for-employers' );
$eminence_candidates_page = get_page_by_path( 'for-candidates' );
?>

<section class="eminence-hero">
	<div class="eminence-hero-inner">
		<h1 class="eminence-hero-title">
			<?php esc_html_e( 'Hire the Best Talent for Your Business with Eminence Consultant', 'eminence-consultant' ); ?>
		</h1>
		<p class="eminence-hero-subtitle">
			<?php esc_html_e( 'Permanent staffing and executive search, with deep expertise in the Transformer industry.', 'eminence-consultant' ); ?>
		</p>
		<div class="eminence-hero-actions">
			<a class="eminence-btn eminence-btn--primary" href="<?php echo esc_url( $eminence_employers_page ? get_permalink( $eminence_employers_page ) : home_url( '/' ) ); ?>">
				<?php esc_html_e( 'For Employers', 'eminence-consultant' ); ?>
			</a>
			<a class="eminence-btn eminence-btn--outline" href="<?php echo esc_url( $eminence_candidates_page ? get_permalink( $eminence_candidates_page ) : home_url( '/' ) ); ?>">
				<?php esc_html_e( 'For Candidates', 'eminence-consultant' ); ?>
			</a>
		</div>
	</div>
</section>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'eminence-content-page eminence-home-page' ); ?>>
	<div class="eminence-page-content">
		<?php
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
		?>
	</div>
</article>

<?php
get_footer();
