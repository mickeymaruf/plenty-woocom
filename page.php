<?php
/**
 * Default page template. Also renders WooCommerce Cart, Checkout and My Account pages.
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="mx-auto w-full max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<h1 class="mb-8 text-4xl font-bold tracking-tight"><?php the_title(); ?></h1>
			<div class="prose-content">
				<?php the_content(); ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
