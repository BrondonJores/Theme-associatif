<?php
/**
 * Interface NotificationInterface
 *
 * Contrat pour l'envoi de notifications aux participants d'un événement.
 * Respecte le principe ouvert/fermé (OCP) : de nouveaux canaux de notification
 * (SMS, push, etc.) peuvent être ajoutés sans modifier les classes existantes.
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface NotificationInterface
 */
interface NotificationInterface {

    /**
     * Envoie une notification de confirmation d'inscription à un participant.
     *
     * @param int $event_id Identifiant de l'événement.
     * @param int $user_id  Identifiant de l'utilisateur qui vient de s'inscrire.
     *
     * @return bool True si la notification a bien été envoyée.
     */
    public function sendConfirmation( int $event_id, int $user_id ): bool;

    /**
     * Envoie une notification d'annulation d'événement à tous les participants.
     *
     * @param int    $event_id Identifiant de l'événement annulé.
     * @param string $reason   Motif optionnel de l'annulation.
     *
     * @return int Nombre de notifications envoyées avec succès.
     */
    public function sendCancellation( int $event_id, string $reason = '' ): int;

    /**
     * Envoie un rappel avant un événement aux participants confirmés.
     *
     * @param int $event_id        Identifiant de l'événement.
     * @param int $hours_before    Nombre d'heures avant le début de l'événement.
     *
     * @return int Nombre de notifications envoyées.
     */
    public function sendReminder( int $event_id, int $hours_before = 24 ): int;

    /**
     * Envoie une notification de mise à jour d'un événement aux participants.
     *
     * @param int   $event_id       Identifiant de l'événement.
     * @param array $changed_fields Liste des champs modifiés.
     *
     * @return int Nombre de notifications envoyées.
     */
    public function sendUpdate( int $event_id, array $changed_fields = [] ): int;

    /**
     * Notifie un utilisateur qu'une place s'est libérée sur liste d'attente.
     *
     * @param int $event_id Identifiant de l'événement.
     * @param int $user_id  Identifiant de l'utilisateur en liste d'attente.
     *
     * @return bool
     */
    public function sendWaitlistAvailability( int $event_id, int $user_id ): bool;
}
