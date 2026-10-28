<?php
/**
 * One-time seed: store the design defaults as real ACF values, so merchants edit text instead of
 * filling empty fields. The front end looks the same before and after.
 *
 * Run with WP-CLI from the WordPress root:  wp eval-file wp-content/themes/plenty/bin/seed-defaults.php
 *
 * - Defaults come from plenty_content_defaults() / plenty_image_defaults() (inc/content.php).
 * - Targets: the static front page (Home, Site Settings, Product Page Settings groups), pages using
 *   the About / Contact templates, and products (only fields that have a default in code).
 * - Fills only empty fields; existing values are never overwritten, so re-running is harmless.
 * - Default images are imported into the Media Library once (tagged with _plenty_default_image)
 *   and reused afterwards. SVGs need the Safe SVG plugin (imported through it so they're sanitized).
 *   Skipped images keep rendering the bundled file (see plenty_seed_image_skip_reason()).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'update_field' ) || ! function_exists( 'plenty_content_defaults' ) ) {
	echo "ACF and the Plenty theme must be active.\n";
	return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Why a default image can't be stored as an attachment without changing the front end, or ''.
 */
function plenty_seed_image_skip_reason( string $name, string $file ): string {
	// Safe SVG allows, sanitizes and sizes SVG attachments; without it WordPress rejects them.
	if ( 'svg' === strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) && ! class_exists( 'SafeSvg\\safe_svg' ) ) {
		return 'SVG (needs the Safe SVG plugin)';
	}

	// template-parts/home/features.php sizes the bundled file with different classes than an upload.
	if ( 'feature_1_icon' === $name ) {
		return 'would render larger as an attachment';
	}

	return '';
}

/**
 * Whether a stored ACF value counts as empty (never saved or cleared in the editor).
 *
 * @param mixed $raw Raw post meta.
 */
function plenty_seed_is_empty( $raw, string $type ): bool {
	if ( 'image' === $type ) {
		return ! (int) $raw;
	}

	if ( is_array( $raw ) ) {
		return ! array_filter( $raw, fn( $v ) => '' !== $v && null !== $v );
	}

	return null === $raw || false === $raw || '' === $raw;
}

/**
 * Attachment ID of a bundled default image, importing it into the Media Library on first use.
 *
 * @param array $stats Counters: imported / reused.
 */
function plenty_seed_attachment( string $file, array &$stats ): int {
	static $ids = array();

	$rel = 'assets/images/' . $file;

	if ( isset( $ids[ $rel ] ) ) {
		++$stats['reused'];
		return $ids[ $rel ];
	}

	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_status' => 'any',
			'numberposts' => 1,
			'fields'      => 'ids',
			'meta_key'    => '_plenty_default_image', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $rel, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	if ( $existing ) {
		++$stats['reused'];
		return $ids[ $rel ] = (int) $existing[0];
	}

	if ( 'svg' === strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ) ) {
		$id = plenty_seed_sideload_svg( $file );

		if ( $id ) {
			update_post_meta( $id, '_plenty_default_image', $rel );
			++$stats['imported'];
			$ids[ $rel ] = $id;
		}

		return $id;
	}

	$upload = wp_upload_bits( $file, null, (string) file_get_contents( PLENTY_DIR . '/' . $rel ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions

	if ( $upload['error'] ) {
		echo "  ! {$file}: {$upload['error']}\n";
		return 0;
	}

	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $upload['file'] )['type'],
			'post_title'     => pathinfo( $file, PATHINFO_FILENAME ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$upload['file'],
		0,
		true
	);

	if ( is_wp_error( $id ) ) {
		echo "  ! {$file}: {$id->get_error_message()}\n";
		return 0;
	}

	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_plenty_default_image', $rel );
	++$stats['imported'];

	return $ids[ $rel ] = $id;
}

/**
 * Import a bundled SVG through the regular upload path, so Safe SVG sanitizes it and records its
 * size. Runs as an administrator because Safe SVG only accepts SVGs from users allowed to upload them.
 */
function plenty_seed_sideload_svg( string $file ): int {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$admins   = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) );
	$previous = get_current_user_id();
	$tmp      = wp_tempnam( $file );

	copy( PLENTY_DIR . '/assets/images/' . $file, $tmp );
	wp_set_current_user( (int) ( $admins[0] ?? 0 ) );

	$id = media_handle_sideload(
		array(
			'name'     => $file,
			'tmp_name' => $tmp,
		),
		0,
		pathinfo( $file, PATHINFO_FILENAME )
	);

	wp_set_current_user( $previous );

	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		echo "  ! {$file}: {$id->get_error_message()}\n";
		return 0;
	}

	return (int) $id;
}

