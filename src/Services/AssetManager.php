<?php
/**
 * Gestionnaire d'assets (scripts et styles CSS)
 *
 * Implementation concrète de AssetManagerInterface.
 * Gère l'enregistrement et l'enqueue des ressources statiques du theme
 * avec versionnage automatique par hash de fichier.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion exclusive des assets (CSS et JS).
 * - Open/Closed           : Configuration externe ; pas besoin de modifier la classe.
 * - Liskov Substitution   : Implementte pleinement AssetManagerInterface.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Contracts\AssetManagerInterface;
use ThemeAssociatif\Core\Configuration;

/**
 * AssetManager
 *
 * Gestionnaire d'assets du theme avec support du versionnage par hash de fichier.
 * Charge la configuration depuis config/assets.php.
 */
final class AssetManager implements AssetManagerInterface
{
    /**
     * Chemin absolu vers le dossier du theme.
     *
     * @var string
     */
    private string $themeDir;

    /**
     * URL de base du theme.
     *
     * @var string
     */
    private string $themeUrl;

    /**
     * Constructeur - Injecter la configuration.
     *
     * @param Configuration $config  Le gestionnaire de configuration.
     */
    public function __construct(private readonly Configuration $config)
    {
        $this->themeDir = get_template_directory();
        $this->themeUrl = get_template_directory_uri();
    }

    /**
     * Enqueue les styles CSS du theme depuis config/assets.php.
     *
     * @return void
     */
    public function enqueueStyles(): void
    {
        /** @var array<string, array{src: string, deps?: array<string>, media?: string}> $styles */
        $styles = $this->config->get('assets.styles', []);

        foreach ($styles as $handle => $style) {
            $src     = $this->resolveAssetUrl((string) $style['src']);
            $deps    = (array) ($style['deps'] ?? []);
            $media   = (string) ($style['media'] ?? 'all');
            $version = $this->getFileVersion((string) $style['src']);

            wp_enqueue_style($handle, $src, $deps, $version, $media);
        }
    }

    /**
     * Enqueue les scripts JavaScript du theme depuis config/assets.php.
     *
     * @return void
     */
    public function enqueueScripts(): void
    {
        /** @var array<string, array{src: string, deps?: array<string>, footer?: bool, data?: array<string, mixed>}> $scripts */
        $scripts = $this->config->get('assets.scripts', []);

        foreach ($scripts as $handle => $script) {
            $src      = $this->resolveAssetUrl((string) $script['src']);
            $deps     = (array) ($script['deps'] ?? []);
            $inFooter = (bool) ($script['footer'] ?? true);
            $version  = $this->getFileVersion((string) $script['src']);

            wp_enqueue_script($handle, $src, $deps, $version, $inFooter);

            // Localiser des donnees PHP vers JavaScript si specifiees.
            if (! empty($script['data']) && is_array($script['data'])) {
                $objectName = (string) ($script['data']['object_name'] ?? 'themeData');
                $data       = (array) ($script['data']['values'] ?? []);
                wp_localize_script($handle, $objectName, $data);
            }
        }
    }

    /**
     * Enregistrer un style CSS personnalise.
     *
     * @param string $handle  Identifiant unique du style.
     * @param string $src     Chemin relatif ou URL du fichier CSS.
     * @param array  $deps    Tableau des dependances.
     * @param string $media   Media query.
     *
     * @return void
     */
    public function registerStyle(string $handle, string $src, array $deps = [], string $media = 'all'): void
    {
        $resolvedSrc = $this->resolveAssetUrl($src);
        $version     = $this->getFileVersion($src);
        wp_register_style($handle, $resolvedSrc, $deps, $version, $media);
    }

    /**
     * Enregistrer un script JavaScript personnalise.
     *
     * @param string $handle    Identifiant unique du script.
     * @param string $src       Chemin relatif ou URL du fichier JS.
     * @param array  $deps      Tableau des dependances.
     * @param bool   $inFooter  True pour charger en bas de page.
     *
     * @return void
     */
    public function registerScript(string $handle, string $src, array $deps = [], bool $inFooter = true): void
    {
        $resolvedSrc = $this->resolveAssetUrl($src);
        $version     = $this->getFileVersion($src);
        wp_register_script($handle, $resolvedSrc, $deps, $version, $inFooter);
    }

    /**
     * Calculer la version d'un fichier asset par hash MD5 de son contenu.
     *
     * Permet le versionnage automatique pour invalider le cache navigateur.
     * Utilise filemtime() comme fallback si le fichier n'existe pas localement.
     *
     * @param string $relativePath  Chemin relatif depuis la racine du theme.
     *
     * @return string  Hash MD5 partiel du fichier ou version du theme comme fallback.
     */
    private function getFileVersion(string $relativePath): string
    {
        $absolutePath = $this->themeDir . '/' . ltrim($relativePath, '/');

        if (file_exists($absolutePath)) {
            return substr(md5_file($absolutePath) ?: '', 0, 8);
        }

        return $this->config->get('app.version', '1.0.0');
    }

    /**
     * Resoudre l'URL complete d'un asset depuis un chemin relatif ou une URL absolue.
     *
     * @param string $src  Chemin relatif (ex: 'resources/css/main.css') ou URL absolue.
     *
     * @return string  URL complete de l'asset.
     */
    private function resolveAssetUrl(string $src): string
    {
        // Si le chemin est deja une URL complete, le retourner tel quel.
        if (filter_var($src, FILTER_VALIDATE_URL) !== false) {
            return $src;
        }

        return $this->themeUrl . '/' . ltrim($src, '/');
    }
}
