/**
 * Home hero background slider (2026-07-31). Pure crossfade via a toggled "is-active"
 * class — no carousel library, consistent with this theme's "no framework" decision
 * (see specs/001-site-shell-navigation/research.md #4).
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var slides = document.querySelectorAll( '.eminence-hero-slide' );
		if ( slides.length < 2 ) {
			return;
		}

		var current = 0;

		setInterval( function () {
			slides[ current ].classList.remove( 'is-active' );
			current = ( current + 1 ) % slides.length;
			slides[ current ].classList.add( 'is-active' );
		}, 5000 );
	} );
} )();
