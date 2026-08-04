/**
 * Generic content slider — wires up every .eminence-slider on the page independently
 * (querySelectorAll, not the single-instance querySelector this started as). Needed once
 * page-community.php put two sliders (testimonials, industry leaders) side by side on one
 * page (2026-08-04) — a single-instance script would have left the second slider's
 * buttons dead. CSS scroll-snap does the actual sliding; this just scrolls each track by
 * one slide's width per click — no carousel library, no manual index/transform tracking.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var sliders = document.querySelectorAll( '.eminence-slider' );

		sliders.forEach( function ( slider ) {
			var track = slider.querySelector( '.eminence-slider-track' );
			var prevBtn = slider.querySelector( '.eminence-slider-prev' );
			var nextBtn = slider.querySelector( '.eminence-slider-next' );
			var firstSlide = slider.querySelector( '.eminence-slider-slide' );

			if ( ! track || ! firstSlide ) {
				return;
			}

			function slideWidth() {
				return firstSlide.getBoundingClientRect().width + 16; // + gap
			}

			if ( prevBtn ) {
				prevBtn.addEventListener( 'click', function () {
					track.scrollBy( { left: -slideWidth(), behavior: 'smooth' } );
				} );
			}

			if ( nextBtn ) {
				nextBtn.addEventListener( 'click', function () {
					track.scrollBy( { left: slideWidth(), behavior: 'smooth' } );
				} );
			}
		} );
	} );
} )();
