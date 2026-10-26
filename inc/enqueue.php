<?php
/**
 * Front-end assets.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'plenty_enqueue_assets' );

function plenty_enqueue_assets(): void {
	wp_enqueue_style( 'plenty-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Montserrat:wght@400;600&display=swap', array(), null );

	$css_path = PLENTY_DIR . '/dist/app.css';

	// dist/ is a build artifact: run `npm run build` (or `npm run dev`) to create it.
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style( 'plenty', PLENTY_URI . '/dist/app.css', array( 'plenty-fonts' ), (string) filemtime( $css_path ) );
	}

	wp_enqueue_script(
		'plenty',
		PLENTY_URI . '/assets/js/theme.js',
		array(),
		(string) filemtime( PLENTY_DIR . '/assets/js/theme.js' ),
		array( 'strategy' => 'defer' )
	);
}

add_filter( 'wp_resource_hints', 'plenty_font_resource_hints', 10, 2 );

function plenty_font_resource_hints( array $urls, string $relation_type ): array {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
