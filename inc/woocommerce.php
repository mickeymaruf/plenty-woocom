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
	echo '<main id="main" class="container-site py-12">';
}

function plenty_wc_wrapper_end(): void {
	echo '</main>';
}

/**
 * Cart link (shopping bag icon) with item count, used in the header.
 */
function plenty_cart_link(): void {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="plenty-cart-link relative flex size-8 items-center justify-center hover:opacity-70" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php echo plenty_asset_img( 'icon-bag.svg', 26, 28.67, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="sr-only">
			<?php
			/* translators: %d: number of items in the cart. */
			printf( esc_html( _n( 'Cart, %d item', 'Cart, %d items', $count, 'plenty' ) ), (int) $count );
			?>
		</span>
		<?php if ( $count ) : ?>
			<span class="absolute -right-1.5 -top-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-navy px-1 text-[11px] font-semibold leading-none text-white" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
		<?php endif; ?>
	</a>
	<?php
}

// Product page button label (editable in Product Page Settings); external products keep their own label.
add_filter( 'woocommerce_product_single_add_to_cart_text', 'plenty_single_add_to_cart_text', 10, 2 );

function plenty_single_add_to_cart_text( string $text, $product ): string {
	return $product instanceof WC_Product && $product->is_type( 'external' ) ? $text : (string) plenty_content( 'pdp_add_to_cart_label' );
}

// Refresh the header cart count after AJAX add-to-cart.
add_filter( 'woocommerce_add_to_cart_fragments', 'plenty_cart_link_fragment' );

function plenty_cart_link_fragment( array $fragments ): array {
	ob_start();
	plenty_cart_link();
	$fragments['a.plenty-cart-link'] = ob_get_clean();

	return $fragments;
}
