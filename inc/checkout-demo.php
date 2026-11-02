<?php
/**
 * TEMPORARY DEMO — Amazon-style checkout, to show what a fully custom classic checkout can look like.
 * Not for production. Remove with: delete this file, woocommerce/checkout/form-checkout.php,
 * assets/css/checkout-demo.css, assets/js/checkout-demo.js and the require line in functions.php.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

// The demo template places payment and the summary itself; undo the theme's checkout layout hooks.
remove_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment' );
remove_action( 'woocommerce_checkout_before_order_review_heading', 'plenty_checkout_summary_start' );
remove_action( 'woocommerce_checkout_after_order_review', 'plenty_checkout_summary_end' );
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );

// Coupon moves into the summary box; no "ship to a different address", no order notes.
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );

// Fields: optional ones stripped, Spanish labels, a DNI field added, reordered into a 2-column grid.
add_filter( 'woocommerce_checkout_fields', 'plenty_demo_checkout_fields', 20 );

function plenty_demo_checkout_fields( array $fields ): array {
	unset( $fields['billing']['billing_company'], $fields['billing']['billing_address_2'] );

	$layout = array(
		'billing_first_name' => array( 'Nombre', 10, 'half' ),
		'billing_last_name'  => array( 'Apellido', 20, 'half' ),
		'billing_dni'        => array( 'DNI', 30, 'half' ),
		'billing_phone'      => array( 'Teléfono', 40, 'half' ),
		'billing_email'      => array( 'Email', 50, 'full' ),
		'billing_address_1'  => array( 'Calle y número', 60, 'full' ),
		'billing_city'       => array( 'Ciudad', 70, 'half' ),
		'billing_state'      => array( 'Provincia', 80, 'half' ),
		'billing_postcode'   => array( 'Código postal', 90, 'half' ),
		'billing_country'    => array( 'País', 100, 'hidden' ), // Only Argentina is sold to; kept as a hidden input.
	);

	$fields['billing']['billing_dni'] = array(
		'type'     => 'text',
		'required' => true,
		'label'    => 'DNI',
	);

	foreach ( $layout as $key => [ $label, $priority, $width ] ) {
		if ( isset( $fields['billing'][ $key ] ) ) {
			$fields['billing'][ $key ]['label']       = $label;
			$fields['billing'][ $key ]['priority']    = $priority;
			$fields['billing'][ $key ]['placeholder'] = '';
			$fields['billing'][ $key ]['class']       = array( 'demo-field', "demo-field--{$width}" );
		}
	}
	$fields['billing']['billing_phone']['required'] = true;

	return $fields;
}

// WooCommerce's address script re-applies these labels, placeholders and priorities in the browser,
// so they must match the layout above (Argentina's own format would say "Province" in English).
add_filter( 'woocommerce_get_country_locale_default', 'plenty_demo_locale_default' );
add_filter( 'woocommerce_get_country_locale', 'plenty_demo_country_locale' );

function plenty_demo_locale_default( array $locale ): array {
	$demo = array(
		'first_name' => array( 'Nombre', 10 ),
		'last_name'  => array( 'Apellido', 20 ),
		'address_1'  => array( 'Calle y número', 60 ),
		'city'       => array( 'Ciudad', 70 ),
		'state'      => array( 'Provincia', 80 ),
		'postcode'   => array( 'Código postal', 90 ),
		'country'    => array( 'País', 100 ),
		'phone'      => array( 'Teléfono', 40 ),
	);
	foreach ( $demo as $key => [ $label, $priority ] ) {
		$locale[ $key ]['label']       = $label;
		$locale[ $key ]['priority']    = $priority;
		$locale[ $key ]['placeholder'] = '';
	}
	$locale['phone']['required'] = true;

	return $locale;
}

function plenty_demo_country_locale( array $locale ): array {
	$locale['AR']['state']['label']    = 'Provincia';
	$locale['AR']['postcode']['label'] = 'Código postal';

	return $locale;
}

add_action( 'wp_enqueue_scripts', 'plenty_demo_checkout_assets' );

function plenty_demo_checkout_assets(): void {
	if ( ! is_checkout() || is_order_received_page() ) {
		return;
	}

	wp_enqueue_style( 'plenty-checkout-demo', PLENTY_URI . '/assets/css/checkout-demo.css', array(), (string) filemtime( PLENTY_DIR . '/assets/css/checkout-demo.css' ) );
	wp_enqueue_script( 'plenty-checkout-demo', PLENTY_URI . '/assets/js/checkout-demo.js', array( 'jquery', 'wc-checkout' ), (string) filemtime( PLENTY_DIR . '/assets/js/checkout-demo.js' ), true );
}
