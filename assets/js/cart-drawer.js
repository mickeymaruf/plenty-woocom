/**
 * Cart drawer (inc/cart-drawer.php). Plain JS, no build step; loaded with `defer`.
 *
 * - Header cart icon opens the drawer instead of navigating to the cart page.
 * - The product page add-to-cart form is submitted over AJAX: the drawer opens straight away in a
 *   loading state, then shows the updated cart (or closes and shows the error under the form).
 * - Quantity and remove buttons in the drawer update the cart over AJAX.
 * - WooCommerce's own AJAX add-to-cart buttons (shop archives) also open the drawer.
 */
( () => {
	const drawer = document.querySelector( '[data-cart-drawer]' );
	const config = window.plentyCart;

	if ( ! drawer || ! config ) {
		return;
	}

	const panel = drawer.querySelector( '[data-cart-panel]' );
	const loaderText = drawer.querySelector( '[data-cart-loader-text]' );
	const announcer = drawer.querySelector( '[data-cart-announcer]' );
	const focusable = 'a[href], button:not([disabled]), input:not([disabled]), select, textarea, [tabindex]:not([tabindex="-1"])';
	let returnFocus = null;
	let busy = false;

	const isOpen = () => drawer.hasAttribute( 'data-open' );

	const focusFirst = () => drawer.querySelector( 'button[data-cart-close]' )?.focus( { preventScroll: true } );

	const open = () => {
		if ( isOpen() ) {
			return;
		}
		returnFocus = document.activeElement;
		drawer.inert = false;
		drawer.setAttribute( 'data-open', '' );
		document.documentElement.classList.add( 'overflow-hidden' );
		focusFirst();
	};

	const close = () => {
		if ( ! isOpen() ) {
			return;
		}
		drawer.removeAttribute( 'data-open' );
		drawer.inert = true;
		document.documentElement.classList.remove( 'overflow-hidden' );
		returnFocus?.focus?.( { preventScroll: true } );
	};

	const setBusy = ( message ) => {
		busy = Boolean( message );
		drawer.setAttribute( 'aria-busy', String( busy ) );
		if ( message ) {
			loaderText.textContent = message;
		}
	};

	const announce = ( message ) => {
		announcer.textContent = '';
		window.setTimeout( () => ( announcer.textContent = message ), 50 );
	};

	// Replace each fragment's element (keys are CSS selectors) with the fresh server markup.
	const applyFragments = ( fragments ) => {
		Object.entries( fragments || {} ).forEach( ( [ selector, html ] ) => {
			document.querySelectorAll( selector ).forEach( ( element ) => ( element.outerHTML = html ) );
		} );
	};

	const post = async ( url, body ) => {
		const response = await fetch( url, { method: 'POST', body, credentials: 'same-origin' } );
		if ( ! response.ok ) {
			throw new Error( response.statusText );
		}
		return response.json();
	};

	// Let WooCommerce scripts and other plugins know the cart changed.
	const triggerJquery = ( event, args ) => window.jQuery?.( document.body ).trigger( event, args );

	/* Header cart icon */

	document.addEventListener( 'click', ( event ) => {
		const link = event.target.closest( 'a.plenty-cart-link' );
		if ( link && ! event.metaKey && ! event.ctrlKey && ! event.shiftKey ) {
			event.preventDefault();
			open();
		}
	} );

	/* Close: backdrop, close button, Escape; keep Tab inside the open drawer */

	drawer.addEventListener( 'click', ( event ) => {
		if ( event.target.closest( '[data-cart-close]' ) ) {
			close();
		}
	} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( ! isOpen() ) {
			return;
		}
		if ( event.key === 'Escape' ) {
			close();
		}
		if ( event.key === 'Tab' ) {
			const items = [ ...panel.querySelectorAll( focusable ) ].filter( ( el ) => el.offsetParent !== null );
			const first = items[ 0 ];
			const last = items[ items.length - 1 ];
			if ( ! first ) {
				return;
			}
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && ( document.activeElement === last || ! panel.contains( document.activeElement ) ) ) {
				event.preventDefault();
				first.focus();
			}
		}
	} );

	/* Product page: add to cart */

	const setButtonBusy = ( button, isBusy ) => {
		if ( ! button ) {
			return;
		}
		button.setAttribute( 'aria-busy', String( isBusy ) );
		button.disabled = isBusy;
	};

	const showFormError = ( form, message ) => {
		let error = form.parentElement.querySelector( '[data-add-to-cart-error]' );
		if ( ! error ) {
			error = document.createElement( 'p' );
			error.className = 'rounded-lg bg-[#fef2f2] px-3 py-2 text-[13px] leading-5 text-[#cc1818]';
			error.setAttribute( 'role', 'alert' );
			error.setAttribute( 'data-add-to-cart-error', '' );
			form.after( error );
		}
		error.textContent = message;
	};

	const clearFormError = ( form ) => form.parentElement.querySelector( '[data-add-to-cart-error]' )?.remove();

	document.addEventListener( 'submit', async ( event ) => {
		const form = event.target.closest( 'form.cart' );

		// Only the product page buy panel; external products submit a GET form to another site.
		if ( ! form || ! form.closest( '.plenty-buy' ) || form.method.toLowerCase() !== 'post' ) {
			return;
		}

		event.preventDefault();
		if ( busy ) {
			return;
		}

		const button = event.submitter || form.querySelector( '[type="submit"]' );
		const data = new FormData( form, button?.name ? button : null );
		const productId = data.get( 'add-to-cart' ) || button?.value;

		// Renamed so WooCommerce's non-AJAX handler doesn't also add (and possibly redirect).
		data.delete( 'add-to-cart' );
		data.set( 'plenty_add_to_cart', productId );

		let failed = false;

		clearFormError( form );
		setButtonBusy( button, true );
		open();
		setBusy( config.i18n.adding );

		try {
			const result = await post( config.addUrl, data );
			applyFragments( result.fragments );

			if ( result.error ) {
				failed = true;
				showFormError( form, result.error );
			} else {
				announce( config.i18n.added );
				triggerJquery( 'added_to_cart', [ result.fragments, result.cart_hash ] );
				focusFirst(); // After the event: WooCommerce's handler re-applies the fragments.
			}
		} catch ( error ) {
			failed = true;
			showFormError( form, config.i18n.error );
		} finally {
			setBusy( false );
			setButtonBusy( button, false );
		}

		// On failure close the drawer and return to the (re-enabled) button, next to the error.
		if ( failed ) {
			returnFocus = button;
			close();
		}
	} );

	/* Drawer: quantity and remove */

	drawer.addEventListener( 'click', async ( event ) => {
		const button = event.target.closest( '[data-cart-update]' );
		if ( ! button || button.disabled || busy ) {
			return;
		}

		const { key, quantity, action } = button.dataset;
		const data = new FormData();
		data.set( 'cart_item_key', key );
		data.set( 'quantity', quantity );

		setBusy( config.i18n.updating );

		try {
			const result = await post( config.updateUrl, data );
			applyFragments( result.fragments );

			if ( result.error ) {
				drawer.querySelector( '[data-cart-error]' ).textContent = result.error;
			}

			triggerJquery( 'wc_fragments_refreshed' );
		} catch ( error ) {
			const slot = drawer.querySelector( '[data-cart-error]' );
			if ( slot ) {
				slot.textContent = config.i18n.error;
			}
		} finally {
			setBusy( false );
			// The content was re-rendered: put focus back on the same control, or the close button.
			const same = drawer.querySelector( `[data-cart-update][data-key="${ CSS.escape( key ) }"][data-action="${ action }"]:not([disabled])` );
			( same || drawer.querySelector( 'button[data-cart-close]' ) )?.focus( { preventScroll: true } );
		}
	} );

	/* WooCommerce AJAX add-to-cart buttons (shop archives) already apply the fragments; just open. */

	window.jQuery?.( document.body ).on( 'added_to_cart', () => open() );
} )();
