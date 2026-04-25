<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\PermissionCheckerInterface;

/**
 * Class PermissionCheckerService
 *
 * Implementation du service de verification des permissions avec cache en memoire.
 * Le cache evite les appels repetitifs a current_user_can() et user_can()
 * qui peuvent generer des requetes base de donnees.
 *
 * Le cache est invalide automatiquement lors des changements de role
 * via le hook 'set_user_role' de WordPress.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class PermissionCheckerService implements PermissionCheckerInterface
{
    /**
     * Cache des resultats de verification de permissions.
     * Structure : [userId => [cacheKey => bool]]
     *
     * @var array<int, array<string, bool>>
     */
    private array $cache = [];

    /**
     * Constructeur : enregistre le hook d'invalidation de cache.
     */
    public function __construct()
    {
        add_action('set_user_role', [$this, 'flushCache']);
        add_action('add_user_meta', [$this, 'handleMetaChange']);
        add_action('update_user_meta', [$this, 'handleMetaChange']);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise le cache en memoire pour eviter les appels repetitifs
     * a la fonction WordPress current_user_can().
     */
    public function currentUserCan(string $capability, mixed ...$args): bool
    {
        $userId   = get_current_user_id();
        $cacheKey = $this->buildCacheKey($capability, $args);

        if (isset($this->cache[$userId][$cacheKey])) {
            return $this->cache[$userId][$cacheKey];
        }

        $result = current_user_can($capability, ...$args);

        $this->cache[$userId][$cacheKey] = $result;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function userCan(int $userId, string $capability, mixed ...$args): bool
    {
        $cacheKey = $this->buildCacheKey($capability, $args);

        if (isset($this->cache[$userId][$cacheKey])) {
            return $this->cache[$userId][$cacheKey];
        }

        $user = get_user_by('id', $userId);

        if ($user === false) {
            $this->cache[$userId][$cacheKey] = false;

            return false;
        }

        $result = user_can($user, $capability, ...$args);

        $this->cache[$userId][$cacheKey] = $result;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * Verifie la permission sur un objet specifique (post, taxonomie, etc.)
     * en passant l'ID de l'objet comme argument supplementaire.
     */
    public function currentUserCanOnObject(string $capability, int $objectId): bool
    {
        return $this->currentUserCan($capability, $objectId);
    }

    /**
     * {@inheritdoc}
     *
     * Interrompt l'execution avec wp_die() si la capability est manquante.
     * Le message d'erreur est generique pour ne pas reveler de details
     * sur la structure des permissions du site.
     */
    public function requireCapability(string $capability, string $message = ''): void
    {
        if ($this->currentUserCan($capability)) {
            return;
        }

        $errorMessage = !empty($message)
            ? $message
            : __('Vous n\'avez pas les permissions necessaires pour effectuer cette action.', 'theme-associatif');

        wp_die(
            esc_html($errorMessage),
            esc_html__('Acces refuse', 'theme-associatif'),
            ['response' => 403, 'back_link' => true]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentUserId(): int
    {
        return get_current_user_id();
    }

    /**
     * {@inheritdoc}
     */
    public function isLoggedIn(): bool
    {
        return is_user_logged_in();
    }

    /**
     * {@inheritdoc}
     *
     * Verifie le role 'administrator' ou le statut super admin
     * pour les installations multisite WordPress.
     */
    public function isAdmin(): bool
    {
        if (is_multisite() && is_super_admin()) {
            return true;
        }

        return $this->currentUserCan('administrator');
    }

    /**
     * {@inheritdoc}
     *
     * Si userId est null, vide tout le cache.
     * Si un userId est fourni, vide uniquement le cache de cet utilisateur.
     */
    public function flushCache(?int $userId = null): void
    {
        if ($userId === null) {
            $this->cache = [];

            return;
        }

        unset($this->cache[$userId]);
    }

    /**
     * Hook WordPress : invalide le cache lors d'un changement de meta utilisateur.
     * Intercepte les modifications de capabilities stockees dans les meta.
     *
     * @param  int $metaId L'ID de la meta modifiee (non utilise directement).
     * @return void
     */
    public function handleMetaChange(int $metaId): void
    {
        $this->flushCache();
    }

    /**
     * Construit une cle de cache unique pour une capability et ses arguments.
     *
     * @param  string        $capability La capability verifiee.
     * @param  array<mixed>  $args       Les arguments supplementaires.
     * @return string La cle de cache.
     */
    private function buildCacheKey(string $capability, array $args): string
    {
        if (empty($args)) {
            return $capability;
        }

        return $capability . '_' . md5(serialize($args));
    }
}
