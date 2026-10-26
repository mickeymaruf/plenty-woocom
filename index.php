<?php
/**
 * Fallback template (blog index, archives, search).
 *
 * @package Plenty
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<main id="main" class="container-site py-12">
	<?php if ( is_home() && ! is_front_page() ) : ?>
		<h1 class="mb-10 text-3xl font-bold tracking-tight"><?php single_post_title(); ?></h1>
	<?php elseif ( is_archive() ) : ?>
		<?php the_archive_title( '<h1 class="mb-10 text-3xl font-bold tracking-tight">', '</h1>' ); ?>
	<?php elseif ( is_search() ) : ?>
		<h1 class="mb-10 text-3xl font-bold tracking-tight">
			<?php
			/* translators: %s: search query. */
			printf( esc_html__( 'Search results for "%s"', 'plenty' ), esc_html( get_search_query() ) );
			?>
		</h1>
	<?php endif; ?>

	<?php if ( have_posts() ) : ?>
		<div class="grid gap-10 md:grid-cols-2 lg:grid-cols-3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'flex flex-col gap-3' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a href="<?php the_permalink(); ?>" class="block overflow-hidden rounded-lg">
							<?php the_post_thumbnail( 'medium_large', array( 'class' => 'aspect-[4/3] w-full object-cover' ) ); ?>
						</a>
					<?php endif; ?>
					<h2 class="text-xl font-semibold"><a class="hover:underline" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="text-neutral-600"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>

		<div class="mt-12">
			<?php the_posts_pagination(); ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'plenty' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
