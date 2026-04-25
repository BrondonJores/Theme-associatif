<?php
/**
 * Fournisseur de services pour les menus de navigation
 *
 * Enregistre et demarre le gestionnaire de menus dans le conteneur.
 * Configure les emplacements de navigation du theme via WordPress.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce provider gere uniquement les menus de navigation.
 * - Dependency Inversion  : Lie MenuManagerInterface a une implementation concrete.
 *
 * @package ThemeAssociatif\Providers
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Providers;

use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Contracts\MenuManagerInterface;
use ThemeAssociatif\Services\MenuManager;

/**
 * MenuServiceProvider
 *
 * Enregistre le service de gestion des menus et configure les
 * emplacements de navigation WordPress du theme.
 */
final class MenuServiceProvider extends AbstractServiceProvider
{
    /**
     * Enregistrer le binding MenuManagerInterface dans le conteneur.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            MenuManagerInterface::class,
            static function (ContainerInterface $c): MenuManager {
                return new MenuManager($c->get(\ThemeAssociatif\Core\Configuration::class));
            }
        );
    }

    /**
     * Enregistrer les emplacements de menus via les hooks WordPress.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        /** @var MenuManagerInterface $menuManager */
        $menuManager = $container->get(MenuManagerInterface::class);

        // Enregistrer les emplacements de menus apres la configuration du theme.
        add_action('after_setup_theme', [$menuManager, 'registerLocations']);
    }
}
