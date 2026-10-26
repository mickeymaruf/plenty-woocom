<?php
/**
 * Single product page (Figma "Plenty Website Design", node 31:7786).
 *
 * Replaces WooCommerce's default single-product layout. Per-product content (tagline, video,
 * sensation, FAQ) comes from the product's "Product Page" fields; shared content (installments,
 * story rows, labels) from the front page's "Product Page Settings" fields.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	global $product;
	?>
	<main id="main">
		<div class="container-site">
			<?php do_action( 'woocommerce_before_single_product' ); // Notices (e.g. "added to your cart"). ?>
		</div>

		<?php if ( post_password_required() ) : ?>
			<div class="container-site py-20"><?php echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
		<?php else : ?>
			<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
				<?php
				get_template_part( 'template-parts/product/summary', null, array( 'product' => $product ) );
				get_template_part( 'template-parts/product/sensation', null, array( 'product' => $product ) );
				get_template_part( 'template-parts/product/stories' );
				get_template_part( 'template-parts/product/faq', null, array( 'product' => $product ) );
				?>
			</div>
			<?php
			// Product schema, normally collected from the default summary hooks this template doesn't use.
			WC()->structured_data->generate_product_data( $product );
			?>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/sections/support' ); ?>
	</main>
	<?php
endwhile;

get_footer();
