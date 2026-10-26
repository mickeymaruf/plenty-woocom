<?php
/**
 * Home: hero banner (Figma "Frame 8").
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

$button = plenty_content( 'hero_button' );
?>
<div class="relative isolate flex min-h-[479px] flex-col justify-center overflow-hidden rounded-3xl px-6 py-14 sm:px-[80px] sm:py-[80px] lg:justify-start">
	<?php echo plenty_content_image( 'hero_image', 'full', array( 'class' => 'absolute inset-0 -z-10 size-full object-cover object-top', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	<div class="absolute inset-0 -z-10 bg-[linear-gradient(139.41deg,rgba(0,15,26,0.8)_11.283%,rgba(9,45,71,0)_53.679%)] max-lg:bg-[linear-gradient(139.41deg,rgba(0,15,26,0.8)_11.283%,rgba(9,45,71,0.2)_80%)]" aria-hidden="true"></div>

	<div class="flex max-w-[561px] flex-col items-start gap-12 text-white">
		<div class="flex flex-col gap-4">
			<h1 class="text-[40px] font-semibold leading-[1.1] sm:text-[60px]"><?php echo esc_html( plenty_content( 'hero_title' ) ); ?></h1>
			<p class="max-w-[512px] text-[16px] leading-[1.4]"><?php echo esc_html( plenty_content( 'hero_text' ) ); ?></p>
		</div>

		<?php if ( plenty_link_title( $button ) ) : ?>
			<a class="rounded-full bg-white px-8 py-4 text-[18px] font-semibold leading-[1.4] text-navy transition hover:bg-surface" <?php echo plenty_link_attrs( $button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php echo esc_html( plenty_link_title( $button ) ); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
