<?php
/**
 * Interface EventSecurityInterface
 *
 * Contrat définissant les vérifications de sécurité liées aux événements :
 * validation des nonces, contrôle des capacités WordPress et assainissement
 * des données d'entrée.
 *
 * Toutes les opérations sensibles (création, modification, inscription,
 * suppression) doivent passer par ce service avant traitement.
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface EventSecurityInterface
 */
interface EventSecurityInterface {

    /**
     * Vérifie qu'un nonce WordPress est valide pour une action donnée.
     *
     * @param string $nonce  Valeur du nonce soumis.
     * @param string $action Identifiant de l'action (ex : 'event_rsvp_action').
     *
     * @return bool
     */
    public function verifyNonce( string $nonce, string $action ): bool;

    /**
     * Vérifie que l'utilisateur courant possède la capacité requise.
     *
     * @param string $capability Capacité WordPress à vérifier (ex : 'edit_events').
     * @param int    $user_id    Identifiant de l'utilisateur (0 = utilisateur courant).
     *
     * @return bool
     */
    public function currentUserCan( string $capability, int $user_id = 0 ): bool;

    /**
     * Assainit et valide les données d'un formulaire d'événement.
     * Retourne les données nettoyées ou lève une exception en cas d'erreur.
     *
     * @param array $raw_data Données brutes issues de $_POST.
     *
     * @return array Données assainies et validées.
     *
     * @throws \InvalidArgumentException Si des données obligatoires sont manquantes ou invalides.
     */
    public function sanitizeEventData( array $raw_data ): array;

    /**
     * Assainit et valide les données d'un formulaire RSVP.
     *
     * @param array $raw_data Données brutes issues de $_POST.
     *
     * @return array Données assainies et validées.
     *
     * @throws \InvalidArgumentException Si des données obligatoires sont manquantes ou invalides.
     */
    public function sanitizeRSVPData( array $raw_data ): array;

    /**
     * Génère un nonce pour une action liée aux événements.
     *
     * @param string $action Identifiant de l'action.
     *
     * @return string Valeur du nonce généré.
     */
    public function createNonce( string $action ): string;

    /**
     * Vérifie que l'utilisateur courant peut gérer un événement spécifique
     * (auteur ou administrateur).
     *
     * @param int $event_id Identifiant de l'événement.
     *
     * @return bool
     */
    public function canManageEvent( int $event_id ): bool;
}
