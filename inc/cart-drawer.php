<?php
/**
 * Cart drawer (Figma "CartDrawer", node 33:9628): off-canvas mini cart opened from the header
 * cart icon and after adding to cart, so shoppers aren't redirected.
 *
 * The drawer content is rendered server-side (template-parts/cart/drawer-content.php) and kept in
 * sync through WooCommerce cart fragments. Two wc-ajax endpoints back assets/js/cart-drawer.js:
 *   - plenty_add_to_cart:       runs WooCommerce's own add-to-cart form handler (simple, variable,
 *                               grouped products, validation and stock errors) without redirecting.
 *   - plenty_update_cart_item:  changes a line's quantity (0 removes it).
 * Both respond with { fragments, cart_hash, error }.
 *
 * Only loaded when WooCommerce is active. Disabled on the cart and checkout pages, where the page
 * itself shows the cart and the header icon links there as normal.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

function plenty_cart_drawer_enabled(): bool {
	return ! is_cart() && ! is_checkout();
}

add_action( 'wp_enqueue_scripts', 'plenty_cart_drawer_assets' );

function plenty_cart_drawer_assets(): void {
	if ( ! plenty_cart_drawer_enabled() ) {
		return;
	}

	wp_enqueue_script(
		'plenty-cart-drawer',
		PLENTY_URI . '/assets/js/cart-drawer.js',
		array(),
		(string) filemtime( PLENTY_DIR . '/assets/js/cart-drawer.js' ),
		array( 'strategy' => 'defer' )
	);

	$config = array(
		'addUrl'    => WC_AJAX::get_endpoint( 'plenty_add_to_cart' ),
		'updateUrl' => WC_AJAX::get_endpoint( 'plenty_update_cart_item' ),
		'i18n'      => array(
			'adding'   => __( 'Adding to cart…', 'plenty' ),
			'updating' => __( 'Updating cart…', 'plenty' ),
			'added'    => __( 'Added to your cart.', 'plenty' ),
			'error'    => __( 'Something went wrong. Please try again.', 'plenty' ),
		),
	);
	wp_add_inline_script( 'plenty-cart-drawer', 'window.plentyCart = ' . wp_json_encode( $config ) . ';', 'before' );
}

/**
 * Drawer shell, printed in the footer. The content inside is replaced by cart fragments.
 */
function plenty_cart_drawer(): void {
	if ( plenty_cart_drawer_enabled() ) {
		get_template_part( 'template-parts/cart/drawer' );
	}
}

/**
 * Drawer content (header, free-shipping bar, items, totals).
 */
function plenty_cart_drawer_content(): void {
	get_template_part( 'template-parts/cart/drawer-content' );
}

add_filter( 'woocommerce_add_to_cart_fragments', 'plenty_cart_drawer_fragment' );

function plenty_cart_drawer_fragment( array $fragments ): array {
	ob_start();
	plenty_cart_drawer_content();
	$fragments['div[data-cart-drawer-content]'] = ob_get_clean();

	return $fragments;
}

/**
 * Free-shipping progress for the drawer banner, based on the "Free shipping from" setting
 * (0 = every order ships free). Uses the subtotal after discounts, like WooCommerce's Free
 * Shipping method.
 *
 * @return array{qualifies: bool, remaining: float, percent: float}
 */
function plenty_cart_free_shipping(): array {
	$minimum  = (float) plenty_content( 'cart_free_shipping_min' );
	$cart     = WC()->cart;
	$subtotal = (float) $cart->get_displayed_subtotal() - (float) $cart->get_discount_total();

	if ( $cart->display_prices_including_tax() ) {
		$subtotal -= (float) $cart->get_discount_tax();
	}

	return array(
		'qualifies' => $minimum <= 0 || $subtotal >= $minimum,
		'remaining' => max( 0.0, $minimum - $subtotal ),
		'percent'   => $minimum > 0 ? min( 100.0, max( 0.0, $subtotal / $minimum * 100 ) ) : 100.0,
	);
}

/**
 * Secondary line under a cart item's name: variation values (e.g. "Queen"), otherwise the
 * product's categories, joined with " · ".
 */
