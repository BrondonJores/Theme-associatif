<?php
/**
 * Classe EventManager
 *
 * Service principal de gestion des événements.
 * Centralise toutes les opérations CRUD sur le Custom Post Type 'event'
 * et les métadonnées associées.
 *
 * Respecte le principe d'inversion de dépendances (DIP) :
 * ce service dépend de l'interface EventSecurityInterface et non
 * de l'implémentation concrète, ce qui facilite les tests unitaires.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Interfaces\EventInterface;
use ThemeAssociatif\Interfaces\EventManagerInterface;
use ThemeAssociatif\Interfaces\EventSecurityInterface;
use ThemeAssociatif\Models\Event;
use ThemeAssociatif\PostTypes\EventPostType;
use ThemeAssociatif\Taxonomies\EventCategoryTaxonomy;

/**
 * Classe EventManager
 */
class EventManager implements EventManagerInterface {

    /**
     * Service de sécurité injecté.
     *
     * @var EventSecurityInterface
     */
    private EventSecurityInterface $security;

    /**
     * Constructeur.
     *
     * @param EventSecurityInterface $security Service de sécurité pour la validation des données.
     */
    public function __construct( EventSecurityInterface $security ) {
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     *
     * Crée un post WordPress de type 'event' et enregistre les métadonnées.
     * Lance un hook 'theme_associatif/event/created' après la création.
     */
    public function create( array $data ) {
        // Assainissement et validation des données via le service de sécurité.
        $clean = $this->security->sanitizeEventData( $data );

        $post_id = wp_insert_post(
            [
                'post_type'    => EventPostType::POST_TYPE,
                'post_title'   => $clean['title'],
                'post_content' => $clean['description'],
                'post_status'  => sanitize_text_field( $data['status'] ?? 'publish' ),
                'post_author'  => get_current_user_id(),
            ],
            true
        );

        if ( is_wp_error( $post_id ) ) {
            return false;
        }

        $this->saveMeta( $post_id, $clean );

        // Association aux catégories si fournies.
        if ( ! empty( $clean['category_ids'] ) ) {
            wp_set_object_terms( $post_id, $clean['category_ids'], EventCategoryTaxonomy::TAXONOMY );
        }

        /**
         * Déclenchement d'un hook personnalisé permettant aux extensions
         * du thème de réagir à la création d'un événement.
         *
         * @param int   $post_id Identifiant du post créé.
         * @param array $clean   Données assainies de l'événement.
         */
        do_action( 'theme_associatif/event/created', $post_id, $clean );

        return $post_id;
    }

    /**
     * {@inheritdoc}
     */
    public function update( int $event_id, array $data ): bool {
        if ( ! $this->security->canManageEvent( $event_id ) ) {
            return false;
        }

        $clean = $this->security->sanitizeEventData( $data );

        $result = wp_update_post(
            [
                'ID'           => $event_id,
                'post_title'   => $clean['title'],
                'post_content' => $clean['description'],
                'post_status'  => sanitize_text_field( $data['status'] ?? 'publish' ),
            ],
            true
        );

        if ( is_wp_error( $result ) ) {
            return false;
        }

        $this->saveMeta( $event_id, $clean );

        if ( isset( $clean['category_ids'] ) ) {
            wp_set_object_terms( $event_id, $clean['category_ids'], EventCategoryTaxonomy::TAXONOMY );
        }

        /**
         * Hook déclenché après la mise à jour d'un événement.
         *
         * @param int   $event_id Identifiant de l'événement.
         * @param array $clean    Données assainies.
         */
        do_action( 'theme_associatif/event/updated', $event_id, $clean );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function delete( int $event_id ): bool {
        if ( ! $this->security->canManageEvent( $event_id ) ) {
            return false;
        }

        $result = wp_trash_post( $event_id );

        if ( $result === null || $result === false ) {
            return false;
        }

        /**
         * Hook déclenché après la mise à la corbeille d'un événement.
         *
         * @param int $event_id Identifiant de l'événement supprimé.
         */
        do_action( 'theme_associatif/event/deleted', $event_id );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getById( int $event_id ): ?EventInterface {
        $post = get_post( $event_id );

        if ( ! $post || $post->post_type !== EventPostType::POST_TYPE ) {
            return null;
        }

        return $this->buildFromPost( $post );
    }

    /**
     * {@inheritdoc}
     */
    public function getList( array $args = [] ): array {
        $query_args = $this->buildQueryArgs( $args );
        return $this->runQuery( $query_args );
    }

    /**
     * {@inheritdoc}
     */
    public function getUpcoming( int $limit = 10 ): array {
        $now = current_time( 'Y-m-d H:i:s' );

        $query_args = [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'meta_key'       => '_event_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_event_start_date',
                    'value'   => $now,
                    'compare' => '>=',
                    'type'    => 'DATETIME',
                ],
            ],
        ];

        return $this->runQuery( $query_args );
    }

    /**
     * {@inheritdoc}
     */
    public function getPast( array $args = [] ): array {
        $now = current_time( 'Y-m-d H:i:s' );

        $base_args = [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => (int) ( $args['per_page'] ?? 12 ),
            'paged'          => (int) ( $args['paged'] ?? 1 ),
            'meta_key'       => '_event_end_date',
            'orderby'        => 'meta_value',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'     => '_event_end_date',
                    'value'   => $now,
                    'compare' => '<',
                    'type'    => 'DATETIME',
                ],
            ],
        ];

