<?php
/**
 * Configuration des assets (styles CSS et scripts JavaScript)
 *
 * Definit tous les styles et scripts enqueues par le theme.
 * Chaque entree est chargee automatiquement par AssetManager.
 *
 * Format styles :
 *   'handle' => [
 *     'src'   => chemin relatif depuis la racine du theme,
 *     'deps'  => tableau des handles dependants,
 *     'media' => media query (optionnel, defaut 'all'),
 *   ]
 *
 * Format scripts :
 *   'handle' => [
 *     'src'    => chemin relatif depuis la racine du theme,
 *     'deps'   => tableau des handles dependants,
 *     'footer' => true pour charger en pied de page,
 *     'data'   => [ 'object_name' => 'nom', 'values' => [...] ] (optionnel),
 *   ]
 *
 * Acces : theme_config('assets.styles'), theme_config('assets.scripts')
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Styles CSS
    |--------------------------------------------------------------------------
    */
    'styles' => [
        // Styles principaux du theme.
        'theme-main' => [
            'src'   => 'resources/css/main.css',
            'deps'  => [],
            'media' => 'all',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scripts JavaScript
    |--------------------------------------------------------------------------
    */
    'scripts' => [
        // Script principal du theme.
        'theme-main' => [
            'src'    => 'resources/js/main.js',
            'deps'   => [],
            'footer' => true,
            'data'   => [
                'object_name' => 'ThemeAssociatif',
                'values'      => [
                    'ajaxUrl'   => admin_url('admin-ajax.php'),
                    'nonce'     => wp_create_nonce('theme_associatif_nonce'),
                    'homeUrl'   => home_url('/'),
                    'themeUrl'  => get_template_directory_uri(),
                ],
            ],
        ],
    ],
];
