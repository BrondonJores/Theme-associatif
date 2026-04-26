<?php
/**
 * Interface des fournisseurs de services
 *
 * Definit le contrat que tout service provider doit respecter.
 * Les service providers sont responsables de l'enregistrement et du demarrage
 * des services dans le conteneur d'injection de dependances.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Chaque provider gere un seul domaine fonctionnel.
 * - Interface Segregation : Interface minimale avec deux methodes distinctes.
 * - Dependency Inversion  : Les providers dependent du ContainerInterface, pas d'une implementation.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * ServiceProviderInterface
 *
 * Contrat pour les fournisseurs de services du theme.
 * Chaque fonctionnalite majeure (assets, menus, securite, etc.) est
 * encapsulee dans un provider dedie qui suit ce contrat.
 */
interface ServiceProviderInterface
{
    /**
     * Enregistrer les bindings dans le conteneur de services.
     *
     * Cette methode est appelee lors de la phase d'enregistrement,
     * avant que WordPress n'ait charge tous ses hooks. Elle ne doit
     * effectuer que des operations d'enregistrement de bindings.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function register(ContainerInterface $container): void;

    /**
     * Demarrer le service et enregistrer les hooks WordPress.
     *
     * Cette methode est appelee apres que tous les providers ont ete
     * enregistres. C'est ici que les hooks add_action() et add_filter()
     * doivent etre definis.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void;
}
