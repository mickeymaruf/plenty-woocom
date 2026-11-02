<?php
/**
 * Product page price: regular price struck through, current price, calculated discount badge and
 * the pre-tax price. Rendered for the product and for each variation (see plenty_variation_buy_panel_html()).
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

[ 'regular' => $regular, 'current' => $current, 'discount' => $discount, 'pre_tax' => $pre_tax ] = plenty_product_prices( $args['product'] );

if ( $current <= 0 ) {
	return;
}
?>
<div class="flex flex-col gap-3">
	<p class="flex flex-wrap items-center gap-2.5">
		<?php if ( $discount ) : ?>
			<del class="text-[20px] font-medium leading-7 text-subtle"><?php echo wc_price( $regular ); // phpcs:ignore WordPress.Security.EscapeOutput ?></del>
		<?php endif; ?>
		<ins class="text-[30px] font-bold leading-9 text-ink no-underline"><?php echo wc_price( $current ); // phpcs:ignore WordPress.Security.EscapeOutput ?></ins>
		<?php if ( $discount ) : ?>
			<span class="rounded-full bg-navy px-2.5 py-[3px] text-[12px] font-bold leading-4 text-white"><?php echo esc_html( str_replace( '{percent}', (string) $discount, plenty_content( 'pdp_discount_badge' ) ) ); ?></span>
		<?php endif; ?>
	</p>

	<?php if ( $pre_tax > 0 && round( $pre_tax, 2 ) !== round( $current, 2 ) ) : ?>
		<p class="text-[12px] leading-4 text-[#b4bac4]"><?php echo esc_html( plenty_content( 'pdp_pretax_label' ) ); ?> <?php echo wc_price( $pre_tax ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	<?php endif; ?>
</div>
