<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface SanitizerInterface
 *
 * Contrat pour le service de sanitization des donnees.
 * La sanitization nettoie les donnees en entree pour supprimer tout contenu
 * potentiellement dangereux avant traitement ou stockage.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface SanitizerInterface
{
    /**
     * Sanitise un champ de texte simple (input text).
     * Supprime les balises HTML et les caracteres de controle.
     *
     * @param  string $value La valeur brute a sanitiser.
     * @return string La valeur sanitisee.
     */
    public function sanitizeTextField(string $value): string;

    /**
     * Sanitise un champ textarea (texte multiligne).
     * Autorise les sauts de ligne mais supprime les balises HTML.
     *
     * @param  string $value La valeur brute a sanitiser.
     * @return string La valeur sanitisee.
     */
    public function sanitizeTextarea(string $value): string;

    /**
     * Sanitise une adresse email.
     * Supprime les caracteres non autorises dans une adresse email.
     *
     * @param  string $value L'email brut a sanitiser.
     * @return string L'email sanitise.
     */
    public function sanitizeEmail(string $value): string;

    /**
     * Sanitise une URL.
     * Supprime les caracteres non autorises et valide le protocole.
     *
     * @param  string $value L'URL brute a sanitiser.
     * @return string L'URL sanitisee.
     */
    public function sanitizeUrl(string $value): string;

    /**
     * Sanitise un entier.
     * Convertit la valeur en entier et supprime tout caractere non numerique.
     *
     * @param  mixed $value La valeur brute a sanitiser.
     * @return int  La valeur entiere sanitisee.
     */
    public function sanitizeInt(mixed $value): int;

    /**
     * Sanitise un nombre flottant.
     *
     * @param  mixed $value La valeur brute a sanitiser.
     * @return float La valeur flottante sanitisee.
     */
    public function sanitizeFloat(mixed $value): float;

    /**
     * Sanitise un champ HTML (textarea enrichi / editeur WYSIWYG).
     * Autorise uniquement les balises HTML securisees definies par WordPress.
     *
     * @param  string $value Le contenu HTML brut a sanitiser.
     * @return string Le contenu HTML sanitise.
     */
    public function sanitizeHtml(string $value): string;

    /**
     * Sanitise une cle (slug, identifiant technique).
     * Autorise uniquement les lettres minuscules, chiffres et tirets.
     *
     * @param  string $value La cle brute a sanitiser.
     * @return string La cle sanitisee.
     */
    public function sanitizeKey(string $value): string;

    /**
     * Sanitise un nom de classe CSS ou un identifiant HTML.
     *
     * @param  string $value La valeur brute.
     * @return string La valeur sanitisee.
     */
    public function sanitizeHtmlClass(string $value): string;

    /**
     * Sanitise un nom de fichier.
     * Supprime les caracteres dangereux et les traversees de repertoire.
     *
     * @param  string $value Le nom de fichier brut.
     * @return string Le nom de fichier sanitise.
     */
    public function sanitizeFileName(string $value): string;

    /**
     * Sanitise un tableau de valeurs de maniere recursive.
     * Chaque valeur est sanitisee selon son type detecte.
     *
     * @param  array<mixed> $data    Le tableau de donnees brutes.
     * @param  string       $context Le contexte de sanitization ('text', 'email', 'url', etc.).
     * @return array<mixed> Le tableau sanitise.
     */
    public function sanitizeArray(array $data, string $context = 'text'): array;
}
