/**
 * TEMPORARY DEMO — Amazon-style checkout (inc/checkout-demo.php).
 * "Confirmar compra" buttons press WooCommerce's (hidden) place-order button, so every checkout and
 * payment-plugin handler still runs; the coupon box applies coupons through WooCommerce's own
 * wc-ajax endpoint and refreshes the totals.
 */
( ( $ ) => {
	const params = window.wc_checkout_params;
	if ( ! $ || ! params ) {
		return;
	}

	$( document ).on( 'click', '[data-demo-place-order]', () => document.getElementById( 'place_order' )?.click() );

	// Bottom bar total follows the summary after every refresh.
	$( document.body ).on( 'updated_checkout', () => {
		const total = document.querySelector( '.woocommerce-checkout-review-order-table .order-total td' );
		document.querySelectorAll( '[data-demo-total]' ).forEach( ( el ) => {
			el.innerHTML = total ? total.innerHTML : el.innerHTML;
		} );
	} );

	const box = document.querySelector( '[data-demo-coupon]' );
	if ( ! box ) {
		return;
	}

	const input = box.querySelector( 'input' );
	const message = box.querySelector( '[data-demo-coupon-message]' );

	const apply = () => {
		const code = input.value.trim();
		if ( ! code ) {
			input.focus();
			return;
		}

		$.post( params.wc_ajax_url.replace( '%%endpoint%%', 'apply_coupon' ), {
			security: params.apply_coupon_nonce,
			coupon_code: code,
			billing_email: $( 'input[name="billing_email"]' ).val(),
		} ).done( ( html ) => {
			const text = $( $.parseHTML( html ) ).text().trim();
			const failed = /woocommerce-error|is-error/.test( html );
			message.textContent = text;
			message.className = `mt-2 text-[12px] ${ failed ? 'is-error' : 'is-success' }`;
			if ( ! failed ) {
				input.value = '';
			}
			$( document.body ).trigger( 'update_checkout', { update_shipping_method: false } );
		} );
	};

	box.querySelector( '[data-demo-coupon-apply]' ).addEventListener( 'click', apply );
	input.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Enter' ) {
			event.preventDefault(); // Would submit the whole checkout form.
			apply();
		}
	} );
} )( window.jQuery );
