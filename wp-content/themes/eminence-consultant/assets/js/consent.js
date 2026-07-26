/**
 * Cookie/analytics consent handling (research.md #3, FR-013).
 *
 * - No GA4 script is present in the page at all until consent is "accepted" (server-side
 *   gate lives in footer.php for returning visitors who already accepted).
 * - This file shows the banner only to visitors with no recorded decision, and — for a
 *   visitor who clicks Accept in the current session — injects gtag.js immediately so
 *   they don't need to reload the page to start being tracked.
 * - `eminenceConsent` (cookieName, gaId) is localized from functions.php; this file never
 *   hardcodes either value.
 */
( function () {
	'use strict';

	function getCookie( name ) {
		var match = document.cookie.match( '(?:^|; )' + name + '=([^;]*)' );
		return match ? decodeURIComponent( match[ 1 ] ) : null;
	}

	function setCookie( name, value, days ) {
		var expires = new Date();
		expires.setTime( expires.getTime() + days * 24 * 60 * 60 * 1000 );
		document.cookie =
			name + '=' + encodeURIComponent( value ) + '; expires=' + expires.toUTCString() + '; path=/; SameSite=Lax';
	}

	function loadGA4( gaId ) {
		if ( ! gaId || 'G-XXXXXXXXXX' === gaId || window.eminenceGA4Loaded ) {
			return;
		}
		window.eminenceGA4Loaded = true;

		var script = document.createElement( 'script' );
		script.async = true;
		script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( gaId );
		document.head.appendChild( script );

		window.dataLayer = window.dataLayer || [];
		function gtag() {
			window.dataLayer.push( arguments );
		}
		window.gtag = gtag;
		gtag( 'js', new Date() );
		gtag( 'config', gaId );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( typeof eminenceConsent === 'undefined' ) {
			return;
		}

		var banner = document.getElementById( 'eminence-cookie-notice' );
		var acceptBtn = document.getElementById( 'eminence-cookie-accept' );
		var declineBtn = document.getElementById( 'eminence-cookie-decline' );

		if ( ! banner || ! acceptBtn || ! declineBtn ) {
			return;
		}

		var existingConsent = getCookie( eminenceConsent.cookieName );

		// Only first-time visitors (no recorded decision) see the banner (FR-013).
		if ( null === existingConsent ) {
			banner.hidden = false;
		}

		acceptBtn.addEventListener( 'click', function () {
			setCookie( eminenceConsent.cookieName, 'accepted', 365 );
			banner.hidden = true;
			loadGA4( eminenceConsent.gaId );
		} );

		declineBtn.addEventListener( 'click', function () {
			setCookie( eminenceConsent.cookieName, 'declined', 365 );
			banner.hidden = true;
			// Deliberately no analytics call here — declining must never load GA4.
		} );
	} );
} )();
