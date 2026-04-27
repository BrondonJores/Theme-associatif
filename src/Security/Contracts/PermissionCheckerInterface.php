<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface PermissionCheckerInterface
 *
 * Contrat pour le service de verification des permissions avec cache.
 * Fournit une couche d'abstraction au-dessus du systeme de capabilities
 * WordPress avec mise en cache pour les performances.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface PermissionCheckerInterface
{
    /**
     * Verifie si l'utilisateur courant possede une capability.
     * Le resultat est mis en cache pour eviter les appels repetitifs.
     *
     * @param  string  $capability La capability WordPress a verifier.
     * @param  mixed   ...$args    Arguments supplementaires passes a current_user_can().
     * @return bool    True si l'utilisateur possede la capability.
     */
    public function currentUserCan(string $capability, mixed ...$args): bool;

    /**
     * Verifie si un utilisateur specifique possede une capability.
     *
     * @param  int     $userId     L'identifiant de l'utilisateur.
     * @param  string  $capability La capability a verifier.
     * @param  mixed   ...$args    Arguments supplementaires.
     * @return bool    True si l'utilisateur possede la capability.
     */
    public function userCan(int $userId, string $capability, mixed ...$args): bool;

    /**
     * Verifie si l'utilisateur courant peut effectuer une action sur un objet.
     * Exemple : 'edit_post' sur un post specifique.
     *
     * @param  string $capability  La capability a verifier.
     * @param  int    $objectId    L'identifiant de l'objet WordPress.
     * @return bool   True si l'action est autorisee.
     */
    public function currentUserCanOnObject(string $capability, int $objectId): bool;

    /**
     * Verifie et interrompt l'execution si la capability est manquante.
     * Appelle wp_die() avec un message d'erreur securise si refuse.
     *
     * @param  string $capability La capability requise.
     * @param  string $message    Message d'erreur personnalise (optionnel).
     * @return void
     */
    public function requireCapability(string $capability, string $message = ''): void;

    /**
     * Retourne l'identifiant de l'utilisateur courant.
     *
     * @return int L'ID utilisateur (0 si non connecte).
     */
    public function getCurrentUserId(): int;

    /**
     * Verifie si l'utilisateur courant est connecte.
     *
     * @return bool True si l'utilisateur est authentifie.
     */
    public function isLoggedIn(): bool;

    /**
     * Verifie si l'utilisateur courant est administrateur.
     *
     * @return bool True si l'utilisateur est super admin ou admin.
     */
    public function isAdmin(): bool;

    /**
     * Vide le cache des permissions.
     * A appeler lors d'un changement de role ou de capability.
     *
     * @param  int|null $userId L'ID utilisateur (null = vider tout le cache).
     * @return void
     */
    public function flushCache(?int $userId = null): void;
}
