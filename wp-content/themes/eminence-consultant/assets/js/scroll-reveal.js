/**
 * Scroll-reveal (2026-08-14, "luxury top class" design pass) — a restrained fade-up
 * entrance for content-page sections and cards; the one deliberate motion signature for
 * this pass, not scattered effects everywhere. Progressive enhancement: the hidden-state
 * class is added here in JS, not in markup/CSS, so if this script fails to load the
 * targeted elements simply render normally instead of staying invisible.
 */
( function () {
	'use strict';

	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		if ( ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var targets = document.querySelectorAll(
			'.eminence-page-content > h2, .eminence-page-content > h3, .eminence-page-content > p, ' +
			'.eminence-page-content > ul, .eminence-page-content > ol, .eminence-page-content > blockquote, ' +
			'.eminence-stat-card, .eminence-community-half'
		);

		if ( ! targets.length ) {
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-revealed' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ threshold: 0.15, rootMargin: '0px 0px -40px 0px' }
		);

		targets.forEach( function ( el, index ) {
			el.classList.add( 'eminence-reveal' );
			// Small stagger within each visual group so items don't all pop at once.
			el.style.transitionDelay = ( index % 4 ) * 80 + 'ms';
			observer.observe( el );
		} );
	} );
} )();
