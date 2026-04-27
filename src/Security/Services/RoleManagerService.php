<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\RoleManagerInterface;

/**
 * Class RoleManagerService
 *
 * Implementation du gestionnaire de roles et capabilities WordPress
 * pour le contexte associatif estudiantin.
 *
 * Roles definis :
 * - ta_president     : Acces complet a la gestion de l'association
 * - ta_bureau        : Membre du bureau, peut gerer evenements et membres
 * - ta_membre_actif  : Membre actif avec acces aux fonctionnalites de base
 * - ta_adherent      : Adherent avec acces en lecture seule
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class RoleManagerService implements RoleManagerInterface
{
    /**
     * Identifiant du role President de l'association.
     */
    public const ROLE_PRESIDENT = 'ta_president';

    /**
     * Identifiant du role Membre du bureau.
     */
    public const ROLE_BUREAU = 'ta_bureau';

    /**
     * Identifiant du role Membre actif.
     */
    public const ROLE_MEMBRE_ACTIF = 'ta_membre_actif';

    /**
     * Identifiant du role Adherent.
     */
    public const ROLE_ADHERENT = 'ta_adherent';

    /**
     * Definition des roles avec leurs capabilities et labels.
     * Format : [role_name => [label, [capabilities]]]
     *
     * @var array<string, array{label: string, capabilities: array<string, bool>}>
     */
    private array $roleDefinitions = [
        self::ROLE_PRESIDENT => [
            'label'        => 'Président de l\'association',
            'capabilities' => [
                'read'                         => true,
                'ta_manage_association'        => true,
                'ta_manage_members'            => true,
                'ta_delete_members'            => true,
                'ta_manage_events'             => true,
                'ta_delete_events'             => true,
                'ta_manage_finances'           => true,
                'ta_view_reports'              => true,
                'ta_manage_roles'              => true,
                'ta_send_notifications'        => true,
                'ta_manage_content'            => true,
                'ta_publish_content'           => true,
                'ta_view_security_logs'        => true,
            ],
        ],
        self::ROLE_BUREAU => [
            'label'        => 'Membre du bureau',
            'capabilities' => [
                'read'                         => true,
                'ta_manage_members'            => true,
                'ta_manage_events'             => true,
                'ta_view_reports'              => true,
                'ta_send_notifications'        => true,
                'ta_manage_content'            => true,
                'ta_publish_content'           => true,
            ],
        ],
        self::ROLE_MEMBRE_ACTIF => [
            'label'        => 'Membre actif',
            'capabilities' => [
                'read'                         => true,
                'ta_view_events'               => true,
                'ta_register_events'           => true,
                'ta_view_members_directory'    => true,
                'ta_edit_own_profile'          => true,
                'ta_create_content'            => true,
            ],
        ],
        self::ROLE_ADHERENT => [
            'label'        => 'Adhérent',
            'capabilities' => [
                'read'                         => true,
                'ta_view_events'               => true,
                'ta_register_events'           => true,
                'ta_edit_own_profile'          => true,
            ],
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * Cree chaque role personnalise s'il n'existe pas encore.
     * WordPress stocke les roles dans la table wp_options.
     */
    public function registerRoles(): void
    {
        foreach ($this->roleDefinitions as $roleName => $definition) {
            if (get_role($roleName) === null) {
                add_role($roleName, $definition['label'], $definition['capabilities']);
            }
        }

        $this->grantCapabilitiesToAdministrator();
    }

    /**
     * {@inheritdoc}
     *
     * Supprime uniquement les roles crees par ce theme.
     * Les utilisateurs assignes a ces roles sont ramenes au role 'subscriber'.
     */
    public function removeRoles(): void
    {
        foreach (array_keys($this->roleDefinitions) as $roleName) {
            remove_role($roleName);
        }

        $this->revokeCapabilitiesFromAdministrator();
    }

    /**
     * {@inheritdoc}
     */
    public function addCapabilityToRole(string $roleName, string $capability, bool $grant = true): bool
    {
        $role = get_role($roleName);

        if ($role === null) {
            return false;
        }

        $role->add_cap($capability, $grant);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCapabilityFromRole(string $roleName, string $capability): bool
    {
        $role = get_role($roleName);

        if ($role === null) {
            return false;
        }

        $role->remove_cap($capability);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function roleHasCapability(string $roleName, string $capability): bool
    {
        $role = get_role($roleName);

        if ($role === null) {
            return false;
        }

        return isset($role->capabilities[$capability]) && $role->capabilities[$capability] === true;
    }

    /**
     * {@inheritdoc}
     *
     * Retourne toutes les capabilities specifiques au theme
     * avec leur description pour la documentation.
     */
    public function getThemeCapabilities(): array
    {
        return [
            'ta_manage_association'     => 'Gerer tous les parametres de l\'association',
            'ta_manage_members'         => 'Creer, modifier et gerer les membres',
            'ta_delete_members'         => 'Supprimer des membres de l\'association',
            'ta_manage_events'          => 'Creer et modifier des evenements',
            'ta_delete_events'          => 'Supprimer des evenements',
            'ta_manage_finances'        => 'Acceder et gerer les finances de l\'association',
            'ta_view_reports'           => 'Consulter les rapports et statistiques',
            'ta_manage_roles'           => 'Gerer les roles et permissions des membres',
            'ta_send_notifications'     => 'Envoyer des notifications aux membres',
            'ta_manage_content'         => 'Gerer le contenu du site (pages, articles)',
            'ta_publish_content'        => 'Publier du contenu sans moderation',
            'ta_create_content'         => 'Creer du contenu soumis a moderation',
            'ta_view_security_logs'     => 'Consulter les journaux de securite',
            'ta_view_events'            => 'Consulter les evenements de l\'association',
            'ta_register_events'        => 'S\'inscrire aux evenements',
            'ta_view_members_directory' => 'Consulter l\'annuaire des membres',
            'ta_edit_own_profile'       => 'Modifier son propre profil',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function assignRoleToUser(int $userId, string $roleName): bool
    {
        $user = get_user_by('id', $userId);

        if ($user === false) {
            return false;
        }

        if (!array_key_exists($roleName, $this->roleDefinitions) && get_role($roleName) === null) {
            return false;
        }

        $user->set_role($roleName);

        return true;
    }

    /**
     * Accorde toutes les capabilities du theme au role administrateur.
     * L'administrateur WordPress doit avoir acces a toutes les fonctionnalites.
     *
     * @return void
     */
    private function grantCapabilitiesToAdministrator(): void
    {
        $admin = get_role('administrator');

        if ($admin === null) {
            return;
        }

        foreach ($this->getThemeCapabilities() as $capability => $description) {
            $admin->add_cap($capability, true);
        }
    }

    /**
     * Retire les capabilities du theme du role administrateur.
     * Appele lors de la desactivation du theme.
     *
     * @return void
     */
    private function revokeCapabilitiesFromAdministrator(): void
    {
        $admin = get_role('administrator');

        if ($admin === null) {
            return;
        }

        foreach (array_keys($this->getThemeCapabilities()) as $capability) {
            $admin->remove_cap($capability);
        }
    }
}
