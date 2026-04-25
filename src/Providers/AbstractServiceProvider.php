<?php
/**
 * Fournisseur de services abstrait (classe de base)
 *
 * Classe abstraite fournissant un comportement commun a tous les service providers.
 * Simplifie l'acces a la configuration et au conteneur pour les providers concrets.
 *
 * Principe SOLID applique :
 * - Liskov Substitution   : Les providers concrets peuvent remplacer cette classe sans probleme.
 * - Open/Closed           : Les sous-classes etendent sans modifier ce comportement de base.
 * - DRY                   : Evite la duplication de code entre providers.
 *
 * @package ThemeAssociatif\Providers
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Providers;

use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Contracts\ServiceProviderInterface;
use ThemeAssociatif\Core\Configuration;

/**
 * AbstractServiceProvider
 *
 * Classe de base pour tous les service providers du theme.
 * Fournit un acces commode a la configuration et au conteneur.
 */
abstract class AbstractServiceProvider implements ServiceProviderInterface
{
    /**
     * Raccourci pour recuperer la configuration depuis le conteneur.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return Configuration  Le gestionnaire de configuration.
     */
    protected function getConfig(ContainerInterface $container): Configuration
    {
        return $container->get(Configuration::class);
    }
}
