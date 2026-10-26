<?php
/**
 * "Sleep Specialists Available 24/7" contact cards (home and product pages).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="bg-surface py-[100px]">
	<div class="container-narrow flex flex-col items-center gap-10">
		<h2 class="text-center text-[32px] font-semibold leading-[45px] tracking-[0.3691px] text-ink sm:text-[40px]"><?php echo esc_html( plenty_content( 'support_title' ) ); ?></h2>

		<ul class="grid w-full grid-cols-1 gap-5 md:grid-cols-3">
			<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
				<?php
				$title = plenty_content( "support_{$i}_title" );
				$link  = plenty_content( "support_{$i}_link" );

				if ( ! $title ) {
					continue;
				}
				?>
				<li>
					<a class="group flex items-center gap-5 rounded-[14px] bg-white p-8 transition hover:shadow-md" <?php echo plenty_link_attrs( $link ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="flex min-w-0 flex-1 flex-col gap-1.5">
							<span class="text-[16px] font-semibold leading-6 tracking-[-0.3125px] text-ink"><?php echo esc_html( $title ); ?></span>
							<span class="truncate text-[14px] font-medium leading-5 tracking-[-0.1504px] text-indigo group-hover:underline"><?php echo esc_html( plenty_link_title( $link ) ); ?></span>
						</span>
						<?php echo plenty_content_image( "support_{$i}_icon", 'thumbnail', array( 'class' => 'block size-[42px] shrink-0 object-contain' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
				</li>
			<?php endfor; ?>
		</ul>
	</div>
</section>
