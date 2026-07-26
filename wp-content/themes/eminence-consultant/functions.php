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
