<?php
/**
 * About: "Who we are" heading and text, full-bleed image and the stats card.
 *
 * @package Plenty
 *
 * @var array $args { @type int $post_id About page ID. }
 */

defined( 'ABSPATH' ) || exit;

$page_id = (int) $args['post_id'];
?>
<section class="py-12 lg:py-20">
	<div class="container-site relative">
		<div class="flex flex-col gap-4 lg:max-w-[min(910px,calc(100%-330px))]">
			<h1 class="text-[40px] font-semibold capitalize leading-[1.2] text-[#0e0e0e] sm:text-[60px]"><?php echo esc_html( plenty_content( 'about_title', $page_id ) ); ?></h1>
			<p class="text-[18px] leading-[1.3] text-[#1b1b1b] sm:text-[20px]"><?php echo esc_html( plenty_content( 'about_text', $page_id ) ); ?></p>
		</div>

		<?php // On wide screens the image runs into the right gutter, as in the design. ?>
		<div class="mt-10 h-[300px] overflow-hidden rounded-3xl sm:h-[530px] lg:mt-[60px] lg:-mr-[100px] lg:rounded-r-none">
			<?php echo plenty_content_image( 'about_image', 'full', array( 'class' => 'size-full object-cover object-top' ), array(), $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</div>

		<dl class="mt-6 grid grid-cols-3 gap-4 rounded-2xl bg-navy px-4 py-[30px] text-white lg:absolute lg:right-[100px] lg:top-0 lg:mt-0 lg:w-[270px] lg:grid-cols-1 lg:gap-10">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php $value = plenty_content( "about_stat_{$i}_value", $page_id ); ?>
				<?php if ( $value ) : ?>
					<div class="flex flex-col items-center gap-5 text-center">
						<dt class="order-last text-[14px] leading-[1.3] sm:text-[18px] sm:leading-[27px]"><?php echo esc_html( plenty_content( "about_stat_{$i}_label", $page_id ) ); ?></dt>
						<dd class="text-[28px] font-semibold leading-[27px] sm:text-[36px]"><?php echo esc_html( $value ); ?></dd>
					</div>
				<?php endif; ?>
			<?php endfor; ?>
		</dl>
	</div>
</section>
