<?php
/**
 * Contact form (Figma "Input area"). Handled by inc/contact-form.php; submitted with fetch by
 * assets/js/theme.js, with field errors, a loading button and a success/error message.
 *
 * @package Plenty
 *
 * @var array $args { @type int $post_id Contact page ID. }
 */

defined( 'ABSPATH' ) || exit;

$page_id = (int) $args['post_id'];
$status  = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
$field   = 'w-full border-0 border-b border-[#cacaca] bg-transparent px-2.5 pb-8 pt-2.5 text-[20px] leading-9 text-black outline-none transition placeholder:text-black focus:border-navy aria-invalid:border-[#cc1818] sm:text-[24px]';
$error   = 'mt-2 hidden px-2.5 text-[13px] leading-5 text-[#cc1818] [&:not(:empty)]:block';
$inputs  = array(
	'name'  => array( 'text', 'name', true ),
	'email' => array( 'email', 'email', true ),
	'phone' => array( 'tel', 'tel', false ),
);
?>
<form
	id="contact-form"
	class="flex w-full flex-col items-start gap-12"
	method="post"
	action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
	data-contact-form
	data-error-message="<?php esc_attr_e( 'Something went wrong. Please try again.', 'plenty' ); ?>"
>
	<input type="hidden" name="action" value="plenty_contact">
	<input type="hidden" name="page_id" value="<?php echo esc_attr( $page_id ); ?>">

	<?php // Honeypot: hidden from people, filled in by bots. ?>
	<div class="absolute -left-[9999px]" aria-hidden="true">
		<label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
	</div>

	<div class="grid w-full gap-8 md:grid-cols-3">
		<?php foreach ( $inputs as $name => [ $type, $autocomplete, $required ] ) : ?>
			<?php $label = plenty_content( "contact_{$name}_label", $page_id ); ?>
			<div>
				<label class="sr-only" for="contact-<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
				<input
					id="contact-<?php echo esc_attr( $name ); ?>"
					class="<?php echo esc_attr( $field ); ?>"
					type="<?php echo esc_attr( $type ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					placeholder="<?php echo esc_attr( $label ); ?>"
					autocomplete="<?php echo esc_attr( $autocomplete ); ?>"
					aria-describedby="contact-<?php echo esc_attr( $name ); ?>-error"
					<?php echo $required ? 'required' : ''; ?>
				>
				<p id="contact-<?php echo esc_attr( $name ); ?>-error" class="<?php echo esc_attr( $error ); ?>" data-error-for="<?php echo esc_attr( $name ); ?>"></p>
			</div>
		<?php endforeach; ?>
	</div>

	<?php $message_label = plenty_content( 'contact_message_label', $page_id ); ?>
	<div class="w-full">
		<label class="sr-only" for="contact-message"><?php echo esc_html( $message_label ); ?></label>
		<textarea id="contact-message" class="<?php echo esc_attr( $field ); ?> h-[146px] resize-none pb-2.5" name="message" placeholder="<?php echo esc_attr( $message_label ); ?>" aria-describedby="contact-message-error" maxlength="5000" required></textarea>
		<p id="contact-message-error" class="<?php echo esc_attr( $error ); ?>" data-error-for="message"></p>
	</div>

	<p
		class="w-full rounded-xl px-4 py-3 text-[14px] leading-5 data-[type=error]:bg-[#fef2f2] data-[type=error]:text-[#cc1818] data-[type=success]:bg-[#ecfdf5] data-[type=success]:text-[#007a55]"
		role="status"
		data-contact-status
		<?php if ( 'sent' === $status ) : ?>
			data-type="success"
		<?php elseif ( 'error' === $status ) : ?>
			data-type="error"
		<?php else : ?>
			hidden
		<?php endif; ?>
	><?php
	if ( 'sent' === $status ) {
		echo esc_html( plenty_content( 'contact_success', $page_id ) );
	} elseif ( 'error' === $status ) {
		esc_html_e( 'Your message couldn’t be sent. Please check the fields and try again.', 'plenty' );
	}
	?></p>

	<button type="submit" class="group flex items-center justify-center gap-2 rounded-full bg-navy px-[42px] py-5 text-[16px] font-semibold leading-[1.2] text-white transition hover:bg-ink aria-busy:cursor-progress" data-contact-submit>
		<?php echo esc_html( plenty_content( 'contact_button_label', $page_id ) ); ?>
		<?php echo plenty_asset_img( 'icon-arrow-right-20.svg', 20, 20, array( 'class' => 'block group-aria-busy:hidden', 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<span class="hidden size-5 animate-spin rounded-full border-2 border-white/35 border-t-white group-aria-busy:block" aria-hidden="true"></span>
	</button>
</form>
