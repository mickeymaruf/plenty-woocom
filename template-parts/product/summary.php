<?php
/**
 * Product page: gallery + benefits (left) and buy panel (right).
 *
 * @package Plenty
 *
 * @var array $args { @type WC_Product $product }
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'];
?>
<section class="container-site py-12 lg:py-20">
	<div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-[60px]">
		<?php // Columns keep the design's 732:448 ratio, so the buy panel stays wide enough below 1440px. ?>
		<div class="flex w-full min-w-0 flex-col gap-6 lg:flex-[732_1_0]">
			<?php get_template_part( 'template-parts/product/gallery', null, array( 'product' => $product ) ); ?>

			<?php
			// Titles are the home page benefit cards 2–4; icons are the design's emoji (rendered from Figma, so they
			// look the same on every OS) and the Inter "∞" glyph.
			$benefit_icons = array(
				2 => plenty_asset_img( 'emoji-calendar.png', 24, 24, array( 'class' => 'block' ) ),
				3 => '<span class="text-[24px] leading-8 text-[#0d0d0d]" aria-hidden="true">∞</span>',
				4 => plenty_asset_img( 'emoji-truck.png', 24, 24, array( 'class' => 'block' ) ),
			);
			?>
			<ul class="grid grid-cols-3 gap-2">
				<?php foreach ( $benefit_icons as $i => $icon ) : ?>
					<?php $title = plenty_content( "feature_{$i}_title" ); ?>
					<?php if ( $title ) : ?>
						<li class="flex flex-col items-center gap-2 text-center">
							<span class="flex h-8 items-center justify-center"><?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput -- static markup. ?></span>
							<span class="text-[12px] font-medium leading-4 text-[#4a5565]"><?php echo esc_html( $title ); ?></span>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php get_template_part( 'template-parts/product/buy-panel', null, array( 'product' => $product ) ); ?>
	</div>
</section>
