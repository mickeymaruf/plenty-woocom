<?php
/**
 * Footer payment logos. Each slot can be replaced with an uploaded image (footer_payment_1–4);
 * empty slots render the logos from the design, composed exactly as in Figma.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

$piece = fn( string $file, float $width, float $height, string $inset ) => sprintf(
	'<span class="absolute %s">%s</span>',
	esc_attr( $inset ),
	plenty_asset_img( $file, $width, $height, array( 'class' => 'block size-full' ) )
);

$defaults = array(
	1 => array( 85, 'Mercado Pago', $piece( 'pay-mercadopago-a.svg', 36.93, 16.11, 'inset-[31.27%_12.46%_31.27%_44.09%]' ) . $piece( 'pay-mercadopago-b.svg', 23.29, 16.12, 'inset-[31.26%_60.14%_31.26%_12.46%]' ) ),
	2 => array( 70, 'Visa', $piece( 'pay-visa.svg', 52.48, 17.69, 'inset-[29.44%_9.67%_29.43%_15.35%]' ) . $piece( 'pay-visa-flag.svg', 11.28, 9.32, 'inset-[30.13%_75.47%_48.19%_8.42%]' ) ),
	3 => array( 70, 'Mastercard', $piece( 'pay-mastercard.svg', 70, 43, 'inset-0' ) ),
	4 => array( 70, 'American Express', $piece( 'pay-amex.svg', 70, 43, 'inset-0' ) ),
);
?>
<ul class="flex flex-wrap gap-2.5">
	<?php foreach ( $defaults as $slot => [ $width, $label, $markup ] ) : ?>
		<?php $custom = plenty_content_image( "footer_payment_{$slot}", 'medium', array( 'class' => 'h-[43px] w-auto' ) ); ?>
		<li>
			<?php if ( $custom ) : ?>
				<?php echo $custom; // phpcs:ignore WordPress.Security.EscapeOutput -- generated <img> markup. ?>
			<?php else : ?>
				<span class="relative block h-[43px] overflow-hidden" style="width: <?php echo (int) $width; ?>px" role="img" aria-label="<?php echo esc_attr( $label ); ?>">
					<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput -- built from plenty_asset_img(). ?>
				</span>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
