<?php
/**
 * Product page: "Sensation" — firmness scale and up to six construction features, all set per
 * product. Hidden when the product has neither.
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product_id = $args['product']->get_id();
$firmness   = (int) plenty_field( 'product_firmness', 0, $product_id );
$features   = array();

for ( $i = 1; $i <= 6; $i++ ) {
	$title = plenty_field( "product_sensation_{$i}_title", '', $product_id );
	if ( $title ) {
		$features[] = array(
			'title' => $title,
			'text'  => plenty_field( "product_sensation_{$i}_text", '', $product_id ),
			'icon'  => (int) plenty_field( "product_sensation_{$i}_icon", 0, $product_id ),
		);
	}
}

if ( ! $firmness && ! $features ) {
	return;
}
?>
<section class="bg-surface py-[100px]">
	<div class="container-site">
		<h2 class="text-center text-[32px] font-bold leading-[1.2] text-ink sm:text-[48px]"><?php echo esc_html( plenty_content( 'pdp_sensation_title' ) ); ?></h2>

		<?php if ( $firmness ) : ?>
			<div class="mx-auto flex h-5 max-w-[600px] my-11" role="img" aria-label="<?php /* translators: %d: firmness level 1–4. */ echo esc_attr( sprintf( __( 'Firmeza %d de 4', 'plenty' ), $firmness ) ); ?>">
				<?php for ( $level = 1; $level <= 4; $level++ ) : ?>
					<span class="flex-1 border-y border-r border-[#767676] first:border-l <?php echo $level === $firmness ? 'bg-[#273c66]' : ''; ?>"></span>
				<?php endfor; ?>
			</div>
		<?php endif; ?>

		<?php if ( $features ) : ?>
			<ul class="flex flex-wrap justify-center gap-x-[30px] gap-y-2.5 font-montserrat <?php echo $firmness ? '' : 'mt-10'; ?>">
				<?php foreach ( $features as $feature ) : ?>
					<li class="flex w-full flex-col items-center gap-4 p-5 text-center sm:w-[calc(50%-15px)] lg:w-[calc(25%-22.5px)]">
						<?php if ( $feature['icon'] ) : ?>
							<?php echo wp_get_attachment_image( $feature['icon'], 'thumbnail', false, array( 'class' => 'block size-[60px] object-contain' ) ); ?>
						<?php endif; ?>
						<h3 class="text-[14px] font-semibold uppercase leading-5 text-black"><?php echo esc_html( $feature['title'] ); ?></h3>
						<?php if ( $feature['text'] ) : ?>
							<p class="text-[14px] leading-5 text-[#999]"><?php echo esc_html( $feature['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>
</section>
