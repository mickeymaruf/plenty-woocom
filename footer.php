<?php
/**
 * Site footer.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;
?>
<footer class="mt-16 border-t border-neutral-200">
	<div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 text-sm text-neutral-600 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-8">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>

		<nav aria-label="<?php esc_attr_e( 'Footer', 'plenty' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'flex flex-wrap gap-4',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
