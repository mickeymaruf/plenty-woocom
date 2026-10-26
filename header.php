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
<body <?php body_class( 'bg-white text-neutral-900 antialiased' ); ?>>
<?php wp_body_open(); ?>
<a class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:bg-white focus:px-4 focus:py-2" href="#main"><?php esc_html_e( 'Skip to content', 'plenty' ); ?></a>

<header class="border-b border-neutral-200">
	<div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
		<div class="shrink-0">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="text-xl font-bold tracking-tight" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
			<?php endif; ?>
		</div>

		<nav class="hidden md:block" aria-label="<?php esc_attr_e( 'Primary', 'plenty' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'flex items-center gap-6 text-sm font-medium',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<?php if ( function_exists( 'plenty_cart_link' ) ) : ?>
			<?php plenty_cart_link(); ?>
		<?php endif; ?>
	</div>
</header>
