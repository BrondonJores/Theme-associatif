<?php
/**
 * Fournisseur de services pour la securite
 *
 * Enregistre et demarre le service de securite dans le conteneur.
 * Configure les en-tetes de securite HTTP et les protections WordPress.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce provider gere uniquement la securite.
 * - Dependency Inversion  : Lie SecurityServiceInterface a une implementation concrete.
 *
 * @package ThemeAssociatif\Providers
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Providers;

use ThemeAssociatif\Contracts\ContainerInterface;
use ThemeAssociatif\Contracts\SecurityServiceInterface;
use ThemeAssociatif\Services\SecurityService;

/**
 * SecurityServiceProvider
 *
 * Enregistre le service de securite et configure les protections
 * WordPress (suppression de metadonnees sensibles, en-tetes securises, etc.).
 */
final class SecurityServiceProvider extends AbstractServiceProvider
{
    /**
     * Enregistrer le binding SecurityServiceInterface dans le conteneur.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function register(ContainerInterface $container): void
    {
        $container->singleton(
            SecurityServiceInterface::class,
            static fn (): SecurityService => new SecurityService()
        );
    }

    /**
     * Configurer les protections de securite WordPress via les hooks.
     *
     * @param ContainerInterface $container  Le conteneur de services.
     *
     * @return void
     */
    public function boot(ContainerInterface $container): void
    {
        // Supprimer la version WordPress des assets publics pour eviter la divulgation de version.
        add_filter('style_loader_src', [$this, 'removeVersionQuery'], 9999);
        add_filter('script_loader_src', [$this, 'removeVersionQuery'], 9999);

        // Supprimer le generateur de version WordPress de la balise <head>.
        remove_action('wp_head', 'wp_generator');

        // Supprimer les liens d'API REST inutiles de l'entete.
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');

        // Supprimer les liens RSD et Windows Live Writer.
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');

        // Supprimer les liens de posts adjacents de l'entete.
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

        // Ajouter des en-tetes de securite HTTP modernes.
        add_action('send_headers', [$this, 'addSecurityHeaders']);

        // Desactiver l'enumeration des utilisateurs via les parametres d'URL.
        add_action('template_redirect', [$this, 'preventAuthorEnumeration']);
    }

    /**
     * Supprimer le parametre de version des URLs d'assets.
     *
     * Evite d'exposer la version de WordPress dans les URLs publiques.
     *
     * @param string $src  URL de la ressource.
     *
     * @return string  URL sans parametre de version WordPress.
     */
    public function removeVersionQuery(string $src): string
    {
        global $wp_version;

        if (strpos($src, 'ver=' . $wp_version) !== false) {
            $src = remove_query_arg('ver', $src);
        }

        return $src;
    }

    /**
     * Ajouter des en-tetes HTTP de securite modernes.
     *
     * Configure les en-tetes de securite recommandes par OWASP.
     *
     * @return void
     */
    public function addSecurityHeaders(): void
    {
        // Empecher l'interpretation du type MIME par le navigateur.
        header('X-Content-Type-Options: nosniff');

        // Activer la protection XSS du navigateur.
        header('X-XSS-Protection: 1; mode=block');

        // Empecher le chargement dans une iframe (protection clickjacking).
        header('X-Frame-Options: SAMEORIGIN');

        // Controler les informations du refereur envoyes aux autres sites.
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Politique de permissions : desactiver les fonctionnalites non utilisees.
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    }

    /**
     * Empecher l'enumeration des auteurs via ?author=N.
     *
     * Redirige les requetes d'enumeration vers la page d'accueil.
     *
     * @return void
     */
    public function preventAuthorEnumeration(): void
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['author']) && ! is_admin()) {
            wp_redirect(home_url('/'), 301);
            exit;
        }
    }
}
