<?php
/**
 * WordPress Archive Template
 *
 * Displays category, tag, date, author and custom taxonomy archives.
 * Posts are rendered using the card component in a responsive grid.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main">
    <div class="container">

        <!-- Archive Header -->
        <header class="archive-header" aria-label="<?php esc_attr_e('Archive header', 'theme-associatif'); ?>">
            <?php the_archive_title('<h1 class="archive-title">', '</h1>'); ?>
            <?php the_archive_description('<div class="archive-description">', '</div>'); ?>
        </header>

        <?php if (have_posts()) : ?>

            <div class="grid grid-cols-1 grid-cols-2@sm grid-cols-3@lg gap-8" role="list">
                <?php
                while (have_posts()) :
                    the_post();
                ?>
                    <div role="listitem">
                        <?php
                        get_template_part('template-parts/components/card', null, array(
                            'title'   => get_the_title(),
                            'content' => theme_associatif_excerpt(25),
                            'image'   => get_the_post_thumbnail_url(get_the_ID(), 'medium_large'),
                            'url'     => get_the_permalink(),
                            'meta'    => array(
                                'date'   => get_the_date(),
                                'author' => get_the_author(),
                            ),
                        ));
                        ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination(array(
                'prev_text'          => '&larr; ' . esc_html__('Previous', 'theme-associatif'),
                'next_text'          => esc_html__('Next', 'theme-associatif') . ' &rarr;',
                'before_page_number' => '<span class="sr-only">' . esc_html__('Page', 'theme-associatif') . ' </span>',
            ));
            ?>

        <?php else : ?>

            <p class="archive-no-results">
                <?php esc_html_e('No posts found.', 'theme-associatif'); ?>
            </p>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
