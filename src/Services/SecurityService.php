<?php
/**
 * Service de securite - Implementation concrete
 *
 * Centralise toutes les operations de securite du theme :
 * sanitization des entrees, escaping des sorties, gestion des nonces
 * et verification des capacites utilisateurs.
 *
 * Ce service est un adaptateur pour les fonctions de securite WordPress,
 * permettant de les tester et de les remplacer facilement.
 *
 * Principe SOLID applique :
 * - Single Responsibility : Ce service ne gere que la securite.
 * - Liskov Substitution   : Implemente pleinement SecurityServiceInterface.
 * - Dependency Inversion  : Le code client depend de SecurityServiceInterface.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Contracts\SecurityServiceInterface;

/**
 * SecurityService
 *
 * Adaptateur pour les fonctions de securite WordPress.
 * Centralise sanitization, escaping, nonces et verification des capacites.
 */
final class SecurityService implements SecurityServiceInterface
{
    /**
     * Sanitiser une chaine de texte brut (aucun HTML autorise).
     *
     * @param string $input  La valeur a sanitiser.
     *
     * @return string  La chaine sanitisee sans balises HTML.
     */
    public function sanitizeText(string $input): string
    {
        return sanitize_text_field($input);
    }

    /**
     * Sanitiser une URL.
     *
     * @param string $url  L'URL a sanitiser.
     *
     * @return string  L'URL sanitisee.
     */
    public function sanitizeUrl(string $url): string
    {
        return sanitize_url($url);
    }

    /**
     * Sanitiser un contenu HTML (autorise les balises securisees).
     *
     * Utilise wp_kses_post() pour autoriser uniquement les balises HTML
     * communes dans les contenus de publication.
     *
     * @param string $input  Le contenu HTML a sanitiser.
     *
     * @return string  Le contenu HTML sanitise.
     */
    public function sanitizeHtml(string $input): string
    {
        return wp_kses_post($input);
    }

    /**
     * Sanitiser une adresse email.
     *
     * @param string $email  L'email a sanitiser.
     *
     * @return string  L'email sanitise.
     */
    public function sanitizeEmail(string $email): string
    {
        return sanitize_email($email);
    }

    /**
     * Echapper une chaine pour un affichage HTML securise.
     *
     * @param string $output  La valeur a echapper.
     *
     * @return string  La valeur echappee pour HTML.
     */
    public function escHtml(string $output): string
    {
        return esc_html($output);
    }

    /**
     * Echapper une valeur pour un attribut HTML.
     *
     * @param string $output  La valeur de l'attribut.
     *
     * @return string  La valeur echappee pour attribut HTML.
     */
    public function escAttr(string $output): string
    {
        return esc_attr($output);
    }

    /**
     * Echapper une URL pour un affichage securise.
     *
     * @param string $url  L'URL a echapper.
     *
     * @return string  L'URL echappee.
     */
    public function escUrl(string $url): string
    {
        return esc_url($url);
    }

    /**
     * Generer un champ nonce HTML pour la protection CSRF.
     *
     * @param string $action  Nom de l'action WordPress pour le nonce.
     *
     * @return string  Le champ HTML du nonce.
     */
    public function nonceField(string $action): string
    {
        ob_start();
        wp_nonce_field($action);
        return (string) ob_get_clean();
    }

    /**
     * Verifier la validite d'un nonce.
     *
     * @param string $nonce   La valeur du nonce a verifier.
     * @param string $action  L'action associee au nonce.
     *
     * @return bool  True si le nonce est valide.
     */
    public function verifyNonce(string $nonce, string $action): bool
    {
        return (bool) wp_verify_nonce($nonce, $action);
    }

    /**
     * Verifier si l'utilisateur courant possede une capacite WordPress.
     *
     * @param string $capability  La capacite a verifier (ex: 'manage_options').
     *
     * @return bool  True si l'utilisateur possede la capacite.
     */
    public function currentUserCan(string $capability): bool
    {
        return current_user_can($capability);
    }
}
