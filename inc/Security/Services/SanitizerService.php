<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\SanitizerInterface;

/**
 * Class SanitizerService
 *
 * Implementation du service de sanitization des donnees.
 * Utilise les fonctions natives WordPress pour garantir la compatibilite
 * et beneficier des filtres 'sanitize_*' du coeur WordPress.
 *
 * Principe SOLID applique :
 * - Responsabilite unique : gere uniquement la sanitization des entrees.
 * - Ouvert/Ferme : extensible via les filtres WordPress sans modification.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class SanitizerService implements SanitizerInterface
{
    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_text_field() de WordPress qui :
     * - Supprime les balises HTML
     * - Convertit les entites HTML en texte
     * - Supprime les caracteres de controle Unicode
     * - Supprime les espaces superflus
     */
    public function sanitizeTextField(string $value): string
    {
        return sanitize_text_field($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_textarea_field() de WordPress qui applique
     * sanitize_text_field() sur chaque ligne tout en preservant les sauts de ligne.
     */
    public function sanitizeTextarea(string $value): string
    {
        return sanitize_textarea_field($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_email() de WordPress qui supprime les caracteres
     * non autorises dans les adresses email selon la RFC 2822.
     * Note : cette fonction sanitise mais ne valide pas ; utiliser
     * ValidatorService::validate() avec EmailRule pour la validation.
     */
    public function sanitizeEmail(string $value): string
    {
        return sanitize_email($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise esc_url_raw() de WordPress pour nettoyer l'URL sans echapper
     * les caracteres speciaux (contrairement a esc_url() qui est pour l'affichage).
     * Filtre les protocoles non autorises (javascript:, data:, etc.).
     */
    public function sanitizeUrl(string $value): string
    {
        return esc_url_raw($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise absint() de WordPress qui retourne la valeur absolue
     * d'un entier, garantissant un entier positif ou zero.
     * Pour les entiers negatifs, utilise intval() directement.
     */
    public function sanitizeInt(mixed $value): int
    {
        return intval($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise floatval() pour extraire la valeur numerique flottante.
     */
    public function sanitizeFloat(mixed $value): float
    {
        return floatval($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise wp_kses_post() de WordPress qui applique la liste blanche
     * de balises HTML autorisees pour le contenu des posts.
     * Inclut : p, a, strong, em, ul, ol, li, blockquote, code, pre, etc.
     */
    public function sanitizeHtml(string $value): string
    {
        return wp_kses_post($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_key() de WordPress qui :
     * - Convertit en minuscules
     * - Conserve uniquement les lettres, chiffres, tirets et underscores
     */
    public function sanitizeKey(string $value): string
    {
        return sanitize_key($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_html_class() de WordPress pour nettoyer les noms
     * de classes CSS et identifiants HTML.
     */
    public function sanitizeHtmlClass(string $value): string
    {
        return sanitize_html_class($value);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise sanitize_file_name() de WordPress qui :
     * - Supprime les caracteres speciaux
     * - Remplace les espaces par des tirets
     * - Supprime les tentatives de traversee de repertoire (../)
     */
    public function sanitizeFileName(string $value): string
    {
        return sanitize_file_name($value);
    }

    /**
     * {@inheritdoc}
     *
     * Traite chaque element du tableau recursivement.
     * Le contexte determine quelle methode de sanitization est appliquee.
     *
     * Contextes disponibles : 'text', 'textarea', 'email', 'url', 'key', 'html'
     */
    public function sanitizeArray(array $data, string $context = 'text'): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $sanitizedKey = is_string($key) ? $this->sanitizeKey($key) : $key;

            if (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeArray($value, $context);
            } elseif (is_string($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeByContext($value, $context);
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Applique la methode de sanitization appropriee selon le contexte.
     *
     * @param  string $value   La valeur a sanitiser.
     * @param  string $context Le contexte de sanitization.
     * @return string La valeur sanitisee.
     */
    private function sanitizeByContext(string $value, string $context): string
    {
        return match ($context) {
            'textarea' => $this->sanitizeTextarea($value),
            'email'    => $this->sanitizeEmail($value),
            'url'      => $this->sanitizeUrl($value),
            'key'      => $this->sanitizeKey($value),
            'html'     => $this->sanitizeHtml($value),
            'class'    => $this->sanitizeHtmlClass($value),
            'filename' => $this->sanitizeFileName($value),
            default    => $this->sanitizeTextField($value),
        };
    }
}
