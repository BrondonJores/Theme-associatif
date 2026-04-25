<?php
/**
 * Interface RSVPInterface
 *
 * Contrat définissant les opérations du système d'inscription (RSVP)
 * aux événements. Le RSVP gère les statuts de participation, les limites
 * de capacité et les tarifs d'inscription.
 *
 * Statuts disponibles :
 *   - 'interested'  : L'utilisateur est intéressé mais non confirmé.
 *   - 'confirmed'   : L'inscription est confirmée.
 *   - 'absent'      : L'utilisateur ne viendra pas.
 *   - 'cancelled'   : L'inscription a été annulée.
 *   - 'waitlisted'  : L'événement est complet, l'utilisateur est en liste d'attente.
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface RSVPInterface
 */
interface RSVPInterface {

    /**
     * Enregistre ou met à jour le RSVP d'un utilisateur pour un événement.
     *
     * @param int    $event_id  Identifiant de l'événement.
     * @param int    $user_id   Identifiant de l'utilisateur WordPress.
     * @param string $status    Statut RSVP ('interested', 'confirmed', 'absent', 'cancelled').
     * @param array  $meta      Données supplémentaires (ex : montant payé, commentaire).
     *
     * @return bool True en cas de succès.
     */
    public function setRSVP( int $event_id, int $user_id, string $status, array $meta = [] ): bool;

    /**
     * Retourne le statut RSVP d'un utilisateur pour un événement donné.
     *
     * @param int $event_id Identifiant de l'événement.
     * @param int $user_id  Identifiant de l'utilisateur.
     *
     * @return string|null Statut RSVP ou null si aucun RSVP enregistré.
     */
    public function getStatus( int $event_id, int $user_id ): ?string;

    /**
     * Retourne tous les participants d'un événement avec leur statut.
     *
     * @param int    $event_id Identifiant de l'événement.
     * @param string $status   Filtre optionnel par statut.
     *
     * @return array Tableau associatif ['user_id' => int, 'status' => string, 'meta' => array][].
     */
    public function getParticipants( int $event_id, string $status = '' ): array;

    /**
     * Retourne le nombre de participants confirmés pour un événement.
     *
     * @param int $event_id Identifiant de l'événement.
     *
     * @return int
     */
    public function getConfirmedCount( int $event_id ): int;

    /**
     * Vérifie si un utilisateur peut encore s'inscrire à un événement
     * (capacité non atteinte ou liste d'attente disponible).
     *
     * @param int $event_id Identifiant de l'événement.
     *
     * @return bool
     */
    public function canRegister( int $event_id ): bool;

    /**
     * Annule le RSVP d'un utilisateur pour un événement.
     *
     * @param int $event_id Identifiant de l'événement.
     * @param int $user_id  Identifiant de l'utilisateur.
     *
     * @return bool
     */
    public function cancel( int $event_id, int $user_id ): bool;

    /**
     * Exporte la liste des participants d'un événement (CSV ou tableau).
     *
     * @param int    $event_id Identifiant de l'événement.
     * @param string $format   Format de sortie : 'array' ou 'csv'.
     *
     * @return array|string
     */
    public function exportParticipants( int $event_id, string $format = 'array' );
}
