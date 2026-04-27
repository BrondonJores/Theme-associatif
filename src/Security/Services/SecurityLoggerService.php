<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Services;

use ThemeAssociatif\Security\Contracts\SecurityLoggerInterface;

/**
 * Class SecurityLoggerService
 *
 * Implementation du service de logging des evenements de securite.
 * Ecrit les evenements dans deux destinations :
 * - La table WordPress (via la fonction error_log() et les options WP)
 * - Le fichier de log PHP configuré dans php.ini (en mode debug)
 *
 * Les evenements sont stockes dans la table wp_options sous la cle
 * 'ta_security_events' avec une rotation automatique (max 1000 entrees).
 *
 * Note : en production, il est recommande de connecter ce service
 * a un systeme de logging externe (Sentry, Papertrail, etc.)
 * via le filtre 'ta_security_log_event'.
 *
 * @package ThemeAssociatif\Security\Services
 * @since   1.0.0
 */
class SecurityLoggerService implements SecurityLoggerInterface
{
    /**
     * Nom de l'option WordPress pour le stockage des evenements.
     */
    private const OPTION_KEY = 'ta_security_events';

    /**
     * Nombre maximum d'evenements conserves en base de donnees.
     * Les plus anciens sont supprimes automatiquement.
     */
    private const MAX_EVENTS = 1000;

    /**
     * Indique si le mode debug WordPress est actif.
     * En mode debug, les evenements sont aussi ecrits dans le log PHP.
     *
     * @var bool
     */
    private bool $debugMode;

    /**
     * Constructeur du service de logging.
     *
     * @param bool|null $debugMode Forcer le mode debug (null = lire WP_DEBUG).
     */
    public function __construct(?bool $debugMode = null)
    {
        $this->debugMode = $debugMode ?? (defined('WP_DEBUG') && WP_DEBUG);
    }

    /**
     * {@inheritdoc}
     *
     * Chaque evenement est enregistre avec :
     * - Un horodatage ISO 8601
     * - L'adresse IP de la requete
     * - L'ID de l'utilisateur courant
     * - L'URL de la requete
     * - Les donnees contextuelles supplementaires
     */
    public function log(
        string $event,
        string $message,
        string $level = self::LEVEL_INFO,
        array $context = []
    ): void {
        $entry = [
            'timestamp'  => gmdate('c'),
            'event'      => $event,
            'level'      => $level,
            'message'    => $message,
            'user_id'    => get_current_user_id(),
            'ip_address' => $this->getClientIpAddress(),
            'request_uri' => isset($_SERVER['REQUEST_URI'])
                ? sanitize_text_field(wp_unslash((string) $_SERVER['REQUEST_URI']))
                : '',
            'context'    => $context,
        ];

        $this->persistEvent($entry);

        if ($this->debugMode) {
            $this->writeToPhpLog($entry);
        }

        /**
         * Filtre permettant d'envoyer l'evenement a un service externe.
         * Les integrateurs peuvent utiliser ce filtre pour connecter
         * un service de monitoring (Sentry, Datadog, etc.).
         *
         * @param array<string, mixed> $entry Les donnees de l'evenement.
         */
        do_action('ta_security_log_event', $entry);
    }

    /**
     * {@inheritdoc}
     */
    public function logUnauthorizedAccess(string $capability, string $resource): void
    {
        $this->log(
            'unauthorized_access',
            sprintf(
                'Acces refuse a "%s" : capability "%s" manquante.',
                $resource,
                $capability
            ),
            self::LEVEL_WARNING,
            [
                'required_capability' => $capability,
                'resource'            => $resource,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function logCsrfViolation(string $action, string $ipAddress): void
    {
        $this->log(
            'csrf_violation',
            sprintf(
                'Violation CSRF detectee pour l\'action "%s" depuis l\'IP %s.',
                $action,
                $ipAddress
            ),
            self::LEVEL_ERROR,
            [
                'action'     => $action,
                'ip_address' => $ipAddress,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function logValidationFailure(string $formName, array $errors): void
    {
        $this->log(
            'validation_failure',
            sprintf('Echec de validation sur le formulaire "%s".', $formName),
            self::LEVEL_INFO,
            [
                'form'   => $formName,
                'errors' => $errors,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function logAuthFailure(string $username, string $ipAddress): void
    {
        $this->log(
            'auth_failure',
            sprintf(
                'Tentative de connexion echouee pour "%s" depuis %s.',
                $username,
                $ipAddress
            ),
            self::LEVEL_WARNING,
            [
                'username'   => $username,
                'ip_address' => $ipAddress,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function logPermissionChange(int $targetUserId, string $change, int $actorUserId): void
    {
        $this->log(
            'permission_change',
            sprintf(
                'Modification de permissions sur l\'utilisateur #%d par l\'utilisateur #%d : %s',
                $targetUserId,
                $actorUserId,
                $change
            ),
            self::LEVEL_INFO,
            [
                'target_user_id' => $targetUserId,
                'actor_user_id'  => $actorUserId,
                'change'         => $change,
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * Retourne les evenements depuis la base de donnees, du plus recent au plus ancien.
     * Filtre optionnellement par niveau de severite.
     */
    public function getRecentEvents(int $limit = 50, ?string $level = null): array
    {
        $events = get_option(self::OPTION_KEY, []);

        if (!is_array($events)) {
            return [];
        }

        $events = array_reverse($events);

        if ($level !== null) {
            $events = array_filter(
                $events,
                static fn(array $event): bool => $event['level'] === $level
            );
        }

        return array_values(array_slice($events, 0, $limit));
    }

    /**
     * Persiste un evenement dans la base de donnees WordPress.
     * Applique la rotation automatique si le nombre maximum est atteint.
     *
     * @param  array<string, mixed> $entry L'evenement a persister.
     * @return void
     */
    private function persistEvent(array $entry): void
    {
        $events = get_option(self::OPTION_KEY, []);

        if (!is_array($events)) {
            $events = [];
        }

        if (count($events) >= self::MAX_EVENTS) {
            $events = array_slice($events, -(self::MAX_EVENTS - 1));
        }

        $events[] = $entry;

        update_option(self::OPTION_KEY, $events, false);
    }

    /**
     * Ecrit un evenement dans le fichier de log PHP (mode debug uniquement).
     * Formate le message de maniere lisible pour le debogage.
     *
     * @param  array<string, mixed> $entry L'evenement a journaliser.
     * @return void
     */
    private function writeToPhpLog(array $entry): void
    {
        $message = sprintf(
            '[Theme-Associatif Security] [%s] [%s] %s | User: %d | IP: %s | URI: %s',
            strtoupper((string) $entry['level']),
            $entry['event'],
            $entry['message'],
            $entry['user_id'],
            $entry['ip_address'],
            $entry['request_uri']
        );

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($message);
    }

    /**
     * Recupere l'adresse IP du client de maniere securisee.
     *
     * @return string L'adresse IP ou 'unknown'.
     */
    private function getClientIpAddress(): string
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        if (!is_string($ipAddress) || !filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return 'unknown';
        }

        return $ipAddress;
    }
}
