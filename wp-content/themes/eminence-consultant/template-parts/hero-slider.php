<?php
/**
 * Rotating background slide images for any hero section (.eminence-hero and its
 * modifiers). Sourced from eminence_get_hero_slides() (functions.php) — 4 Customizer
 * image settings under Appearance -> Customize -> Homepage Hero, shared by every hero
 * that includes this part rather than each hero having its own separate image set, so
 * there's one place the business owner manages them regardless of how many pages use a
 * hero banner. Renders nothing if all 4 slots are empty; the hero's own background color
 * (set on .eminence-hero) is the fallback in that case.
 */

$eminence_hero_slides = eminence_get_hero_slides();
?>
<?php if ( ! empty( $eminence_hero_slides ) ) : ?>
	<div class="eminence-hero-slides" aria-hidden="true">
		<?php foreach ( $eminence_hero_slides as $eminence_index => $eminence_slide_url ) : ?>
			<img
				class="eminence-hero-slide<?php echo 0 === $eminence_index ? ' is-active' : ''; ?>"
				src="<?php echo esc_url( $eminence_slide_url ); ?>"
				alt=""
				<?php echo 0 === $eminence_index ? 'fetchpriority="high"' : 'loading="lazy"'; ?>
			/>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
