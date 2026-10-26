<?php
/**
 * Product card (Figma "ProductCard"). Feature lines come from the product's "Card features" fields.
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;

if ( ! $product instanceof WC_Product ) {
	return;
}

// Feature line icons are fixed per position, as in the design: comfort, support, cooling.
$features = array();
foreach ( array( 1 => 'icon-bed.svg', 2 => 'icon-shield.svg', 3 => 'icon-snowflake.svg' ) as $i => $icon ) {
	$text = plenty_field( "product_feature_{$i}", '', $product->get_id() );
	if ( $text ) {
		$features[ $icon ] = $text;
	}
}

// Category slugs including ancestors, so a parent-category tab also matches products in its subcategories.
$category_slugs = array();
foreach ( wc_get_product_term_ids( $product->get_id(), 'product_cat' ) as $term_id ) {
	foreach ( array_merge( array( $term_id ), get_ancestors( $term_id, 'product_cat', 'taxonomy' ) ) as $id ) {
		$term = get_term( $id, 'product_cat' );
		if ( $term instanceof WP_Term ) {
			$category_slugs[ $term->slug ] = $term->slug;
		}
	}
}

$price       = $product->is_type( 'variable' ) ? $product->get_variation_price( 'min' ) : $product->get_price();
$description = plenty_field( 'product_card_text', wp_strip_all_tags( $product->get_short_description() ), $product->get_id() );
$image_attrs = array(
	'class' => 'size-full object-cover',
	'alt'   => $product->get_name(),
);
?>
<li class="flex flex-col overflow-hidden rounded-2xl border border-[#f7f7f7] bg-white p-px" data-categories="<?php echo esc_attr( implode( ' ', $category_slugs ) ); ?>">
	<a class="block h-[215px] bg-[#f9fafb]" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		if ( $product->get_image_id() ) {
			echo wp_get_attachment_image( $product->get_image_id(), 'woocommerce_single', false, $image_attrs );
		} else {
			[ $file, $width, $height ] = plenty_image_defaults()['product_image'];
			echo plenty_asset_img( $file, $width, $height, $image_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>
	</a>

	<div class="flex flex-1 flex-col gap-4 p-[30px]">
		<div>
			<h3 class="text-[16px] font-bold leading-5 tracking-[-0.3125px] text-ink">
				<a class="hover:underline" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
			</h3>
			<?php if ( $description ) : ?>
				<p class="pt-1 text-[12px] leading-[19.5px] text-muted"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $features ) : ?>
			<ul class="flex flex-col gap-2.5">
				<?php foreach ( $features as $icon => $feature ) : ?>
					<li class="flex items-center gap-2.5">
						<?php echo plenty_asset_img( $icon, 20, 20, array( 'class' => 'block shrink-0' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<span class="text-[13px] font-medium leading-[18px] text-muted"><?php echo esc_html( $feature ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<div class="mt-auto flex items-center justify-between border-t border-[#f3f4f6] pt-[9px]">
			<div>
				<p class="text-[12px] leading-4 text-subtle"><?php echo esc_html( plenty_content( 'products_price_label' ) ); ?></p>
				<?php if ( '' !== (string) $price ) : ?>
					<p class="text-[14px] font-bold leading-5 tracking-[-0.1504px] text-ink">
						<?php
						/* translators: %s: lowest product price. */
						printf( esc_html__( 'From %s', 'plenty' ), wc_price( wc_get_price_to_display( $product, array( 'price' => $price ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- wc_price() escapes.
						?>
					</p>
				<?php endif; ?>
			</div>

			<a class="flex items-center gap-1.5 rounded-full bg-navy px-4 py-2 text-[12px] font-semibold leading-4 text-white transition hover:bg-ink" href="<?php echo esc_url( $product->get_permalink() ); ?>">
				<?php echo plenty_asset_img( 'icon-cart.svg', 13, 13, array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php echo esc_html( plenty_content( 'products_button_label' ) ); ?>
				<span class="sr-only"><?php echo esc_html( $product->get_name() ); ?></span>
			</a>
		</div>
	</div>
</li>
