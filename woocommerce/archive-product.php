<?php
/**
 * Shop page and product category/tag archives (Figma frame 27:5519): "Find your perfect mattress"
 * heading, category tabs, product card grid with pagination, then "Sleep Specialists".
 *
 * Unlike the home section, the tabs are links to the category archives, so filtering works with
 * pagination. Products per page = Customizer → WooCommerce → Product Catalog (columns × rows).
 *
 * Overrides plugins/woocommerce/templates/archive-product.php.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();

$current    = is_product_category() ? get_queried_object() : null;
$categories = plenty_product_filter_categories();
$active     = 0;

if ( $current instanceof WP_Term ) {
	// A subcategory highlights its ancestor's tab.
	$lineage = array_merge( array( $current->term_id ), get_ancestors( $current->term_id, 'product_cat', 'taxonomy' ) );
	foreach ( $categories as $category ) {
		if ( in_array( $category->term_id, $lineage, true ) ) {
			$active = $category->term_id;
			break;
		}
	}
}

$title      =is_shop() || $active ? plenty_content( 'products_title' ) : woocommerce_page_title( false );
$tab        = 'shrink-0 rounded-full py-2 text-[14px] font-medium leading-5 tracking-[-0.1504px] text-[#6b7280] transition px-4 hover:text-navy aria-[current=page]:bg-navy aria-[current=page]:text-white';
$pagination = paginate_links( // Not $pages: the_post() overwrites that WordPress global.
	array(
		'total'     => (int) $GLOBALS['wp_query']->max_num_pages,
		'current'   => max( 1, (int) get_query_var( 'paged' ) ),
		'mid_size'  => 1,
		'prev_text' => '<span aria-hidden="true">&lsaquo;</span><span class="sr-only">' . esc_html__( 'Página anterior', 'plenty' ) . '</span>',
		'next_text' => '<span aria-hidden="true">&rsaquo;</span><span class="sr-only">' . esc_html__( 'Página siguiente', 'plenty' ) . '</span>',
	)
);
?>
<main id="main">
	<section class="container-site flex flex-col items-center gap-8 pt-[50px] pb-[50px] lg:pb-[100px]">
		<div class="flex w-full flex-col items-center gap-8">
			<h1 class="text-center text-[32px] font-semibold capitalize leading-[1.2] text-ink sm:text-[48px]"><?php echo esc_html( $title ); ?></h1>

			<?php if ( ( is_shop() || $active ) && count( $categories ) > 1 ) : ?>
				<nav class="flex max-w-full items-center overflow-x-auto rounded-full border border-[#e5e7eb] bg-white p-[5px]" aria-label="<?php esc_attr_e( 'Filtrar colchones', 'plenty' ); ?>">
					<a class="<?php echo esc_attr( $tab ); ?>" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" <?php echo $active ? '' : 'aria-current="page"'; ?>><?php echo esc_html( plenty_content( 'products_all_label' ) ); ?></a>
					<?php foreach ( $categories as $category ) : ?>
						<a class="<?php echo esc_attr( $tab ); ?>" href="<?php echo esc_url( get_term_link( $category ) ); ?>" <?php echo $active === $category->term_id ? 'aria-current="page"' : ''; ?>><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>
		</div>

		<?php if ( woocommerce_product_loop() ) : ?>
			<div class="w-full has-[.woocommerce-notices-wrapper:empty]:hidden">
				<?php do_action( 'woocommerce_before_shop_loop' ); // Notices. ?>
			</div>

			<ul class="grid w-full grid-cols-1 gap-[30px] sm:grid-cols-2 lg:grid-cols-3">
				<?php
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' ); // Fired per product as in WooCommerce's loop, for plugins.
					get_template_part( 'template-parts/product-card', null, array( 'product' => wc_get_product( get_the_ID() ) ) );
				}
				?>
			</ul>

			<?php if ( $pagination ) : ?>
				<nav class="flex flex-wrap items-center justify-center gap-2 *:flex *:size-10 *:items-center *:justify-center *:rounded-full *:text-[14px] *:font-medium *:text-[#6b7280] [&>.dots]:size-auto [&>a]:transition [&>a:hover]:text-navy [&>[aria-current]]:bg-navy [&>[aria-current]]:text-white" aria-label="<?php esc_attr_e( 'Páginas de productos', 'plenty' ); ?>">
					<?php echo $pagination; // phpcs:ignore WordPress.Security.EscapeOutput -- paginate_links() output. ?>
				</nav>
			<?php endif; ?>
		<?php else : ?>
			<div class="w-full">
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div>
		<?php endif; ?>
	</section>

	<?php get_template_part( 'template-parts/sections/support' ); ?>
</main>
<?php
get_footer();