        // Fusion avec les filtres de catégorie ou de recherche si fournis.
        if ( ! empty( $args['category'] ) ) {
            $base_args['tax_query'] = [
                [
                    'taxonomy' => EventCategoryTaxonomy::TAXONOMY,
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $args['category'] ),
                ],
            ];
        }

        return $this->runQuery( $base_args );
    }

    /**
     * {@inheritdoc}
     */
    public function getByMonth( int $year, int $month ): array {
        // Calcul des bornes du mois demandé.
        $month_start = sprintf( '%04d-%02d-01 00:00:00', $year, $month );
        $month_end   = sprintf( '%04d-%02d-%02d 23:59:59', $year, $month, cal_days_in_month( CAL_GREGORIAN, $month, $year ) );

        $query_args = [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_event_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => [
                [
                    'key'     => '_event_start_date',
                    'value'   => [ $month_start, $month_end ],
                    'compare' => 'BETWEEN',
                    'type'    => 'DATETIME',
                ],
            ],
        ];

        return $this->runQuery( $query_args );
    }

    /**
     * Construit les arguments de WP_Query à partir de filtres utilisateur.
     *
     * @param array $args Filtres : category, date_from, date_to, search, per_page, paged.
     *
     * @return array Arguments WP_Query.
     */
    private function buildQueryArgs( array $args ): array {
        $query_args = [
            'post_type'      => EventPostType::POST_TYPE,
            'post_status'    => sanitize_text_field( $args['status'] ?? 'publish' ),
            'posts_per_page' => (int) ( $args['per_page'] ?? 12 ),
            'paged'          => (int) ( $args['paged'] ?? 1 ),
            'meta_key'       => '_event_start_date',
            'orderby'        => 'meta_value',
            'order'          => sanitize_text_field( $args['order'] ?? 'ASC' ),
        ];

        $meta_query = [];

        // Filtre par date de début minimum.
        if ( ! empty( $args['date_from'] ) ) {
            $meta_query[] = [
                'key'     => '_event_start_date',
                'value'   => date( 'Y-m-d H:i:s', strtotime( $args['date_from'] ) ),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ];
        }

        // Filtre par date de fin maximum.
        if ( ! empty( $args['date_to'] ) ) {
            $meta_query[] = [
                'key'     => '_event_start_date',
                'value'   => date( 'Y-m-d H:i:s', strtotime( $args['date_to'] ) ),
                'compare' => '<=',
                'type'    => 'DATETIME',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        // Filtre par catégorie.
        if ( ! empty( $args['category'] ) ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => EventCategoryTaxonomy::TAXONOMY,
                    'field'    => is_numeric( $args['category'] ) ? 'term_id' : 'slug',
                    'terms'    => is_numeric( $args['category'] ) ? (int) $args['category'] : sanitize_text_field( $args['category'] ),
                ],
            ];
        }

        // Filtre par recherche textuelle.
        if ( ! empty( $args['search'] ) ) {
            $query_args['s'] = sanitize_text_field( $args['search'] );
        }

        return $query_args;
    }

    /**
     * Exécute une WP_Query et retourne un tableau d'objets EventInterface.
     *
     * @param array $query_args Arguments WP_Query.
     *
     * @return EventInterface[]
     */
    private function runQuery( array $query_args ): array {
        $query  = new \WP_Query( $query_args );
        $events = [];

        foreach ( $query->posts as $post ) {
            $events[] = $this->buildFromPost( $post );
        }

        wp_reset_postdata();

        return $events;
    }

    /**
     * Construit un objet Event à partir d'un post WordPress.
     *
     * @param \WP_Post $post Post WordPress de type 'event'.
     *
     * @return Event
     */
    private function buildFromPost( \WP_Post $post ): Event {
        $meta     = get_post_meta( $post->ID );
        $term_ids = wp_get_post_terms( $post->ID, EventCategoryTaxonomy::TAXONOMY, [ 'fields' => 'ids' ] );
        $term_ids = is_wp_error( $term_ids ) ? [] : $term_ids;

        return new Event( $post, $meta, $term_ids );
    }

    /**
     * Enregistre les métadonnées d'un événement dans la base de données.
     *
     * @param int   $post_id Identifiant du post.
     * @param array $data    Données assainies.
     *
     * @return void
     */
    private function saveMeta( int $post_id, array $data ): void {
        $meta_map = [
            '_event_start_date'   => $data['start_date']   ?? '',
            '_event_end_date'     => $data['end_date']     ?? '',
            '_event_location'     => $data['location']     ?? '',
            '_event_capacity'     => $data['capacity']     ?? 0,
            '_event_pricing_type' => $data['pricing_type'] ?? 'free',
            '_event_price_amount' => $data['price_amount'] ?? 0,
        ];

        foreach ( $meta_map as $key => $value ) {
            update_post_meta( $post_id, $key, $value );
        }
    }
}
