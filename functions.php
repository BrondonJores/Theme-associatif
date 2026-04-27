<?php

/**
 * Point d'entree principal du theme WordPress
 *
 * Ce fichier est le point d'entree unique du theme. Il est responsable de :
 * - Verifier les prerequis (version PHP, WordPress)
 * - Charger l'autoloader Composer (PSR-4)
 * - Instancier et demarrer le ThemeManager
 * - Exposer les helpers globaux pour les templates
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce fichier ne fait qu'initialiser le theme.
 * - Dependency Inversion  : On depend d'abstractions (interfaces), pas de concretions.
 *
 * @package ThemeAssociatif
 * @since   1.0.0
 */

declare(strict_types=1);

// Securite : empecher l'acces direct au fichier.
if (! defined('ABSPATH')) {
    exit;
}

// Verifier la version minimale de PHP requise par ce theme.
if (version_compare(PHP_VERSION, '8.1', '<')) {
    add_action('admin_notices', static function (): void {
        $message = sprintf(
            /* translators: 1: Version PHP requise, 2: Version PHP actuelle */
            esc_html__(
                'Theme Associatif requiert PHP %1$s ou superieur. Version detectee : %2$s.',
                'theme-associatif'
            ),
            '8.1',
            PHP_VERSION
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    });
    return;
}

// Verifier la version minimale de WordPress.
global $wp_version;
if (version_compare($wp_version, '6.0', '<')) {
    add_action('admin_notices', static function (): void {
        $message = sprintf(
            /* translators: 1: Version WordPress requise, 2: Version WordPress actuelle */
            esc_html__(
                'Theme Associatif requiert WordPress %1$s ou superieur. Version detectee : %2$s.',
                'theme-associatif'
            ),
            '6.0',
            $GLOBALS['wp_version']
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    });
    return;
}

// Charger l'autoloader PSR-4 de Composer.
// Si le dossier vendor n'existe pas, afficher un avertissement dans l'admin.
$autoloader = get_template_directory() . '/vendor/autoload.php';
if (! file_exists($autoloader)) {
    add_action('admin_notices', static function (): void {
        $message = esc_html__(
            'Theme Associatif : les dependances Composer sont manquantes. '
            . 'Executez "composer install" dans le dossier du theme.',
            'theme-associatif'
        );
        echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
    });
    return;
}
require_once $autoloader;

// Demarrer le gestionnaire principal du theme.
// ThemeManager suit le pattern Singleton pour garantir une initialisation unique.
ThemeAssociatif\Core\ThemeManager::getInstance()->boot();

/**
 * Retourne le SecurityServiceProvider initialise.
 *
 * Cette fonction utilitaire permet aux templates d'acceder aux services
 * de securite sans connaitre les details d'implementation
 * (Dependency Inversion Principle).
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
