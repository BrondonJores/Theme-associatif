<?php
/**
 * Classe RecurrenceService
 *
 * Service de gestion des événements récurrents.
 * Génère, modifie et supprime les occurrences d'une série d'événements
 * à partir d'une règle de récurrence (fréquence, intervalle, date de fin).
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Interfaces\RecurrenceInterface;
use ThemeAssociatif\Interfaces\EventInterface;
use ThemeAssociatif\PostTypes\EventPostType;

/**
 * Classe RecurrenceService
 */
class RecurrenceService implements RecurrenceInterface {

    /**
     * Nombre maximum d'occurrences pouvant être générées en une fois.
     * Protège contre les règles générant des milliers d'événements.
     *
     * @var int
     */
    const MAX_OCCURRENCES = 365;

    /**
     * {@inheritdoc}
     *
     * Génère les occurrences à partir de la date de début du post parent.
     * Chaque occurrence est un post WordPress distinct avec un lien vers le parent.
     */
    public function generateOccurrences( int $parent_event_id, array $rule ): array {
        $parent = get_post( $parent_event_id );

        if ( ! $parent || $parent->post_type !== EventPostType::POST_TYPE ) {
            return [];
        }

        $start_date_str = get_post_meta( $parent_event_id, '_event_start_date', true );
        $end_date_str   = get_post_meta( $parent_event_id, '_event_end_date', true );

        if ( ! $start_date_str ) {
            return [];
        }

        // Calcul de la durée de l'événement pour l'appliquer à chaque occurrence.
        $start_ts    = strtotime( $start_date_str );
        $end_ts      = $end_date_str ? strtotime( $end_date_str ) : $start_ts + HOUR_IN_SECONDS;
        $duration    = $end_ts - $start_ts;

        $frequency  = sanitize_text_field( $rule['frequency'] ?? 'weekly' );
        $interval   = max( 1, (int) ( $rule['interval'] ?? 1 ) );
        $end_date   = $rule['end_date'] ?? '';
        $max_count  = min( self::MAX_OCCURRENCES, (int) ( $rule['count'] ?? self::MAX_OCCURRENCES ) );
        $end_limit  = $end_date ? strtotime( $end_date ) : false;

        $created_ids = [];
        $current_ts  = $start_ts;
        $count       = 0;

        // Marquage du parent comme modèle de série.
        update_post_meta( $parent_event_id, '_event_is_recurring', '1' );
        update_post_meta( $parent_event_id, '_event_recurrence_rule', maybe_serialize( $rule ) );

        while ( true ) {
            // Avancement à la prochaine occurrence.
            $current_ts = $this->advance( $current_ts, $frequency, $interval );

            // Conditions d'arrêt.
            if ( $end_limit && $current_ts > $end_limit ) {
                break;
            }
            if ( $count >= $max_count ) {
                break;
            }

            $occurrence_start = date( 'Y-m-d H:i:s', $current_ts );
            $occurrence_end   = date( 'Y-m-d H:i:s', $current_ts + $duration );

            // Création du post occurrence en dupliquant le parent.
            $occurrence_id = wp_insert_post( [
                'post_type'    => EventPostType::POST_TYPE,
                'post_title'   => $parent->post_title,
                'post_content' => $parent->post_content,
                'post_status'  => $parent->post_status,
                'post_author'  => $parent->post_author,
            ] );

            if ( is_wp_error( $occurrence_id ) ) {
                continue;
            }

            // Copie des métadonnées du parent, puis écrasement des dates.
            $parent_meta = get_post_meta( $parent_event_id );
            foreach ( $parent_meta as $key => $values ) {
                update_post_meta( $occurrence_id, $key, $values[0] );
            }

            update_post_meta( $occurrence_id, '_event_start_date', $occurrence_start );
            update_post_meta( $occurrence_id, '_event_end_date', $occurrence_end );
            update_post_meta( $occurrence_id, '_event_recurrence_parent_id', $parent_event_id );

            // Copie des termes de taxonomie.
            $terms = wp_get_post_terms( $parent_event_id, 'event_category', [ 'fields' => 'ids' ] );
            if ( ! is_wp_error( $terms ) && $terms ) {
                wp_set_object_terms( $occurrence_id, $terms, 'event_category' );
            }

            $created_ids[] = $occurrence_id;
            $count++;
        }

        /**
         * Hook déclenché après la génération des occurrences d'une série.
         *
         * @param int   $parent_event_id Identifiant de l'événement parent.
         * @param int[] $created_ids     Identifiants des occurrences créées.
         * @param array $rule            Règle de récurrence appliquée.
         */
        do_action( 'theme_associatif/recurrence/generated', $parent_event_id, $created_ids, $rule );

        return $created_ids;
    }

