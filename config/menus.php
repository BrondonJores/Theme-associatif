<?php
/**
 * Configuration des emplacements de menus de navigation
 *
 * Definit les emplacements de navigation disponibles dans le theme.
 * Chaque emplacement peut recevoir un menu via l'interface WordPress
 * Apparence > Menus.
 *
 * Format :
 *   'slug-emplacement' => 'Libelle lisible pour l'administrateur'
 *
 * Acces : theme_config('menus.locations')
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Emplacements de navigation
    |--------------------------------------------------------------------------
    | Definis ici, enregistres dans WordPress via MenuManager::registerLocations()
    */
    'locations' => [
        // Navigation principale dans l'en-tete du site.
        'primary'   => __('Navigation principale', 'theme-associatif'),

        // Navigation secondaire en pied de page (liens utiles).
        'footer'    => __('Navigation pied de page', 'theme-associatif'),

        // Navigation mobile (hamburger menu).
        'mobile'    => __('Navigation mobile', 'theme-associatif'),

        // Menu espace membres (connectes uniquement).
        'members'   => __('Espace membres', 'theme-associatif'),

        // Menu des liens rapides de la sidebar.
        'sidebar'   => __('Menu lateral', 'theme-associatif'),
    ],
];
