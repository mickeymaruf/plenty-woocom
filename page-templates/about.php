<?php
/**
 * Template Name: About
 *
 * About page (Figma node 55:1331); also used for the Hotels page (/hotels/, Figma node 76:153,
 * same layout). The intro, values and FAQ are this page's own "About Page" fields; the story rows, product grid and "Sleep Specialists" are the shared sections from the
 * product and home pages (same content).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();

$page_id = get_queried_object_id();
?>

<main id="main">
	<?php
	get_template_part( 'template-parts/about/intro', null, array( 'post_id' => $page_id ) );
	get_template_part( 'template-parts/about/values', null, array( 'post_id' => $page_id ) );
	get_template_part( 'template-parts/product/stories', null, array( 'class' => 'py-[60px]' ) );
	get_template_part(
		'template-parts/home/products',
		null,
		array(
			'tabs'  => false,
			'limit' => 3,
			'class' => 'pb-[50px] lg:py-[100px]', // On mobile the story rows above already leave enough space.
		)
	);
	get_template_part(
		'template-parts/product/faq',
		null,
		array(
			'post_id' => $page_id,
			'prefix'  => 'page_faq',
		)
	);
	get_template_part( 'template-parts/sections/support' );
	?>
</main>

<?php
get_footer();
