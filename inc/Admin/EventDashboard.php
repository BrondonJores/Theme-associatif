<?php
/**
 * Classe EventDashboard
 *
 * Page d'administration dédiée à la gestion des événements :
 * statistiques globales, liste des prochains événements, export des participants
 * et déclenchement d'opérations en lot (rappels, annulations).
 *
 * @package ThemeAssociatif\Admin
 * @since   1.0.0
 */

namespace ThemeAssociatif\Admin;

use ThemeAssociatif\Interfaces\EventManagerInterface;
use ThemeAssociatif\Interfaces\RSVPInterface;
use ThemeAssociatif\Interfaces\NotificationInterface;
use ThemeAssociatif\Services\EventSecurityService;

/**
 * Classe EventDashboard
 */
class EventDashboard {

    /**
     * Identifiant de la page d'administration.
     *
     * @var string
     */
    const PAGE_SLUG = 'event-dashboard';

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
     * Service de notification injecté.
     *
     * @var NotificationInterface
     */
    private NotificationInterface $notifier;

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
     * @param NotificationInterface $notifier      Service de notification.
     * @param EventSecurityService  $security      Service de sécurité.
     */
    public function __construct(
        EventManagerInterface $event_manager,
        RSVPInterface $rsvp_manager,
        NotificationInterface $notifier,
        EventSecurityService $security
    ) {
        $this->event_manager = $event_manager;
        $this->rsvp_manager  = $rsvp_manager;
        $this->notifier      = $notifier;
        $this->security      = $security;
    }

    /**
     * Attache les hooks d'administration.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'admin_menu',             [ $this, 'addMenuPage' ] );
        add_action( 'admin_enqueue_scripts',  [ $this, 'enqueueAssets' ] );
        add_action( 'wp_ajax_event_export_participants', [ $this, 'handleExportParticipants' ] );
        add_action( 'wp_ajax_event_send_reminder',       [ $this, 'handleSendReminder' ] );
    }

    /**
     * Enregistre la page dans le menu d'administration, sous "Événements".
     *
     * @return void
     */
    public function addMenuPage(): void {
        add_submenu_page(
            'edit.php?post_type=event',
            __( 'Tableau de bord événements', 'theme-associatif' ),
            __( 'Tableau de bord', 'theme-associatif' ),
            'edit_posts',
            self::PAGE_SLUG,
            [ $this, 'renderPage' ]
        );
    }

    /**
     * Charge les assets CSS/JS spécifiques au dashboard événements.
     *
     * @param string $hook Identifiant de la page d'administration courante.
     *
     * @return void
     */
    public function enqueueAssets( string $hook ): void {
        // Ne charge les assets que sur la page du dashboard.
        if ( strpos( $hook, self::PAGE_SLUG ) === false ) {
            return;
        }

        wp_enqueue_style(
            'theme-event-dashboard',
            get_template_directory_uri() . '/assets/css/events/dashboard.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'theme-event-dashboard',
            get_template_directory_uri() . '/assets/js/events/dashboard.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );

        wp_localize_script(
            'theme-event-dashboard',
            'eventDashboard',
            [
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonce'     => $this->security->createNonce( EventSecurityService::NONCE_EVENT_MANAGE ),
                'i18n'      => [
                    'exportSuccess'  => __( 'Export réussi', 'theme-associatif' ),
                    'reminderSent'   => __( 'Rappels envoyés', 'theme-associatif' ),
                    'confirmExport'  => __( 'Exporter les participants de cet événement ?', 'theme-associatif' ),
                    'confirmRemind'  => __( 'Envoyer un rappel à tous les participants ?', 'theme-associatif' ),
                ],
            ]
        );
    }

    /**
     * Affiche la page principale du dashboard événements.
     *
     * @return void
     */
    public function renderPage(): void {
        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'Permissions insuffisantes.', 'theme-associatif' ) );
        }

        $upcoming = $this->event_manager->getUpcoming( 10 );
        $stats    = $this->getGlobalStats();

        require get_template_directory() . '/templates/admin/event-dashboard.php';
    }

    /**
     * Gère la requête AJAX d'export des participants au format CSV.
     *
     * @return void
     */
    public function handleExportParticipants(): void {
        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! $this->security->verifyNonce( $nonce, EventSecurityService::NONCE_EVENT_MANAGE ) ) {
            wp_send_json_error( __( 'Nonce invalide.', 'theme-associatif' ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permissions insuffisantes.', 'theme-associatif' ) );
        }

        $event_id = absint( $_POST['event_id'] ?? 0 );
        if ( ! $event_id ) {
            wp_send_json_error( __( 'Identifiant d\'événement manquant.', 'theme-associatif' ) );
        }

        $csv = $this->rsvp_manager->exportParticipants( $event_id, 'csv' );

        wp_send_json_success( [
            'csv'      => $csv,
            'filename' => 'participants-event-' . $event_id . '-' . date( 'Ymd' ) . '.csv',
        ] );
    }

    /**
     * Gère la requête AJAX d'envoi de rappels aux participants d'un événement.
     *
     * @return void
     */
    public function handleSendReminder(): void {
        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! $this->security->verifyNonce( $nonce, EventSecurityService::NONCE_EVENT_MANAGE ) ) {
            wp_send_json_error( __( 'Nonce invalide.', 'theme-associatif' ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( __( 'Permissions insuffisantes.', 'theme-associatif' ) );
        }

        $event_id = absint( $_POST['event_id'] ?? 0 );
        if ( ! $event_id ) {
            wp_send_json_error( __( 'Identifiant d\'événement manquant.', 'theme-associatif' ) );
        }

        $sent = $this->notifier->sendReminder( $event_id, 24 );

        wp_send_json_success( [
            'sent'    => $sent,
            'message' => sprintf(
                /* translators: %d : nombre de notifications envoyées */
                __( '%d rappel(s) envoyé(s).', 'theme-associatif' ),
                $sent
            ),
        ] );
    }

    /**
     * Calcule les statistiques globales des événements pour l'affichage.
     *
     * @return array Statistiques : total, upcoming, past, total_rsvp.
     */
    private function getGlobalStats(): array {
        global $wpdb;

        $total = (int) $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->posts}
             WHERE post_type = 'event' AND post_status = 'publish'"
        );

        $now = current_time( 'Y-m-d H:i:s' );

        $upcoming = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT p.ID)
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'event'
                   AND p.post_status = 'publish'
                   AND pm.meta_key = '_event_start_date'
                   AND pm.meta_value >= %s",
                $now
            )
        );

        $past = $total - $upcoming;

        $rsvp_table = $wpdb->prefix . \ThemeAssociatif\Services\RSVPManager::TABLE_BASE;
        $total_rsvp = 0;

        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $rsvp_table ) ) === $rsvp_table ) {
            $total_rsvp = (int) $wpdb->get_var(
                "SELECT COUNT(*) FROM {$rsvp_table} WHERE status = 'confirmed'"
            );
        }

        return [
            'total'      => $total,
            'upcoming'   => $upcoming,
            'past'       => $past,
            'total_rsvp' => $total_rsvp,
        ];
    }
}
