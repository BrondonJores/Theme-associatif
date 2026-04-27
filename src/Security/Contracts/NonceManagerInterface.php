<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface NonceManagerInterface
 *
 * Contrat pour la gestion complete des nonces WordPress.
 * Les nonces (number used once) protegent contre les attaques CSRF
 * en associant un token unique a une action specifique.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface NonceManagerInterface
{
    /**
     * Cree un nonce WordPress pour une action donnee.
     *
     * @param  string     $action     L'identifiant unique de l'action protegee.
     * @param  int|string $userId     L'identifiant de l'utilisateur (0 = utilisateur courant).
     * @return string Le token nonce genere.
     */
    public function create(string $action, int|string $userId = 0): string;

    /**
     * Verifie la validite d'un nonce pour une action.
     * Retourne 1 si le nonce est valide et recent (moins de 12h),
     * retourne 2 si le nonce est valide mais plus ancien (entre 12h et 24h).
     *
     * @param  string     $nonce  Le token nonce a verifier.
     * @param  string     $action L'action attendue.
     * @param  int|string $userId L'identifiant de l'utilisateur (0 = utilisateur courant).
     * @return int|false  1, 2 si valide ; false si invalide.
     */
    public function verify(string $nonce, string $action, int|string $userId = 0): int|false;

    /**
     * Verifie le nonce et interrompt l'execution si invalide.
     * Appelle wp_die() avec un message d'erreur securise.
     *
     * @param  string $nonce  Le token nonce a verifier.
     * @param  string $action L'action attendue.
     * @return void
     */
    public function check(string $nonce, string $action): void;

    /**
     * Genere un champ HTML hidden contenant le nonce pour les formulaires.
     *
     * @param  string $action   L'action protegee.
     * @param  string $fieldName Le nom de l'attribut name du champ hidden.
     * @param  bool   $referer  Si true, ajoute aussi un champ referer.
     * @param  bool   $echo     Si true, affiche directement le HTML.
     * @return string Le champ HTML (vide si echo=true).
     */
    public function field(
        string $action,
        string $fieldName = '_wpnonce',
        bool $referer = true,
        bool $echo = false
    ): string;

    /**
     * Genere une URL avec un nonce en parametre de requete.
     *
     * @param  string $url    L'URL de base.
     * @param  string $action L'action protegee.
     * @return string L'URL avec le parametre nonce ajoute.
     */
    public function url(string $url, string $action): string;

    /**
     * Verifie le nonce depuis la requete courante (GET ou POST).
     * Recherche automatiquement le nonce dans $_GET et $_POST.
     *
     * @param  string $action    L'action attendue.
     * @param  string $fieldName Le nom du champ nonce dans la requete.
     * @return bool True si le nonce est valide.
     */
    public function verifyRequest(string $action, string $fieldName = '_wpnonce'): bool;
}
