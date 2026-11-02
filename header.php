<?php
/**
 * Site header.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white font-sans text-ink antialiased' ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2" href="#main"><?php esc_html_e( 'Ir al contenido', 'plenty' ); ?></a>

<header class="relative z-40 bg-white">
	<div class="container-site relative flex h-[100px] items-center justify-between">
		<a class="shrink-0 text-navy" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php echo wp_get_attachment_image( get_theme_mod( 'custom_logo' ), 'full', false, array( 'class' => 'h-[68px] w-auto' ) ); ?>
			<?php else : ?>
				<?php // Figma 4x export (632×272), sharper than the CSS-mask wordmark at this size. ?>
				<?php echo plenty_asset_img( 'logo.png', 158, 68, array( 'class' => 'block h-[68px] w-auto', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endif; ?>
			<span class="sr-only"><?php bloginfo( 'name' ); ?></span>
		</a>

		<?php if ( has_nav_menu( 'primary' ) ) : ?>
			<nav id="site-nav" data-nav class="absolute inset-x-0 top-full hidden border-t border-neutral-100 bg-white px-4 py-4 shadow-lg lg:absolute lg:inset-x-auto lg:left-1/2 lg:top-1/2 lg:block lg:-translate-x-1/2 lg:-translate-y-1/2 lg:border-0 lg:p-0 lg:shadow-none" aria-label="<?php esc_attr_e( 'Principal', 'plenty' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'flex flex-col gap-4 text-[18px] font-medium leading-[1.2] text-black lg:flex-row lg:items-center lg:gap-8 [&_a:hover]:text-navy',
						'depth'          => 1,
					)
				);
				?>
			</nav>
		<?php endif; ?>

		<div class="flex items-center gap-4">
			<?php if ( function_exists( 'plenty_cart_link' ) ) : ?>
				<?php plenty_cart_link(); ?>
			<?php endif; ?>

			<?php if ( has_nav_menu( 'primary' ) ) : ?>
				<button type="button" class="flex size-8 items-center justify-center lg:hidden" data-nav-toggle aria-controls="site-nav" aria-expanded="false">
					<span class="sr-only"><?php esc_html_e( 'Menú', 'plenty' ); ?></span>
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
				</button>
			<?php endif; ?>
		</div>
	</div>
</header>
