<?php
/**
 * Template index - Modele de repli WordPress
 *
 * Ce fichier est le modele de dernier recours de WordPress. Il est utilise
 * lorsqu'aucun autre modele de la hierarchie ne correspond a la requete en cours.
 * Dans ce theme, il deleguera le rendu au TemplateEngine via le ThemeManager.
 *
 * Hierarchie des modeles WordPress :
 * https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

// Securite : empecher l'acces direct au fichier.
if (! defined('ABSPATH')) {
    exit;
}

// Recuperer le moteur de template depuis le conteneur de services.
$template_engine = ThemeAssociatif\Core\ThemeManager::getInstance()
    ->getContainer()
    ->get(\ThemeAssociatif\Contracts\TemplateEngineInterface::class);

// Rendre le modele de repli generique.
$template_engine->render('layouts/base', [
    'title'   => get_the_title(),
    'content' => function () {
        if (have_posts()) {
            while (have_posts()) {
                the_post();
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                get_template_part('resources/views/partials/entry');
            }
        } else {
            get_template_part('resources/views/partials/no-results');
        }
    },
]);
