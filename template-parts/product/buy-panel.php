<?php
/**
 * Product page buy panel (Figma "ProductPanel"): title, tagline, short description, price with
 * discount, size selector + add to cart, installments box and the sleep specialist box.
 *
 * Add to cart is WooCommerce's own form (works for simple and variable products; the size buttons
 * are woocommerce/single-product/add-to-cart/variable.php); it is styled in src/css/app.css under
 * ".plenty-buy".
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
$tagline = plenty_field( 'product_tagline', '', $product->get_id() );

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

	<?php // data-variation-html: replaced with the picked variation's markup (assets/js/theme.js); no whitespace inside, so empty:hidden works. ?>
	<div class="max-w-[440px] empty:hidden" data-variation-html="plenty_price" aria-live="polite"><?php get_template_part( 'template-parts/product/price', null, array( 'product' => $product ) ); ?></div>

	<?php woocommerce_template_single_add_to_cart(); ?>

	<div class="empty:hidden" data-variation-html="plenty_installments"><?php get_template_part( 'template-parts/product/installments', null, array( 'product' => $product ) ); ?></div>

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
