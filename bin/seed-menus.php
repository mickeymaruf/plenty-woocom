<?php
/**
 * One-time seed: create the header and footer menus from the design (Figma home frame 10:89,
 * footer node 18:4775) and assign them to their locations. A footer menu's name is its column heading.
 *
 * Run with WP-CLI from the WordPress root:  wp eval-file wp-content/themes/plenty/bin/seed-menus.php
 *
 * - A location that already has a menu is left alone; an existing menu with the same name is
 *   reused (assigned, not refilled). Re-running is harmless.
 * - Links point to the matching page when it exists and is published (Shop, About/Contact page
 *   templates, privacy policy, terms, blog), otherwise "#" until set in Appearance → Menus.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

/**
 * Published page for a link target, or 0. Targets: shop, terms, privacy, blog, or template:<file>.
 */
function plenty_seed_menu_page( string $target ): int {
	if ( str_starts_with( $target, 'template:' ) ) {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'numberposts' => 1,
				'fields'      => 'ids',
				'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'  => substr( $target, strlen( 'template:' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);

		return (int) ( $pages[0] ?? 0 );
	}

	switch ( $target ) {
		case 'shop':
			$id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
			break;
		case 'terms':
			$id = function_exists( 'wc_terms_and_conditions_page_id' ) ? wc_terms_and_conditions_page_id() : 0;
			break;
		case 'privacy':
			$id = (int) get_option( 'wp_page_for_privacy_policy' );
			break;
		case 'blog':
			$id = (int) get_option( 'page_for_posts' );
			break;
		default:
			$id = 0;
	}

	return $id > 0 && 'publish' === get_post_status( $id ) ? (int) $id : 0;
}

// Location => [ menu name, [ link title => target ] ].
$plenty_menus = array(
	'primary'  => array(
		'Header',
		array(
			'Colchones'     => 'shop',
			'Quienes Somos' => 'template:page-templates/about.php',
			'Contacto'      => 'template:page-templates/contact.php',
		),
	),
	'footer_1' => array(
		'Habitación',
		array(
			'Colchones' => 'shop',
			'Almohadas' => '',
			'Blancos'   => '',
		),
	),
	'footer_2' => array(
		'Ayuda',
		array(
			'Tiendas'              => '',
			'Preguntas frecuentes' => '',
			'Garantía'             => '',
			'Métodos de pago'      => '',
			'Blog'                 => 'blog',
		),
	),
	'footer_3' => array(
		'Legales',
		array(
			'Términos y Condiciones' => 'terms',
			'Aviso de privacidad'    => 'privacy',
			'PROFECO'                => '',
			'CONDUSEF'               => '',
			'Certificaciones'        => '',
		),
	),
);

$plenty_locations = get_nav_menu_locations();

foreach ( $plenty_menus as $plenty_location => list( $plenty_name, $plenty_links ) ) {
	if ( ! empty( $plenty_locations[ $plenty_location ] ) && wp_get_nav_menu_object( $plenty_locations[ $plenty_location ] ) ) {
		echo "{$plenty_location}: already has menu '", wp_get_nav_menu_name( $plenty_location ), "', left alone.\n";
		continue;
	}

	$plenty_menu = wp_get_nav_menu_object( $plenty_name );

	if ( $plenty_menu ) {
		$plenty_menu_id = (int) $plenty_menu->term_id;
		echo "{$plenty_location}: reusing existing menu '{$plenty_name}'.\n";
	} else {
		$plenty_menu_id = wp_create_nav_menu( $plenty_name );

		if ( is_wp_error( $plenty_menu_id ) ) {
			echo "{$plenty_location}: could not create '{$plenty_name}': {$plenty_menu_id->get_error_message()}\n";
			continue;
		}

		$plenty_linked = array();

		foreach ( $plenty_links as $plenty_title => $plenty_target ) {
			$plenty_page = plenty_seed_menu_page( $plenty_target );
			$plenty_item = $plenty_page
				? array(
					'menu-item-object'    => 'page',
					'menu-item-object-id' => $plenty_page,
					'menu-item-type'      => 'post_type',
				)
				: array(
					'menu-item-type' => 'custom',
					'menu-item-url'  => '#',
				);

			wp_update_nav_menu_item(
				$plenty_menu_id,
				0,
				$plenty_item + array(
					'menu-item-title'  => $plenty_title,
					'menu-item-status' => 'publish',
				)
			);

			$plenty_linked[] = $plenty_page ? "{$plenty_title} → " . get_the_title( $plenty_page ) : "{$plenty_title} → #";
		}

		echo "{$plenty_location}: created '{$plenty_name}': ", implode( ', ', $plenty_linked ), "\n";
	}

	$plenty_locations[ $plenty_location ] = $plenty_menu_id;
}

set_theme_mod( 'nav_menu_locations', $plenty_locations );
