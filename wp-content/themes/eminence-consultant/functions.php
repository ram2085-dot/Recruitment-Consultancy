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
				'default'           => __( 'Permanent staffing and executive search, with deep expertise in the Transformer industry.', 'eminence-consultant' ),
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
 * Customizer: header phone number (visual reference: reference-design screenshot's header
 * CTA). Placeholder until the business owner confirms a real number (BRD Section 9).
 */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_setting(
			'eminence_phone_number',
			array(
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		$wp_customize->add_control(
			'eminence_phone_number',
			array(
				'label'   => __( 'Header Phone Number', 'eminence-consultant' ),
				'section' => 'title_tagline',
				'type'    => 'text',
			)
		);
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
 * Consent meta box (research.md #2). A checkbox is the editorial interface; the real
 * enforcement is the save_post hook below, not this control by itself.
 */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box(
			'eminence_testimonial_consent',
			__( 'Publishing Consent', 'eminence-consultant' ),
			function ( $post ) {
				wp_nonce_field( 'eminence_testimonial_consent', 'eminence_testimonial_consent_nonce' );
				$checked = get_post_meta( $post->ID, 'eminence_consent_obtained', true );
				?>
				<label>
					<input type="checkbox" name="eminence_consent_obtained" value="1" <?php checked( $checked, '1' ); ?> />
					<?php esc_html_e( 'Documented consent has been obtained from the person or company named in this testimonial (required to publish).', 'eminence-consultant' ); ?>
				</label>
				<?php
			},
			'eminence_testimonial',
			'side',
			'high'
		);
	}
);

/**
 * FR-008 enforcement: a testimonial cannot be `publish` status unless consent is
 * recorded, regardless of what the editor tried to save — see research.md #2 for why
 * this has to be a save_post hook rather than only a UI checkbox.
 */
add_action(
	'save_post_eminence_testimonial',
	function ( $post_id ) {
		if ( ! isset( $_POST['eminence_testimonial_consent_nonce'] )
			|| ! wp_verify_nonce( $_POST['eminence_testimonial_consent_nonce'], 'eminence_testimonial_consent' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$consent_given = ! empty( $_POST['eminence_consent_obtained'] );
		update_post_meta( $post_id, 'eminence_consent_obtained', $consent_given ? '1' : '' );

		// wp_update_post() re-fires this same hook, but by then the post row already
		// shows post_status = draft, so the condition below is false on that second pass
		// and the recursion terminates itself after one extra (harmless) invocation.
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

/**
 * Published testimonials of one type, for page-testimonials.php.
 *
 * @param string $type_slug 'client' or 'candidate'.
 * @return WP_Query
 */
function eminence_get_testimonials( $type_slug ) {
	return new WP_Query(
		array(
			'post_type'      => 'eminence_testimonial',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'tax_query'      => array(
				array(
					'taxonomy' => 'testimonial_type',
					'field'    => 'slug',
					'terms'    => $type_slug,
				),
			),
		)
	);
}
