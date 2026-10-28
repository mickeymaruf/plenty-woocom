<?php
/**
 * Front page (Figma "Plenty Website Design" home, node 10:89).
 *
 * Section content is editable in the "Home Page" ACF fields on the static front page;
 * see inc/content.php for the defaults used when a field is empty.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main">
	<section class="container-site pb-[50px]">
		<?php get_template_part( 'template-parts/home/hero' ); ?>
		<?php get_template_part( 'template-parts/home/features' ); ?>
	</section>

	<?php get_template_part( 'template-parts/home/products' ); ?>
	<?php get_template_part( 'template-parts/sections/support' ); ?>
	<?php
	get_template_part(
		'template-parts/home/split',
		null,
		array(
			'prefix'     => 'hotel',
			'background' => 'bg-white',
		)
	);
	get_template_part(
		'template-parts/home/split',
		null,
		array(
			'prefix'      => 'factory',
			'background'  => 'bg-surface-alt',
			'image_right' => true,
		)
	);
	?>
</main>

<?php
get_footer();
