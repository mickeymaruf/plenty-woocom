<?php
/**
 * Variable product add to cart, with the attribute dropdowns replaced by option buttons (Figma
 * "Seleccioná la medida"). Each attribute keeps WooCommerce's <select>, hidden, so WooCommerce's
 * variation script still finds variations, prices, stock and the variation ID; the buttons are
 * radios that assets/js/theme.js mirrors to and from it.
 *
 * Each button shows the option name and, for global attributes (Products → Attributes), the term's
 * description as a second line ("90 × 190 cm" / "1 plaza"). With one attribute its heading is
 * "Size selector heading" (Product Page Settings), otherwise the attribute name. Without "Default
 * form values" on the product, the first in-stock variation is preselected.
 *
 * Based on WooCommerce's single-product/add-to-cart/variable.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package Plenty
 * @version 11.1.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

// Preselect the first in-stock variation unless the product has defaults (or the URL picks options).
$preselect = array();
if ( ! $product->get_default_attributes() && is_array( $available_variations ) ) {
	foreach ( $available_variations as $variation ) {
		if ( $variation['is_in_stock'] && $variation['is_purchasable'] && ! in_array( '', $variation['attributes'], true ) ) {
			$preselect = $variation['attributes'];
			break;
		}
	}
}

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
	<?php else : ?>
		<div class="variations flex flex-col gap-4">
			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				$field_name = 'attribute_' . sanitize_title( $attribute_name );
				$selected   = isset( $_REQUEST[ $field_name ] ) || ! isset( $preselect[ $field_name ] ) ? false : $preselect[ $field_name ]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// Same options, order and labels as wc_dropdown_variation_attribute_options().
				$choices = array();
				if ( taxonomy_exists( $attribute_name ) ) {
					foreach ( wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) ) as $term ) {
						if ( in_array( $term->slug, $options, true ) ) {
							$choices[ $term->slug ] = array( apply_filters( 'woocommerce_variation_option_name', $term->name, $term, $attribute_name, $product ), $term->description );
						}
					}
				} else {
					foreach ( $options as $option ) {
						$choices[ $option ] = array( apply_filters( 'woocommerce_variation_option_name', $option, null, $attribute_name, $product ), '' );
					}
				}
				?>
				<fieldset class="flex min-w-0 flex-col gap-2.5" data-attribute-name="<?php echo esc_attr( $field_name ); ?>">
					<legend class="mb-2.5 text-[14px] font-semibold text-navy">
						<?php echo 1 === count( $attributes ) ? esc_html( plenty_content( 'pdp_size_heading' ) ) : wc_attribute_label( $attribute_name ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</legend>
					<?php
					wc_dropdown_variation_attribute_options(
						array(
							'options'    => $options,
							'attribute'  => $attribute_name,
							'product'    => $product,
							'selected'   => $selected,
							'aria-label' => wc_attribute_label( $attribute_name ),
						)
					);
					?>
					<div class="grid grid-cols-3 gap-2">
						<?php foreach ( $choices as $value => [ $label, $description ] ) : ?>
							<label class="flex min-w-0 cursor-pointer flex-col items-center justify-center gap-0.5 rounded-[10px] border border-[#d9dee5] bg-white px-1 py-2.5 text-center sm:px-2 transition hover:border-navy has-checked:bg-[#eef3f8] has-checked:shadow-[inset_0_0_0_0.5px_var(--color-navy)] has-checked:border-navy has-disabled:cursor-not-allowed has-disabled:opacity-40 has-disabled:hover:border-[#d9dee5] has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-navy">
								<input class="sr-only" type="radio" name="plenty_<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $value ); ?>">
								<span class="text-[13px] font-semibold text-navy sm:text-[14px]"><?php echo esc_html( $label ); ?></span>
								<?php if ( $description ) : ?>
									<span class="text-[12px] text-muted"><?php echo esc_html( $description ); ?></span>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endforeach; ?>
			<?php if ( count( $attributes ) > 1 ) : ?>
				<?php echo wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ); ?>
			<?php endif; ?>
		</div>
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
