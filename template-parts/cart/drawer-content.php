<?php
/**
 * Cart drawer content. Returned as a WooCommerce cart fragment after every cart change, so it
 * must render from the current cart alone.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

$cart     = WC()->cart;
$count    = $cart ? $cart->get_cart_contents_count() : 0;
$items    = $cart ? $cart->get_cart() : array();
$shipping = $items ? plenty_cart_free_shipping() : null;
$muted    = 'flex items-start justify-between text-[12px] leading-4 text-muted';
?>
<div class="flex min-h-0 flex-1 flex-col" data-cart-drawer-content>
	<div class="flex items-center justify-between border-b border-[#f3f4f6] px-6 pb-[17px] pt-4">
		<div class="flex items-center gap-2.5">
			<?php echo plenty_asset_img( 'icon-cart-drawer.svg', 16, 16, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<h2 id="cart-drawer-title" class="text-[14px] font-semibold leading-5 text-ink"><?php echo esc_html( plenty_content( 'cart_title' ) ); ?></h2>
			<?php if ( $count ) : ?>
				<span class="flex size-5 items-center justify-center rounded-full bg-navy text-[10px] font-bold leading-[15px] text-white">
					<?php echo esc_html( $count ); ?>
					<span class="sr-only"><?php echo esc_html( _n( 'item', 'items', $count, 'plenty' ) ); ?></span>
				</span>
			<?php endif; ?>
		</div>
		<button type="button" class="-m-1 rounded p-1 hover:bg-[#f3f4f6]" data-cart-close>
			<?php echo plenty_asset_img( 'icon-close.svg', 18, 18, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="sr-only"><?php esc_html_e( 'Close cart', 'plenty' ); ?></span>
		</button>
	</div>

	<?php if ( ! $items ) : ?>
		<?php $empty_button = plenty_content( 'cart_empty_button' ); ?>
		<div class="flex flex-1 flex-col items-center justify-center gap-5 px-6 py-16 text-center">
			<?php echo plenty_asset_img( 'icon-bag.svg', 26, 28.67, array( 'class' => 'block opacity-40', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<p class="text-[14px] leading-5 text-muted"><?php echo esc_html( plenty_content( 'cart_empty_text' ) ); ?></p>
			<?php if ( plenty_link_title( $empty_button ) ) : ?>
				<a class="rounded-full bg-navy px-6 py-3 text-[12px] font-bold leading-4 tracking-[0.3px] text-white hover:bg-ink" <?php echo plenty_link_attrs( $empty_button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( plenty_link_title( $empty_button ) ); ?></a>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<div class="border-b border-[#e0e7ff] bg-[#eef2ff] px-6 pb-[13px] pt-3">
			<p class="flex items-center justify-center gap-1.5 text-center text-[12px] font-semibold leading-4 <?php echo $shipping['qualifies'] ? 'text-[#007a55]' : 'text-navy'; ?>">
				<?php if ( $shipping['qualifies'] ) : ?>
					<?php echo plenty_asset_img( 'icon-check-green.svg', 12, 12, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo esc_html( plenty_content( 'cart_free_shipping_done' ) ); ?>
				<?php else : ?>
					<?php echo str_replace( '{amount}', wc_price( $shipping['remaining'] ), esc_html( plenty_content( 'cart_free_shipping_todo' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped label + wc_price(). ?>
				<?php endif; ?>
			</p>
			<div class="mt-2 h-1 overflow-hidden rounded-full bg-[#c7d2fe]" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( (string) round( $shipping['percent'] ) ); ?>" aria-label="<?php esc_attr_e( 'Progress to free shipping', 'plenty' ); ?>">
				<div class="h-1 rounded-full bg-navy transition-[width] duration-500" style="width: <?php echo esc_attr( (string) round( $shipping['percent'], 2 ) ); ?>%"></div>
			</div>
		</div>

		<div class="min-h-0 flex-1 overflow-y-auto px-6 py-4">
			<p class="mb-4 hidden rounded-lg bg-[#fef2f2] px-3 py-2 text-[12px] leading-4 text-[#cc1818] [&:not(:empty)]:block" role="alert" data-cart-error></p>

			<ul class="flex flex-col gap-4 [&>li+li]:border-t [&>li+li]:border-[#f3f4f6] [&>li+li]:pt-4">
				<?php foreach ( $items as $key => $item ) : ?>
					<?php
					$product = $item['data'];
					if ( ! $product instanceof WC_Product || ! $product->exists() || $item['quantity'] <= 0 ) {
						continue;
					}

					$name     = $product->is_type( 'variation' ) ? get_the_title( $product->get_parent_id() ) : $product->get_name();
					$link     = $product->is_visible() ? $product->get_permalink( $item ) : '';
					$meta     = plenty_cart_item_meta( $item );
					$quantity = (int) $item['quantity'];
					$max      = $product->get_max_purchase_quantity();
					$image    = $product->get_image_id()
						? wp_get_attachment_image( $product->get_image_id(), 'woocommerce_gallery_thumbnail', false, array( 'class' => 'size-full object-cover', 'alt' => '' ) )
						: wc_placeholder_img( 'woocommerce_gallery_thumbnail', array( 'class' => 'size-full object-cover' ) );
					$stepper  = 'flex size-7 items-center justify-center hover:bg-[#f3f4f6] disabled:cursor-not-allowed disabled:opacity-30 disabled:hover:bg-transparent';
					?>
					<li class="flex items-start gap-4">
						<div class="size-20 shrink-0 overflow-hidden border border-[#f3f4f6] bg-[#f9fafb] p-px">
							<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput -- generated <img> markup. ?>
						</div>

						<div class="flex min-w-0 flex-1 flex-col self-stretch">
							<div class="flex items-start justify-between gap-3">
								<div class="min-w-0">
									<p class="text-[12px] font-semibold leading-[16.5px] text-ink">
										<?php if ( $link ) : ?>
											<a class="hover:underline" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $name ); ?>
										<?php endif; ?>
									</p>
									<?php if ( $meta ) : ?>
										<p class="pt-0.5 text-[10px] leading-[15px] text-subtle"><?php echo esc_html( $meta ); ?></p>
									<?php endif; ?>
								</div>
								<button type="button" class="shrink-0 pt-0.5 opacity-100 hover:opacity-60" data-cart-update data-key="<?php echo esc_attr( $key ); ?>" data-quantity="0" data-action="remove">
									<?php echo plenty_asset_img( 'icon-remove.svg', 13, 13, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<span class="sr-only">
										<?php
										/* translators: %s: product name. */
										printf( esc_html__( 'Remove %s from cart', 'plenty' ), esc_html( $name ) );
										?>
									</span>
								</button>
							</div>

							<div class="mt-auto flex items-center justify-between pt-2.5">
								<div class="flex items-center border border-[#e5e7eb] p-px">
									<button type="button" class="<?php echo esc_attr( $stepper ); ?>" data-cart-update data-key="<?php echo esc_attr( $key ); ?>" data-quantity="<?php echo esc_attr( $quantity - 1 ); ?>" data-action="decrease" <?php disabled( $quantity <= 1 ); ?>>
										<?php echo plenty_asset_img( 'icon-minus.svg', 11, 11, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										<span class="sr-only"><?php esc_html_e( 'Decrease quantity', 'plenty' ); ?></span>
									</button>
									<span class="w-7 text-center text-[12px] font-semibold leading-4 text-[#1e2939]" aria-label="<?php esc_attr_e( 'Quantity', 'plenty' ); ?>"><?php echo esc_html( $quantity ); ?></span>
									<button type="button" class="<?php echo esc_attr( $stepper ); ?>" data-cart-update data-key="<?php echo esc_attr( $key ); ?>" data-quantity="<?php echo esc_attr( $quantity + 1 ); ?>" data-action="increase" <?php disabled( $max > 0 && $quantity >= $max ); ?>>
										<?php echo plenty_asset_img( 'icon-plus.svg', 11, 11, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										<span class="sr-only"><?php esc_html_e( 'Increase quantity', 'plenty' ); ?></span>
									</button>
								</div>
								<p class="text-right text-[14px] font-bold leading-5 text-ink"><?php echo $cart->get_product_subtotal( $product, $quantity ); // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce price markup. ?></p>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="flex flex-col gap-4 border-t border-[#f3f4f6] px-6 pb-6 pt-[17px]">
			<dl class="flex flex-col gap-1.5">
				<div class="<?php echo esc_attr( $muted ); ?>">
					<dt><?php echo esc_html( plenty_content( 'cart_subtotal_label' ) ); ?></dt>
					<dd><?php echo $cart->get_cart_subtotal(); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
				</div>
				<?php if ( $cart->get_discount_total() > 0 ) : ?>
					<div class="<?php echo esc_attr( $muted ); ?>">
						<dt><?php esc_html_e( 'Discount', 'plenty' ); ?></dt>
						<dd>-<?php echo wc_price( $cart->get_discount_total() + ( $cart->display_prices_including_tax() ? $cart->get_discount_tax() : 0 ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
					</div>
				<?php endif; ?>
				<div class="<?php echo esc_attr( $muted ); ?>">
					<dt><?php echo esc_html( plenty_content( 'cart_shipping_label' ) ); ?></dt>
					<?php if ( $shipping['qualifies'] ) : ?>
						<dd class="font-medium text-[#096]"><?php echo esc_html( plenty_content( 'cart_shipping_free' ) ); ?></dd>
					<?php else : ?>
						<dd><?php echo esc_html( plenty_content( 'cart_shipping_pending' ) ); ?></dd>
					<?php endif; ?>
				</div>
				<div class="mt-1 flex items-start justify-between border-t border-[#f3f4f6] pt-[7px] text-[14px] font-bold leading-5 text-ink">
					<dt><?php echo esc_html( plenty_content( 'cart_total_label' ) ); ?></dt>
					<dd><?php echo $cart->get_total(); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
				</div>
			</dl>

			<a class="flex w-full items-center justify-center gap-2 rounded-full bg-navy py-3.5 text-[14px] font-bold leading-5 tracking-[0.35px] text-white transition hover:bg-ink" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
				<?php echo esc_html( plenty_content( 'cart_checkout_label' ) ); ?>
				<?php echo plenty_asset_img( 'icon-arrow-right.svg', 15, 15, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>

			<ul class="flex flex-wrap items-center justify-center gap-4">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<?php $trust = plenty_content( "cart_trust_{$i}" ); ?>
					<?php if ( $trust ) : ?>
						<li class="flex items-center gap-1 text-[10px] leading-[15px] text-subtle">
							<?php echo plenty_asset_img( 'icon-check-sm.svg', 9, 9, array( 'class' => 'block', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<?php echo esc_html( $trust ); ?>
						</li>
					<?php endif; ?>
				<?php endfor; ?>
			</ul>
		</div>
	<?php endif; ?>
</div>
