<?php
/**
 * Configuration principale de l'application
 *
 * Parametres generaux du theme : version, nom, options globales.
 * Ce fichier retourne un tableau PHP chargé par la classe Configuration.
 *
 * Acces : theme_config('app.version'), theme_config('app.name')
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Identite du theme
    |--------------------------------------------------------------------------
    */

    // Nom court du theme utilise pour les prefixes et les identifiants.
    'name'        => 'theme-associatif',

    // Version actuelle du theme (format semver).
    'version'     => '1.0.0',

    // Nom lisible du theme utilise dans l'interface WordPress.
    'label'       => 'Theme Associatif',

    /*
    |--------------------------------------------------------------------------
    | Parametres de developpement
    |--------------------------------------------------------------------------
    */

    // Mode debug : active les logs et les messages d'erreur detailles.
    // En production, cette valeur doit etre false.
    'debug'       => defined('WP_DEBUG') && WP_DEBUG,

    /*
    |--------------------------------------------------------------------------
    | Parametres de l'association
    |--------------------------------------------------------------------------
    | Ces valeurs peuvent etre surchargees via le Customizer WordPress.
    */

    // Nom de l'association etudiante.
    'association' => [
        'name'        => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'email'       => get_option('admin_email', ''),
        'url'         => home_url('/'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    // Nombre de posts par page pour les archives et les listings.
    'per_page'    => (int) get_option('posts_per_page', 10),

    /*
    |--------------------------------------------------------------------------
    | Fonctionnalites du theme
    |--------------------------------------------------------------------------
    */

    // Activer/desactiver les fonctionnalites optionnelles du theme.
    'features'    => [
        'breadcrumbs'    => true,
        'back-to-top'    => true,
        'search-overlay' => true,
        'dark-mode'      => false,
    ],
];
