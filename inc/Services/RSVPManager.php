<?php
/**
 * Classe RSVPManager
 *
 * Service de gestion des inscriptions (RSVP) aux événements.
 * Stocke les inscriptions dans une table dédiée créée lors de l'activation
 * du thème, gère les capacités maximales, les listes d'attente et l'export.
 *
 * Schéma de la table wp_event_rsvp :
 *   id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
 *   event_id    BIGINT UNSIGNED NOT NULL (identifiant du post WordPress)
 *   user_id     BIGINT UNSIGNED NOT NULL (identifiant de l'utilisateur WordPress)
 *   status      VARCHAR(20) NOT NULL ('interested','confirmed','absent','cancelled','waitlisted')
 *   paid_amount INT UNSIGNED DEFAULT 0  (montant payé en centimes)
 *   comment     TEXT
 *   created_at  DATETIME NOT NULL
 *   updated_at  DATETIME NOT NULL
 *
 * @package ThemeAssociatif\Services
 * @since   1.0.0
 */

namespace ThemeAssociatif\Services;

use ThemeAssociatif\Interfaces\RSVPInterface;
use ThemeAssociatif\Interfaces\NotificationInterface;

/**
 * Classe RSVPManager
 */
class RSVPManager implements RSVPInterface {

    /**
     * Nom de la table sans préfixe WordPress.
     *
     * @var string
     */
    const TABLE_BASE = 'event_rsvp';

    /**
     * Service de notification injecté (facultatif pour respecter SRP).
     *
     * @var NotificationInterface|null
     */
    private ?NotificationInterface $notifier;

    /**
     * Constructeur.
     *
     * @param NotificationInterface|null $notifier Service de notification (null = désactivé).
     */
    public function __construct( ?NotificationInterface $notifier = null ) {
        $this->notifier = $notifier;
    }

    /**
     * Retourne le nom complet de la table (avec préfixe WordPress).
     *
     * @return string
     */
    private function getTable(): string {
        global $wpdb;
        return $wpdb->prefix . self::TABLE_BASE;
    }

    /**
     * {@inheritdoc}
     *
     * Si la capacité est atteinte, inscrit l'utilisateur en liste d'attente.
     * Déclenche des hooks et notifications selon le changement de statut.
     */
    public function setRSVP( int $event_id, int $user_id, string $status, array $meta = [] ): bool {
        global $wpdb;
        $table = $this->getTable();

        // Vérification de la capacité uniquement pour une confirmation.
        if ( $status === 'confirmed' && ! $this->canRegister( $event_id ) ) {
            // Inscription automatique en liste d'attente si complet.
            $status = 'waitlisted';
        }

        $existing = $this->getRow( $event_id, $user_id );
        $old_status = $existing ? $existing->status : null;
        $now = current_time( 'mysql' );

        if ( $existing ) {
            // Mise à jour du RSVP existant.
            $result = $wpdb->update(
                $table,
                [
                    'status'      => $status,
                    'paid_amount' => absint( $meta['paid_amount'] ?? $existing->paid_amount ?? 0 ),
                    'comment'     => sanitize_textarea_field( $meta['comment'] ?? $existing->comment ?? '' ),
                    'updated_at'  => $now,
                ],
                [ 'event_id' => $event_id, 'user_id' => $user_id ],
                [ '%s', '%d', '%s', '%s' ],
                [ '%d', '%d' ]
            );
        } else {
            // Création d'un nouveau RSVP.
            $result = $wpdb->insert(
                $table,
                [
                    'event_id'    => $event_id,
                    'user_id'     => $user_id,
                    'status'      => $status,
                    'paid_amount' => absint( $meta['paid_amount'] ?? 0 ),
                    'comment'     => sanitize_textarea_field( $meta['comment'] ?? '' ),
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ],
                [ '%d', '%d', '%s', '%d', '%s', '%s', '%s' ]
            );
        }

        if ( $result === false ) {
            return false;
        }

        /**
         * Hook déclenché après un changement de statut RSVP.
         *
         * @param int         $event_id   Identifiant de l'événement.
         * @param int         $user_id    Identifiant de l'utilisateur.
         * @param string      $status     Nouveau statut.
         * @param string|null $old_status Ancien statut (null si nouvel enregistrement).
         */
        do_action( 'theme_associatif/rsvp/changed', $event_id, $user_id, $status, $old_status );

        // Envoi de notification de confirmation si le service est disponible.
        if ( $this->notifier && $status === 'confirmed' ) {
            $this->notifier->sendConfirmation( $event_id, $user_id );
        }

        // Si une place se libère (annulation), notification du premier en liste d'attente.
        if ( $this->notifier && in_array( $status, [ 'cancelled', 'absent' ], true ) && $old_status === 'confirmed' ) {
            $this->notifyFirstWaitlisted( $event_id );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus( int $event_id, int $user_id ): ?string {
        $row = $this->getRow( $event_id, $user_id );
        return $row ? $row->status : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getParticipants( int $event_id, string $status = '' ): array {
        global $wpdb;
        $table = $this->getTable();

        if ( $status !== '' ) {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT user_id, status, paid_amount, comment, created_at
                     FROM {$table}
                     WHERE event_id = %d AND status = %s
                     ORDER BY created_at ASC",
                    $event_id,
                    $status
                ),
                ARRAY_A
            );
        } else {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT user_id, status, paid_amount, comment, created_at
                     FROM {$table}
                     WHERE event_id = %d
                     ORDER BY created_at ASC",
                    $event_id
                ),
                ARRAY_A
            );
        }

