<?php
/**
 * Advanced Custom Fields (free) integration.
 *
 * Field groups are stored as JSON in acf-json/ so they are versioned with the theme.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/settings/save_json', 'plenty_acf_json_save_point' );
add_filter( 'acf/settings/load_json', 'plenty_acf_json_load_point' );

function plenty_acf_json_save_point(): string {
	return PLENTY_DIR . '/acf-json';
}

function plenty_acf_json_load_point( array $paths ): array {
	// Drop the default (theme root) path and load only from acf-json/.
	unset( $paths[0] );
	$paths[] = PLENTY_DIR . '/acf-json';

	return $paths;
}

/**
 * Read an ACF field with a fallback, so templates keep working when ACF is inactive.
 *
 * @param string    $selector Field name.
 * @param mixed     $fallback Value used when ACF is missing or the field is empty.
 * @param int|false $post_id  Post ID, defaults to the current post.
 * @return mixed
 */
function plenty_field( string $selector, $fallback = '', $post_id = false ) {
	if ( ! function_exists( 'get_field' ) ) {
		return $fallback;
	}

	$value = get_field( $selector, $post_id );

	return ( null === $value || '' === $value || false === $value || array() === $value ) ? $fallback : $value;
}

// Show the design default as the placeholder of empty theme fields, so editors see what renders.
add_filter( 'acf/prepare_field', 'plenty_acf_default_placeholder' );

function plenty_acf_default_placeholder( $field ) {
	if ( ! is_array( $field ) || ! str_starts_with( (string) $field['key'], 'field_plenty_' ) ) {
		return $field;
	}

	$default = plenty_content_defaults()[ $field['_name'] ?? $field['name'] ] ?? null;

	if ( in_array( $field['type'], array( 'text', 'textarea', 'number' ), true ) && is_scalar( $default ) && empty( $field['placeholder'] ) ) {
		$field['placeholder'] = (string) $default;
	}

	return $field;
}

/*
 * Under theme image fields that have a bundled default, show that image, so editors see what the
 * site displays while the field is empty. Hidden by CSS once an image is picked (ACF adds .has-value).
 */
add_action( 'acf/render_field/type=image', 'plenty_acf_default_image_preview' );

function plenty_acf_default_image_preview( array $field ): void {
	if ( ! str_starts_with( (string) $field['key'], 'field_plenty_' ) ) {
		return;
	}

	$default = plenty_image_defaults()[ $field['_name'] ?? $field['name'] ] ?? null;

	if ( ! $default ) {
		return;
	}
	?>
	<div class="plenty-acf-default">
		<p class="description"><?php esc_html_e( 'Se muestra actualmente (predeterminado):', 'plenty' ); ?></p>
		<?php echo plenty_asset_img( $default[0], $default[1], $default[2], array( 'loading' => 'eager' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<?php
}

add_action( 'acf/input/admin_head', 'plenty_acf_default_image_styles' );

function plenty_acf_default_image_styles(): void {
	?>
	<style>
		.plenty-acf-default { margin-top: 8px; }
		.plenty-acf-default img { display: block; max-width: 150px; max-height: 80px; width: auto; height: auto; margin-top: 4px; padding: 6px; background: #f6f7f7; border: 1px solid #dcdcde; border-radius: 4px; }
		.acf-image-uploader.has-value + .plenty-acf-default { display: none; }
	</style>
	<?php
}

/*
 * The site-wide groups (Site Settings, Product Page Settings) share the front page's edit screen
 * with the Home Page fields, so start them collapsed. WordPress remembers collapsed boxes per user;
 * this closes them once per user, after which their own open/closed choice sticks.
 */
add_action( 'load-post.php', 'plenty_acf_collapse_site_wide_groups' );

function plenty_acf_collapse_site_wide_groups(): void {
	$user_id = get_current_user_id();

	if ( ! $user_id || get_user_meta( $user_id, 'plenty_collapsed_site_wide_groups', true ) ) {
		return;
	}

	$closed = get_user_meta( $user_id, 'closedpostboxes_page', true );
	$closed = is_array( $closed ) ? $closed : array();

	update_user_meta( $user_id, 'closedpostboxes_page', array_values( array_unique( array_merge( $closed, array( 'acf-group_plenty_site_settings', 'acf-group_plenty_product_settings' ) ) ) ) );
	update_user_meta( $user_id, 'plenty_collapsed_site_wide_groups', 1 );
}
