<?php
/**
 * Theme setup for Eminence Consultant.
 * Implements specs/001-site-shell-navigation. Every function here is scoped to that
 * feature's requirements — see the FR references in comments for traceability.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// GA4 measurement ID (spec FR-012). Replace with the real ID before launch.
if ( ! defined( 'EMINENCE_GA4_ID' ) ) {
	define( 'EMINENCE_GA4_ID', 'G-XXXXXXXXXX' );
}

define( 'EMINENCE_CONSENT_COOKIE', 'eminence_consent' );

/**
 * Page templates that use a hero banner (rotating background slides + transparent
 * overlay header), beyond the front page itself (2026-08-01, rolled out to every content
 * page except the Employee Login placeholder and 404 — see page-with-hero.php for why
 * those two are excluded). Centralizing the list here — rather than checking specific
 * template filenames separately in header.php, functions.php's script enqueue, and the
 * CSS body class — means adding another page only requires appending one filename here.
 *
 * @return string[] Template filenames (relative to the theme root).
 */
function eminence_page_hero_templates() {
	return array( 'page-with-hero.php', 'page-community.php' );
}

/**
 * True if the current request is the front page or one of eminence_page_hero_templates().
 */
function eminence_current_page_has_hero() {
	return is_front_page() || is_page_template( eminence_page_hero_templates() );
}

/**
 * Theme support and nav menu registration (FR-001, FR-002, US2 T028/T029).
 */
add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support(
			'html5',
			array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
		);
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'editor-styles' );
		add_editor_style( 'assets/css/theme.css' );

		// Business owner's real logo (assets/images/logo.png), set via
		// Appearance -> Customize -> Site Identity. header.php falls back to an inline
		// SVG mark + wordmark when no custom logo is set.
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 80,
				'width'       => 80,
				'flex-height' => true,
				'flex-width'  => true,
			)
		);

		// Consistent editable image sizing across content pages (US2 T028).
		add_image_size( 'eminence-content', 1200, 675, true );

		register_nav_menus(
			array(
				'primary' => __( 'Primary Navigation', 'eminence-consultant' ),
				'footer'  => __( 'Footer Navigation', 'eminence-consultant' ),
			)
		);
	}
);

