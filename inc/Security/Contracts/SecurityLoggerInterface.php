<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface SecurityLoggerInterface
 *
 * Contrat pour le service de logging des evenements de securite.
 * Trace les tentatives d'acces non autorise, les echecs de validation,
 * les violations CSRF et autres evenements de securite importants.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface SecurityLoggerInterface
{
    /**
     * Niveau de log : information generale.
     */
    public const LEVEL_INFO = 'info';

    /**
     * Niveau de log : avertissement (evenement suspect mais non bloquant).
     */
    public const LEVEL_WARNING = 'warning';

    /**
     * Niveau de log : erreur de securite (acces refuse, token invalide, etc.).
     */
    public const LEVEL_ERROR = 'error';

    /**
     * Niveau de log : evenement critique (attaque detectee, breach potentiel).
     */
    public const LEVEL_CRITICAL = 'critical';

    /**
     * Enregistre un evenement de securite.
     *
     * @param  string               $event   L'identifiant de l'evenement (ex: 'csrf_violation').
     * @param  string               $message Le message descriptif de l'evenement.
     * @param  string               $level   Le niveau de severite (utiliser les constantes LEVEL_*).
     * @param  array<string, mixed> $context Les donnees contextuelles supplementaires.
     * @return void
     */
    public function log(
        string $event,
        string $message,
        string $level = self::LEVEL_INFO,
        array $context = []
    ): void;

    /**
     * Enregistre une tentative d'acces non autorisee.
     *
     * @param  string $capability La capability requise et manquante.
     * @param  string $resource   La ressource a laquelle l'acces a ete tente.
     * @return void
     */
    public function logUnauthorizedAccess(string $capability, string $resource): void;

    /**
     * Enregistre une violation CSRF.
     *
     * @param  string $action    L'action CSRF dont la verification a echoue.
     * @param  string $ipAddress L'adresse IP de la requete.
     * @return void
     */
    public function logCsrfViolation(string $action, string $ipAddress): void;

    /**
     * Enregistre un echec de validation.
     *
     * @param  string               $formName Les erreurs de validation par champ.
     * @param  array<string, mixed> $errors   Les erreurs de validation.
     * @return void
     */
    public function logValidationFailure(string $formName, array $errors): void;

    /**
     * Enregistre une tentative de connexion echouee.
     *
     * @param  string $username  Le nom d'utilisateur utilise.
     * @param  string $ipAddress L'adresse IP de la tentative.
     * @return void
     */
    public function logAuthFailure(string $username, string $ipAddress): void;

    /**
     * Enregistre une modification de permissions.
     *
     * @param  int    $targetUserId L'ID de l'utilisateur dont les permissions ont change.
     * @param  string $change       La description du changement (role ou capability).
     * @param  int    $actorUserId  L'ID de l'utilisateur ayant effectue le changement.
     * @return void
     */
    public function logPermissionChange(int $targetUserId, string $change, int $actorUserId): void;

    /**
     * Retourne les derniers evenements de securite enregistres.
     *
     * @param  int                     $limit  Le nombre maximum d'evenements a retourner.
     * @param  string|null             $level  Filtrer par niveau de severite (optionnel).
     * @return array<int, array<string, mixed>> Les evenements enregistres.
     */
    public function getRecentEvents(int $limit = 50, ?string $level = null): array;
}
