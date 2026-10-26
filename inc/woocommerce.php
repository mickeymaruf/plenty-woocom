<?php
/**
 * WooCommerce integration. Only loaded when WooCommerce is active.
 *
 * Template overrides go in woocommerce/ (copied from plugins/woocommerce/templates/).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

// Replace WooCommerce's default content wrappers with the theme's layout container.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action( 'woocommerce_before_main_content', 'plenty_wc_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'plenty_wc_wrapper_end', 10 );

function plenty_wc_wrapper_start(): void {
	echo '<main id="main" class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">';
}

function plenty_wc_wrapper_end(): void {
	echo '</main>';
}

/**
 * Cart link with item count, used in the header.
 */
function plenty_cart_link(): void {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="plenty-cart-link inline-flex items-center gap-2 text-sm font-medium hover:underline" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php esc_html_e( 'Cart', 'plenty' ); ?>
		<span class="inline-flex min-w-6 items-center justify-center rounded-full bg-neutral-900 px-1.5 text-xs text-white"><?php echo esc_html( $count ); ?></span>
	</a>
	<?php
}

// Refresh the header cart count after AJAX add-to-cart.
add_filter( 'woocommerce_add_to_cart_fragments', 'plenty_cart_link_fragment' );

function plenty_cart_link_fragment( array $fragments ): array {
	ob_start();
	plenty_cart_link();
	$fragments['a.plenty-cart-link'] = ob_get_clean();

	return $fragments;
}