    /**
     * {@inheritdoc}
     */
    public function updateOccurrence( int $occurrence_id, array $data ): bool {
        $post = get_post( $occurrence_id );

        if ( ! $post || $post->post_type !== EventPostType::POST_TYPE ) {
            return false;
        }

        $update_data = [ 'ID' => $occurrence_id ];

        if ( isset( $data['title'] ) ) {
            $update_data['post_title'] = sanitize_text_field( $data['title'] );
        }
        if ( isset( $data['description'] ) ) {
            $update_data['post_content'] = wp_kses_post( $data['description'] );
        }

        if ( count( $update_data ) > 1 ) {
            $result = wp_update_post( $update_data, true );
            if ( is_wp_error( $result ) ) {
                return false;
            }
        }

        // Mise à jour des métadonnées de date si présentes.
        if ( ! empty( $data['start_date'] ) ) {
            update_post_meta( $occurrence_id, '_event_start_date', date( 'Y-m-d H:i:s', strtotime( $data['start_date'] ) ) );
        }
        if ( ! empty( $data['end_date'] ) ) {
            update_post_meta( $occurrence_id, '_event_end_date', date( 'Y-m-d H:i:s', strtotime( $data['end_date'] ) ) );
        }
        if ( isset( $data['location'] ) ) {
            update_post_meta( $occurrence_id, '_event_location', sanitize_text_field( $data['location'] ) );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateFutureOccurrences( int $parent_event_id, string $from_date, array $data ): int {
        $from_ts      = strtotime( $from_date );
        $occurrences  = $this->getFutureOccurrenceIds( $parent_event_id, $from_ts );
        $updated      = 0;

        foreach ( $occurrences as $occurrence_id ) {
            if ( $this->updateOccurrence( $occurrence_id, $data ) ) {
                $updated++;
            }
        }

        return $updated;
    }

    /**
     * {@inheritdoc}
     */
    public function cancelOccurrence( int $occurrence_id ): bool {
        $result = wp_update_post( [
            'ID'          => $occurrence_id,
            'post_status' => 'cancelled',
        ] );

        return ! is_wp_error( $result );
    }

    /**
     * {@inheritdoc}
     */
    public function deleteFutureOccurrences( int $parent_event_id, string $from_date ): int {
        $from_ts     = strtotime( $from_date );
        $occurrences = $this->getFutureOccurrenceIds( $parent_event_id, $from_ts );
        $deleted     = 0;

        foreach ( $occurrences as $occurrence_id ) {
            if ( wp_trash_post( $occurrence_id ) ) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function getOccurrences( int $parent_event_id, string $date_from, string $date_to ): array {
        $query = new \WP_Query( [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => [ 'publish', 'private' ],
            'posts_per_page' => -1,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => '_event_recurrence_parent_id',
                    'value' => $parent_event_id,
                    'type'  => 'NUMERIC',
                ],
                [
                    'key'     => '_event_start_date',
                    'value'   => [
                        date( 'Y-m-d H:i:s', strtotime( $date_from ) ),
                        date( 'Y-m-d H:i:s', strtotime( $date_to ) ),
                    ],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ],
            ],
            'orderby'        => 'meta_value',
            'meta_key'       => '_event_start_date',
            'order'          => 'ASC',
        ] );

        $events = [];
        foreach ( $query->posts as $post ) {
            $meta     = get_post_meta( $post->ID );
            $term_ids = wp_get_post_terms( $post->ID, 'event_category', [ 'fields' => 'ids' ] );
            $term_ids = is_wp_error( $term_ids ) ? [] : $term_ids;

            $events[] = new \ThemeAssociatif\Models\Event( $post, $meta, $term_ids );
        }

        wp_reset_postdata();

        return $events;
    }

    /**
     * Calcule le prochain timestamp selon la fréquence et l'intervalle.
     *
     * @param int    $current_ts Timestamp actuel.
     * @param string $frequency  Fréquence : 'daily', 'weekly', 'monthly', 'yearly'.
     * @param int    $interval   Nombre d'unités de temps.
     *
     * @return int Nouveau timestamp.
     */
    private function advance( int $current_ts, string $frequency, int $interval ): int {
        $date = new \DateTime( '@' . $current_ts );
        $date->setTimezone( wp_timezone() );

        switch ( $frequency ) {
            case 'daily':
                $date->modify( "+{$interval} day" );
                break;
            case 'weekly':
                $date->modify( "+{$interval} week" );
                break;
            case 'monthly':
                $date->modify( "+{$interval} month" );
                break;
            case 'yearly':
                $date->modify( "+{$interval} year" );
                break;
            default:
                $date->modify( '+1 week' );
        }

        return $date->getTimestamp();
    }

    /**
     * Retourne les identifiants des occurrences futures d'une série.
     *
     * @param int $parent_event_id Identifiant de l'événement parent.
     * @param int $from_ts         Timestamp minimum (inclus) pour la date de début.
     *
     * @return int[]
     */
    private function getFutureOccurrenceIds( int $parent_event_id, int $from_ts ): array {
        $query = new \WP_Query( [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'   => '_event_recurrence_parent_id',
                    'value' => $parent_event_id,
                    'type'  => 'NUMERIC',
                ],
                [
                    'key'     => '_event_start_date',
                    'value'   => date( 'Y-m-d H:i:s', $from_ts ),
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ],
            ],
        ] );

        return $query->posts ? array_map( 'intval', $query->posts ) : [];
    }
}
