<?php
/**
 * Home: four benefit cards under the hero. Each card has an icon, a title and an optional link
 * (rendered as the grey line under the title, e.g. "Prequalify Now").
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;
?>
<ul class="mt-[30px] grid grid-cols-1 gap-[30px] sm:grid-cols-2 lg:grid-cols-4">
	<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
		<?php
		$title = plenty_content( "feature_{$i}_title" );
		$link  = plenty_content( "feature_{$i}_link" );

		if ( ! $title ) {
			continue;
		}
		?>
		<li class="flex min-h-[151.25px] flex-col items-center justify-center gap-3 rounded-2xl bg-surface px-5 py-8 text-center">
			<?php
			// Uploads are capped at 44px tall; bundled icons keep their design size (the Affirm logo is 68×28).
			echo plenty_content_image( "feature_{$i}_icon", 'medium', array( 'class' => 'block h-auto max-h-[44px] w-auto max-w-[160px]' ), array( 'class' => 'block h-auto' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
			?>
			<p class="text-[14px] font-semibold leading-[19.25px] tracking-[-0.1504px] text-black"><?php echo esc_html( $title ); ?></p>
			<?php if ( plenty_link_title( $link ) ) : ?>
				<a class="text-[12px] leading-4 text-subtle hover:text-navy hover:underline" <?php echo plenty_link_attrs( $link ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( plenty_link_title( $link ) ); ?></a>
			<?php endif; ?>
		</li>
	<?php endfor; ?>
</ul>
