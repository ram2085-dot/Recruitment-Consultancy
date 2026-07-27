/**
 * Industry Leaders slider (008-industry-leaders-page, research.md #2). CSS scroll-snap
 * does the actual sliding; this just scrolls the track by one slide's width per click —
 * no carousel library, no manual index/transform tracking.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var slider = document.querySelector( '.eminence-slider' );
		if ( ! slider ) {
			return;
		}

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
} )();
