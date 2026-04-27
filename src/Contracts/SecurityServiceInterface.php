<?php
/**
 * Interface du service de securite
 *
 * Definit le contrat pour les operations de securite dans le theme :
 * sanitization des entrees, escaping des sorties, validation des nonces
 * et protection CSRF.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce service gere exclusivement la securite.
 * - Interface Segregation : Interface complete mais cohesive pour la securite.
 * - Dependency Inversion  : Le code depend de cette abstraction, pas d'une implementation.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * SecurityServiceInterface
 *
 * Contrat pour le service de securite du theme.
 * Centralise toutes les operations de securite pour eviter la duplication
 * et garantir l'application coherente des bonnes pratiques.
 */
interface SecurityServiceInterface
{
    /**
     * Sanitiser une chaine de texte brut (aucun HTML autorise).
     *
     * @param string $input  La valeur a sanitiser.
     *
     * @return string  La valeur sanitisee.
     */
    public function sanitizeText(string $input): string;

    /**
     * Sanitiser une URL.
     *
     * @param string $url  L'URL a sanitiser.
     *
     * @return string  L'URL sanitisee.
     */
    public function sanitizeUrl(string $url): string;

    /**
     * Sanitiser un champ de texte HTML (autorise uniquement les balises securisees).
     *
     * @param string $input  Le contenu HTML a sanitiser.
     *
     * @return string  Le contenu HTML sanitise.
     */
    public function sanitizeHtml(string $input): string;

    /**
     * Sanitiser une adresse email.
     *
     * @param string $email  L'email a sanitiser.
     *
     * @return string  L'email sanitise.
     */
    public function sanitizeEmail(string $email): string;

    /**
     * Echapper une chaine pour un affichage HTML securise.
     *
     * @param string $output  La valeur a echapper.
     *
     * @return string  La valeur echappee.
     */
    public function escHtml(string $output): string;

    /**
     * Echapper une valeur pour un attribut HTML.
     *
     * @param string $output  La valeur de l'attribut a echapper.
     *
     * @return string  La valeur echappee.
     */
    public function escAttr(string $output): string;

    /**
     * Echapper une URL pour un affichage securise.
     *
     * @param string $url  L'URL a echapper.
     *
     * @return string  L'URL echappee.
     */
    public function escUrl(string $url): string;

    /**
     * Generer un champ nonce HTML pour la protection CSRF.
     *
     * @param string $action  Nom de l'action pour le nonce.
     *
     * @return string  Le champ HTML du nonce.
     */
    public function nonceField(string $action): string;

    /**
     * Verifier la validite d'un nonce.
     *
     * @param string $nonce   La valeur du nonce a verifier.
     * @param string $action  L'action associee au nonce.
     *
     * @return bool  True si le nonce est valide, false sinon.
     */
    public function verifyNonce(string $nonce, string $action): bool;

    /**
     * Verifier si l'utilisateur courant possede une capacite donnee.
     *
     * @param string $capability  La capacite WordPress a verifier.
     *
     * @return bool  True si l'utilisateur possede la capacite, false sinon.
     */
    public function currentUserCan(string $capability): bool;
}
