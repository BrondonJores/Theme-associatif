<?php
/**
 * Interface du moteur de templates
 *
 * Definit le contrat pour le rendu des templates PHP du theme.
 * Permet l'abstraction du systeme de template utilise, facilitant
 * une eventuelle migration vers Blade, Twig ou autre.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Gestion exclusive du rendu des templates.
 * - Open/Closed           : Nouveau moteur peut etre substitue sans modifier le code client.
 * - Liskov Substitution   : Toute implementation respecte ce contrat.
 * - Interface Segregation : Interface minimale et focalisee.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * TemplateEngineInterface
 *
 * Contrat pour le moteur de rendu des templates du theme.
 * Les templates se trouvent dans le dossier resources/views/.
 */
interface TemplateEngineInterface
{
    /**
     * Rendre un template et retourner le HTML genere.
     *
     * @param string               $template  Chemin relatif du template (sans extension .php).
     *                                        Exemple : 'layouts/base', 'components/card'.
     * @param array<string, mixed> $data      Donnees a injecter dans le template.
     *
     * @return string  Le HTML genere par le template.
     *
     * @throws \RuntimeException Si le template est introuvable.
     */
    public function render(string $template, array $data = []): string;

    /**
     * Afficher un template directement (echo du resultat).
     *
     * @param string               $template  Chemin relatif du template (sans extension .php).
     * @param array<string, mixed> $data      Donnees a injecter dans le template.
     *
     * @return void
     *
     * @throws \RuntimeException Si le template est introuvable.
     */
    public function display(string $template, array $data = []): void;

    /**
     * Verifier si un template existe.
     *
     * @param string $template  Chemin relatif du template (sans extension .php).
     *
     * @return bool  True si le template existe et est lisible, false sinon.
     */
    public function exists(string $template): bool;
}
