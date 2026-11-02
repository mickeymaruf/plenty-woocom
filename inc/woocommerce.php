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
 * Category filter tabs for the product grids (home section and shop page): the categories picked
 * in Home Page → Products, or every top-level category with products.
 *
 * @return WP_Term[]
 */
function plenty_product_filter_categories(): array {
	$category_ids = array_filter( array_map( 'intval', (array) plenty_content( 'products_categories' ) ) );
	$categories   = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'include'    => $category_ids,
			'orderby'    => $category_ids ? 'include' : 'menu_order',
			'parent'     => $category_ids ? '' : 0,
			'exclude'    => $category_ids ? array() : array( (int) get_option( 'default_product_cat' ) ),
		)
	);

	return is_wp_error( $categories ) ? array() : $categories;
}

// The shop page shows neither the result count nor the sorting dropdown (not in the design).
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Cart link (shopping bag icon) with item count, used in the header.
 */
function plenty_cart_link(): void {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	?>
	<a class="plenty-cart-link relative flex size-8 items-center justify-center hover:opacity-70" href="<?php echo esc_url( wc_get_cart_url() ); ?>" <?php echo function_exists( 'plenty_cart_drawer_enabled' ) && plenty_cart_drawer_enabled() ? 'aria-controls="cart-drawer" aria-haspopup="dialog"' : ''; ?>>
		<?php echo plenty_asset_img( 'icon-bag.svg', 26, 28.67, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="sr-only">
			<?php
			/* translators: %d: number of items in the cart. */
			printf( esc_html( _n( 'Carrito, %d producto', 'Carrito, %d productos', $count, 'plenty' ) ), (int) $count );
			?>
		</span>
		<?php if ( $count ) : ?>
			<span class="absolute -right-1.5 -top-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-navy px-1 text-[11px] font-semibold leading-none text-white" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
		<?php endif; ?>
	</a>
	<?php
}

/**
 * Prices for the product page price block and installments, as displayed in the shop (incl./excl.
 * tax per settings). A variable product uses its cheapest variation until a size is picked.
 *
 * @return array{ regular: float, current: float, discount: int, pre_tax: float }
 */
function plenty_product_prices( WC_Product $product ): array {
	if ( $product->is_type( 'variable' ) ) {
		$prices  = $product->get_variation_prices( true )['price']; // Sorted by price, cheapest first.
		$product = $prices ? wc_get_product( array_key_first( $prices ) ) : $product;
	}

	$price    = $product->get_price();
	$regular  = '' !== (string) $product->get_regular_price() ? (float) wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) : 0.0;
	$current  = '' !== (string) $price ? (float) wc_get_price_to_display( $product, array( 'price' => $price ) ) : 0.0;
	$discount = $regular > $current && $current > 0 ? (int) round( ( $regular - $current ) / $regular * 100 ) : 0;
	$pre_tax  = wc_tax_enabled() && $current > 0 ? (float) wc_get_price_excluding_tax( $product, array( 'price' => $price ) ) : 0.0;

	return compact( 'regular', 'current', 'discount', 'pre_tax' );
}

// Each variation carries its own price block and installments markup, which assets/js/theme.js swaps
// in when a size is picked (the formatting stays in PHP).
add_filter( 'woocommerce_available_variation', 'plenty_variation_buy_panel_html', 10, 3 );

function plenty_variation_buy_panel_html( array $data, $product, $variation ): array {
	foreach ( array( 'price', 'installments' ) as $part ) {
		ob_start();
		get_template_part( "template-parts/product/{$part}", null, array( 'product' => $variation ) );
		$data[ "plenty_{$part}" ] = ob_get_clean();
	}

	return $data;
}

// Out-of-stock sizes can't be picked (WooCommerce disables inactive variation options).
add_filter( 'woocommerce_variation_is_active', 'plenty_variation_is_active', 10, 2 );

function plenty_variation_is_active( bool $active, $variation ): bool {
	return $active && $variation->is_in_stock();
}

// Checkout (classic): customer details and payment on the left, the order summary ("Your order" +
// review table) wrapped on the right; laid out in src/css/app.css. WooCommerce refreshes both parts
// by selector (update_order_review fragments), so payment can live outside #order_review.
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_checkout_after_customer_details', 'woocommerce_checkout_payment' );
add_action( 'woocommerce_checkout_before_order_review_heading', 'plenty_checkout_summary_start' );
add_action( 'woocommerce_checkout_after_order_review', 'plenty_checkout_summary_end' );

function plenty_checkout_summary_start(): void {
	echo '<div class="plenty-checkout-summary">';
}

function plenty_checkout_summary_end(): void {
	echo '</div>';
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
