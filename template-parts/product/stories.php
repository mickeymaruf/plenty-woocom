<?php
/**
 * Three alternating image + text rows (safety, warranty, accessories), on the product and About
 * pages. Edited in the front page's "Product Page Settings" fields.
 *
 * @package Plenty
 *
 * @var array $args { @type string $class Extra section classes (e.g. padding). }
 */

defined( 'ABSPATH' ) || exit;

$rows = array_filter( array( 1, 2, 3 ), fn( $i ) => (bool) plenty_content( "story_{$i}_title" ) );

if ( ! $rows ) {
	return;
}
?>
<section class="container-site <?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<?php foreach ( array_values( $rows ) as $index => $i ) : ?>
		<?php
		$eyebrow = plenty_content( "story_{$i}_eyebrow" );
		$button  = plenty_content( "story_{$i}_button" );
		?>
		<div class="flex flex-col gap-10 py-16 lg:flex-row lg:items-center lg:gap-[100px] lg:px-[60px] <?php echo $index < count( $rows ) - 1 ? 'border-b border-[#f3f4f6]' : ''; ?>">
			<div class="h-[300px] w-full shrink-0 overflow-hidden bg-[#f9fafb] sm:h-[420px] lg:w-[548px] <?php echo 1 === $index % 2 ? 'lg:order-last' : ''; ?>">
				<?php echo plenty_content_image( "story_{$i}_image", 'large', array( 'class' => 'size-full object-cover' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>

			<div class="flex min-w-0 flex-1 flex-col items-start gap-5">
				<?php if ( $eyebrow ) : ?>
					<p class="text-[10px] font-bold uppercase leading-[15px] tracking-[1px] text-navy"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<h2 class="text-[30px] font-semibold leading-[1.25] text-ink sm:text-[36px] sm:leading-[45px]"><?php echo esc_html( plenty_content( "story_{$i}_title" ) ); ?></h2>
				<p class="max-w-[448px] text-[16px] leading-[1.3] text-muted"><?php echo esc_html( plenty_content( "story_{$i}_text" ) ); ?></p>
				<?php if ( plenty_link_title( $button ) ) : ?>
					<a class="mt-2 rounded-full bg-navy px-6 py-3 text-[12px] font-bold leading-4 tracking-[0.3px] text-white transition hover:bg-ink" <?php echo plenty_link_attrs( $button ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( plenty_link_title( $button ) ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</section>
