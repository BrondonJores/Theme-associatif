<?php
/**
 * Classe EventArchive
 *
 * Gère la page d'archive des événements avec filtrage par catégorie,
 * par date et par recherche textuelle.
 * Enregistre également le traitement des formulaires RSVP via AJAX.
 *
 * @package ThemeAssociatif\Frontend
 * @since   1.0.0
 */

namespace ThemeAssociatif\Frontend;

use ThemeAssociatif\Interfaces\EventManagerInterface;
use ThemeAssociatif\Interfaces\RSVPInterface;
use ThemeAssociatif\Services\EventSecurityService;

/**
 * Classe EventArchive
 */
class EventArchive {

    /**
     * Gestionnaire d'événements injecté.
     *
     * @var EventManagerInterface
     */
    private EventManagerInterface $event_manager;

    /**
     * Gestionnaire RSVP injecté.
     *
     * @var RSVPInterface
     */
    private RSVPInterface $rsvp_manager;

    /**
     * Service de sécurité injecté.
     *
     * @var EventSecurityService
     */
    private EventSecurityService $security;

    /**
     * Constructeur.
     *
     * @param EventManagerInterface $event_manager Gestionnaire d'événements.
     * @param RSVPInterface         $rsvp_manager  Gestionnaire RSVP.
     * @param EventSecurityService  $security      Service de sécurité.
     */
    public function __construct(
        EventManagerInterface $event_manager,
        RSVPInterface $rsvp_manager,
        EventSecurityService $security
    ) {
        $this->event_manager = $event_manager;
        $this->rsvp_manager  = $rsvp_manager;
        $this->security      = $security;
    }

    /**
     * Attache les hooks WordPress nécessaires.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'wp_enqueue_scripts',            [ $this, 'enqueueAssets' ] );
        add_action( 'wp_ajax_event_rsvp',            [ $this, 'handleRSVP' ] );
        add_action( 'wp_ajax_nopriv_event_rsvp',     [ $this, 'handleRSVPUnauthenticated' ] );
        add_action( 'pre_get_posts',                 [ $this, 'modifyArchiveQuery' ] );
        add_filter( 'template_include',              [ $this, 'filterTemplates' ] );
    }

    /**
     * Charge les assets CSS/JS des événements sur les pages concernées.
     *
     * @return void
     */
    public function enqueueAssets(): void {
        if ( ! is_post_type_archive( 'event' ) && ! is_singular( 'event' ) ) {
            return;
        }

        wp_enqueue_style(
            'theme-events',
            get_template_directory_uri() . '/assets/css/events/events.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'theme-events-rsvp',
            get_template_directory_uri() . '/assets/js/events/rsvp.js',
            [],
            '1.0.0',
            true
        );

        wp_localize_script(
            'theme-events-rsvp',
            'eventRSVPData',
            [
                'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => $this->security->createNonce( EventSecurityService::NONCE_RSVP ),
                'isLoggedIn' => is_user_logged_in(),
                'loginUrl'   => wp_login_url( get_permalink() ),
                'i18n'       => [
                    'confirm'     => __( 'Je participe', 'theme-associatif' ),
                    'interested'  => __( 'Intéressé(e)', 'theme-associatif' ),
                    'absent'      => __( 'Absent(e)', 'theme-associatif' ),
                    'cancelled'   => __( 'Annuler mon inscription', 'theme-associatif' ),
                    'waitlisted'  => __( 'Liste d\'attente', 'theme-associatif' ),
                    'loginNeeded' => __( 'Vous devez être connecté pour vous inscrire.', 'theme-associatif' ),
                    'error'       => __( 'Une erreur est survenue. Veuillez réessayer.', 'theme-associatif' ),
                    'full'        => __( 'L\'événement est complet.', 'theme-associatif' ),
                ],
            ]
        );
    }

    /**
     * Gère la requête AJAX de RSVP pour les utilisateurs connectés.
     *
     * @return void
     */
    public function handleRSVP(): void {
        if ( ! is_user_logged_in() ) {
            wp_send_json_error( __( 'Vous devez être connecté.', 'theme-associatif' ), 401 );
        }

        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! $this->security->verifyNonce( $nonce, EventSecurityService::NONCE_RSVP ) ) {
            wp_send_json_error( __( 'Nonce invalide.', 'theme-associatif' ), 403 );
        }

