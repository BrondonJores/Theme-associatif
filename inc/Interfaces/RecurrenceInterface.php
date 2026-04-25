<?php
/**
 * Interface RecurrenceInterface
 *
 * Contrat pour la gestion des événements récurrents.
 * Définit les opérations de génération, modification et annulation
 * des occurrences d'une série d'événements périodiques.
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface RecurrenceInterface
 */
interface RecurrenceInterface {

    /**
     * Génère les occurrences d'une série récurrente à partir d'une règle.
     *
     * @param int   $parent_event_id Identifiant de l'événement parent (modèle).
     * @param array $rule            Règle de récurrence :
     *                               [
     *                                 'frequency'  => 'daily'|'weekly'|'monthly'|'yearly',
     *                                 'interval'   => int,    // Tous les N jours/semaines...
     *                                 'days'       => int[],  // Jours de la semaine (0=Lun...6=Dim).
     *                                 'end_date'   => string, // Date de fin ISO 8601.
     *                                 'count'      => int,    // Nombre max d'occurrences.
     *                               ]
     *
     * @return int[] Tableau des identifiants des occurrences créées.
     */
    public function generateOccurrences( int $parent_event_id, array $rule ): array;

    /**
     * Modifie une occurrence unique sans altérer les autres.
     *
     * @param int   $occurrence_id Identifiant de l'occurrence à modifier.
     * @param array $data          Données à mettre à jour.
     *
     * @return bool
     */
    public function updateOccurrence( int $occurrence_id, array $data ): bool;

    /**
     * Modifie toutes les occurrences futures d'une série à partir d'une date.
     *
     * @param int    $parent_event_id  Identifiant de l'événement parent.
     * @param string $from_date        Date à partir de laquelle appliquer les modifications (ISO 8601).
     * @param array  $data             Données à mettre à jour.
     *
     * @return int Nombre d'occurrences modifiées.
     */
    public function updateFutureOccurrences( int $parent_event_id, string $from_date, array $data ): int;

    /**
     * Annule une occurrence unique.
     *
     * @param int $occurrence_id Identifiant de l'occurrence.
     *
     * @return bool
     */
    public function cancelOccurrence( int $occurrence_id ): bool;

    /**
     * Supprime toutes les occurrences futures d'une série.
     *
     * @param int    $parent_event_id Identifiant de l'événement parent.
     * @param string $from_date       Date à partir de laquelle supprimer (ISO 8601).
     *
     * @return int Nombre d'occurrences supprimées.
     */
    public function deleteFutureOccurrences( int $parent_event_id, string $from_date ): int;

    /**
     * Retourne les occurrences d'une série sur une période donnée.
     *
     * @param int    $parent_event_id Identifiant de l'événement parent.
     * @param string $date_from       Date de début de la période (ISO 8601).
     * @param string $date_to         Date de fin de la période (ISO 8601).
     *
     * @return EventInterface[]
     */
    public function getOccurrences( int $parent_event_id, string $date_from, string $date_to ): array;
}