function plenty_cart_item_meta( array $cart_item ): string {
	$parts = array();

	foreach ( (array) ( $cart_item['variation'] ?? array() ) as $attribute => $value ) {
		$taxonomy = str_replace( 'attribute_', '', $attribute );
		$term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $value, $taxonomy ) : false;
		$parts[]  = $term ? $term->name : $value;
	}

	if ( ! $parts ) {
		$terms = get_the_terms( $cart_item['product_id'], 'product_cat' );
		$parts = is_array( $terms ) ? wp_list_pluck( $terms, 'name' ) : array();
	}

	return implode( ' · ', array_filter( array_map( 'wc_clean', $parts ) ) );
}

/**
 * JSON response shared by both endpoints. Always includes fresh fragments so the UI matches
 * the cart even when the request failed.
 */
function plenty_cart_send_response( string $error = '' ): void {
	WC()->cart->calculate_totals();

	wp_send_json(
		array(
			'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
			'cart_hash' => WC()->cart->get_cart_hash(),
			'error'     => $error,
		)
	);
}

/**
 * First error notice WooCommerce queued during this request (as plain text), then clear all
 * notices so they don't reappear on the next page load.
 */
function plenty_cart_take_error(): string {
	$errors = wc_get_notices( 'error' );
	wc_clear_notices();

	return $errors ? trim( wp_strip_all_tags( $errors[0]['notice'] ?? '' ) ) : '';
}

add_action( 'wc_ajax_plenty_add_to_cart', 'plenty_ajax_add_to_cart' );

function plenty_ajax_add_to_cart(): void {
	// The product page form is posted with "add-to-cart" renamed, so WooCommerce's own
	// wp_loaded handler (which may redirect) doesn't pick it up first.
	$product_id = isset( $_POST['plenty_add_to_cart'] ) ? absint( wp_unslash( $_POST['plenty_add_to_cart'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification -- same as WooCommerce's add-to-cart form.

	if ( ! $product_id ) {
		plenty_cart_send_response( __( 'Please choose a product.', 'plenty' ) );
	}

	$added = false;
	add_action( 'woocommerce_add_to_cart', function () use ( &$added ) {
		$added = true;
	} );
	add_filter( 'woocommerce_add_to_cart_redirect', '__return_false', PHP_INT_MAX );
	add_filter( 'pre_option_woocommerce_cart_redirect_after_add', fn() => 'no' );

	$_REQUEST['add-to-cart'] = $product_id;
	WC_Form_Handler::add_to_cart_action();

	$error = plenty_cart_take_error();

	if ( ! $error && ! $added ) {
		$error = __( 'This product couldn’t be added to your cart.', 'plenty' );
	}

	plenty_cart_send_response( $error );
}

add_action( 'wc_ajax_plenty_update_cart_item', 'plenty_ajax_update_cart_item' );

function plenty_ajax_update_cart_item(): void {
	// phpcs:disable WordPress.Security.NonceVerification -- the cart item key scopes the change to the visitor's own cart (as in WooCommerce's remove_from_cart endpoint).
	$key      = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	$quantity = isset( $_POST['quantity'] ) ? (int) wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 0;
	// phpcs:enable
	$item = $key ? WC()->cart->get_cart_item( $key ) : array();

	if ( ! $item ) {
		plenty_cart_send_response( __( 'This item is no longer in your cart.', 'plenty' ) );
	}

	if ( $quantity <= 0 ) {
		WC()->cart->remove_cart_item( $key );
		plenty_cart_send_response( plenty_cart_take_error() );
	}

	$max = $item['data']->get_max_purchase_quantity();
	if ( $max > 0 && $quantity > $max ) {
		/* translators: %d: maximum quantity. */
		plenty_cart_send_response( sprintf( __( 'Only %d available.', 'plenty' ), $max ) );
	}

	if ( apply_filters( 'woocommerce_update_cart_validation', true, $key, $item, $quantity ) ) {
		WC()->cart->set_quantity( $key, $quantity, true );
	}

	plenty_cart_send_response( plenty_cart_take_error() );
}
