<?php
/**
 * Partial : resume d'article (vue listing / apercu)
 *
 * Affiche un article sous forme de resume visuel dans les pages de listing :
 * archive, recherche, page d'accueil de blog.
 *
 * Ce template suppose que la boucle WordPress est active et que
 * the_post() a deja ete appele par le template parent.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

use ThemeAssociatif\Support\HeroIcon;

$post_title   = esc_html(get_the_title());
$post_link    = esc_url(get_permalink());
$post_date    = esc_html(get_the_date());
$post_excerpt = wp_kses_post(get_the_excerpt());

// Recuperer la premiere categorie pour l'afficher comme badge.
$post_category = '';
$categories    = get_the_category();
if (! empty($categories)) {
    $post_category = esc_html($categories[0]->name);
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('post-summary'); ?>>

    <!-- Image a la une -->
    <?php if (has_post_thumbnail()) : ?>
        <a
            href="<?php echo $post_link; ?>"
            class="post-summary__thumbnail-link"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="post-summary__thumbnail">
                <?php
                the_post_thumbnail('theme-card', [
                    'class'   => 'post-summary__thumbnail-img',
                    'loading' => 'lazy',
                    'alt'     => $post_title,
                ]);
                ?>
            </div><!-- .post-summary__thumbnail -->
        </a>
    <?php endif; ?>

    <div class="post-summary__body">

        <!-- Badge de categorie -->
        <?php if ($post_category) : ?>
            <span class="post-summary__category"><?php echo $post_category; ?></span>
        <?php endif; ?>

        <!-- Date de publication -->
        <time
            class="post-summary__date"
            datetime="<?php echo esc_attr(get_the_date('c')); ?>"
        >
            <?php HeroIcon::display('calendar', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--xs']); ?>
            <span><?php echo $post_date; ?></span>
        </time>

        <!-- Titre de l'article avec lien -->
        <h2 class="post-summary__title">
            <a href="<?php echo $post_link; ?>" class="post-summary__title-link">
                <?php echo $post_title; ?>
            </a>
        </h2>

        <!-- Extrait du contenu -->
        <?php if ($post_excerpt) : ?>
            <div class="post-summary__excerpt">
                <?php echo $post_excerpt; ?>
            </div><!-- .post-summary__excerpt -->
        <?php endif; ?>

        <!-- Lien "Lire la suite" -->
        <a href="<?php echo $post_link; ?>" class="post-summary__read-more" aria-label="<?php echo esc_attr(sprintf(
            /* translators: %s: Titre de l'article */
            __('Lire la suite de : %s', 'theme-associatif'),
            get_the_title()
        )); ?>">
            <span><?php esc_html_e('Lire la suite', 'theme-associatif'); ?></span>
            <?php HeroIcon::display('arrow-right', HeroIcon::STYLE_OUTLINE, ['class' => 'icon icon--sm']); ?>
        </a>

    </div><!-- .post-summary__body -->

</article><!-- #post-<?php the_ID(); ?> -->
