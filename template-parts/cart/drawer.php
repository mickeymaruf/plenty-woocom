<?php
/**
 * Cart drawer shell: backdrop, sliding panel, loading overlay and live region.
 * Opened/closed by assets/js/cart-drawer.js via the data-open attribute.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;
?>
<?php // Visibility switches instantly on open (so focus can move in) and after the slide-out on close. ?>
<div id="cart-drawer" class="group invisible fixed inset-0 z-[100000] transition-[visibility] duration-300 data-open:visible data-open:transition-none"data-cart-drawer role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" aria-busy="false" inert>
	<div class="absolute inset-0 bg-black/50 opacity-0 transition-opacity duration-300 group-data-open:opacity-100 motion-reduce:transition-none" data-cart-close></div>

	<div class="absolute inset-y-0 right-0 flex w-full max-w-[420px] translate-x-full flex-col bg-white shadow-[0_25px_25px_rgba(0,0,0,0.25)] transition-transform duration-300 ease-out group-data-open:translate-x-0 motion-reduce:transition-none" data-cart-panel>
		<?php plenty_cart_drawer_content(); ?>

		<div class="absolute inset-0 hidden flex-col items-center justify-center gap-3 bg-white/90 group-aria-busy:flex" aria-hidden="true">
			<span class="size-7 animate-spin rounded-full border-2 border-navy/20 border-t-navy"></span>
			<span class="text-[12px] font-semibold leading-4 text-navy" data-cart-loader-text></span>
		</div>

		<p class="sr-only" aria-live="polite" data-cart-announcer></p>
	</div>
</div>
