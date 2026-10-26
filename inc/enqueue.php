<?php
/**
 * Front-end assets.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'plenty_enqueue_assets' );

function plenty_enqueue_assets(): void {
	$css_path = PLENTY_DIR . '/dist/app.css';

	// dist/ is a build artifact: run `npm run build` (or `npm run dev`) to create it.
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'plenty', PLENTY_URI . '/dist/app.css', array(), (string) filemtime( $css_path ) );
	}
}