/**
 * Fill the empty fields of some field groups on one post.
 */
function plenty_seed_post( int $post_id, array $groups ): void {
	$texts  = plenty_content_defaults();
	$images = plenty_image_defaults();
	$stats  = array(
		'filled'   => 0,
		'kept'     => 0,
		'imported' => 0,
		'reused'   => 0,
	);
	$skipped = array();

	foreach ( $groups as $group ) {
		foreach ( acf_get_fields( $group ) ?: array() as $field ) {
			$name = $field['name'];

			if ( 'image' === $field['type'] ) {
				if ( ! isset( $images[ $name ] ) ) {
					continue;
				}
			} elseif ( ! array_key_exists( $name, $texts ) ) {
				continue;
			}

			if ( ! plenty_seed_is_empty( get_post_meta( $post_id, $name, true ), $field['type'] ) ) {
				++$stats['kept'];
				continue;
			}

			if ( 'image' === $field['type'] ) {
				$reason = plenty_seed_image_skip_reason( $name, $images[ $name ][0] );

				if ( $reason ) {
					$skipped[] = "{$name} ({$reason})";
					continue;
				}

				$value = plenty_seed_attachment( $images[ $name ][0], $stats );

				if ( ! $value ) {
					continue;
				}
			} else {
				$value = $texts[ $name ];
			}

			// update_metadata() unslashes, and ACF passes the value through as is.
			if ( update_field( $field['key'], wp_slash( $value ), $post_id ) ) {
				++$stats['filled'];
			} else {
				echo "  ! could not save {$name}\n";
			}
		}
	}

	printf(
		"#%d %s [%s]: %d filled, %d already set, images %d imported / %d reused\n",
		$post_id,
		get_the_title( $post_id ),
		get_post_status( $post_id ),
		$stats['filled'],
		$stats['kept'],
		$stats['imported'],
		$stats['reused']
	);

	foreach ( $skipped as $line ) {
		echo "  - skipped image {$line}\n";
	}
}

/**
 * Field groups that have at least one default in code.
 */
function plenty_seed_groups_with_defaults( array $groups ): array {
	$names = array_keys( plenty_content_defaults() + plenty_image_defaults() );

	return array_values(
		array_filter(
			$groups,
			fn( $group ) => (bool) array_intersect( wp_list_pluck( acf_get_fields( $group ) ?: array(), 'name' ), $names )
		)
	);
}

// Front page: home sections plus the site-wide groups that live on it.
$plenty_front_id = plenty_front_page_id();

echo "== Front page\n";
if ( $plenty_front_id ) {
	plenty_seed_post( $plenty_front_id, array( 'group_plenty_home', 'group_plenty_site_settings', 'group_plenty_product_settings' ) );
} else {
	echo "No static front page (Settings → Reading), skipped.\n";
}

// Pages using the About / Contact templates.
foreach (
	array(
		'page-templates/about.php'   => 'group_plenty_about_page',
		'page-templates/contact.php' => 'group_plenty_contact_page',
	) as $plenty_template => $plenty_group
) {
	echo "== {$plenty_template}\n";

	$plenty_pages = get_posts(
		array(
			'post_type'   => 'page',
			'post_status' => 'any',
			'numberposts' => -1,
			'fields'      => 'ids',
			'meta_key'    => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'  => $plenty_template, // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	if ( ! $plenty_pages ) {
		echo "No page uses this template, skipped.\n";
	}

	foreach ( $plenty_pages as $plenty_page_id ) {
		plenty_seed_post( (int) $plenty_page_id, array( $plenty_group ) );
	}
}

// Products: only groups with defaults in code (product card text falls back to the short description, not a default).
echo "== Products\n";
$plenty_product_groups = plenty_seed_groups_with_defaults( array( 'group_plenty_product_card', 'group_plenty_product_page' ) );

if ( ! $plenty_product_groups ) {
	echo "No product field has a default in code, skipped.\n";
} else {
	foreach ( get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) ) as $plenty_product_id ) {
		plenty_seed_post( (int) $plenty_product_id, $plenty_product_groups );
	}
}
