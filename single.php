<?php
/**
 * WordPress Single Post Template
 *
 * Renders a single blog post with header, content, author bio,
 * post navigation, related posts and comments.
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

            <article id="post-<?php the_ID(); ?>" <?php post_class('entry entry--single'); ?>>

                <!-- Post Header -->
                <header class="entry-header entry-header--single">

                    <?php
                    $categories = get_the_category();
                    if (!empty($categories)) :
                        $first_cat = $categories[0];
                    ?>
                        <div class="entry-categories">
                            <a href="<?php echo esc_url(get_category_link($first_cat->term_id)); ?>" class="badge badge--primary">
                                <?php echo esc_html($first_cat->name); ?>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php the_title('<h1 class="entry-title entry-title--single">', '</h1>'); ?>

                    <div class="entry-meta">
                        <?php theme_associatif_posted_on(); ?>
                        <?php theme_associatif_posted_by(); ?>
                        <?php
                        $reading_time = max(1, (int) ceil(str_word_count(strip_tags(get_the_content())) / THEME_ASSOCIATIF_READING_WPM));
                        ?>
                        <span class="entry-meta__reading-time">
                            <?php
                            echo esc_html(
                                sprintf(
                                    /* translators: %d: estimated reading time in minutes. */
                                    _n('%d min read', '%d min read', $reading_time, 'theme-associatif'),
                                    $reading_time
                                )
                            );
                            ?>
                        </span>
                    </div>

                    <?php theme_associatif_thumbnail('large'); ?>

                </header>

                <!-- Post Content -->
                <div class="entry-content">
                    <?php
                    the_content(
                        sprintf(
                            '<span class="btn btn--outline">' . esc_html__('Continue reading', 'theme-associatif') . '</span>'
                        )
                    );

                    wp_link_pages(array(
                        'before' => '<nav class="page-links" aria-label="' . esc_attr__('Post pages', 'theme-associatif') . '"><span class="page-links__label">' . esc_html__('Pages:', 'theme-associatif') . '</span>',
                        'after'  => '</nav>',
                    ));
                    ?>
                </div>

                <!-- Post Footer: Tags -->
                <?php
                $tags = get_the_tags();
                if ($tags) :
                ?>
                    <footer class="entry-footer entry-footer--tags">
                        <div class="entry-tags">
                            <span class="entry-tags__label"><?php esc_html_e('Tagged:', 'theme-associatif'); ?></span>
                            <?php foreach ($tags as $tag) : ?>
                                <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="badge badge--default badge--outline">
                                    <?php echo esc_html($tag->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </footer>
                <?php endif; ?>

            </article>

            <!-- Author Bio -->
            <?php if (get_the_author_meta('description')) : ?>
                <aside class="author-bio" aria-label="<?php esc_attr_e('About the author', 'theme-associatif'); ?>">
                    <div class="author-bio__inner">
                        <div class="author-bio__avatar">
                            <?php echo get_avatar(get_the_author_meta('ID'), 80, '', '', array('class' => 'author-bio__avatar-img')); ?>
                        </div>
                        <div class="author-bio__content">
                            <h2 class="author-bio__name">
                                <?php echo esc_html(get_the_author()); ?>
                            </h2>
                            <p class="author-bio__description">
                                <?php echo wp_kses_post(get_the_author_meta('description')); ?>
                            </p>
                        </div>
                    </div>
                </aside>
            <?php endif; ?>

            <!-- Post Navigation -->
            <?php theme_associatif_the_post_navigation(); ?>

            <!-- Related Posts -->
            <?php
            $related_args = array(
                'posts_per_page'      => 3,
                'post__not_in'        => array(get_the_ID()),
                'category__in'        => wp_get_post_categories(get_the_ID()),
                'ignore_sticky_posts' => 1,
                'orderby'             => 'rand',
            );

            $related_query = new WP_Query($related_args);

            if ($related_query->have_posts()) :
            ?>
                <section class="related-posts" aria-label="<?php esc_attr_e('Related posts', 'theme-associatif'); ?>">
                    <h2 class="related-posts__title"><?php esc_html_e('Related articles', 'theme-associatif'); ?></h2>
                    <div class="grid grid-cols-1 grid-cols-3@md gap-6">
                        <?php
                        while ($related_query->have_posts()) :
                            $related_query->the_post();
                            get_template_part('template-parts/components/card', null, array(
                                'title'   => get_the_title(),
                                'content' => theme_associatif_excerpt(20),
                                'image'   => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                                'url'     => get_the_permalink(),
                                'meta'    => array(
                                    'date' => get_the_date(),
                                ),
                            ));
                        endwhile;

                        wp_reset_postdata();
                        ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Comments -->
            <?php
            if (comments_open() || get_comments_number()) :
                comments_template();
            endif;
            ?>

        <?php endwhile; ?>

    </div>
</main>

<?php get_footer(); ?>
