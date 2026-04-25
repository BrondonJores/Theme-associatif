<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\NonceManagerInterface;

/**
 * Class NonceManagerService
 *
 * Implementation du gestionnaire de nonces WordPress.
 * Les nonces WordPress sont des tokens cryptographiques lies a un utilisateur,
 * une session, une heure et une action specifique.
 *
 * Duree de validite par defaut : 24 heures (configurable via le filtre
 * 'nonce_life' de WordPress).
 *
 * Remarque : les nonces WordPress ne sont pas de vrais nonces (ils peuvent
 * etre reutilises dans leur fenetre de validite), mais ils offrent une
 * protection CSRF robuste dans le contexte WordPress.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class NonceManagerService implements NonceManagerInterface
{
    /**
     * Prefixe applique a toutes les actions nonce du theme.
     * Permet d'identifier et d'isoler les nonces du theme.
     *
     * @var string
     */
    private string $actionPrefix;

    /**
     * Constructeur du gestionnaire de nonces.
     *
     * @param string $actionPrefix Prefixe pour les actions (defaut: 'ta_').
     */
    public function __construct(string $actionPrefix = 'ta_')
    {
        $this->actionPrefix = $actionPrefix;
    }

    /**
     * {@inheritdoc}
     *
     * Prefixe l'action avec l'identifiant du theme pour eviter
     * les collisions avec d'autres plugins ou themes.
     */
    public function create(string $action, int|string $userId = 0): string
    {
        $prefixedAction = $this->prefixAction($action);

        if ($userId !== 0) {
            add_filter('nonce_user_logged_out', static function () use ($userId) {
                return $userId;
            });
        }

        return wp_create_nonce($prefixedAction);
    }

    /**
     * {@inheritdoc}
     *
     * La valeur de retour indique l'age du nonce :
     * - 1 : valide, genere il y a moins de 12 heures (heure courante)
     * - 2 : valide, genere entre 12 et 24 heures (heure precedente)
     * - false : invalide ou expire
     */
    public function verify(string $nonce, string $action, int|string $userId = 0): int|false
    {
        $prefixedAction = $this->prefixAction($action);

        return wp_verify_nonce($nonce, $prefixedAction);
    }

    /**
     * {@inheritdoc}
     *
     * En cas d'echec, wp_die() est appele avec un message d'erreur
     * generique pour ne pas reveler d'information sur le systeme.
     */
    public function check(string $nonce, string $action): void
    {
        $prefixedAction = $this->prefixAction($action);

        if (!wp_verify_nonce($nonce, $prefixedAction)) {
            wp_die(
                esc_html__('La verification de securite a echoue. Veuillez rafraichir la page et reessayer.', 'theme-associatif'),
                esc_html__('Erreur de securite', 'theme-associatif'),
                ['response' => 403, 'back_link' => true]
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * Genere un champ input hidden avec le nonce et optionnellement
     * un champ referer pour la compatibilite avec check_admin_referer().
     */
    public function field(
        string $action,
        string $fieldName = '_wpnonce',
        bool $referer = true,
        bool $echo = false
    ): string {
        $prefixedAction = $this->prefixAction($action);

        if ($echo) {
            wp_nonce_field($prefixedAction, $fieldName, $referer, true);

            return '';
        }

        return wp_nonce_field($prefixedAction, $fieldName, $referer, false);
    }

    /**
     * {@inheritdoc}
     *
     * Utilise wp_nonce_url() qui ajoute le parametre '_wpnonce' a l'URL.
     */
    public function url(string $url, string $action): string
    {
        $prefixedAction = $this->prefixAction($action);

        return wp_nonce_url($url, $prefixedAction);
    }

    /**
     * {@inheritdoc}
     *
     * Cherche le nonce dans $_POST en priorite, puis dans $_GET.
     * Sanitise le nonce avant verification pour eviter tout risque.
     */
    public function verifyRequest(string $action, string $fieldName = '_wpnonce'): bool
    {
        $nonce = isset($_POST[$fieldName])
            ? sanitize_text_field(wp_unslash((string) $_POST[$fieldName]))
            : (isset($_GET[$fieldName])
                ? sanitize_text_field(wp_unslash((string) $_GET[$fieldName]))
                : '');

        if (empty($nonce)) {
            return false;
        }

        return $this->verify($nonce, $action) !== false;
    }

    /**
     * Prefixe une action avec l'identifiant du theme.
     *
     * @param  string $action L'action a prefixer.
     * @return string L'action prefixee.
     */
    private function prefixAction(string $action): string
    {
        if (str_starts_with($action, $this->actionPrefix)) {
            return $action;
        }

        return $this->actionPrefix . $action;
    }
}
