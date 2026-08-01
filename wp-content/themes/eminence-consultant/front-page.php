<?php
/**
 * Home page (002-home-page): hero (headline/subtitle from Customizer — see
 * functions.php "Homepage Hero" section — so the business owner can edit them without a
 * deployment, per constitution Principle I / spec 001 FR-014) plus a key-services summary
 * from the Page's own the_content(). Renders inside the Site Shell (001).
 *
 * Hero background is a rotating slider, sourced from 4 Customizer image settings
 * (Appearance -> Customize -> Homepage Hero -> "Hero Background — Slide 1-4") so the
 * business owner can swap them from wp-admin without a deployment — see
 * eminence_get_hero_slides() in functions.php. Defaults to the theme's bundled
 * placeholders (assets/images/hero/hero-slide-1..4.jpg, 4 generic recruitment stock
 * images supplied 2026-07-31) until the owner uploads their own. The navy tint layered on
 * top is what makes this "images on a blue background" rather than a full photo takeover —
 * keeps the white hero text legible over what would otherwise be a busy photo. Crossfade
 * is driven by assets/js/hero-slider.js, enqueued on any page using a hero (see
 * eminence_page_hero_templates() in functions.php). The slide markup itself lives in
 * template-parts/hero-slider.php so About Us's page hero (page-about-us.php) can reuse
 * the exact same images/markup instead of duplicating it.
 */

get_header();

// Slug lookups, not hardcoded IDs — IDs are specific to this preview install and would be
// wrong (or point at an unrelated page) on any other WordPress install.
$eminence_employers_page  = get_page_by_path( 'for-employers' );
$eminence_candidates_page = get_page_by_path( 'for-candidates' );
?>

<section class="eminence-hero">
	<?php get_template_part( 'template-parts/hero-slider' ); ?>
	<div class="eminence-hero-tint" aria-hidden="true"></div>
	<div class="eminence-hero-inner">
		<span class="eminence-hero-eyebrow"><?php esc_html_e( 'Recruitment Partner — Transformer Industry', 'eminence-consultant' ); ?></span>
		<h1 class="eminence-hero-title">
			<?php echo esc_html( get_theme_mod( 'eminence_hero_headline', __( 'Hire the Best Talent for Your Business with Eminence Consultant', 'eminence-consultant' ) ) ); ?>
		</h1>
		<p class="eminence-hero-subtitle">
			<?php echo esc_html( get_theme_mod( 'eminence_hero_subtitle', __( 'Permanent staffing and executive search, with major clients in the Transformer, wire & cables industry.', 'eminence-consultant' ) ) ); ?>
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
