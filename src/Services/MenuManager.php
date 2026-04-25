<?php
/**
 * Gestionnaire des menus de navigation WordPress
 *
 * Implementation concrète de MenuManagerInterface.
 * Gère l'enregistrement des emplacements de navigation et leur rendu HTML,
 * en chargeant les emplacements depuis la configuration centralisee.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion exclusive des menus de navigation.
 * - Liskov Substitution   : Implemente pleinement MenuManagerInterface.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Contracts\MenuManagerInterface;
use ThemeAssociatif\Core\Configuration;

/**
 * MenuManager
 *
 * Gestionnaire des emplacements de navigation du theme.
 * Charge les emplacements depuis config/menus.php.
 */
final class MenuManager implements MenuManagerInterface
{
    /**
     * Constructeur - Injecter la configuration.
     *
     * @param Configuration $config  Le gestionnaire de configuration.
     */
    public function __construct(private readonly Configuration $config)
    {
    }

    /**
     * Enregistrer tous les emplacements de menus dans WordPress.
     *
     * Charge les emplacements depuis config/menus.php et les enregistre
     * via register_nav_menus().
     *
     * @return void
     */
    public function registerLocations(): void
    {
        $locations = $this->getLocations();

        if (! empty($locations)) {
            register_nav_menus($locations);
        }
    }

    /**
     * Recuperer les emplacements de menus depuis la configuration.
     *
     * @return array<string, string>  Tableau [slug => label].
     */
    public function getLocations(): array
    {
        /** @var array<string, string> $locations */
        $locations = $this->config->get('menus.locations', []);
        return $locations;
    }

    /**
     * Verifier si un menu est assigne a un emplacement donne.
     *
     * @param string $location  Slug de l'emplacement de menu.
     *
     * @return bool  True si un menu est assigne a cet emplacement.
     */
    public function hasMenu(string $location): bool
    {
        $locations = get_nav_menu_locations();
        return ! empty($locations[$location]);
    }

    /**
     * Rendre un menu de navigation en HTML.
     *
     * Genere le HTML du menu avec les arguments par defaut du theme,
     * fusionnes avec les arguments personnalises passes en parametre.
     *
     * @param string               $location  Slug de l'emplacement de menu.
     * @param array<string, mixed> $args      Arguments additionnels pour wp_nav_menu().
     *
     * @return string  HTML du menu rendu, chaine vide si absent.
     */
    public function renderMenu(string $location, array $args = []): string
    {
        if (! $this->hasMenu($location)) {
            return '';
        }

        // Arguments par defaut avec echo desactive pour capturer la sortie.
        $defaultArgs = [
            'theme_location'  => $location,
            'menu_class'       => 'nav__list',
            'container'        => 'nav',
            'container_class'  => 'nav nav--' . esc_attr($location),
            'container_id'     => 'menu-' . esc_attr($location),
            'echo'             => false,
            'fallback_cb'      => false,
            'depth'            => 2,
            'walker'           => null,
        ];

        $mergedArgs = array_merge($defaultArgs, $args, ['echo' => false]);
        $html       = wp_nav_menu($mergedArgs);

        return is_string($html) ? $html : '';
    }
}
