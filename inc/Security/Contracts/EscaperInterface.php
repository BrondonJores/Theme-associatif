<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface EscaperInterface
 *
 * Contrat pour le service d'echappement des donnees en sortie.
 * L'escaping protege contre les attaques XSS en encodant les caracteres
 * speciaux selon le contexte de sortie (HTML, attribut, JavaScript, CSS, URL).
 *
 * Principe : toujours echapper au dernier moment, juste avant l'affichage.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface EscaperInterface
{
    /**
     * Echappe une chaine pour un contexte HTML.
     * Convertit les caracteres speciaux HTML (<, >, &, ", ') en entites HTML.
     *
     * @param  string $value La valeur a echapper.
     * @return string La valeur echappee pour l'affichage HTML.
     */
    public function escHtml(string $value): string;

    /**
     * Echappe et affiche directement une chaine pour un contexte HTML.
     *
     * @param  string $value La valeur a echapper et afficher.
     * @return void
     */
    public function escHtmlE(string $value): void;

    /**
     * Echappe une chaine pour un attribut HTML.
     * Plus restrictif que escHtml : encode davantage de caracteres.
     *
     * @param  string $value La valeur a echapper.
     * @return string La valeur echappee pour un attribut HTML.
     */
    public function escAttr(string $value): string;

    /**
     * Echappe et affiche directement une chaine pour un attribut HTML.
     *
     * @param  string $value La valeur a echapper et afficher.
     * @return void
     */
    public function escAttrE(string $value): void;

    /**
     * Echappe une URL pour une utilisation dans un attribut href, src, action, etc.
     * Valide le protocole et encode les caracteres speciaux.
     *
     * @param  string $value L'URL a echapper.
     * @return string L'URL echappee.
     */
    public function escUrl(string $value): string;

    /**
     * Echappe et affiche directement une URL.
     *
     * @param  string $value L'URL a echapper et afficher.
     * @return void
     */
    public function escUrlE(string $value): void;

    /**
     * Echappe une URL brute (sans validation de protocole).
     * A utiliser uniquement pour les URLs internes connues.
     *
     * @param  string $value L'URL brute a echapper.
     * @return string L'URL echappee.
     */
    public function escUrlRaw(string $value): string;

    /**
     * Echappe une chaine pour une utilisation dans un bloc JavaScript.
     * Encode les caracteres qui pourraient terminer le contexte JS.
     *
     * @param  string $value La valeur a echapper.
     * @return string La valeur echappee pour JavaScript.
     */
    public function escJs(string $value): string;

    /**
     * Echappe une chaine pour une utilisation dans un bloc CSS.
     * Encode les caracteres qui pourraient injecter du code CSS malveillant.
     *
     * @param  string $value La valeur a echapper.
     * @return string La valeur echappee pour CSS.
     */
    public function escCss(string $value): string;

    /**
     * Echappe du contenu HTML en autorisant uniquement les balises autorisees.
     * Utilise la liste blanche de balises de WordPress.
     *
     * @param  string               $value           Le contenu HTML a echapper.
     * @param  array<string, mixed> $allowedTags     Les balises HTML autorisees (override optionnel).
     * @return string Le contenu HTML filtre.
     */
    public function escHtmlKses(string $value, array $allowedTags = []): string;

    /**
     * Echappe une chaine pour un contexte de traduction.
     * Combine l'echappement HTML avec la fonction de traduction.
     *
     * @param  string $value  La chaine a traduire et echapper.
     * @param  string $domain Le domaine de traduction.
     * @return string La chaine traduite et echappee.
     */
    public function escHtmlTrans(string $value, string $domain = 'theme-associatif'): string;
}