/**
 * Enqueue styles and scripts (FR-004, FR-005, FR-012, FR-013).
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		$theme_version = wp_get_theme()->get( 'Version' );

		// Poppins (headings/logo) + Inter (body) — matches the BRD's suggested typography
		// (Section 10: "Modern sans-serif fonts, e.g. Inter, Poppins"). Loaded from Google
		// Fonts for now; self-hosting is a reasonable later optimization, not a blocker.
		wp_enqueue_style(
			'eminence-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap',
			array(),
			null
		);

		wp_enqueue_style(
			'eminence-theme',
			get_template_directory_uri() . '/assets/css/theme.css',
			array( 'eminence-fonts' ),
			$theme_version
		);

		wp_enqueue_script(
			'eminence-mobile-nav',
			get_template_directory_uri() . '/assets/js/mobile-nav.js',
			array(),
			$theme_version,
			true
		);

		wp_enqueue_script(
			'eminence-consent',
			get_template_directory_uri() . '/assets/js/consent.js',
			array(),
			$theme_version,
			true
		);

		// Pass the GA4 ID and cookie name to consent.js without hardcoding them in JS
		// (FR-012/FR-013 — analytics must stay gated behind consent on the JS side too).
		wp_localize_script(
			'eminence-consent',
			'eminenceConsent',
			array(
				'gaId'       => EMINENCE_GA4_ID,
				'cookieName' => EMINENCE_CONSENT_COOKIE,
			)
		);

		// Slider controls only where a content slider actually is (page-community.php,
		// which has two independent sliders side by side — content-slider.js wires up
		// every .eminence-slider on the page, not just the first, since 2026-08-04).
		if ( is_page_template( 'page-community.php' ) ) {
			wp_enqueue_script(
				'eminence-content-slider',
				get_template_directory_uri() . '/assets/js/content-slider.js',
				array(),
				$theme_version,
				true
			);
		}

		// Hero background crossfade — any page using a hero banner (see
		// eminence_page_hero_templates()), not just the front page.
		if ( eminence_current_page_has_hero() ) {
			wp_enqueue_script(
				'eminence-hero-slider',
				get_template_directory_uri() . '/assets/js/hero-slider.js',
				array(),
				$theme_version,
				true
			);
		}

		// Fade-up entrance for content sections/cards (2026-08-14 "luxury" pass) — sitewide
		// since the selectors it targets (.eminence-page-content children, stat cards,
		// community-page halves) simply won't match on pages without them; no per-template
		// gating needed the way the slider scripts above require.
		wp_enqueue_script(
			'eminence-scroll-reveal',
			get_template_directory_uri() . '/assets/js/scroll-reveal.js',
			array(),
			$theme_version,
			true
		);
	}
);

/**
 * Customizer: Home page hero content (002-home-page data-model.md "Hero Content").
 * Keeps hero copy editable by the business owner instead of hardcoded in front-page.php.
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'eminence_hero',
			array(
				'title'    => __( 'Homepage Hero', 'eminence-consultant' ),
				'priority' => 155,
			)
		);

		$wp_customize->add_setting(
			'eminence_hero_headline',
			array(
				'default'           => __( 'Hire the Best Talent for Your Business with Eminence Consultant', 'eminence-consultant' ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'eminence_hero_headline',
			array(
				'label'   => __( 'Hero Headline', 'eminence-consultant' ),
				'section' => 'eminence_hero',
				'type'    => 'text',
			)
		);

		$wp_customize->add_setting(
			'eminence_hero_subtitle',
			array(
				'default'           => __( 'Permanent staffing and executive search, with major clients in the Transformer, wire & cables industry.', 'eminence-consultant' ),
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'eminence_hero_subtitle',
			array(
				'label'   => __( 'Hero Subtitle', 'eminence-consultant' ),
				'section' => 'eminence_hero',
				'type'    => 'text',
			)
		);

		// Hero background slides — 4 fixed slots (not a CPT/repeater: a fixed set of
		// background images, not a growing/queryable list, same reasoning as keeping
		// About Us's leadership team as plain content instead of a custom post type).
		// Defaults point at the theme's own bundled placeholders (assets/images/hero/)
		// so the slider still has something to show before the business owner uploads
		// their own images via wp-admin.
		foreach ( range( 1, 4 ) as $eminence_slide_num ) {
			$setting_id = "eminence_hero_slide_{$eminence_slide_num}";

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => get_template_directory_uri() . "/assets/images/hero/hero-slide-{$eminence_slide_num}.svg",
					'sanitize_callback' => 'esc_url_raw',
				)
			);

			$wp_customize->add_control(
				new WP_Customize_Image_Control(
					$wp_customize,
					$setting_id,
					array(
						/* translators: %d: slide position, 1-4 */
						'label'    => sprintf( __( 'Hero Background — Slide %d', 'eminence-consultant' ), $eminence_slide_num ),
						'section'  => 'eminence_hero',
						'settings' => $setting_id,
					)
				)
			);
		}
	}
);

/**
 * Non-empty hero slide URLs, in slot order (1-4). A slot left blank (business owner
 * clicked "Remove" in the Customizer, which clears the setting rather than restoring the
 * default) is omitted, not rendered as a broken image — same omit-gracefully rule used
 * for Social Links and testimonial logos.
 *
 * @return string[] Image URLs for the slides that actually have one set.
 */
function eminence_get_hero_slides() {
	$slides = array();

	foreach ( range( 1, 4 ) as $eminence_slide_num ) {
		// The fallback here must match add_setting()'s registered default above — WP only
		// applies the Customizer's own default inside the live-preview context; a normal
		// front-end page load only ever sees get_theme_mod()'s own $default argument. Same
		// duplication as eminence_hero_headline/_subtitle in front-page.php, for the same
		// reason.
		$default = get_template_directory_uri() . "/assets/images/hero/hero-slide-{$eminence_slide_num}.svg";
		$url     = get_theme_mod( "eminence_hero_slide_{$eminence_slide_num}", $default );
		if ( ! empty( $url ) ) {
			$slides[] = $url;
		}
	}

	return $slides;
}

/**
 * Marks pages using a hero banner (see eminence_page_hero_templates()) with a body class,
 * so theme.css can zero out .eminence-site-main's top padding for them — otherwise that
 * padding shows as a visible white gap between the header and the hero. Removed when the
 * header stopped being a transparent overlay (2026-08-01), on the reasoning that the hero
 * no longer needed to sit flush under it; that reasoning missed that .eminence-site-main's
 * own padding still applies regardless of the header being solid or overlaid, and the gap
 * is exactly as visible either way. Re-added the same day once a screenshot showed it.
 */
