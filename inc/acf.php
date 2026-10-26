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

	return ( null === $value || '' === $value || false === $value ) ? $fallback : $value;
}
