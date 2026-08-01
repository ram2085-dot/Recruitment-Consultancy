<?php
/**
 * Template Name: About Us (with Hero)
 *
 * About Us (003-about-us-page) with a page hero banner: the same rotating background
 * slider used on Home (template-parts/hero-slider.php, images shared via
 * eminence_get_hero_slides() so there's one place the business owner manages them, not a
 * separate set per page), scaled down (.eminence-hero--page — no stats bar, no CTA
 * buttons, just the page title on the tinted photo background). Added 2026-08-01 after
 * feedback that interior pages "still look very basic" next to the redesigned Home page;
 * see eminence_page_hero_templates() for how to extend this to another page.
 *
 * Assign this template to the About Us page in wp-admin (Page Attributes -> Template).
 * The generic page.php remains the template for pages that don't have this assigned.
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
