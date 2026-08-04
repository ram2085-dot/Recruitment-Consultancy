<?php
/**
 * Template Name: Page With Hero
 *
 * Generic content page with a page hero banner: the same rotating background slider used
 * on Home (template-parts/hero-slider.php, images shared via eminence_get_hero_slides()
 * so there's one place the business owner manages them, not a separate set per page),
 * scaled down (.eminence-hero--page — no stats bar, no CTA buttons, just the page title
 * on the tinted photo background). Otherwise identical to page.php.
 *
 * Started as page-about-us.php (2026-08-01, one page only); generalized the same day
 * once the request became "roll out to the rest of the pages" — About Us, What We Do,
 * For Employers, For Candidates, Contact Us, and Privacy Policy all want the exact same
 * hero+content structure, so one shared template beats six near-identical files. Assign
 * via wp-admin (Page Attributes -> Template -> "Page With Hero").
 *
 * Deliberately NOT applied to the Employee Login placeholder or the 404 page — those are
 * utility pages, not marketing content, and a photo hero would misrepresent them as more
 * built-out than they are. The merged Testimonials & Industry Leaders page gets the same
 * hero treatment too, but via its own dedicated template (page-community.php, 2026-08-04)
 * since it needs CPT query logic this generic template doesn't have — see
 * eminence_page_hero_templates() for the full list of hero-bearing templates.
 */

get_header();
?>

<section class="eminence-hero eminence-hero--page">
	<?php get_template_part( 'template-parts/hero-slider' ); ?>
	<div class="eminence-hero-tint" aria-hidden="true"></div>
	<div class="eminence-hero-inner eminence-hero-inner--page">
		<h1 class="eminence-page-title"><?php the_title(); ?></h1>
	</div>
</section>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'eminence-content-page eminence-after-hero' ); ?>>
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