add_filter(
	'body_class',
	function ( $classes ) {
		if ( is_page_template( eminence_page_hero_templates() ) ) {
			$classes[] = 'eminence-has-page-hero';
		}
		return $classes;
	}
);

/**
 * Customizer: Social Link fields (data-model.md "Social Link").
 * A URL left blank means: omit that icon (spec Edge Case), enforced in
 * template-parts/footer-widgets.php, not here.
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'eminence_social_links',
			array(
				'title'    => __( 'Social Links', 'eminence-consultant' ),
				'priority' => 160,
			)
		);

		$platforms = eminence_social_platforms();

		foreach ( $platforms as $key => $label ) {
			$setting_id = "eminence_social_{$key}";

			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => '',
					'sanitize_callback' => 'esc_url_raw',
				)
			);

			$wp_customize->add_control(
				$setting_id,
				array(
					'label'   => $label,
					'section' => 'eminence_social_links',
					'type'    => 'url',
				)
			);
		}
	}
);

/**
 * Fixed set of supported social platforms (data-model.md: "Platform name, not free-form").
 *
 * @return array<string, string> Map of setting key => display label.
 */
function eminence_social_platforms() {
	return array(
		'linkedin'  => __( 'LinkedIn URL', 'eminence-consultant' ),
		'facebook'  => __( 'Facebook URL', 'eminence-consultant' ),
		'instagram' => __( 'Instagram URL', 'eminence-consultant' ),
		'twitter'   => __( 'Twitter / X URL', 'eminence-consultant' ),
	);
}

/**
 * Non-empty social links only, for rendering (spec Edge Case: omit unconfirmed links).
 *
 * @return array<string, string> Map of platform key => URL, empty entries excluded.
 */
function eminence_get_social_links() {
	$links = array();

	foreach ( array_keys( eminence_social_platforms() ) as $key ) {
		$url = get_theme_mod( "eminence_social_{$key}", '' );
		if ( ! empty( $url ) ) {
			$links[ $key ] = $url;
		}
	}

	return $links;
}

/**
 * Server-side consent check (research.md #3) — lets footer.php decide whether to output
 * the GA4 tag on page load, without waiting on JS to run first.
 *
 * @return bool True if the visitor has previously accepted non-essential cookies.
 */
function eminence_has_analytics_consent() {
	return isset( $_COOKIE[ EMINENCE_CONSENT_COOKIE ] ) && 'accepted' === $_COOKIE[ EMINENCE_CONSENT_COOKIE ];
}

/**
 * Testimonials (007-testimonials-page data-model.md).
 * A custom post type — not plain content — because testimonials repeat with a hard
 * consent gate (FR-008) and an "omit gracefully if empty" render rule (FR-002) that plain
 * block-editor content can't enforce. See research.md #1.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'eminence_testimonial',
			array(
				'labels'       => array(
					'name'          => __( 'Testimonials', 'eminence-consultant' ),
					'singular_name' => __( 'Testimonial', 'eminence-consultant' ),
					'add_new_item'  => __( 'Add New Testimonial', 'eminence-consultant' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-format-quote',
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
			)
		);

		register_taxonomy(
			'testimonial_type',
			'eminence_testimonial',
			array(
				'labels'            => array(
					'name'          => __( 'Testimonial Type', 'eminence-consultant' ),
					'singular_name' => __( 'Type', 'eminence-consultant' ),
				),
				'hierarchical'      => false,
				'show_admin_column' => true,
				'public'            => false,
				'show_ui'           => true,
			)
		);
	}
);

/**
 * Pre-create the two fixed testimonial_type terms (data-model.md: "Platform name, not
 * free-form" — same closed-set pattern as Social Links) so editors pick from Client /
 * Candidate rather than inventing new types.
 */
add_action(
	'init',
	function () {
		if ( ! term_exists( 'client', 'testimonial_type' ) ) {
			wp_insert_term( __( 'Client', 'eminence-consultant' ), 'testimonial_type', array( 'slug' => 'client' ) );
		}
		if ( ! term_exists( 'candidate', 'testimonial_type' ) ) {
			wp_insert_term( __( 'Candidate', 'eminence-consultant' ), 'testimonial_type', array( 'slug' => 'candidate' ) );
		}
	},
	20
);

