<?php
/**
 * Moteur de rendu des templates PHP
 *
 * Implementation concrete de TemplateEngineInterface.
 * Charge et rend les templates PHP depuis le dossier resources/views/,
 * en injectant les donnees dans le scope du template.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Rendu exclusif des templates, pas d'autre logique.
 * - Open/Closed           : Chemin des templates configurable sans modifier la classe.
 * - Liskov Substitution   : Implemente pleinement TemplateEngineInterface.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Contracts\TemplateEngineInterface;
use ThemeAssociatif\Core\Configuration;
use RuntimeException;

/**
 * TemplateEngine
 *
 * Moteur de templates PHP simple et securise.
 * Tous les templates se trouvent dans resources/views/ et sont charges
 * avec un scope isole pour eviter les conflits de variables.
 */
final class TemplateEngine implements TemplateEngineInterface
{
    /**
     * Constructeur - Configurer les chemins des templates.
     *
     * @param string        $viewsPath  Chemin absolu vers le dossier des templates.
     * @param Configuration $config     Le gestionnaire de configuration.
     *
     * @throws RuntimeException Si le dossier des templates est introuvable.
     */
    public function __construct(
        private readonly string $viewsPath,
        private readonly Configuration $config
    ) {
        if (! is_dir($this->viewsPath)) {
            throw new RuntimeException(
                sprintf(
                    'Le dossier des templates est introuvable : %s',
                    $this->viewsPath
                )
            );
        }
    }

    /**
     * Rendre un template et retourner le HTML genere.
     *
     * @param string               $template  Chemin relatif du template (sans .php).
     * @param array<string, mixed> $data      Donnees a injecter dans le template.
     *
     * @return string  Le HTML genere.
     *
     * @throws RuntimeException Si le template est introuvable.
     */
    public function render(string $template, array $data = []): string
    {
        $templatePath = $this->resolvePath($template);

        if (! file_exists($templatePath)) {
            throw new RuntimeException(
                sprintf(
                    'Template introuvable : "%s" (%s)',
                    $template,
                    $templatePath
                )
            );
        }

        // Utiliser un buffer de sortie isole pour capturer le rendu.
        ob_start();

        // Extraire les donnees dans le scope du template.
        // Les callables sont executes directement pour le rendu de sections.
        extract($this->prepareData($data), EXTR_SKIP);

        // Rendre la variable $config disponible dans tous les templates.
        $config = $this->config;

        // Inclure le template dans le scope courant.
        include $templatePath;

        return (string) ob_get_clean();
    }

    /**
     * Afficher un template directement (echo du resultat).
     *
     * @param string               $template  Chemin relatif du template (sans .php).
     * @param array<string, mixed> $data      Donnees a injecter dans le template.
     *
     * @return void
     */
    public function display(string $template, array $data = []): void
    {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->render($template, $data);
    }

    /**
     * Verifier si un template existe.
     *
     * @param string $template  Chemin relatif du template (sans .php).
     *
     * @return bool  True si le template est lisible.
     */
    public function exists(string $template): bool
    {
        return file_exists($this->resolvePath($template));
    }

    /**
     * Resoudre le chemin absolu d'un template.
     *
     * Securite : empeche la traversee de repertoire (path traversal)
     * en verifiant que le chemin resolu est bien dans le dossier des templates.
     *
     * @param string $template  Nom du template en dot ou slash notation.
     *
     * @return string  Chemin absolu du fichier template.
     *
     * @throws RuntimeException En cas de tentative de traversee de repertoire.
     */
    private function resolvePath(string $template): string
    {
        // Normaliser les separateurs (accepter dot ou slash notation).
        $normalized = str_replace(['.', '\\'], '/', $template);

        // Construire le chemin absolu.
        $absolutePath = realpath($this->viewsPath . '/' . $normalized . '.php');

        // Protection contre la traversee de repertoire.
        if ($absolutePath !== false) {
            $realViewsPath = realpath($this->viewsPath);
            if ($realViewsPath === false || strpos($absolutePath, $realViewsPath) !== 0) {
                throw new RuntimeException(
                    sprintf('Acces refuse : le template "%s" est hors du dossier autorise.', $template)
                );
            }
        }

        // Si realpath echoue (fichier inexistant), retourner le chemin construit
        // pour que l'appelant puisse generer une erreur descriptive.
        return $absolutePath !== false
            ? $absolutePath
            : $this->viewsPath . '/' . $normalized . '.php';
    }

    /**
     * Preparer les donnees a injecter dans le template.
     *
     * Convertit les callables en leur resultat pour les sections de contenu.
     *
     * @param array<string, mixed> $data  Donnees brutes.
     *
     * @return array<string, mixed>  Donnees preparees.
     */
    private function prepareData(array $data): array
    {
        $prepared = [];

        foreach ($data as $key => $value) {
            // Executer les callables et capturer leur sortie comme chaine.
            if (is_callable($value)) {
                ob_start();
                $value($this);
                $prepared[$key] = (string) ob_get_clean();
            } else {
                $prepared[$key] = $value;
            }
        }

        return $prepared;
    }
}
