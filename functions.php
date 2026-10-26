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

if ( class_exists( 'WooCommerce' ) ) {
	require PLENTY_DIR . '/inc/woocommerce.php';
}
