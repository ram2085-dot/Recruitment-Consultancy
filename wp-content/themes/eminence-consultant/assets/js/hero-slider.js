/**
 * Home hero background slider (2026-07-31), extended 2026-08-01 with slide-position dots
 * (click-to-jump + kept in sync with the active slide) after the business owner pointed
 * at sapphirerecruitment.ae as a preferred reference. Pure crossfade via a toggled
 * "is-active" class — no carousel library, consistent with this theme's "no framework"
 * decision (see specs/001-site-shell-navigation/research.md #4).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var slides = document.querySelectorAll( '.eminence-hero-slide' );
		var dots = document.querySelectorAll( '.eminence-hero-dot' );
		if ( slides.length < 2 ) {
			return;
		}

		var current = 0;
		var intervalId = null;

		function goTo( index ) {
			slides[ current ].classList.remove( 'is-active' );
			if ( dots[ current ] ) {
				dots[ current ].classList.remove( 'is-active' );
				dots[ current ].setAttribute( 'aria-selected', 'false' );
			}

			current = index;

			slides[ current ].classList.add( 'is-active' );
			if ( dots[ current ] ) {
				dots[ current ].classList.add( 'is-active' );
				dots[ current ].setAttribute( 'aria-selected', 'true' );
			}
		}

		function startAutoplay() {
			intervalId = setInterval( function () {
				goTo( ( current + 1 ) % slides.length );
			}, 5000 );
		}

		startAutoplay();

		dots.forEach( function ( dot, index ) {
			dot.addEventListener( 'click', function () {
				if ( index === current ) {
					return;
				}
				clearInterval( intervalId );
				goTo( index );
				startAutoplay();
			} );
		} );
	} );
} )();
