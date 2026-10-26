<?php
/**
 * Theme supports and menus.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'plenty_setup' );

function plenty_setup(): void {
	load_theme_textdomain( 'plenty', PLENTY_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 240,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	// WooCommerce.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 3,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
	// No wc-product-gallery-* supports: the product page uses its own gallery (template-parts/product/gallery.php).

	// Footer columns use the assigned menu's name as the column heading.
	register_nav_menus(
		array(
			'primary'  => __( 'Primary', 'plenty' ),
			'footer_1' => __( 'Footer column 1', 'plenty' ),
			'footer_2' => __( 'Footer column 2', 'plenty' ),
			'footer_3' => __( 'Footer column 3', 'plenty' ),
		)
	);
}
