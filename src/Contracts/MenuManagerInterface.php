<?php
/**
 * Interface du gestionnaire de menus de navigation
 *
 * Definit le contrat pour l'enregistrement et la gestion des emplacements
 * de navigation du theme WordPress.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion exclusive des menus de navigation.
 * - Interface Segregation : Interface focalisee, sans methodes superflues.
 * - Liskov Substitution   : Toute implementation peut remplacer une autre.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * MenuManagerInterface
 *
 * Contrat pour le gestionnaire des emplacements de navigation.
 * Gere l'enregistrement des nav_menus et leur affichage.
 */
interface MenuManagerInterface
{
    /**
     * Enregistrer tous les emplacements de menus du theme.
     *
     * Appelle register_nav_menus() avec les emplacements definis
     * dans la configuration du theme.
     *
     * @return void
     */
    public function registerLocations(): void;

    /**
     * Recuperer la liste des emplacements de menus enregistres.
     *
     * @return array<string, string>  Tableau associatif [slug => label].
     */
    public function getLocations(): array;

    /**
     * Verifier si un menu est assigne a un emplacement donne.
     *
     * @param string $location  Slug de l'emplacement de menu.
     *
     * @return bool  True si un menu est assigne, false sinon.
     */
    public function hasMenu(string $location): bool;

    /**
     * Rendre un menu de navigation HTML.
     *
     * @param string $location  Slug de l'emplacement de menu.
     * @param array  $args      Arguments additionnels pour wp_nav_menu().
     *
     * @return string  HTML du menu rendu.
     */
    public function renderMenu(string $location, array $args = []): string;
}
