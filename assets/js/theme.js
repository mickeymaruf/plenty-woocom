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

// Product page sizes: the option buttons (radios) mirror WooCommerce's hidden variation selects,
// whose script only speaks jQuery events. A found variation swaps in its price and installments
// markup (plenty_price / plenty_installments in its data); a reset restores the page's own.
document.querySelectorAll( '.plenty-buy form.variations_form' ).forEach( ( form ) => {
	const $ = window.jQuery;
	if ( ! $ ) {
		return;
	}

	const parts = [ ...form.closest( '.plenty-buy' ).querySelectorAll( '[data-variation-html]' ) ].map( ( el ) => ( { el, key: el.dataset.variationHtml, initial: el.innerHTML } ) );
	const groups = form.querySelectorAll( '[data-attribute-name]' );
	const selectFor = ( group ) => group.querySelector( `select[name="${ group.dataset.attributeName }"]` );

	// WooCommerce removes options no variation matches and disables inactive (out-of-stock) ones.
	const sync = () => groups.forEach( ( group ) => {
		const select = selectFor( group );
		group.querySelectorAll( 'input[type="radio"]' ).forEach( ( radio ) => {
			const option = [ ...select.options ].find( ( item ) => item.value === radio.value );
			radio.disabled = ! option || option.disabled;
			radio.checked = select.value === radio.value;
		} );
	} );

	form.addEventListener( 'change', ( event ) => {
		const group = event.target.closest( '[data-attribute-name]' );
		if ( group && event.target.type === 'radio' ) {
			$( selectFor( group ) ).val( event.target.value ).trigger( 'change' );
		}
	} );

	$( form )
		.on( 'woocommerce_update_variation_values reset_data found_variation', sync )
		.on( 'found_variation', ( event, variation ) => parts.forEach( ( { el, key } ) => {
			if ( typeof variation[ key ] === 'string' ) {
				el.innerHTML = variation[ key ];
			}
		} ) )
		.on( 'reset_data', () => parts.forEach( ( { el, initial } ) => {
			el.innerHTML = initial;
		} ) );

	sync();
} );

// FAQ (product and About pages): "Show all" reveals questions beyond the first eight; one question
// open at a time, answers slide open and closed. data-closing turns the icon back while closing.
document.querySelectorAll( '[data-faq]' ).forEach( ( faq ) => {
	const items = faq.querySelectorAll( '[data-faq-item]' );
	const duration = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 0 : 300;
	const animations = new WeakMap();

	const toggle = ( item, open ) => {
		const panel = item.querySelector( '[data-faq-panel]' );
		const from = item.open ? panel.getBoundingClientRect().height : 0; // Mid-animation height when interrupted.

		animations.get( item )?.cancel();
		item.open = true;
		item.toggleAttribute( 'data-closing', ! open );

		const animation = panel.animate( { height: [ `${ from }px`, open ? `${ panel.scrollHeight }px` : '0px' ] }, { duration, easing: 'ease-out' } );
		animations.set( item, animation );
		animation.onfinish = () => {
			animations.delete( item );
			if ( ! open ) {
				item.open = false;
				item.removeAttribute( 'data-closing' );
			}
		};
	};

	items.forEach( ( item ) => {
		// The shared name makes the browser close the others instantly; the script closes them animated instead.
		item.removeAttribute( 'name' );

		item.querySelector( 'summary' ).addEventListener( 'click', ( event ) => {
			event.preventDefault();
			const open = ! item.open || item.hasAttribute( 'data-closing' );

			if ( open ) {
				items.forEach( ( other ) => other !== item && other.open && ! other.hasAttribute( 'data-closing' ) && toggle( other, false ) );
			}
			toggle( item, open );
		} );
	} );

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
