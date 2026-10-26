/**
 * Plenty theme scripts. Plain JS, no build step; loaded with `defer`.
 */

// Mobile navigation toggle.
document.querySelectorAll( '[data-nav-toggle]' ).forEach( ( button ) => {
	const nav = document.getElementById( button.getAttribute( 'aria-controls' ) );

	button.addEventListener( 'click', () => {
		const open = button.getAttribute( 'aria-expanded' ) !== 'true';
		button.setAttribute( 'aria-expanded', String( open ) );
		nav.classList.toggle( 'hidden', ! open );
	} );
} );

// Home product grid: filter cards by category tab.
document.querySelectorAll( '[data-product-filter]' ).forEach( ( section ) => {
	const tabs = section.querySelectorAll( '[data-filter]' );
	const cards = section.querySelectorAll( '[data-categories]' );

	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', () => {
			const filter = tab.dataset.filter;

			tabs.forEach( ( other ) => other.setAttribute( 'aria-pressed', String( other === tab ) ) );
			cards.forEach( ( card ) => {
				card.hidden = filter !== 'all' && ! card.dataset.categories.split( ' ' ).includes( filter );
			} );
		} );
	} );
} );

// Product gallery: thumbnails swap the main image.
document.querySelectorAll( '[data-gallery]' ).forEach( ( gallery ) => {
	const main = gallery.querySelector( '[data-gallery-main]' );
	const thumbs = gallery.querySelectorAll( '[data-gallery-thumb]' );

	thumbs.forEach( ( thumb ) => {
		thumb.addEventListener( 'click', () => {
			main.src = thumb.dataset.src;
			main.srcset = thumb.dataset.srcset || '';
			thumbs.forEach( ( other ) => other.setAttribute( 'aria-current', String( other === thumb ) ) );
		} );
	} );
} );

// Product FAQ: "Show all" reveals questions beyond the first eight.
document.querySelectorAll( '[data-faq]' ).forEach( ( faq ) => {
	faq.querySelector( '[data-faq-show-all]' )?.addEventListener( 'click', () => {
		faq.querySelectorAll( '[data-faq-extra]' ).forEach( ( item ) => ( item.hidden = false ) );
		faq.querySelector( '[data-faq-more]' ).remove();
	} );
} );
