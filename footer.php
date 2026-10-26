<?php
/**
 * Site footer. Text and images are editable in the front page's "Site Settings" fields;
 * the link columns are the menus assigned to the Footer column 1–3 locations.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

$footer_label = 'font-helvetica text-[16px] tracking-[0.1501px] uppercase';
?>
<footer class="bg-navy font-helvetica leading-[normal] text-white">
	<div class="container-site flex flex-col gap-12 pb-[46px] pt-20 lg:min-h-[522.5px] lg:flex-row lg:items-start lg:justify-between">
		<div class="flex max-w-[528px] flex-col gap-4 text-[16px] tracking-[0.1501px]">
			<?php
			$footer_logo = plenty_content_image( 'footer_logo', 'medium', array( 'class' => 'h-[68px] w-auto' ) );
			if ( $footer_logo ) {
				echo $footer_logo; // phpcs:ignore WordPress.Security.EscapeOutput -- generated <img> markup.
			} else {
				plenty_logo( 'text-white' );
			}
			?>

			<?php if ( $address = plenty_content( 'footer_address' ) ) : ?>
				<p><?php echo nl2br( esc_html( $address ) ); ?></p>
			<?php endif; ?>

			<?php if ( $hours = plenty_content( 'footer_hours' ) ) : ?>
				<p class="[&_a]:underline-offset-2 [&_a:hover]:underline"><?php echo wp_kses_post( make_clickable( nl2br( esc_html( $hours ) ) ) ); ?></p>
			<?php endif; ?>

			<?php
			$socials = array_filter(
				array(
					'facebook'  => array( plenty_content( 'footer_facebook_url' ), 'Facebook', 'icon-facebook.svg', 18.4, 31.9 ),
					'instagram' => array( plenty_content( 'footer_instagram_url' ), 'Instagram', 'icon-instagram.svg', 31.9, 31.9 ),
				),
				fn( $social ) => '' !== $social[0]
			);
			?>
			<?php if ( $socials ) : ?>
				<p class="<?php echo esc_attr( $footer_label ); ?>"><?php echo esc_html( plenty_content( 'footer_follow_label' ) ); ?></p>
				<div class="flex gap-4">
					<?php foreach ( $socials as $social ) : ?>
						<a class="flex size-[38px] items-center justify-center hover:opacity-80" href="<?php echo esc_url( $social[0] ); ?>" target="_blank" rel="noopener">
							<?php echo plenty_asset_img( $social[2], $social[3], $social[4], array( 'class' => 'block' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							<span class="sr-only"><?php echo esc_html( $social[1] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<p class="<?php echo esc_attr( $footer_label ); ?>"><?php echo esc_html( plenty_content( 'footer_payments_label' ) ); ?></p>
			<?php get_template_part( 'template-parts/footer/payment-methods' ); ?>
		</div>

		<div class="grid grid-cols-2 gap-x-[60px] gap-y-10 sm:flex sm:px-2.5">
			<?php foreach ( array( 'footer_1', 'footer_2', 'footer_3' ) as $location ) : ?>
				<?php if ( has_nav_menu( $location ) ) : ?>
					<nav class="flex flex-col gap-4" aria-label="<?php echo esc_attr( wp_get_nav_menu_name( $location ) ); ?>">
						<p class="text-[22px] tracking-[0.1501px]"><?php echo esc_html( wp_get_nav_menu_name( $location ) ); ?></p>
						<?php
						wp_nav_menu(
							array(
								'theme_location' => $location,
								'container'      => false,
								'menu_class'     => 'flex flex-col gap-4 text-[16px] tracking-[0.1501px] [&_a:hover]:underline',
								'depth'          => 1,
							)
						);
						?>
					</nav>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
