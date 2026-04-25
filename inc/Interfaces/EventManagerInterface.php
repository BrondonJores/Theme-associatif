<?php
/**
 * Interface EventManagerInterface
 *
 * Contrat définissant les opérations de gestion (CRUD) des événements.
 * Tout service gérant des événements doit implémenter cette interface,
 * ce qui permet de substituer l'implémentation sans modifier les classes
 * consommatrices (principe de substitution de Liskov et d'inversion de
 * dépendances de SOLID).
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface EventManagerInterface
 */
interface EventManagerInterface {

    /**
     * Crée un nouvel événement à partir des données fournies.
     *
     * @param array $data Données de l'événement (title, description, start_date, etc.).
     *
     * @return int|false Identifiant WordPress du post créé, ou false en cas d'erreur.
     */
    public function create( array $data );

    /**
     * Met à jour un événement existant.
     *
     * @param int   $event_id Identifiant WordPress du post événement.
     * @param array $data     Données à mettre à jour.
     *
     * @return bool True en cas de succès, false sinon.
     */
    public function update( int $event_id, array $data ): bool;

    /**
     * Supprime un événement (mise à la corbeille).
     *
     * @param int $event_id Identifiant WordPress du post événement.
     *
     * @return bool True en cas de succès, false sinon.
     */
    public function delete( int $event_id ): bool;

    /**
     * Récupère un événement par son identifiant.
     *
     * @param int $event_id Identifiant WordPress du post événement.
     *
     * @return EventInterface|null L'objet événement, ou null s'il n'existe pas.
     */
    public function getById( int $event_id ): ?EventInterface;

    /**
     * Retourne une liste paginée d'événements selon des critères.
     *
     * @param array $args Arguments de filtrage (category, status, date_from, date_to, etc.).
     *
     * @return EventInterface[] Tableau d'objets événements.
     */
    public function getList( array $args = [] ): array;

    /**
     * Retourne les événements à venir.
     *
     * @param int $limit Nombre maximum d'événements à retourner.
     *
     * @return EventInterface[]
     */
    public function getUpcoming( int $limit = 10 ): array;

    /**
     * Retourne les événements passés (archive).
     *
     * @param array $args Arguments de filtrage et pagination.
     *
     * @return EventInterface[]
     */
    public function getPast( array $args = [] ): array;

    /**
     * Retourne les événements d'un mois donné pour le calendrier.
     *
     * @param int $year  Année (ex : 2025).
     * @param int $month Mois de 1 à 12.
     *
     * @return EventInterface[]
     */
    public function getByMonth( int $year, int $month ): array;
}
