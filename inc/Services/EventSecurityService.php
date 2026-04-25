<?php
/**
 * Classe EventSecurityService
 *
 * Service de sécurité centralisant la validation des nonces,
 * la vérification des permissions et l'assainissement des données
 * liées aux événements et au système RSVP.
 *
 * Respecte le principe de responsabilité unique (SRP) :
 * toute la logique de sécurité est regroupée ici afin que les autres
 * services n'aient pas à la dupliquer.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Interfaces\EventSecurityInterface;

/**
 * Classe EventSecurityService
 */
class EventSecurityService implements EventSecurityInterface {

    /**
     * Identifiants d'actions de nonce utilisés dans le thème.
     */
    const NONCE_RSVP          = 'event_rsvp_action';
    const NONCE_EVENT_MANAGE  = 'event_manage_action';
    const NONCE_CALENDAR_AJAX = 'event_calendar_ajax';

    /**
     * Statuts RSVP autorisés.
     *
     * @var string[]
     */
    private array $allowed_statuses = [ 'interested', 'confirmed', 'absent', 'cancelled' ];

    /**
     * Types de tarifs autorisés.
     *
     * @var string[]
     */
    private array $allowed_pricing_types = [ 'free', 'paid', 'free_price' ];

    /**
     * {@inheritdoc}
     */
    public function verifyNonce( string $nonce, string $action ): bool {
        return (bool) wp_verify_nonce( $nonce, $action );
    }

    /**
     * {@inheritdoc}
     */
    public function currentUserCan( string $capability, int $user_id = 0 ): bool {
        if ( $user_id > 0 ) {
            return user_can( $user_id, $capability );
        }
        return current_user_can( $capability );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizeEventData( array $raw_data ): array {
        $errors = [];

        // Titre obligatoire.
        if ( empty( $raw_data['title'] ) ) {
            $errors[] = __( 'Le titre de l\'événement est obligatoire.', 'theme-associatif' );
        }

        // Date de début obligatoire et valide.
        if ( empty( $raw_data['start_date'] ) || strtotime( $raw_data['start_date'] ) === false ) {
            $errors[] = __( 'La date de début est obligatoire et doit être valide.', 'theme-associatif' );
        }

        // Date de fin obligatoire, valide et postérieure à la date de début.
        if ( empty( $raw_data['end_date'] ) || strtotime( $raw_data['end_date'] ) === false ) {
            $errors[] = __( 'La date de fin est obligatoire et doit être valide.', 'theme-associatif' );
        } elseif ( ! empty( $raw_data['start_date'] ) && strtotime( $raw_data['end_date'] ) < strtotime( $raw_data['start_date'] ) ) {
            $errors[] = __( 'La date de fin doit être postérieure à la date de début.', 'theme-associatif' );
        }

        if ( ! empty( $errors ) ) {
            throw new \InvalidArgumentException( implode( ' ', $errors ) );
        }

        $pricing_type = sanitize_text_field( $raw_data['pricing_type'] ?? 'free' );
        if ( ! in_array( $pricing_type, $this->allowed_pricing_types, true ) ) {
            $pricing_type = 'free';
        }

        return [
            'title'        => sanitize_text_field( wp_unslash( $raw_data['title'] ) ),
            'description'  => wp_kses_post( wp_unslash( $raw_data['description'] ?? '' ) ),
            'start_date'   => date( 'Y-m-d H:i:s', strtotime( $raw_data['start_date'] ) ),
            'end_date'     => date( 'Y-m-d H:i:s', strtotime( $raw_data['end_date'] ) ),
            'location'     => sanitize_text_field( wp_unslash( $raw_data['location'] ?? '' ) ),
            'capacity'     => absint( $raw_data['capacity'] ?? 0 ),
            'pricing_type' => $pricing_type,
            'price_amount' => absint( $raw_data['price_amount'] ?? 0 ),
            'category_ids' => array_map( 'absint', (array) ( $raw_data['category_ids'] ?? [] ) ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function sanitizeRSVPData( array $raw_data ): array {
        $errors = [];

        if ( empty( $raw_data['event_id'] ) || ! is_numeric( $raw_data['event_id'] ) ) {
            $errors[] = __( 'Identifiant d\'événement invalide.', 'theme-associatif' );
        }

        $status = sanitize_text_field( $raw_data['status'] ?? '' );
        if ( ! in_array( $status, $this->allowed_statuses, true ) ) {
            $errors[] = __( 'Statut RSVP invalide.', 'theme-associatif' );
        }

        if ( ! empty( $errors ) ) {
            throw new \InvalidArgumentException( implode( ' ', $errors ) );
        }

        return [
            'event_id'         => absint( $raw_data['event_id'] ),
            'status'           => $status,
            'paid_amount'      => absint( $raw_data['paid_amount'] ?? 0 ),
            'comment'          => sanitize_textarea_field( wp_unslash( $raw_data['comment'] ?? '' ) ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createNonce( string $action ): string {
        return wp_create_nonce( $action );
    }

    /**
     * {@inheritdoc}
     */
    public function canManageEvent( int $event_id ): bool {
        // Les administrateurs et éditeurs peuvent gérer tous les événements.
        if ( current_user_can( 'edit_others_posts' ) ) {
            return true;
        }

        // L'auteur peut gérer son propre événement.
        $post = get_post( $event_id );
        if ( $post && (int) $post->post_author === get_current_user_id() ) {
            return current_user_can( 'edit_post', $event_id );
        }

        return false;
    }
}