/**
 * Registers a "Publishing Consent" meta box + save_post enforcement for any post type
 * that needs it (008-industry-leaders-page/research.md #1 — extracted from what was
 * originally 007-only code, so both features enforce the identical rule instead of two
 * copies of the same logic). A checkbox is the editorial interface; the real enforcement
 * is the save_post hook, not the control by itself.
 *
 * @param string $post_type       The post type to gate.
 * @param string $box_title       Meta box title.
 * @param string $checkbox_label  Label shown next to the consent checkbox.
 */
function eminence_register_consent_gate( $post_type, $box_title, $checkbox_label ) {
	$nonce_action = "eminence_{$post_type}_consent";
	$nonce_name   = "eminence_{$post_type}_consent_nonce";

	add_action(
		'add_meta_boxes',
		function () use ( $post_type, $box_title, $checkbox_label, $nonce_action, $nonce_name ) {
			add_meta_box(
				"eminence_{$post_type}_consent",
				$box_title,
				function ( $post ) use ( $checkbox_label, $nonce_action, $nonce_name ) {
					wp_nonce_field( $nonce_action, $nonce_name );
					$checked = get_post_meta( $post->ID, 'eminence_consent_obtained', true );
					?>
					<label>
						<input type="checkbox" name="eminence_consent_obtained" value="1" <?php checked( $checked, '1' ); ?> />
						<?php echo esc_html( $checkbox_label ); ?>
					</label>
					<?php
				},
				$post_type,
				'side',
				'high'
			);
		}
	);

	add_action(
		"save_post_{$post_type}",
		function ( $post_id ) use ( $nonce_action, $nonce_name ) {
			if ( ! isset( $_POST[ $nonce_name ] ) || ! wp_verify_nonce( $_POST[ $nonce_name ], $nonce_action ) ) {
				return;
			}
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			$consent_given = ! empty( $_POST['eminence_consent_obtained'] );
			update_post_meta( $post_id, 'eminence_consent_obtained', $consent_given ? '1' : '' );

			// wp_update_post() re-fires this same hook, but by then the post row already
			// shows post_status = draft, so the condition below is false on that second
			// pass and the recursion terminates itself after one extra (harmless) call.
			if ( ! $consent_given && 'publish' === get_post_status( $post_id ) ) {
				wp_update_post(
					array(
						'ID'          => $post_id,
						'post_status' => 'draft',
					)
				);
			}
		}
	);
}

eminence_register_consent_gate(
	'eminence_testimonial',
	__( 'Publishing Consent', 'eminence-consultant' ),
	__( 'Documented consent has been obtained from the person or company named in this testimonial (required to publish).', 'eminence-consultant' )
);

/**
 * All published testimonials regardless of type, for page-community.php's single combined
 * slider (2026-08-04) — client and candidate testimonials no longer sit in two separately
 * headed sections; each slide shows its own type badge instead (see testimonial-card.php).
 *
 * @return WP_Query
 */
function eminence_get_all_testimonials() {
	return new WP_Query(
		array(
			'post_type'      => 'eminence_testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
		)
	);
}

/**
 * Gallery Photo (008-industry-leaders-page data-model.md). Same consent-gate reasoning as
 * Testimonials (007) — a photo of an identifiable individual can't be published without
 * consent, enforced via the shared eminence_register_consent_gate() helper above.
 */
add_action(
	'init',
	function () {
		register_post_type(
			'eminence_gallery',
			array(
				'labels'       => array(
					'name'          => __( 'Industry Leaders Gallery', 'eminence-consultant' ),
					'singular_name' => __( 'Gallery Photo', 'eminence-consultant' ),
					'add_new_item'  => __( 'Add New Photo', 'eminence-consultant' ),
				),
				'public'       => false,
				'show_ui'      => true,
				'show_in_menu' => true,
				'menu_icon'    => 'dashicons-format-gallery',
				'supports'     => array( 'title', 'thumbnail' ),
			)
		);
	}
);

eminence_register_consent_gate(
	'eminence_gallery',
	__( 'Publishing Permission', 'eminence-consultant' ),
	__( 'Documented permission has been obtained from the identifiable individual(s) shown in this photo (required to publish).', 'eminence-consultant' )
);

/**
 * Published gallery photos, for page-community.php.
 *
 * @return WP_Query
 */
function eminence_get_gallery_photos() {
	return new WP_Query(
		array(
			'post_type'      => 'eminence_gallery',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		)
	);
}
