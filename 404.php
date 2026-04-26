<?php
/**
 * Template WordPress : page d'erreur 404
 *
 * Affiche un message d'erreur lorsqu'aucune page ne correspond a la requete.
 * Propose un formulaire de recherche pour aider l'utilisateur a trouver
 * le contenu souhaite.
 *
 * Position dans la hierarchie des templates WordPress :
 * 404.php (terminal - aucun fallback)
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
    'title'   => esc_html__('Page introuvable', 'theme-associatif'),
    'content' => function (): void {
        ?>
        <section class="error-404" aria-labelledby="error-404-title">
            <div class="container">

                <header class="error-404__header">
                    <h1 class="error-404__title" id="error-404-title">
                        <?php esc_html_e('Cette page est introuvable.', 'theme-associatif'); ?>
                    </h1>
                </header><!-- .error-404__header -->

                <div class="error-404__content">
                    <p>
                        <?php
                        esc_html_e(
                            'Il semblerait que la page que vous recherchez n\'existe plus ou a ete deplacee. '
                            . 'Utilisez la recherche ci-dessous pour trouver ce que vous cherchez.',
                            'theme-associatif'
                        );
                        ?>
                    </p>

                    <?php get_search_form(); ?>

                    <p>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">
                            <?php esc_html_e('Retour a l\'accueil', 'theme-associatif'); ?>
                        </a>
                    </p>
                </div><!-- .error-404__content -->

            </div><!-- .container -->
        </section><!-- .error-404 -->
        <?php
    },
]);
