<?php
/**
 * Template WordPress : resultats de recherche
 *
 * Affiche les resultats d'une recherche WordPress.
 * Presente le terme recherche, le nombre de resultats,
 * et la liste des articles correspondants.
 *
 * Position dans la hierarchie des templates WordPress :
 * search.php > index.php
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

$template_engine = ThemeAssociatif\Core\ThemeManager::getInstance()
    ->getContainer()
    ->get(\ThemeAssociatif\Contracts\TemplateEngineInterface::class);

$template_engine->display('layouts/base', [
    'title'   => sprintf(
        /* translators: %s: Terme recherche */
        esc_html__('Resultats pour : %s', 'theme-associatif'),
        get_search_query()
    ),
    'content' => function (): void {
        ?>
        <section class="search-results" aria-labelledby="search-results-title">
            <div class="container">

                <header class="search-results__header">
                    <h1 class="search-results__title" id="search-results-title">
                        <?php
                        if (have_posts()) {
                            printf(
                                /* translators: 1: Nombre de resultats, 2: Terme recherche */
                                esc_html__('%1$s resultat(s) pour "%2$s"', 'theme-associatif'),
                                (int) $GLOBALS['wp_query']->found_posts,
                                '<span class="search-results__query">'
                                    . esc_html(get_search_query())
                                . '</span>'
                            );
                        } else {
                            printf(
                                /* translators: %s: Terme recherche */
                                esc_html__('Aucun resultat pour "%s"', 'theme-associatif'),
                                '<span class="search-results__query">'
                                    . esc_html(get_search_query())
                                . '</span>'
                            );
                        }
                        ?>
                    </h1>

                    <?php get_search_form(); ?>
                </header><!-- .search-results__header -->

                <div class="search-results__posts">
                    <?php
                    if (have_posts()) {
                        while (have_posts()) {
                            the_post();
                            get_template_part('resources/views/partials/entry-summary');
                        }

                        the_posts_pagination([
                            'mid_size'  => 2,
                            'prev_text' => heroicon_render('arrow-left', 'outline', ['class' => 'icon'])
                                . '<span>' . esc_html__('Precedent', 'theme-associatif') . '</span>',
                            'next_text' => '<span>' . esc_html__('Suivant', 'theme-associatif') . '</span>'
                                . heroicon_render('arrow-right', 'outline', ['class' => 'icon']),
                        ]);
                    } else {
                        get_template_part('resources/views/partials/no-results');
                    }
                    ?>
                </div><!-- .search-results__posts -->

            </div><!-- .container -->
        </section><!-- .search-results -->
        <?php
    },
]);
