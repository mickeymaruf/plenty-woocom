<?php
/**
 * Small rendering helpers shared by templates.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

function plenty_asset_url( string $file ): string {
	return PLENTY_URI . '/assets/images/' . $file;
}

/**
 * <img> tag for a bundled theme image.
 */
function plenty_asset_img( string $file, float $width, float $height, array $attrs = array() ): string {
	$attrs = array_merge(
		array(
			'src'      => plenty_asset_url( $file ),
			'width'    => (int) round( $width ),
			'height'   => (int) round( $height ),
			'alt'      => '',
			'loading'  => 'lazy',
			'decoding' => 'async',
		),
		$attrs
	);

	$html = '<img';
	foreach ( $attrs as $name => $value ) {
		$html .= sprintf( ' %s="%s"', esc_attr( $name ), 'src' === $name ? esc_url( $value ) : esc_attr( (string) $value ) );
	}

	return $html . '>';
}

/**
 * Plenty wordmark, drawn in currentColor. Set the color with a text-* class.
 */
function plenty_logo( string $class = '' ): void {
	printf(
		'<span class="plenty-logo %s" style="%s" aria-hidden="true"></span>',
		esc_attr( $class ),
		esc_attr( "--logo-mask:url('" . esc_url( plenty_asset_url( 'logo-mask.png' ) ) . "')" )
	);
}

/**
 * href (and target/rel) attributes for an ACF link array.
 */
function plenty_link_attrs( $link ): string {
	$url    = is_array( $link ) ? ( $link['url'] ?? '' ) : '';
	$target = is_array( $link ) ? ( $link['target'] ?? '' ) : '';
	$attrs  = sprintf( ' href="%s"', esc_url( $url ? $url : '#' ) );

	if ( '_blank' === $target ) {
		$attrs .= ' target="_blank" rel="noopener"';
	}

	return $attrs;
}

function plenty_link_title( $link ): string {
	return is_array( $link ) ? (string) ( $link['title'] ?? '' ) : '';
}
