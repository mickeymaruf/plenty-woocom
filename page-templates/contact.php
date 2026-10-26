<?php
/**
 * Template Name: Contact
 *
 * Contact page (Figma node 53:86): intro with social links, contact form, and the shared
 * "Sleep Specialists" section. Content: the page's "Contact Page" fields; social links come
 * from Site Settings.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_queried_object_id();
$socials = array(
	array( plenty_content( 'footer_facebook_url' ), 'Facebook', '' ),
	array( plenty_content( 'footer_instagram_url' ), 'Instagram', plenty_asset_img( 'social-instagram.svg', 18, 17.89, array( 'class' => 'absolute left-4 top-[15.9px]' ) ) ),
	array( plenty_content( 'footer_x_url' ), 'X', plenty_asset_img( 'social-x.png', 18, 18, array( 'class' => 'absolute left-4 top-[15.6px] size-[18px]' ) ) ),
);
?>

<main id="main">
	<section class="container-site flex flex-col gap-20 py-16 lg:py-[100px]">
		<div class="flex flex-col gap-10 sm:flex-row sm:items-start sm:justify-between">
			<div class="flex max-w-[759px] flex-col gap-4 text-black">
				<p class="text-[20px] leading-9 sm:text-[24px]"><?php echo esc_html( plenty_content( 'contact_eyebrow', $page_id ) ); ?></p>
				<h1 class="text-[36px] font-semibold capitalize leading-[1.2] sm:text-[48px] lg:text-[60px]"><?php echo esc_html( plenty_content( 'contact_title', $page_id ) ); ?></h1>
			</div>

			<ul class="flex shrink-0 gap-6 sm:flex-col">
				<?php foreach ( $socials as [ $url, $label, $icon ] ) : ?>
					<?php if ( $url ) : ?>
						<li>
							<a class="relative block size-[50px] rounded-full transition hover:bg-surface" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener">
								<?php if ( $icon ) : ?>
									<?php echo plenty_asset_img( 'social-circle.svg', 51, 51, array( 'class' => 'absolute inset-0 size-[50px]' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
									<?php echo $icon; // phpcs:ignore WordPress.Security.EscapeOutput -- from plenty_asset_img(). ?>
								<?php else : ?>
									<?php echo plenty_asset_img( 'social-facebook.svg', 51, 51, array( 'class' => 'absolute inset-0 size-[50px]' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
								<?php endif; ?>
								<span class="sr-only"><?php echo esc_html( $label ); ?></span>
							</a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php get_template_part( 'template-parts/contact/form', null, array( 'post_id' => $page_id ) ); ?>
	</section>

	<?php get_template_part( 'template-parts/sections/support' ); ?>
</main>

<?php
get_footer();
