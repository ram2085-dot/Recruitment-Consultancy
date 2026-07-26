/**
 * Mobile navigation toggle (FR-004). No framework, no dependencies.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var toggle = document.querySelector( '.eminence-nav-toggle' );
		var nav = document.getElementById( 'eminence-primary-nav' );

		if ( ! toggle || ! nav ) {
			return;
		}

		function closeMenu() {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}

		function toggleMenu() {
			var isOpen = nav.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
		}

		toggle.addEventListener( 'click', toggleMenu );

		// Close the menu whenever a nav link is activated (small-viewport UX).
		nav.addEventListener( 'click', function ( event ) {
			if ( event.target.tagName === 'A' ) {
				closeMenu();
			}
		} );

		// Close on Escape for keyboard users.
		document.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
				closeMenu();
				toggle.focus();
			}
		} );
	} );
} )();
