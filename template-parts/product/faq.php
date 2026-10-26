<?php
/**
 * FAQ accordion (up to 10 questions): per product on the product page, per page on the About
 * page. The first 8 show; "Show all" reveals the rest (assets/js/theme.js). Hidden when there
 * are no answered questions.
 *
 * @package Plenty
 *
 * @var array $args {
 *     @type WC_Product $product Product (product page), or:
 *     @type int        $post_id Page holding the fields.
 *     @type string     $prefix  Field name prefix: "product_faq" (default) or "page_faq".
 * }
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $args['product'] ) ? $args['product']->get_id() : (int) $args['post_id'];
$prefix  = $args['prefix'] ?? 'product_faq';
$visible = 8;
$items   = array();

for ( $i = 1; $i <= 10; $i++ ) {
	$question = plenty_field( "{$prefix}_{$i}_question", '', $post_id );
	$answer   = plenty_field( "{$prefix}_{$i}_answer", '', $post_id );
	if ( $question && $answer ) {
		$items[] = array( $question, $answer );
	}
}

if ( ! $items ) {
	return;
}
?>
<section class="py-[100px]">
	<div class="container-site pb-4" data-faq>
		<h2 class="text-[32px] font-semibold leading-[1.2] tracking-[-0.96px] text-[#181831] sm:text-[48px] sm:leading-[38.4px]"><?php echo esc_html( plenty_content( 'pdp_faq_title' ) ); ?></h2>

		<div class="mt-10">
			<?php foreach ( $items as $index => [ $question, $answer ] ) : ?>
				<details class="group border-b border-[#d1cfd7]" <?php echo $index >= $visible ? 'hidden data-faq-extra' : ''; ?>>
					<summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-6 py-4 text-[18px] leading-[25.2px] text-[#181831] [&::-webkit-details-marker]:hidden">
						<?php echo esc_html( $question ); ?>
						<svg class="shrink-0 transition-transform group-open:rotate-45" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="M8 2v12M2 8h12"/></svg>
					</summary>
					<div class="max-w-3xl pb-6 text-[16px] leading-[1.6] text-muted [&_a]:underline [&_p+p]:mt-3"><?php echo wp_kses_post( wpautop( $answer ) ); ?></div>
				</details>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $items ) > $visible ) : ?>
			<p class="mt-8 flex flex-wrap items-center gap-4 text-[14px] leading-[19.6px] text-[#181831]" data-faq-more>
				<span><?php echo esc_html( str_replace( array( '{shown}', '{total}' ), array( $visible, count( $items ) ), plenty_content( 'pdp_faq_showing' ) ) ); ?></span>
				<button type="button" class="font-medium underline hover:no-underline" data-faq-show-all><?php echo esc_html( plenty_content( 'pdp_faq_show_all' ) ); ?></button>
			</p>
		<?php endif; ?>
	</div>
</section>
