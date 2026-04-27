<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface RoleManagerInterface
 *
 * Contrat pour la gestion des roles et capabilities WordPress.
 * Permet de definir et gerer les roles specifiques au contexte associatif
 * estudiantin avec une granularite fine des permissions.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface RoleManagerInterface
{
    /**
     * Enregistre les roles personnalises du theme associatif.
     * Doit etre appele lors de l'activation du theme.
     *
     * @return void
     */
    public function registerRoles(): void;

    /**
     * Supprime les roles personnalises du theme.
     * Doit etre appele lors de la desactivation du theme.
     *
     * @return void
     */
    public function removeRoles(): void;

    /**
     * Ajoute une capability a un role existant.
     *
     * @param  string $roleName     Le nom du role WordPress.
     * @param  string $capability   Le nom de la capability a ajouter.
     * @param  bool   $grant        Si true, accorde la capability ; si false, la refuse explicitement.
     * @return bool   True si l'operation a reussi.
     */
    public function addCapabilityToRole(string $roleName, string $capability, bool $grant = true): bool;

    /**
     * Retire une capability d'un role.
     *
     * @param  string $roleName   Le nom du role.
     * @param  string $capability La capability a retirer.
     * @return bool  True si l'operation a reussi.
     */
    public function removeCapabilityFromRole(string $roleName, string $capability): bool;

    /**
     * Verifie si un role possede une capability specifique.
     *
     * @param  string $roleName   Le nom du role.
     * @param  string $capability La capability a verifier.
     * @return bool  True si le role possede la capability.
     */
    public function roleHasCapability(string $roleName, string $capability): bool;

    /**
     * Retourne la liste de toutes les capabilities definies pour le theme.
     *
     * @return array<string, string> Les capabilities indexees par nom avec leur description.
     */
    public function getThemeCapabilities(): array;

    /**
     * Assigne un role a un utilisateur.
     *
     * @param  int    $userId   L'identifiant de l'utilisateur WordPress.
     * @param  string $roleName Le nom du role a assigner.
     * @return bool  True si l'assignation a reussi.
     */
    public function assignRoleToUser(int $userId, string $roleName): bool;
}
