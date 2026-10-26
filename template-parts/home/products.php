<?php
/**
 * Home: "Find your perfect mattress" product grid with category filter tabs.
 *
 * Products are real WooCommerce products. Tabs are the product categories picked in the
 * "Filter categories" field, or every top-level category with products when none are picked.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

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
$categories   = is_wp_error( $categories ) ? array() : $categories;

$products = wc_get_products(
	array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => max( 1, (int) plenty_content( 'products_limit' ) ),
		'orderby'    => 'menu_order',
		'order'      => 'ASC',
		'category'   => $category_ids ? wp_list_pluck( $categories, 'slug' ) : array(),
	)
);

if ( ! $products ) {
	return;
}

$compare = plenty_content( 'products_compare' );
$tab     = 'rounded-full py-2 text-[14px] font-medium leading-5 tracking-[-0.1504px] text-[#6b7280] transition hover:text-navy aria-pressed:bg-navy aria-pressed:px-6 aria-pressed:text-white';
?>
<section class="container-site flex flex-col items-center gap-8 py-[100px]" data-product-filter>
	<div class="flex w-full flex-col items-center gap-8">
		<h2 class="text-center text-[32px] font-semibold capitalize leading-[1.2] text-ink sm:text-[48px]"><?php echo esc_html( plenty_content( 'products_title' ) ); ?></h2>

		<?php if ( count( $categories ) > 1 ) : ?>
			<div class="flex max-w-full items-center gap-4 overflow-x-auto rounded-full border border-[#e5e7eb] bg-white py-[5px] pl-[5px] pr-[25px]" role="group" aria-label="<?php esc_attr_e( 'Filter mattresses', 'plenty' ); ?>">
				<button type="button" class="<?php echo esc_attr( $tab ); ?> shrink-0" data-filter="all" aria-pressed="true"><?php echo esc_html( plenty_content( 'products_all_label' ) ); ?></button>
				<?php foreach ( $categories as $category ) : ?>
					<button type="button" class="<?php echo esc_attr( $tab ); ?> shrink-0" data-filter="<?php echo esc_attr( $category->slug ); ?>" aria-pressed="false"><?php echo esc_html( $category->name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<ul class="grid w-full grid-cols-1 gap-[30px] sm:grid-cols-2 lg:grid-cols-3">
		<?php foreach ( $products as $product ) : ?>
			<?php get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) ); ?>
		<?php endforeach; ?>
	</ul>

	<?php if ( plenty_link_title( $compare ) ) : ?>
		<a class="flex items-center gap-1.5 text-[16px] font-medium leading-[1.2] text-navy underline hover:no-underline" <?php echo plenty_link_attrs( $compare ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php echo esc_html( plenty_link_title( $compare ) ); ?>
			<?php echo plenty_asset_img( 'icon-chevron-right.svg', 15, 15, array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
	<?php endif; ?>
</section>
