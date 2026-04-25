<?php
/**
 * Classe EventCalendar
 *
 * Gère l'affichage du calendrier interactif frontend et les endpoints
 * AJAX permettant la navigation entre les mois.
 *
 * Le calendrier charge les événements via une requête AJAX sécurisée
 * (nonce) et les transmet en JSON au script JavaScript calendar.js
 * qui génère l'interface interactive.
 *
 * @package ThemeAssociatif\Frontend
 * @since   1.0.0
 */

namespace ThemeAssociatif\Frontend;

use ThemeAssociatif\Interfaces\EventManagerInterface;
use ThemeAssociatif\Services\EventSecurityService;

/**
 * Classe EventCalendar
 */
class EventCalendar {

    /**
     * Gestionnaire d'événements injecté.
     *
     * @var EventManagerInterface
     */
    private EventManagerInterface $event_manager;

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
     * @param EventSecurityService  $security      Service de sécurité.
     */
    public function __construct( EventManagerInterface $event_manager, EventSecurityService $security ) {
        $this->event_manager = $event_manager;
        $this->security      = $security;
    }

    /**
     * Attache les hooks WordPress nécessaires.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'wp_enqueue_scripts',         [ $this, 'enqueueAssets' ] );
        add_action( 'wp_ajax_event_calendar_data',        [ $this, 'handleAjaxCalendar' ] );
        add_action( 'wp_ajax_nopriv_event_calendar_data', [ $this, 'handleAjaxCalendar' ] );
        add_shortcode( 'event_calendar',          [ $this, 'renderShortcode' ] );
    }

    /**
     * Charge les assets du calendrier sur les pages les nécessitant.
     *
     * @return void
     */
    public function enqueueAssets(): void {
        // Chargement uniquement si le shortcode ou la page archive est détectée.
        if ( ! $this->needsCalendarAssets() ) {
            return;
        }

        wp_enqueue_style(
            'theme-event-calendar',
            get_template_directory_uri() . '/assets/css/events/calendar.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'theme-event-calendar',
            get_template_directory_uri() . '/assets/js/events/calendar.js',
            [],
            '1.0.0',
            true
        );

        // Données transmises au JavaScript pour l'initialisation.
        wp_localize_script(
            'theme-event-calendar',
            'eventCalendarData',
            [
                'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
                'nonce'      => $this->security->createNonce( EventSecurityService::NONCE_CALENDAR_AJAX ),
                'today'      => date( 'Y-m-d' ),
                'locale'     => get_locale(),
                'i18n'       => [
                    'months'    => $this->getMonthNames(),
                    'days'      => $this->getDayNames(),
                    'today'     => __( 'Aujourd\'hui', 'theme-associatif' ),
                    'noEvents'  => __( 'Aucun événement ce mois-ci', 'theme-associatif' ),
                    'loading'   => __( 'Chargement...', 'theme-associatif' ),
                    'prevMonth' => __( 'Mois précédent', 'theme-associatif' ),
                    'nextMonth' => __( 'Mois suivant', 'theme-associatif' ),
                ],
            ]
        );
    }

    /**
     * Gère la requête AJAX de chargement des événements d'un mois donné.
     * Répond en JSON avec le tableau des événements formatés pour le calendrier.
     *
     * @return void
     */
    public function handleAjaxCalendar(): void {
        $nonce = sanitize_text_field( wp_unslash( $_GET['nonce'] ?? $_POST['nonce'] ?? '' ) );
        if ( ! $this->security->verifyNonce( $nonce, EventSecurityService::NONCE_CALENDAR_AJAX ) ) {
            wp_send_json_error( __( 'Nonce invalide.', 'theme-associatif' ), 403 );
        }

        $year  = (int) ( $_GET['year']  ?? $_POST['year']  ?? date( 'Y' ) );
        $month = (int) ( $_GET['month'] ?? $_POST['month'] ?? date( 'n' ) );

        // Validation des plages.
        $year  = max( 2000, min( 2100, $year ) );
        $month = max( 1, min( 12, $month ) );

        $events = $this->event_manager->getByMonth( $year, $month );

        $data = array_map( function ( $event ) {
            return [
                'id'         => $event->getId(),
                'title'      => esc_html( $event->getTitle() ),
                'start_date' => $event->getStartDate(),
                'end_date'   => $event->getEndDate(),
                'url'        => get_permalink( $event->getId() ),
                'location'   => esc_html( $event->getLocation() ),
                'categories' => $event->getCategoryIds(),
            ];
        }, $events );

        wp_send_json_success( $data );
    }

    /**
     * Rend le calendrier via le shortcode [event_calendar].
     *
     * Attributs optionnels :
     *   - year  : Année initiale (défaut : année courante).
     *   - month : Mois initial de 1 à 12 (défaut : mois courant).
     *
     * @param array $atts Attributs du shortcode.
     *
     * @return string HTML du calendrier.
     */
    public function renderShortcode( array $atts = [] ): string {
        $atts = shortcode_atts(
            [
                'year'  => date( 'Y' ),
                'month' => date( 'n' ),
            ],
            $atts,
            'event_calendar'
        );

        $year  = max( 2000, min( 2100, (int) $atts['year'] ) );
        $month = max( 1, min( 12, (int) $atts['month'] ) );

        $events = $this->event_manager->getByMonth( $year, $month );

        ob_start();
        include get_template_directory() . '/templates/events/calendar.php';
        return ob_get_clean();
    }

    /**
     * Détermine si les assets du calendrier doivent être chargés sur la page courante.
     *
     * @return bool
     */
    private function needsCalendarAssets(): bool {
        global $post;

        // Archive et single des événements.
        if ( is_post_type_archive( 'event' ) || is_singular( 'event' ) ) {
            return true;
        }

        // Pages contenant le shortcode.
        if ( $post && has_shortcode( $post->post_content, 'event_calendar' ) ) {
            return true;
        }

        return false;
    }

    /**
     * Retourne les noms des mois dans la langue de WordPress.
     *
     * @return string[]
     */
    private function getMonthNames(): array {
        global $wp_locale;
        $months = [];
        for ( $i = 1; $i <= 12; $i++ ) {
            $months[] = $wp_locale->get_month( $i );
        }
        return $months;
    }

    /**
     * Retourne les noms des jours de la semaine abrégés.
     *
     * @return string[]
     */
    private function getDayNames(): array {
        global $wp_locale;
        $days = [];
        for ( $i = 0; $i < 7; $i++ ) {
            $days[] = $wp_locale->get_weekday_abbrev( $wp_locale->get_weekday( $i ) );
        }
        return $days;
    }
}
