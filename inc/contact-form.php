<?php
/**
 * Contact form (page template "Contact", template-parts/contact/form.php).
 *
 * Posts to admin-post.php?action=plenty_contact and emails the message with wp_mail() to the
 * page's "Send messages to" address (default: the site admin email). assets/js/theme.js submits
 * it with fetch and gets JSON back; without JS the handler redirects to the page with
 * ?contact=sent|error.
 *
 * No nonce on purpose: guests' nonces change once WooCommerce starts their session (and break
 * with page caching), and a forged contact message has no effect beyond the message itself.
 * Spam protection is a hidden honeypot field plus a per-IP rate limit.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_post_plenty_contact', 'plenty_contact_submit' );
add_action( 'admin_post_nopriv_plenty_contact', 'plenty_contact_submit' );

function plenty_contact_submit(): void {
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- see file comment.
	$page_id = isset( $_POST['page_id'] ) ? absint( $_POST['page_id'] ) : 0;
	$ajax    = ! empty( $_POST['ajax'] );
	$data    = array(
		'name'    => isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '',
		'email'   => isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '',
		'phone'   => isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '',
		'message' => isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '',
	);
	$honeypot = ! empty( $_POST['website'] );
	// phpcs:enable

	$respond = function ( bool $ok, string $message, array $errors = array() ) use ( $ajax, $page_id ): void {
		if ( $ajax ) {
			$ok ? wp_send_json_success( array( 'message' => $message ) ) : wp_send_json_error( array( 'message' => $message, 'errors' => $errors ) );
		}
		$url = $page_id ? get_permalink( $page_id ) : home_url( '/' );
		wp_safe_redirect( add_query_arg( 'contact', $ok ? 'sent' : 'error', $url ) . '#contact-form' );
		exit;
	};

	$success = (string) plenty_content( 'contact_success', $page_id ?: null );

	// Bots fill every field; pretend it worked.
	if ( $honeypot ) {
		$respond( true, $success );
	}

	$errors = array();
	if ( '' === $data['name'] ) {
		$errors['name'] = __( 'Ingresá tu nombre.', 'plenty' );
	}
	if ( '' === $data['email'] || ! is_email( $data['email'] ) ) {
		$errors['email'] = __( 'Ingresá un email válido.', 'plenty' );
	}
	if ( '' === trim( $data['message'] ) ) {
		$errors['message'] = __( 'Escribí un mensaje.', 'plenty' );
	} elseif ( mb_strlen( $data['message'] ) > 5000 ) {
		$errors['message'] = __( 'El mensaje no puede superar los 5.000 caracteres.', 'plenty' );
	}
	if ( $errors ) {
		$respond( false, __( 'Revisá los campos marcados.', 'plenty' ), $errors );
	}

	// At most 5 messages per IP every 10 minutes.
	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'plenty_contact_' . md5( $ip );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 5 ) {
		$respond( false, __( 'Ya enviaste varios mensajes. Por favor, intentá de nuevo en unos minutos.', 'plenty' ) );
	}
	set_transient( $rate_key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$recipient = $page_id ? (string) plenty_field( 'contact_recipient', '', $page_id ) : '';
	$recipient = is_email( $recipient ) ? $recipient : get_option( 'admin_email' );
	$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	/* translators: 1: site name, 2: sender name. */
	$subject = sprintf( __( '[%1$s] Nuevo mensaje de %2$s', 'plenty' ), $site_name, $data['name'] );
	$body    = implode(
		"\n",
		array(
			/* translators: %s: sender name. */
			sprintf( __( 'Nombre: %s', 'plenty' ), $data['name'] ),
			/* translators: %s: sender email. */
			sprintf( __( 'Email: %s', 'plenty' ), $data['email'] ),
			/* translators: %s: sender phone. */
			sprintf( __( 'Teléfono: %s', 'plenty' ), $data['phone'] ? $data['phone'] : '—' ),
			'',
			$data['message'],
			'',
			'—',
			/* translators: %s: page URL. */
			sprintf( __( 'Enviado desde %s', 'plenty' ), $page_id ? get_permalink( $page_id ) : home_url( '/' ) ),
		)
	);
	$headers = array( sprintf( 'Reply-To: %s <%s>', str_replace( array( '<', '>', '"' ), '', $data['name'] ), $data['email'] ) );

	if ( ! wp_mail( $recipient, $subject, $body, $headers ) ) {
		$respond( false, __( 'No pudimos enviar tu mensaje en este momento. Por favor, intentá más tarde o comunicate por teléfono o WhatsApp.', 'plenty' ) );
	}

	$respond( true, $success );
}
