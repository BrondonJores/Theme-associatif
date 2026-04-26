<?php
/**
 * Partial : article complet (vue individuelle)
 *
 * Affiche un article ou une page WordPress dans son integralite.
 * Utilise dans les templates single.php et page.php.
 *
 * Ce template suppose que la boucle WordPress est active et que
 * the_post() a deja ete appele par le template parent.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Support\HeroIcon;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post post--single'); ?>>

    <header class="post__header">

        <!-- Categories de l'article -->
        <?php if (is_single() && has_category()) : ?>
            <div class="post__categories">
                <?php the_category(' '); ?>
            </div><!-- .post__categories -->
        <?php endif; ?>

        <!-- Titre principal de l'article -->
        <h1 class="post__title">
            <?php the_title(); ?>
        </h1>

        <!-- Metadonnees : auteur, date de publication -->
        <?php if (is_single()) : ?>
            <div class="post__meta" aria-label="<?php esc_attr_e('Informations sur l\'article', 'theme-associatif'); ?>">
                <span class="post__meta-author">
                    <?php HeroIcon::display('user', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--xs']); ?>
                    <span><?php the_author(); ?></span>
                </span>

                <time
                    class="post__meta-date"
                    datetime="<?php echo esc_attr(get_the_date('c')); ?>"
                >
                    <?php HeroIcon::display('calendar', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--xs']); ?>
                    <span><?php echo esc_html(get_the_date()); ?></span>
                </time>
            </div><!-- .post__meta -->
        <?php endif; ?>

        <!-- Image a la une -->
        <?php if (has_post_thumbnail()) : ?>
            <div class="post__thumbnail">
                <?php the_post_thumbnail('theme-featured', ['class' => 'post__thumbnail-img']); ?>
            </div><!-- .post__thumbnail -->
        <?php endif; ?>

    </header><!-- .post__header -->

    <!-- Contenu principal de l'article -->
    <div class="post__content entry-content">
        <?php
        the_content();

        wp_link_pages([
            'before' => '<nav class="post__pages" aria-label="'
                . esc_attr__('Pages de l\'article', 'theme-associatif') . '">'
                . '<span class="post__pages-label">' . esc_html__('Pages :', 'theme-associatif') . '</span>',
            'after'  => '</nav>',
        ]);
        ?>
    </div><!-- .post__content -->

    <!-- Pied de l'article : etiquettes et navigation -->
    <?php if (is_single()) : ?>
        <footer class="post__footer">

            <?php if (has_tag()) : ?>
                <div class="post__tags">
                    <?php HeroIcon::display('tag', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--xs']); ?>
                    <?php the_tags('', ', ', ''); ?>
                </div><!-- .post__tags -->
            <?php endif; ?>

        </footer><!-- .post__footer -->

        <!-- Navigation vers l'article precedent / suivant -->
        <nav
            class="post__navigation"
            aria-label="<?php esc_attr_e('Navigation entre articles', 'theme-associatif'); ?>"
        >
            <?php
            the_post_navigation([
                'prev_text' => heroicon_render('arrow-left', 'outline', ['class' => 'icon'])
                    . '<span class="post__nav-label">' . esc_html__('Article precedent', 'theme-associatif') . '</span>'
                    . '<span class="post__nav-title">%title</span>',
                'next_text' => '<span class="post__nav-label">' . esc_html__('Article suivant', 'theme-associatif') . '</span>'
                    . '<span class="post__nav-title">%title</span>'
                    . heroicon_render('arrow-right', 'outline', ['class' => 'icon']),
            ]);
            ?>
        </nav><!-- .post__navigation -->

        <!-- Section commentaires -->
        <?php
        if (comments_open() || get_comments_number()) {
            comments_template();
        }
        ?>

    <?php endif; ?>

</article><!-- #post-<?php the_ID(); ?> -->
