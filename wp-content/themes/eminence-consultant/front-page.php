<?php
/**
 * Home page (002-home-page): hero (headline/subtitle from Customizer — see
 * functions.php "Homepage Hero" section — so the business owner can edit them without a
 * deployment, per constitution Principle I / spec 001 FR-014) plus a key-services summary
 * from the Page's own the_content(). Renders inside the Site Shell (001).
 *
 * A real photo can replace the gradient background once the business owner supplies one
 * (BRD Section 9); this is styled as a placeholder gradient hero until then.
 */

get_header();

// Slug lookups, not hardcoded IDs — IDs are specific to this preview install and would be
// wrong (or point at an unrelated page) on any other WordPress install.
$eminence_employers_page  = get_page_by_path( 'for-employers' );
$eminence_candidates_page = get_page_by_path( 'for-candidates' );
?>

<section class="eminence-hero">
	<div class="eminence-hero-inner">
		<span class="eminence-hero-eyebrow"><?php esc_html_e( 'Recruitment Partner — Transformer Industry', 'eminence-consultant' ); ?></span>
		<h1 class="eminence-hero-title">
			<?php echo esc_html( get_theme_mod( 'eminence_hero_headline', __( 'Hire the Best Talent for Your Business with Eminence Consultant', 'eminence-consultant' ) ) ); ?>
		</h1>
		<p class="eminence-hero-subtitle">
			<?php echo esc_html( get_theme_mod( 'eminence_hero_subtitle', __( 'Permanent staffing and executive search, with deep expertise in the Transformer industry.', 'eminence-consultant' ) ) ); ?>
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

<?php
/**
 * Placeholder trust stats — BRD Section 9 content (real figures) is still pending from
 * the business owner. Swap these four numbers/labels for confirmed figures before launch.
 */
$eminence_home_stats = array(
	array( 'number' => '10+', 'label' => __( 'Years of Recruitment Experience', 'eminence-consultant' ) ),
	array( 'number' => '500+', 'label' => __( 'Candidates Placed', 'eminence-consultant' ) ),
	array( 'number' => '50+', 'label' => __( 'Client Companies Served', 'eminence-consultant' ) ),
	array( 'number' => '1', 'label' => __( 'Industry Focus: Transformer', 'eminence-consultant' ) ),
);
?>
<div class="eminence-stats">
	<div class="eminence-stats-grid">
		<?php foreach ( $eminence_home_stats as $eminence_stat ) : ?>
			<div class="eminence-stat-card">
				<span class="eminence-stat-number"><?php echo esc_html( $eminence_stat['number'] ); ?></span>
				<span class="eminence-stat-label"><?php echo esc_html( $eminence_stat['label'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>

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
