<?php
/**
 * TEMPORARY DEMO — Amazon-style checkout layout (see inc/checkout-demo.php). Not for production.
 *
 * Based on WooCommerce's checkout/form-checkout.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Plenty
 * @version 9.4.0
 *
 * @var WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;

$cart  = WC()->cart;
$count = $cart->get_cart_contents_count();
$step  = 'flex items-center gap-3 text-[18px] font-bold text-ink';
$num   = 'flex size-7 shrink-0 items-center justify-center rounded-full bg-navy text-[13px] font-bold text-white';
$card  = 'rounded-xl border border-[#e5e7eb] bg-white p-5 sm:p-6';
$cta   = 'flex w-full items-center justify-center rounded-full bg-navy px-6 py-3 text-[14px] font-bold text-white transition hover:bg-ink';

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>
<div class="demo-checkout">
	<div class="mb-6 flex items-center justify-between gap-4 border-b border-[#e5e7eb] pb-4">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="shrink-0"><?php echo plenty_asset_img( 'logo.png', 93, 40, array( 'class' => 'block h-10 w-auto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<p class="text-center text-[20px] font-semibold text-ink sm:text-[24px]">
			Finalizar compra <a class="text-[16px] font-normal text-[#368cf0] hover:underline" href="<?php echo esc_url( wc_get_cart_url() ); ?>">(<?php echo esc_html( sprintf( _n( '%d producto', '%d productos', $count, 'plenty' ), $count ) ); ?>)</a>
		</p>
		<p class="flex shrink-0 items-center gap-1.5 text-[12px] font-semibold text-muted">
			<svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
			Compra segura
		</p>
	</div>

	<form name="checkout" method="post" class="checkout woocommerce-checkout grid grid-cols-1 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_340px]" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">
		<div class="flex min-w-0 flex-col gap-6">
			<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

			<section class="<?php echo esc_attr( $card ); ?>" id="customer_details">
				<h2 class="<?php echo esc_attr( $step ); ?>"><span class="<?php echo esc_attr( $num ); ?>">1</span> Datos de entrega</h2>
				<div class="woocommerce-billing-fields mt-5">
					<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
					<div class="woocommerce-billing-fields__field-wrapper demo-fields">
						<?php foreach ( $checkout->get_checkout_fields( 'billing' ) as $key => $field ) : ?>
							<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
						<?php endforeach; ?>
					</div>
					<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
				</div>
			</section>

			<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

			<section class="<?php echo esc_attr( $card ); ?>">
				<h2 class="<?php echo esc_attr( $step ); ?>"><span class="<?php echo esc_attr( $num ); ?>">2</span> Método de pago</h2>
				<div class="mt-5"><?php woocommerce_checkout_payment(); ?></div>
			</section>

			<section class="<?php echo esc_attr( $card ); ?>">
				<h2 class="<?php echo esc_attr( $step ); ?>"><span class="<?php echo esc_attr( $num ); ?>">3</span> Revisá tus productos y el envío</h2>

				<?php if ( function_exists( 'plenty_shipping_is_quoted' ) && plenty_shipping_is_quoted() ) : ?>
					<div class="mt-5 rounded-lg bg-[#eef3f8] px-4 py-3">
						<p class="text-[15px] font-bold text-[#007a55]">Envío: <?php echo esc_html( plenty_content( 'shipping_quote_label' ) ); ?></p>
						<p class="mt-0.5 text-[13px] text-muted"><?php echo esc_html( plenty_content( 'shipping_quote_note' ) ); ?></p>
					</div>
				<?php endif; ?>

				<ul class="mt-4 divide-y divide-[#f3f4f6]">
					<?php foreach ( $cart->get_cart() as $item ) : ?>
						<?php
						$product = $item['data'];
						if ( ! $product || ! $product->exists() || $item['quantity'] <= 0 ) {
							continue;
						}
						?>
						<li class="flex gap-4 py-4">
							<?php echo $product->get_image( 'thumbnail', array( 'class' => 'size-20 shrink-0 rounded-lg object-cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<div class="min-w-0 flex-1">
								<p class="text-[15px] font-semibold text-ink"><?php echo esc_html( $product->get_name() ); ?></p>
								<p class="mt-1 text-[16px] font-bold text-[#b12704]"><?php echo $cart->get_product_subtotal( $product, $item['quantity'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
								<p class="mt-1 text-[13px] text-muted">Cantidad: <?php echo esc_html( $item['quantity'] ); ?> · <span class="text-[#007a55]">En stock</span></p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>

				<div class="mt-2 flex flex-col items-center gap-4 rounded-lg border border-[#e5e7eb] p-4 sm:flex-row">
					<button type="button" class="<?php echo esc_attr( $cta ); ?> sm:w-auto" data-demo-place-order>Confirmar compra</button>
					<div>
						<p class="text-[18px] font-bold text-[#b12704]">Total del pedido: <span data-demo-total><?php echo $cart->get_total(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span></p>
						<p class="text-[12px] text-muted">Al confirmar aceptás los términos y condiciones de Plenty.</p>
					</div>
				</div>
			</section>
		</div>

		<aside class="lg:sticky lg:top-6">
			<div class="<?php echo esc_attr( $card ); ?> flex flex-col gap-4">
				<button type="button" class="<?php echo esc_attr( $cta ); ?>" data-demo-place-order>Confirmar compra</button>
				<p class="text-center text-[12px] leading-4 text-muted">Al confirmar aceptás los términos y condiciones y la política de privacidad de Plenty.</p>
				<hr class="border-[#e5e7eb]">
				<h3 class="text-[18px] font-bold text-ink">Resumen del pedido</h3>
				<div id="order_review" class="woocommerce-checkout-review-order"><?php woocommerce_order_review(); ?></div>

				<?php if ( wc_coupons_enabled() ) : ?>
					<div class="border-t border-[#e5e7eb] pt-4" data-demo-coupon>
						<label class="text-[13px] font-semibold text-ink" for="demo_coupon_code">¿Tenés un cupón de descuento?</label>
						<div class="mt-2 flex gap-2">
							<input class="min-w-0 flex-1 rounded-lg border border-[#d9dee5] px-3 py-2 text-[14px] outline-none focus:border-navy" type="text" id="demo_coupon_code" autocomplete="off" placeholder="Código">
							<button type="button" class="rounded-lg border border-navy px-4 text-[13px] font-semibold text-navy hover:bg-navy hover:text-white" data-demo-coupon-apply>Aplicar</button>
						</div>
						<p class="mt-2 text-[12px] empty:hidden" role="status" data-demo-coupon-message></p>
					</div>
				<?php endif; ?>
			</div>
		</aside>
	</form>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
