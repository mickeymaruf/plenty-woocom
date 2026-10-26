<?php
/**
 * Front page. Hero content is editable via the "Front Page" ACF field group (acf-json/).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();

$hero_title    = plenty_field( 'hero_title', get_bloginfo( 'name' ) );
$hero_subtitle = plenty_field( 'hero_subtitle', get_bloginfo( 'description' ) );
$hero_image    = plenty_field( 'hero_image', 0 );
$cta_label     = plenty_field( 'hero_cta_label', __( 'Shop now', 'plenty' ) );
$cta_url       = plenty_field( 'hero_cta_url', function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) );
?>

<main id="main">
	<section class="relative overflow-hidden bg-neutral-100">
		<?php if ( $hero_image ) : ?>
			<?php echo wp_get_attachment_image( $hero_image, 'full', false, array( 'class' => 'absolute inset-0 h-full w-full object-cover opacity-40' ) ); ?>
		<?php endif; ?>
		<div class="relative mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 lg:py-32">
			<h1 class="max-w-2xl text-4xl font-bold tracking-tight sm:text-6xl"><?php echo esc_html( $hero_title ); ?></h1>
			<?php if ( $hero_subtitle ) : ?>
				<p class="mt-6 max-w-xl text-lg text-neutral-700"><?php echo esc_html( $hero_subtitle ); ?></p>
			<?php endif; ?>
			<a class="mt-10 inline-block rounded-md bg-neutral-900 px-6 py-3 font-semibold text-white hover:bg-neutral-700" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a>
		</div>
	</section>

	<?php if ( function_exists( 'wc_get_products' ) ) : ?>
		<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
			<h2 class="mb-8 text-2xl font-bold tracking-tight"><?php esc_html_e( 'Featured products', 'plenty' ); ?></h2>
			<?php echo do_shortcode( '[products limit="4" columns="4" visibility="featured"]' ); ?>
		</section>
	<?php endif; ?>

	<?php
	while ( have_posts() ) :
		the_post();
		if ( get_the_content() ) :
			?>
			<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
				<?php the_content(); ?>
			</section>
			<?php
		endif;
	endwhile;
	?>
</main>

<?php
get_footer();
