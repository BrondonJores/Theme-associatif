<?php
/**
 * Classe NotificationService
 *
 * Service d'envoi des notifications par email aux participants des événements.
 * Utilise wp_mail() de WordPress et des templates HTML situés dans
 * templates/emails/.
 *
 * Respecte le principe ouvert/fermé (OCP) : les templates sont séparés
 * du code, ce qui permet de modifier l'apparence sans toucher la logique.
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Interfaces\NotificationInterface;

/**
 * Classe NotificationService
 */
class NotificationService implements NotificationInterface {

    /**
     * Nom du site utilisé dans les emails.
     *
     * @var string
     */
    private string $site_name;

    /**
     * Adresse email d'expédition.
     *
     * @var string
     */
    private string $from_email;

    /**
     * Constructeur.
     * Initialise les informations d'expédition depuis les options WordPress.
     */
    public function __construct() {
        $this->site_name  = get_bloginfo( 'name' );
        $this->from_email = get_option( 'admin_email' );
    }

    /**
     * {@inheritdoc}
     */
    public function sendConfirmation( int $event_id, int $user_id ): bool {
        $user  = get_userdata( $user_id );
        $event = get_post( $event_id );

        if ( ! $user || ! $event ) {
            return false;
        }

        $subject = sprintf(
            /* translators: %s : titre de l'événement */
            __( '[%s] Confirmation d\'inscription : %s', 'theme-associatif' ),
            $this->site_name,
            $event->post_title
        );

        $body = $this->renderTemplate( 'confirmation', [
            'user_name'   => $user->display_name,
            'event_title' => $event->post_title,
            'event_url'   => get_permalink( $event_id ),
            'start_date'  => get_post_meta( $event_id, '_event_start_date', true ),
            'location'    => get_post_meta( $event_id, '_event_location', true ),
        ] );

        return $this->send( $user->user_email, $subject, $body );
    }

    /**
     * {@inheritdoc}
     */
    public function sendCancellation( int $event_id, string $reason = '' ): int {
        $event        = get_post( $event_id );
        $participants = $this->getConfirmedAndInterested( $event_id );
        $sent         = 0;

        if ( ! $event ) {
            return 0;
        }

        foreach ( $participants as $user_id ) {
            $user = get_userdata( $user_id );
            if ( ! $user ) {
                continue;
            }

            $subject = sprintf(
                /* translators: %s : titre de l'événement */
                __( '[%s] Annulation de l\'événement : %s', 'theme-associatif' ),
                $this->site_name,
                $event->post_title
            );

            $body = $this->renderTemplate( 'cancellation', [
                'user_name'   => $user->display_name,
                'event_title' => $event->post_title,
                'reason'      => $reason,
            ] );

            if ( $this->send( $user->user_email, $subject, $body ) ) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * {@inheritdoc}
     */
    public function sendReminder( int $event_id, int $hours_before = 24 ): int {
        $event        = get_post( $event_id );
        $participants = $this->getConfirmedAndInterested( $event_id );
        $sent         = 0;

        if ( ! $event ) {
            return 0;
        }

        $start_date = get_post_meta( $event_id, '_event_start_date', true );

        foreach ( $participants as $user_id ) {
            $user = get_userdata( $user_id );
            if ( ! $user ) {
                continue;
            }

            $subject = sprintf(
                /* translators: %1$s : titre de l'événement, %2$d : heures avant */
                __( '[%s] Rappel : %s dans %d heure(s)', 'theme-associatif' ),
                $this->site_name,
                $event->post_title,
                $hours_before
            );

            $body = $this->renderTemplate( 'reminder', [
                'user_name'    => $user->display_name,
                'event_title'  => $event->post_title,
                'event_url'    => get_permalink( $event_id ),
                'start_date'   => $start_date,
                'location'     => get_post_meta( $event_id, '_event_location', true ),
                'hours_before' => $hours_before,
            ] );

            if ( $this->send( $user->user_email, $subject, $body ) ) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * {@inheritdoc}
     */
    public function sendUpdate( int $event_id, array $changed_fields = [] ): int {
        $event        = get_post( $event_id );
        $participants = $this->getConfirmedAndInterested( $event_id );
        $sent         = 0;

        if ( ! $event ) {
            return 0;
        }

        foreach ( $participants as $user_id ) {
            $user = get_userdata( $user_id );
            if ( ! $user ) {
                continue;
            }

            $subject = sprintf(
                /* translators: %s : titre de l'événement */
                __( '[%s] Mise à jour de l\'événement : %s', 'theme-associatif' ),
                $this->site_name,
                $event->post_title
            );

            $body = $this->renderTemplate( 'update', [
                'user_name'      => $user->display_name,
                'event_title'    => $event->post_title,
                'event_url'      => get_permalink( $event_id ),
                'changed_fields' => $changed_fields,
            ] );

            if ( $this->send( $user->user_email, $subject, $body ) ) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * {@inheritdoc}
     */
    public function sendWaitlistAvailability( int $event_id, int $user_id ): bool {
        $user  = get_userdata( $user_id );
        $event = get_post( $event_id );

        if ( ! $user || ! $event ) {
            return false;
        }

        $subject = sprintf(
            /* translators: %s : titre de l'événement */
            __( '[%s] Une place s\'est libérée : %s', 'theme-associatif' ),
            $this->site_name,
            $event->post_title
        );

        $body = $this->renderTemplate( 'waitlist_available', [
            'user_name'   => $user->display_name,
            'event_title' => $event->post_title,
            'event_url'   => get_permalink( $event_id ),
        ] );

        return $this->send( $user->user_email, $subject, $body );
    }

    /**
     * Récupère les identifiants des participants confirmés et intéressés.
     *
     * @param int $event_id Identifiant de l'événement.
     *
     * @return int[]
     */
    private function getConfirmedAndInterested( int $event_id ): array {
        global $wpdb;
        $table = $wpdb->prefix . RSVPManager::TABLE_BASE;

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT user_id FROM {$table}
                 WHERE event_id = %d AND status IN ('confirmed', 'interested')",
                $event_id
            )
        );

        return $results ? array_map( 'intval', $results ) : [];
    }

    /**
     * Envoie un email en HTML via wp_mail().
     *
     * @param string $to      Adresse email du destinataire.
     * @param string $subject Sujet du message.
     * @param string $body    Corps HTML du message.
     *
     * @return bool
     */
    private function send( string $to, string $subject, string $body ): bool {
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            sprintf( 'From: %s <%s>', $this->site_name, $this->from_email ),
        ];

        return wp_mail( $to, $subject, $body, $headers );
    }

    /**
     * Génère le corps HTML d'un email à partir d'un template.
     * Si le template de fichier n'existe pas, génère un message minimal.
     *
     * @param string $template_name Nom du template (sans extension).
     * @param array  $vars          Variables à injecter dans le template.
     *
     * @return string Corps HTML du message.
     */
    private function renderTemplate( string $template_name, array $vars ): string {
        $template_path = get_template_directory() . '/templates/emails/' . $template_name . '.php';

        if ( file_exists( $template_path ) ) {
            ob_start();
            // Les variables sont extraites dans le scope du template.
            extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
            include $template_path;
            return ob_get_clean();
        }

        // Template de secours minimaliste.
        $lines = [];
        foreach ( $vars as $key => $value ) {
            if ( is_string( $value ) ) {
                $lines[] = '<p><strong>' . esc_html( $key ) . '</strong> : ' . esc_html( $value ) . '</p>';
            }
        }

        return '<html><body>' . implode( '', $lines ) . '</body></html>';
    }
}
