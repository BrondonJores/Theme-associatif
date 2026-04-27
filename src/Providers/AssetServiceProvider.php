<?php
/**
 * Fournisseur de services pour les assets (scripts et styles)
 *
 * Enregistre et demarre le gestionnaire d'assets dans le conteneur.
 * Configure les hooks WordPress pour l'enqueue des ressources statiques.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce provider gere uniquement les assets du theme.
 * - Dependency Inversion  : Enregistre AssetManagerInterface, pas une implementation concrete.
 *
 * @package ThemeAssociatif\Providers
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Providers;

use ThemeAssociatif\Contracts\AssetManagerInterface;
use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Services\AssetManager;

/**
 * AssetServiceProvider
 *
 * Enregistre le service de gestion des assets dans le conteneur
 * et configure les hooks WordPress pour les enqueues.
 */
final class AssetServiceProvider extends AbstractServiceProvider
{
    /**
     * Enregistrer le binding AssetManagerInterface dans le conteneur.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            AssetManagerInterface::class,
            static function (ContainerInterface $c): AssetManager {
                return new AssetManager($c->get(\ThemeAssociatif\Core\Configuration::class));
            }
        );
    }

    /**
     * Enregistrer les hooks WordPress pour l'enqueue des assets.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        /** @var AssetManagerInterface $assetManager */
        $assetManager = $container->get(AssetManagerInterface::class);

        // Enqueue des assets sur les pages publiques (front-end).
        add_action('wp_enqueue_scripts', [$assetManager, 'enqueueStyles']);
        add_action('wp_enqueue_scripts', [$assetManager, 'enqueueScripts']);

        // Enqueue des assets dans l'administration si necessaire.
        add_action('admin_enqueue_scripts', static function (string $hookSuffix) use ($assetManager): void {
            // Limiter aux pages d'edition pour ne pas charger sur toutes les pages admin.
            if (in_array($hookSuffix, ['post.php', 'post-new.php'], true)) {
                $assetManager->enqueueStyles();
            }
        });
    }
}
