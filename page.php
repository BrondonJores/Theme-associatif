<?php
/**
 * WordPress Page Template
 *
 * Renders a standard page with optional sidebar.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

get_header();
?>

<main id="main-content" class="site-main">
    <div class="container">

        <?php while (have_posts()) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class('entry'); ?>>

                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>

                    <?php if (has_post_thumbnail()) : ?>
                        <div class="entry-thumbnail">
                            <?php the_post_thumbnail('large', array('class' => 'entry-thumbnail__img')); ?>
                        </div>
                    <?php endif; ?>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>

                    <?php
                    wp_link_pages(array(
                        'before' => '<nav class="page-links" aria-label="' . esc_attr__('Page navigation', 'theme-associatif') . '"><span class="page-links__label">' . esc_html__('Pages:', 'theme-associatif') . '</span>',
                        'after'  => '</nav>',
                    ));
                    ?>
                </div>

                <?php if (get_edit_post_link()) : ?>
                    <footer class="entry-footer">
                        <?php
                        edit_post_link(
                            esc_html__('Edit this page', 'theme-associatif'),
                            '<span class="edit-link">',
                            '</span>'
                        );
                        ?>
                    </footer>
                <?php endif; ?>

            </article>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
