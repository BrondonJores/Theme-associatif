<?php
/**
 * WordPress fallback template.
 *
 * This file is used when no other template matches the current request.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid grid-cols-1 gap-8">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/components/card', null, array(
						'title'   => get_the_title(),
						'content' => get_the_excerpt(),
						'image'   => get_the_post_thumbnail_url( get_the_ID(), 'medium_large' ),
						'url'     => get_the_permalink(),
						'meta'    => array(
							'date'   => get_the_date(),
							'author' => get_the_author(),
						),
					) );
				endwhile;

				the_posts_pagination( array(
					'prev_text' => __( 'Previous', 'theme-associatif' ),
					'next_text' => __( 'Next', 'theme-associatif' ),
				) );
				?>
			</div>
		<?php else : ?>
			<p class="text-secondary"><?php esc_html_e( 'No content found.', 'theme-associatif' ); ?></p>
		<?php endif; ?>
	</div>
</main>

<?php get_footer(); ?>
