<?php
/**
 * Partial : aucun resultat trouve
 *
 * Affiche un message informatif lorsqu'aucun contenu ne correspond
 * a la requete en cours (recherche vide, archive sans articles, etc.).
 * Propose des actions pour aider l'utilisateur a continuer sa navigation.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */
?>

<section class="no-results" aria-labelledby="no-results-title">
    <div class="container">

        <header class="no-results__header">
            <h2 class="no-results__title" id="no-results-title">
                <?php esc_html_e('Aucun contenu trouve.', 'theme-associatif'); ?>
            </h2>
        </header><!-- .no-results__header -->

        <div class="no-results__content">
            <?php if (is_search()) : ?>

                <p>
                    <?php
                    esc_html_e(
                        'Votre recherche n\'a retourne aucun resultat. '
                        . 'Essayez avec d\'autres mots-cles ou verifiez l\'orthographe.',
                        'theme-associatif'
                    );
                    ?>
                </p>

                <?php get_search_form(); ?>

            <?php else : ?>

                <p>
                    <?php
                    esc_html_e(
                        'Il semble qu\'il n\'y ait pas encore de contenu disponible dans cette section.',
                        'theme-associatif'
                    );
                    ?>
                </p>

                <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">
                    <?php esc_html_e('Retour a l\'accueil', 'theme-associatif'); ?>
                </a>

            <?php endif; ?>
        </div><!-- .no-results__content -->

    </div><!-- .container -->
</section><!-- .no-results -->
