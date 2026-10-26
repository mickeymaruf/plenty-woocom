<?php
/**
 * Template Name: About
 *
 * About page (Figma node 55:1331). The intro, values and FAQ are this page's "About Page"
 * fields; the story rows, product grid and "Sleep Specialists" are the shared sections from the
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
