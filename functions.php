<?php
/**
 * Plenty theme bootstrap.
 *
 * Keep this file thin: each concern lives in its own file under inc/.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

define( 'PLENTY_VERSION', '0.1.0' );
define( 'PLENTY_DIR', get_template_directory() );
define( 'PLENTY_URI', get_template_directory_uri() );

require PLENTY_DIR . '/inc/setup.php';
require PLENTY_DIR . '/inc/enqueue.php';
require PLENTY_DIR . '/inc/acf.php';
require PLENTY_DIR . '/inc/template-tags.php';
require PLENTY_DIR . '/inc/content.php';
require PLENTY_DIR . '/inc/contact-form.php';

if ( class_exists( 'WooCommerce' ) ) {
	require PLENTY_DIR . '/inc/woocommerce.php';
	require PLENTY_DIR . '/inc/shipping.php';
	require PLENTY_DIR . '/inc/cart-drawer.php';
	require PLENTY_DIR . '/inc/checkout-demo.php'; // TEMPORARY DEMO — remove with the demo files.
}
