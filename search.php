<?php
/**
 * WordPress Search Results Template
 *
 * Displays search results with the queried string, result count,
 * and posts rendered via the card component.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main">
    <div class="container">

        <header class="search-header">
            <h1 class="search-title">
                <?php
                /* translators: %s: search query string. */
                printf(
                    esc_html__('Search results for: %s', 'theme-associatif'),
                    '<span class="search-title__query">' . esc_html(get_search_query()) . '</span>'
                );
                ?>
            </h1>

            <?php if (have_posts()) : ?>
                <p class="search-count">
                    <?php
                    global $wp_query;
                    printf(
                        /* translators: %d: number of results found. */
                        esc_html(_n('%d result found', '%d results found', $wp_query->found_posts, 'theme-associatif')),
                        esc_html($wp_query->found_posts)
                    );
                    ?>
                </p>
            <?php endif; ?>
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
                'prev_text' => '&larr; ' . esc_html__('Previous', 'theme-associatif'),
                'next_text' => esc_html__('Next', 'theme-associatif') . ' &rarr;',
            ));
            ?>

        <?php else : ?>

            <div class="search-no-results">
                <p><?php esc_html_e('No results found for your search. Please try different keywords.', 'theme-associatif'); ?></p>
                <?php get_search_form(); ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php get_footer(); ?>
