<?php

declare(strict_types=1);

/**
 * Theme Associatif - Fichier principal du theme
 *
 * Ce fichier est le point d'entree du theme WordPress.
 * Il configure l'autoloader PSR-4, initialise le systeme de securite
 * et enregistre les hooks WordPress fondamentaux du theme.
 *
 * Architecture :
 * - PSR-4 autoloading pour tous les composants sous inc/
 * - Systeme de securite complet via SecurityServiceProvider
 * - Hooks enregistres selon le principe de responsabilite unique
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Constantes du theme.
 * Centralise les chemins et URLs pour eviter les chemins en dur.
 */
define('TA_VERSION', '1.0.0');
define('TA_THEME_DIR', get_template_directory());
define('TA_THEME_URI', get_template_directory_uri());
define('TA_INC_DIR', TA_THEME_DIR . '/inc');

/**
 * Autoloader PSR-4 pour l'espace de noms ThemeAssociatif.
 * Convertit le namespace en chemin de fichier :
 * ThemeAssociatif\Security\Services\SanitizerService
 * => inc/Security/Services/SanitizerService.php
 *
 * @param string $className Le nom complet de la classe a charger.
 * @return void
 */
spl_autoload_register(function (string $className): void {
    $prefix    = 'ThemeAssociatif\\';
    $baseDir   = TA_INC_DIR . '/';

    $len = strlen($prefix);

    if (strncmp($prefix, $className, $len) !== 0) {
        return;
    }

    $relativeClass = substr($className, $len);
    $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Initialisation du systeme de securite.
 * Le SecurityServiceProvider est instancie une seule fois et
 * mis a disposition via une fonction globale pour les templates.
 *
 * Priorite 1 : doit etre charge avant tout autre composant du theme.
 */
add_action('after_setup_theme', function (): void {
    ThemeAssociatif\Security\SecurityServiceProvider::getInstance();
}, 1);

/**
 * Configuration de base du theme WordPress.
 * Enregistre les fonctionnalites supportees par le theme.
 */
add_action('after_setup_theme', function (): void {
    load_theme_textdomain('theme-associatif', TA_THEME_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('custom-logo');
    add_theme_support('automatic-feed-links');
    add_theme_support('responsive-embeds');

    register_nav_menus([
        'primary'   => __('Menu principal', 'theme-associatif'),
        'footer'    => __('Menu pied de page', 'theme-associatif'),
        'member_dashboard' => __('Menu tableau de bord membres', 'theme-associatif'),
    ]);
}, 10);

/**
 * Fonction utilitaire : retourne le SecurityServiceProvider initialise.
 * Permet aux templates d'acceder aux services de securite sans
 * connaitre les details d'implementation (Dependency Inversion).
 *
 * Exemple d'utilisation dans un template :
 *
 *   $sanitizer = ta_security()->getSanitizer();
 *   $name = $sanitizer->sanitizeTextField($_POST['name'] ?? '');
 *
 * @return ThemeAssociatif\Security\SecurityServiceProvider
 */
function ta_security(): ThemeAssociatif\Security\SecurityServiceProvider
{
    return ThemeAssociatif\Security\SecurityServiceProvider::getInstance();
}
