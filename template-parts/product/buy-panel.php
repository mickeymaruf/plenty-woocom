<?php
/**
 * Product page buy panel (Figma "ProductPanel"): title, tagline, short description, price with
 * discount, interest-free installments, add to cart, payment logos and the sleep specialist box.
 *
 * Add to cart is WooCommerce's own form (works for simple and variable products); it is styled
 * in src/css/app.css under ".plenty-buy".
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
$tagline = plenty_field( 'product_tagline', '', $product->get_id() );

// Lowest price for variable products ("from"), as displayed in the shop (incl./excl. tax per settings).
$is_variable = $product->is_type( 'variable' );
$regular     = $is_variable ? $product->get_variation_regular_price( 'min' ) : $product->get_regular_price();
$current     = $is_variable ? $product->get_variation_price( 'min' ) : $product->get_price();
$regular     = '' !== (string) $regular ? (float) wc_get_price_to_display( $product, array( 'price' => $regular ) ) : 0.0;
$current     = '' !== (string) $current ? (float) wc_get_price_to_display( $product, array( 'price' => $current ) ) : 0.0;
$discount    = $regular > $current && $current > 0 ? (int) round( ( $regular - $current ) / $regular * 100 ) : 0;
$pre_tax     = wc_tax_enabled() && $current > 0 ? (float) wc_get_price_excluding_tax( $product, array( 'price' => $is_variable ? $product->get_variation_price( 'min' ) : $product->get_price() ) ) : 0.0;

$plans = array();
for ( $i = 1; $i <= 4; $i++ ) {
	$months = (int) plenty_content( "pdp_plan_{$i}_months" );
	if ( $months > 1 && $current > 0 ) {
		$plans[] = array( $months, (string) plenty_content( "pdp_plan_{$i}_badge" ) );
	}
}

$support_chat = plenty_content( 'support_1_link' ); // Reuses the home "Sleep Specialists" WhatsApp link.
$support_call = plenty_content( 'support_3_link' ); // ...and phone link.
$pill         = 'flex items-center gap-1.5 rounded-full text-[11px] font-semibold leading-[16.5px] transition';
?>
<div class="plenty-buy flex w-full min-w-0 flex-col gap-4 lg:flex-[448_1_0]">
	<h1 class="text-[28px] font-semibold leading-[1.2] text-ink sm:text-[32px]"><?php the_title(); ?></h1>

	<div class="flex flex-col gap-2">
		<?php if ( $tagline ) : ?>
			<p class="text-[12px] font-semibold leading-4 text-[#364153]"><?php echo esc_html( $tagline ); ?></p>
		<?php endif; ?>
		<?php if ( $product->get_short_description() ) : ?>
			<div class="text-[14px] leading-[22.75px] text-muted [&_p+p]:mt-2"><?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?></div>
		<?php endif; ?>
	</div>

	<?php if ( $current > 0 ) : ?>
		<div class="flex max-w-[440px] flex-col gap-3">
			<p class="flex flex-wrap items-center gap-3">
				<?php if ( $discount ) : ?>
					<del class="text-[20px] font-medium leading-7 text-subtle"><?php echo wc_price( $regular ); // phpcs:ignore WordPress.Security.EscapeOutput ?></del>
				<?php endif; ?>
				<ins class="text-[30px] font-bold leading-9 text-ink no-underline"><?php echo wc_price( $current ); // phpcs:ignore WordPress.Security.EscapeOutput ?></ins>
				<?php if ( $discount ) : ?>
					<span class="rounded-full bg-navy px-3 py-1 text-[12px] font-bold uppercase leading-4 text-white"><?php echo esc_html( $discount . ' ' . plenty_content( 'pdp_discount_label' ) ); ?></span>
				<?php endif; ?>
			</p>

			<?php if ( $pre_tax > 0 && round( $pre_tax, 2 ) !== round( $current, 2 ) ) : ?>
				<p class="text-[12px] leading-4 text-subtle"><?php echo esc_html( plenty_content( 'pdp_pretax_label' ) ); ?> <?php echo wc_price( $pre_tax ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
			<?php endif; ?>

			<?php if ( $plans ) : ?>
				<dl class="flex flex-col gap-2 pt-1 text-[14px] leading-5">
					<?php foreach ( $plans as [ $months, $badge ] ) : ?>
						<div class="flex flex-wrap items-center gap-2">
							<dt class="w-24 font-bold text-ink"><?php echo esc_html( $months . ' ' . plenty_content( 'pdp_months_label' ) ); ?></dt>
							<dd class="whitespace-nowrap text-muted">
								<?php
								// Escaped label with the (trusted) wc_price() markup swapped in for {amount}.
								$amount = wc_price( round( $current / $months ), array( 'decimals' => 0 ) );
								echo str_replace( '{amount}', $amount, esc_html( plenty_content( 'pdp_plan_text' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput
								?>
							</dd>
							<?php if ( $badge ) : ?>
								<dd class="rounded bg-navy px-2 py-0.5 text-[10px] font-bold uppercase leading-[15px] text-white"><?php echo esc_html( $badge ); ?></dd>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="flex items-center gap-2">
						<dt class="w-24 font-bold text-ink"><?php echo esc_html( plenty_content( 'pdp_single_payment_label' ) ); ?></dt>
						<dd class="text-muted"><?php echo wc_price( $current ); // phpcs:ignore WordPress.Security.EscapeOutput ?></dd>
					</div>
				</dl>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php woocommerce_template_single_add_to_cart(); ?>

	<div class="flex flex-col items-center">
		<ul class="flex flex-wrap justify-center gap-[11px]">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<?php $logo = plenty_content_image( "pdp_payment_{$i}", 'thumbnail', array( 'class' => 'block h-[27.34px] w-auto' ), array( 'class' => 'block h-[27.34px] w-10 object-cover' ) ); ?>
				<?php if ( $logo ) : ?>
					<li><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
				<?php endif; ?>
			<?php endfor; ?>
		</ul>
		<?php $payments_link = plenty_content( 'pdp_payments_link' ); ?>
		<?php if ( plenty_link_title( $payments_link ) ) : ?>
			<a class="pt-2.5 text-[14px] font-medium leading-5 text-[#368cf0] hover:underline" <?php echo plenty_link_attrs( $payments_link ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( plenty_link_title( $payments_link ) ); ?></a>
		<?php endif; ?>
	</div>

	<div class="flex flex-wrap items-center gap-4 rounded-2xl border border-[#f3f4f6] p-[17px]">
		<div class="relative shrink-0">
			<?php echo plenty_content_image( 'pdp_specialist_image', 'thumbnail', array( 'class' => 'block size-11 rounded-full object-cover shadow-[0_0_0_2px_#eef2ff]' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="absolute left-[34px] top-[34px] size-3 rounded-full border-2 border-white bg-[#00d492]" aria-hidden="true"></span>
		</div>
		<div class="min-w-0 flex-1">
			<p class="text-[12px] font-semibold leading-4 text-[#1e2939]"><?php echo esc_html( plenty_content( 'pdp_specialist_title' ) ); ?></p>
			<p class="pt-0.5 text-[11px] leading-[16.5px] text-subtle"><?php echo esc_html( plenty_content( 'pdp_specialist_text' ) ); ?></p>
		</div>
		<div class="flex gap-2">
			<a class="<?php echo esc_attr( $pill ); ?> bg-navy px-3 py-1.5 text-white hover:bg-ink" <?php echo plenty_link_attrs( $support_chat ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php echo plenty_asset_img( 'icon-chat.svg', 11, 11, array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo esc_html( plenty_content( 'pdp_chat_label' ) ); ?>
			</a>
			<a class="<?php echo esc_attr( $pill ); ?> border border-[#e5e7eb] px-[13px] py-[6px] text-[#4a5565] hover:border-[#4a5565]" <?php echo plenty_link_attrs( $support_call ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php echo plenty_asset_img( 'icon-phone-sm.svg', 11, 11, array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo esc_html( plenty_content( 'pdp_call_label' ) ); ?>
			</a>
		</div>
	</div>
</div>