        if ( ! $rows ) {
            return [];
        }

        return array_map( function ( array $row ) {
            return [
                'user_id' => (int) $row['user_id'],
                'status'  => $row['status'],
                'meta'    => [
                    'paid_amount' => (int) $row['paid_amount'],
                    'comment'     => $row['comment'],
                    'created_at'  => $row['created_at'],
                ],
            ];
        }, $rows );
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmedCount( int $event_id ): int {
        global $wpdb;
        $table = $this->getTable();

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE event_id = %d AND status = 'confirmed'",
                $event_id
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function canRegister( int $event_id ): bool {
        $capacity = (int) get_post_meta( $event_id, '_event_capacity', true );

        // Capacité illimitée : inscription toujours possible.
        if ( $capacity === 0 ) {
            return true;
        }

        return $this->getConfirmedCount( $event_id ) < $capacity;
    }

    /**
     * {@inheritdoc}
     */
    public function cancel( int $event_id, int $user_id ): bool {
        return $this->setRSVP( $event_id, $user_id, 'cancelled' );
    }

    /**
     * {@inheritdoc}
     */
    public function exportParticipants( int $event_id, string $format = 'array' ) {
        $participants = $this->getParticipants( $event_id );

        if ( $format === 'array' ) {
            return $participants;
        }

        // Génération du CSV.
        $lines   = [];
        $lines[] = implode( ';', [
            __( 'ID Utilisateur', 'theme-associatif' ),
            __( 'Nom', 'theme-associatif' ),
            __( 'Email', 'theme-associatif' ),
            __( 'Statut', 'theme-associatif' ),
            __( 'Montant payé (€)', 'theme-associatif' ),
            __( 'Commentaire', 'theme-associatif' ),
            __( 'Date inscription', 'theme-associatif' ),
        ] );

        foreach ( $participants as $p ) {
            $user         = get_userdata( $p['user_id'] );
            $display_name = $user ? $user->display_name : '—';
            $email        = $user ? $user->user_email : '—';
            $price_euros  = number_format( $p['meta']['paid_amount'] / 100, 2, ',', '' );

            $lines[] = implode( ';', [
                $p['user_id'],
                $display_name,
                $email,
                $p['status'],
                $price_euros,
                str_replace( ';', ',', $p['meta']['comment'] ),
                $p['meta']['created_at'],
            ] );
        }

        return implode( "\n", $lines );
    }

    /**
     * Crée la table de base de données pour les RSVP.
     * Appelé lors de l'activation du thème (after_switch_theme).
     *
     * @return void
     */
    public static function createTable(): void {
        global $wpdb;

        $table      = $wpdb->prefix . self::TABLE_BASE;
        $charset    = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id    BIGINT UNSIGNED NOT NULL,
            user_id     BIGINT UNSIGNED NOT NULL,
            status      VARCHAR(20)     NOT NULL DEFAULT 'interested',
            paid_amount INT UNSIGNED    NOT NULL DEFAULT 0,
            comment     TEXT,
            created_at  DATETIME        NOT NULL,
            updated_at  DATETIME        NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY event_user (event_id, user_id),
            KEY event_status (event_id, status),
            KEY user_id (user_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Récupère la ligne RSVP d'un utilisateur pour un événement.
     *
     * @param int $event_id Identifiant de l'événement.
     * @param int $user_id  Identifiant de l'utilisateur.
     *
     * @return object|null Objet de ligne de base de données ou null.
     */
    private function getRow( int $event_id, int $user_id ): ?object {
        global $wpdb;
        $table = $this->getTable();

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE event_id = %d AND user_id = %d",
                $event_id,
                $user_id
            )
        );

        return $row ?: null;
    }

    /**
     * Notifie le premier utilisateur en liste d'attente qu'une place se libère.
     *
     * @param int $event_id Identifiant de l'événement.
     *
     * @return void
     */
    private function notifyFirstWaitlisted( int $event_id ): void {
        if ( ! $this->notifier ) {
            return;
        }

        global $wpdb;
        $table = $this->getTable();

        $first = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT user_id FROM {$table}
                 WHERE event_id = %d AND status = 'waitlisted'
                 ORDER BY created_at ASC
                 LIMIT 1",
                $event_id
            )
        );

        if ( $first ) {
            $this->notifier->sendWaitlistAvailability( $event_id, (int) $first );
        }
    }
}
