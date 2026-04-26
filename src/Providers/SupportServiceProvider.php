<?php
/**
 * Fournisseur de services de support
 *
 * Enregistre le moteur de templates et les services utilitaires
 * transversaux du theme (TemplateEngine, HeroIcon, etc.).
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion des services de support et de rendu.
 * - Dependency Inversion  : Lie TemplateEngineInterface a son implementation.
 *
 * @package ThemeAssociatif\Providers
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Providers;

use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Contracts\TemplateEngineInterface;
use ThemeAssociatif\Services\TemplateEngine;

/**
 * SupportServiceProvider
 *
 * Enregistre les services de support du theme :
 * - Moteur de templates (TemplateEngine)
 * - Helpers utilitaires
 */
final class SupportServiceProvider extends AbstractServiceProvider
{
    /**
     * Enregistrer les bindings du moteur de templates et des services de support.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        // Enregistrer le moteur de templates comme singleton.
        $container->singleton(
            TemplateEngineInterface::class,
            static function (ContainerInterface $c): TemplateEngine {
                return new TemplateEngine(
                    get_template_directory() . '/resources/views',
                    $c->get(\ThemeAssociatif\Core\Configuration::class)
                );
            }
        );
    }

    /**
     * Demarrer les services de support (aucun hook specifique requis).
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Ajouter des filtres WordPress pour le contenu si necessaire.
        // Par exemple, filtrer the_content pour ajouter des fonctionnalites.
    }
}
