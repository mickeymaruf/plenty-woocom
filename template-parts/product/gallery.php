<?php
/**
 * Product gallery: thumbnail column + main image. Images are the product image and gallery
 * images set in WooCommerce; thumbnails swap the main image (assets/js/theme.js).
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product   = $args['product'];
$image_ids = array_values( array_unique( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) ) );
$video     = plenty_field( 'product_video_url', '', $product->get_id() );
$main_size = 'woocommerce_single';
?>
<div class="flex flex-col-reverse gap-3 sm:flex-row" data-gallery>
	<?php if ( count( $image_ids ) > 1 ) : ?>
		<ul class="flex gap-2 overflow-x-auto sm:w-[72px] sm:shrink-0 sm:flex-col sm:overflow-visible">
			<?php foreach ( $image_ids as $index => $image_id ) : ?>
				<li class="shrink-0">
					<button
						type="button"
						class="block size-[72px] overflow-hidden rounded-[10px] border-2 border-transparent p-[2px] transition hover:border-indigo/40 aria-[current=true]:border-indigo"
						data-gallery-thumb
						data-src="<?php echo esc_url( (string) wp_get_attachment_image_url( $image_id, $main_size ) ); ?>"
						data-srcset="<?php echo esc_attr( (string) wp_get_attachment_image_srcset( $image_id, $main_size ) ); ?>"
						aria-current="<?php echo 0 === $index ? 'true' : 'false'; ?>"
					>
						<?php echo wp_get_attachment_image( $image_id, 'woocommerce_gallery_thumbnail', false, array( 'class' => 'size-full rounded-lg object-cover' ) ); ?>
						<span class="sr-only">
							<?php
							/* translators: %d: image number. */
							printf( esc_html__( 'Show image %d', 'plenty' ), (int) $index + 1 );
							?>
						</span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<div class="relative h-[320px] min-w-0 flex-1 overflow-hidden rounded-2xl bg-[#f9fafb] sm:h-[472px]">
		<?php
		$main_attrs = array(
			'class'         => 'size-full object-cover',
			'alt'           => $product->get_name(),
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'data-gallery-main' => '',
		);

		if ( $image_ids ) {
			echo wp_get_attachment_image( $image_ids[0], $main_size, false, $main_attrs );
		} else {
			[ $file, $width, $height ] = plenty_image_defaults()['product_image'];
			echo plenty_asset_img( $file, $width, $height, $main_attrs ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
		?>

		<?php if ( $video ) : ?>
			<a class="absolute bottom-4 left-4 flex items-center gap-2 bg-white/90 px-3 py-1.5 shadow-[0_1px_3px_0_rgba(0,0,0,0.1),0_1px_2px_0_rgba(0,0,0,0.1)] hover:bg-white" href="<?php echo esc_url( $video ); ?>" target="_blank" rel="noopener">
				<span class="flex size-4 items-center justify-center rounded-full bg-indigo pl-0.5">
					<?php echo plenty_asset_img( 'icon-play.svg', 7, 7, array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</span>
				<span class="text-[11px] font-semibold leading-[16.5px] text-[#1e2939]"><?php echo esc_html( plenty_content( 'pdp_video_label' ) ); ?></span>
			</a>
		<?php endif; ?>
	</div>
</div>
