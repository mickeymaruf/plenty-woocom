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

// Home product grid: filter cards by category tab; a navy pill slides behind the active tab.
document.querySelectorAll( '[data-product-filter]' ).forEach( ( section ) => {
	const tabs = section.querySelectorAll( '[data-filter]' );
	const cards = section.querySelectorAll( '[data-categories]' );
	const pill = section.querySelector( '[data-filter-pill]' );

	const movePill = () => {
		const active = section.querySelector( '[data-filter][aria-pressed="true"]' );

		if ( pill && active ) {
			// Sub-pixel rects (offsetLeft/offsetWidth round); relative to the bar's padding box, scroll included.
			const bar = pill.parentElement;
			const tab = active.getBoundingClientRect();
			const left = tab.left - bar.getBoundingClientRect().left - bar.clientLeft + bar.scrollLeft;

			pill.style.width = `${ tab.width }px`;
			pill.style.transform = `translateX(${ left }px)`;
		}
	};

	if ( pill ) {
		movePill();
		pill.getBoundingClientRect(); // Commit the first position before transitions are switched on.
		pill.dataset.ready = '';
		tabs.forEach( ( tab ) => tab.classList.remove( 'aria-pressed:bg-navy' ) );

		// Tab widths change when the web font loads or the label wraps.
		const observer = new ResizeObserver( movePill );
		tabs.forEach( ( tab ) => observer.observe( tab ) );
	}

	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', () => {
			const filter = tab.dataset.filter;

			tabs.forEach( ( other ) => other.setAttribute( 'aria-pressed', String( other === tab ) ) );
			movePill();
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

// Contact form: submit with fetch; loading button, field errors and a status message.
document.querySelectorAll( '[data-contact-form]' ).forEach( ( form ) => {
	const button = form.querySelector( '[data-contact-submit]' );
	const status = form.querySelector( '[data-contact-status]' );

	const setStatus = ( type, message ) => {
		status.textContent = message;
		status.dataset.type = type;
		status.hidden = ! message;
	};

	const clearErrors = () => {
		form.querySelectorAll( '[data-error-for]' ).forEach( ( el ) => ( el.textContent = '' ) );
		form.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => el.removeAttribute( 'aria-invalid' ) );
	};

	form.addEventListener( 'submit', async ( event ) => {
		event.preventDefault();
		if ( button.getAttribute( 'aria-busy' ) === 'true' ) {
			return;
		}

		const data = new FormData( form );
		data.set( 'ajax', '1' );

		clearErrors();
		setStatus( '', '' );
		button.setAttribute( 'aria-busy', 'true' );
		button.disabled = true;

		try {
			// getAttribute: the hidden "action" input shadows form.action.
			const response = await fetch( form.getAttribute( 'action' ), { method: 'POST', body: data, credentials: 'same-origin' } );
			const result = await response.json();

			if ( result.success ) {
				form.reset();
				setStatus( 'success', result.data.message );
			} else {
				Object.entries( result.data?.errors || {} ).forEach( ( [ name, message ] ) => {
					form.elements[ name ]?.setAttribute( 'aria-invalid', 'true' );
					form.querySelector( `[data-error-for="${ name }"]` ).textContent = message;
				} );
				setStatus( 'error', result.data?.message || form.dataset.errorMessage );
				form.querySelector( '[aria-invalid="true"]' )?.focus();
			}
		} catch ( error ) {
			setStatus( 'error', form.dataset.errorMessage );
		} finally {
			button.setAttribute( 'aria-busy', 'false' );
			button.disabled = false;
		}
	} );
} );
