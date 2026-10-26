<?php
/**
 * Home: image + text section (Figma "Section · Hotel Line" and "Section · Our Factory").
 *
 * @package Plenty
 *
 * @var array $args {
 *     @type string $prefix         Field name prefix, e.g. "hotel" or "factory".
 *     @type bool   $image_right    Put the image after the text on wide screens.
 *     @type string $background     Background utility class.
 * }
 */

defined( 'ABSPATH' ) || exit;

$prefix = $args['prefix'];
$button = plenty_content( "{$prefix}_button" );
?>
<section class="<?php echo esc_attr( $args['background'] ?? 'bg-white' ); ?> py-[96px]">
	<div class="container-narrow flex flex-col items-center gap-12 lg:flex-row lg:gap-20">
		<div class="w-full shrink-0 lg:w-[540px] <?php echo ! empty( $args['image_right'] ) ? 'lg:order-last' : ''; ?>">
			<?php echo plenty_content_image( "{$prefix}_image", 'large', array( 'class' => 'aspect-[540/440] w-full rounded-2xl object-cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<div class="flex min-w-0 flex-1 flex-col items-start gap-[18px]">
			<p class="text-[13px] font-semibold uppercase leading-4 tracking-[1.4px] text-navy"><?php echo esc_html( plenty_content( "{$prefix}_eyebrow" ) ); ?></p>
			<h2 class="text-[32px] font-semibold leading-[1.2] tracking-[-0.8px] text-ink sm:text-[40px] sm:leading-[48px]"><?php echo esc_html( plenty_content( "{$prefix}_title" ) ); ?></h2>
			<p class="text-[17px] leading-[27px] text-body"><?php echo esc_html( plenty_content( "{$prefix}_text" ) ); ?></p>

			<ul class="flex flex-col gap-2.5">
				<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
					<?php $bullet = plenty_content( "{$prefix}_bullet_{$i}" ); ?>
					<?php if ( $bullet ) : ?>
						<li class="flex items-center gap-2.5">
							<?php echo plenty_asset_img( 'icon-check.svg', 20, 20, array( 'class' => 'block shrink-0' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="text-[15px] font-medium leading-[21px] text-ink"><?php echo esc_html( $bullet ); ?></span>
						</li>
					<?php endif; ?>
				<?php endfor; ?>
			</ul>

			<?php if ( plenty_link_title( $button ) ) : ?>
				<a class="mt-6 flex items-center gap-2 rounded-full bg-navy px-7 py-[15px] text-[15px] font-semibold leading-5 text-white transition hover:bg-ink" <?php echo plenty_link_attrs( $button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php echo esc_html( plenty_link_title( $button ) ); ?>
					<span aria-hidden="true">&rarr;</span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