        try {
            $data     = $this->security->sanitizeRSVPData( $_POST );
            $user_id  = get_current_user_id();
            $result   = $this->rsvp_manager->setRSVP( $data['event_id'], $user_id, $data['status'], $data );

            if ( ! $result ) {
                wp_send_json_error( __( 'Impossible d\'enregistrer votre réponse.', 'theme-associatif' ), 500 );
            }

            // Récupération du nouveau statut et du nombre de places restantes.
            $new_status = $this->rsvp_manager->getStatus( $data['event_id'], $user_id );
            $capacity   = (int) get_post_meta( $data['event_id'], '_event_capacity', true );
            $confirmed  = $this->rsvp_manager->getConfirmedCount( $data['event_id'] );
            $remaining  = $capacity > 0 ? max( 0, $capacity - $confirmed ) : -1;

            wp_send_json_success( [
                'status'    => $new_status,
                'remaining' => $remaining,
                'message'   => $this->getStatusLabel( $new_status ),
            ] );
        } catch ( \InvalidArgumentException $e ) {
            wp_send_json_error( esc_html( $e->getMessage() ), 400 );
        }
    }

    /**
     * Redirige les utilisateurs non connectés vers la page de connexion
     * lors d'une tentative de RSVP.
     *
     * @return void
     */
    public function handleRSVPUnauthenticated(): void {
        wp_send_json_error(
            [
                'message'  => __( 'Vous devez être connecté pour vous inscrire.', 'theme-associatif' ),
                'login_url' => wp_login_url(),
            ],
            401
        );
    }

    /**
     * Modifie la requête principale pour l'archive des événements :
     * tri par date de début et filtres appliqués via les paramètres GET.
     *
     * @param \WP_Query $query Requête WordPress courante.
     *
     * @return void
     */
    public function modifyArchiveQuery( \WP_Query $query ): void {
        if ( is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( ! is_post_type_archive( 'event' ) ) {
            return;
        }

        // Tri par date de début (croissant pour les événements à venir).
        $query->set( 'meta_key', '_event_start_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'ASC' );
        $query->set( 'posts_per_page', 12 );

        // Filtre sur les événements à venir uniquement (si non demandé autrement).
        $show_past = isset( $_GET['past'] ) && $_GET['past'] === '1';
        $now       = current_time( 'Y-m-d H:i:s' );

        $meta_query = [];

        if ( ! $show_past ) {
            $meta_query[] = [
                'key'     => '_event_start_date',
                'value'   => $now,
                'compare' => '>=',
                'type'    => 'DATETIME',
            ];
        }

        if ( ! empty( $_GET['date_from'] ) && strtotime( $_GET['date_from'] ) !== false ) {
            $meta_query[] = [
                'key'     => '_event_start_date',
                'value'   => date( 'Y-m-d H:i:s', strtotime( sanitize_text_field( $_GET['date_from'] ) ) ),
                'compare' => '>=',
                'type'    => 'DATETIME',
            ];
        }

        if ( ! empty( $_GET['date_to'] ) && strtotime( $_GET['date_to'] ) !== false ) {
            $meta_query[] = [
                'key'     => '_event_start_date',
                'value'   => date( 'Y-m-d H:i:s', strtotime( sanitize_text_field( $_GET['date_to'] ) ) ),
                'compare' => '<=',
                'type'    => 'DATETIME',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $query->set( 'meta_query', $meta_query );
        }

        // Filtre par catégorie.
        if ( ! empty( $_GET['event_category'] ) ) {
            $query->set( 'tax_query', [
                [
                    'taxonomy' => 'event_category',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $_GET['event_category'] ),
                ],
            ] );
        }
    }

    /**
     * Redirige vers les templates du thème pour les vues d'événements.
     *
     * @param string $template Chemin du template WordPress par défaut.
     *
     * @return string Chemin du template à utiliser.
     */
    public function filterTemplates( string $template ): string {
        if ( is_singular( 'event' ) ) {
            $custom = get_template_directory() . '/templates/events/single-event.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }

        if ( is_post_type_archive( 'event' ) ) {
            $custom = get_template_directory() . '/templates/events/archive-event.php';
            if ( file_exists( $custom ) ) {
                return $custom;
            }
        }

        return $template;
    }

    /**
     * Retourne le libellé traduit d'un statut RSVP.
     *
     * @param string|null $status Statut RSVP.
     *
     * @return string
     */
    private function getStatusLabel( ?string $status ): string {
        $labels = [
            'interested'  => __( 'Vous êtes marqué(e) comme intéressé(e).', 'theme-associatif' ),
            'confirmed'   => __( 'Votre inscription est confirmée !', 'theme-associatif' ),
            'absent'      => __( 'Vous avez été marqué(e) absent(e).', 'theme-associatif' ),
            'cancelled'   => __( 'Votre inscription a été annulée.', 'theme-associatif' ),
            'waitlisted'  => __( 'Vous êtes inscrit(e) sur liste d\'attente.', 'theme-associatif' ),
        ];

        return $labels[ $status ] ?? __( 'Statut mis à jour.', 'theme-associatif' );
    }
}
