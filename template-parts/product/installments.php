<?php
/**
 * Product page "bank benefits" box (Figma "Beneficios bancarios"): up to three payment plans
 * calculated from the price, e.g. 12 interest-free installments of the price, 6 of the price − 20 %,
 * 1 payment of the price − 30 %; payment logos after the first plan; payment methods button.
 * Rendered for the product and for each variation (see plenty_variation_buy_panel_html()).
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$current = plenty_product_prices( $args['product'] )['current'];

$plans = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$payments = (int) plenty_content( "pdp_plan_{$i}_payments" );
	$discount = min( 100, max( 0, (float) plenty_content( "pdp_plan_{$i}_discount" ) ) );
	if ( $payments > 0 && $current > 0 ) {
		$plans[] = array(
			'payments' => $payments,
			'discount' => $discount,
			'amount'   => round( $current * ( 100 - $discount ) / 100 / $payments, wc_get_price_decimals() ),
		);
	}
}

if ( ! $plans ) {
	return;
}

$button = plenty_content( 'pdp_payments_button' );
?>
<div class="flex flex-col gap-3.5 rounded-[14px] border-[1.5px] border-navy bg-white px-[22px] py-5 text-navy">
	<?php if ( plenty_content( 'pdp_benefits_title' ) ) : ?>
		<p class="text-[18px] font-bold"><?php echo esc_html( plenty_content( 'pdp_benefits_title' ) ); ?></p>
	<?php endif; ?>

	<?php foreach ( $plans as $index => $plan ) : ?>
		<?php $single = 1 === $plan['payments']; ?>
		<p class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[16px]">
			<span>
				<strong class="font-bold"><?php echo esc_html( $plan['payments'] . ' ' . plenty_content( $single ? 'pdp_single_label' : 'pdp_installments_label' ) ); ?></strong>
				<?php echo esc_html( plenty_content( $single ? 'pdp_single_text' : 'pdp_installments_text' ) ); ?>
				<strong class="font-bold"><?php echo wc_price( $plan['amount'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></strong>
			</span>
			<?php if ( $plan['discount'] > 0 ) : ?>
				<span class="rounded-full bg-navy px-2.5 py-[3px] text-[12px] font-semibold text-white"><?php echo esc_html( str_replace( '{percent}', wc_format_localized_decimal( $plan['discount'] ), plenty_content( 'pdp_discount_badge' ) ) ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( 0 === $index ) : ?>
			<ul class="flex flex-wrap gap-[11px]">
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
					<?php $logo = plenty_content_image( "pdp_payment_{$i}", 'thumbnail', array( 'class' => 'block h-[27.34px] w-auto' ), array( 'class' => 'block h-[27.34px] w-10 object-cover' ) ); ?>
					<?php if ( $logo ) : ?>
						<li><?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endif; ?>
				<?php endfor; ?>
			</ul>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( plenty_link_title( $button ) ) : ?>
		<a class="flex w-full items-center justify-center rounded-full border-[1.5px] border-navy px-5 py-3 text-center text-[14px] font-semibold transition hover:bg-navy hover:text-white" <?php echo plenty_link_attrs( $button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( plenty_link_title( $button ) ); ?></a>
	<?php endif; ?>
</div>
