<?php
/**
 * About: "We are best solution for your home" with three value columns.
 *
 * @package Plenty
 *
 * @var array $args { @type int $post_id About page ID. }
 */

defined( 'ABSPATH' ) || exit;

$page_id = (int) $args['post_id'];
?>
<section class="container-site flex flex-col gap-10 pb-[100px] pt-5 lg:gap-[60px]">
	<h2 class="max-w-[507px] text-[36px] font-semibold capitalize leading-[1.2] text-[#0e0e0e] sm:text-[48px]"><?php echo esc_html( plenty_content( 'about_values_title', $page_id ) ); ?></h2>

	<ul class="grid gap-10 md:grid-cols-3 md:gap-[30px]">
		<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
			<?php $title = plenty_content( "about_value_{$i}_title", $page_id ); ?>
			<?php if ( $title ) : ?>
				<li class="flex flex-col gap-5">
					<div class="h-[54px]">
						<?php echo plenty_content_image( "about_value_{$i}_icon", 'thumbnail', array( 'class' => 'block h-[54px] w-auto' ), array(), $page_id ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<div>
						<h3 class="pt-2.5 text-[24px] capitalize leading-[1.2] text-[#0e0e0e] sm:text-[28px]"><?php echo esc_html( $title ); ?></h3>
						<p class="max-w-[370px] pt-5 text-[18px] leading-[27px] text-[#656565]"><?php echo esc_html( plenty_content( "about_value_{$i}_text", $page_id ) ); ?></p>
					</div>
				</li>
			<?php endif; ?>
		<?php endfor; ?>
	</ul>
</section>
