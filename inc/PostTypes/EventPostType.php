<?php
/**
 * Classe EventPostType
 *
 * Enregistre le Custom Post Type 'event' avec toutes ses métaboxes,
 * colonnes d'administration et champs personnalisés.
 *
 * Responsabilité unique (SRP) : cette classe ne s'occupe que de
 * l'enregistrement du CPT et de l'interface d'administration associée.
 *
 * @package ThemeAssociatif\PostTypes
 * @since   1.0.0
 */

namespace ThemeAssociatif\PostTypes;

/**
 * Classe EventPostType
 */
class EventPostType {

    /**
     * Identifiant du Custom Post Type.
     *
     * @var string
     */
    const POST_TYPE = 'event';

    /**
     * Préfixe des clés de métadonnées pour éviter les conflits.
     *
     * @var string
     */
    const META_PREFIX = '_event_';

    /**
     * Attache les hooks WordPress nécessaires à l'enregistrement
     * du CPT et de ses interfaces d'administration.
     *
     * @return void
     */
    public function register(): void {
        add_action( 'init',                  [ $this, 'registerPostType' ] );
        add_action( 'add_meta_boxes',        [ $this, 'addMetaBoxes' ] );
        add_action( 'save_post_event',       [ $this, 'saveMetaBoxes' ], 10, 2 );
        add_filter( 'manage_event_posts_columns',       [ $this, 'addAdminColumns' ] );
        add_action( 'manage_event_posts_custom_column', [ $this, 'renderAdminColumn' ], 10, 2 );
        add_filter( 'manage_edit-event_sortable_columns', [ $this, 'makeSortableColumns' ] );
        add_action( 'pre_get_posts',         [ $this, 'handleAdminSorting' ] );
    }

    /**
     * Enregistre le Custom Post Type 'event'.
     *
     * @return void
     */
    public function registerPostType(): void {
        $labels = [
            'name'               => __( 'Événements', 'theme-associatif' ),
            'singular_name'      => __( 'Événement', 'theme-associatif' ),
            'add_new'            => __( 'Ajouter', 'theme-associatif' ),
            'add_new_item'       => __( 'Ajouter un événement', 'theme-associatif' ),
            'edit_item'          => __( 'Modifier l\'événement', 'theme-associatif' ),
            'new_item'           => __( 'Nouvel événement', 'theme-associatif' ),
            'view_item'          => __( 'Voir l\'événement', 'theme-associatif' ),
            'search_items'       => __( 'Rechercher des événements', 'theme-associatif' ),
            'not_found'          => __( 'Aucun événement trouvé', 'theme-associatif' ),
            'not_found_in_trash' => __( 'Aucun événement dans la corbeille', 'theme-associatif' ),
            'menu_name'          => __( 'Événements', 'theme-associatif' ),
            'all_items'          => __( 'Tous les événements', 'theme-associatif' ),
        ];

        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => [ 'slug' => 'evenements', 'with_front' => false ],
            'capability_type'     => 'post',
            'map_meta_cap'        => true,
            'has_archive'         => 'evenements',
            'hierarchical'        => false,
            'menu_position'       => 5,
            'menu_icon'           => 'dashicons-calendar-alt',
            'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ],
            'show_in_rest'        => true,
            'rest_base'           => 'events',
        ];

        register_post_type( self::POST_TYPE, $args );
    }

    /**
     * Ajoute les métaboxes à l'écran d'édition d'un événement.
     *
     * @return void
     */
    public function addMetaBoxes(): void {
        add_meta_box(
            'event_dates',
            __( 'Dates et lieu', 'theme-associatif' ),
            [ $this, 'renderDatesMetaBox' ],
            self::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'event_capacity',
            __( 'Capacité et tarifs', 'theme-associatif' ),
            [ $this, 'renderCapacityMetaBox' ],
            self::POST_TYPE,
            'normal',
            'high'
        );

        add_meta_box(
            'event_recurrence',
            __( 'Récurrence', 'theme-associatif' ),
            [ $this, 'renderRecurrenceMetaBox' ],
            self::POST_TYPE,
            'side',
            'default'
        );

        add_meta_box(
            'event_rsvp_stats',
            __( 'Statistiques RSVP', 'theme-associatif' ),
            [ $this, 'renderRSVPStatsMetaBox' ],
            self::POST_TYPE,
            'side',
            'default'
        );
    }

    /**
     * Affiche le contenu de la métabox "Dates et lieu".
     *
     * @param \WP_Post $post Post courant.
     *
     * @return void
     */
    public function renderDatesMetaBox( \WP_Post $post ): void {
        wp_nonce_field( 'event_meta_save', 'event_meta_nonce' );

        $start_date = get_post_meta( $post->ID, '_event_start_date', true );
        $end_date   = get_post_meta( $post->ID, '_event_end_date', true );
        $location   = get_post_meta( $post->ID, '_event_location', true );

        // Formatage pour les champs datetime-local (format HTML5).
        $start_html = $start_date ? date( 'Y-m-d\TH:i', strtotime( $start_date ) ) : '';
        $end_html   = $end_date   ? date( 'Y-m-d\TH:i', strtotime( $end_date ) )   : '';
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="event_start_date"><?php esc_html_e( 'Date de début', 'theme-associatif' ); ?></label>
                </th>
                <td>
                    <input
                        type="datetime-local"
                        id="event_start_date"
                        name="event_start_date"
                        value="<?php echo esc_attr( $start_html ); ?>"
                        class="regular-text"
                        required
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="event_end_date"><?php esc_html_e( 'Date de fin', 'theme-associatif' ); ?></label>
                </th>
                <td>
                    <input
                        type="datetime-local"
                        id="event_end_date"
                        name="event_end_date"
                        value="<?php echo esc_attr( $end_html ); ?>"
                        class="regular-text"
                        required
                    />
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="event_location"><?php esc_html_e( 'Lieu', 'theme-associatif' ); ?></label>
                </th>
                <td>
                    <input
                        type="text"
                        id="event_location"
                        name="event_location"
                        value="<?php echo esc_attr( $location ); ?>"
                        class="large-text"
                        placeholder="<?php esc_attr_e( 'Adresse ou nom du lieu', 'theme-associatif' ); ?>"
                    />
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Affiche le contenu de la métabox "Capacité et tarifs".
     *
     * @param \WP_Post $post Post courant.
     *
     * @return void
     */
    public function renderCapacityMetaBox( \WP_Post $post ): void {
        $capacity     = (int) get_post_meta( $post->ID, '_event_capacity', true );
        $pricing_type = get_post_meta( $post->ID, '_event_pricing_type', true ) ?: 'free';
        $price_amount = (int) get_post_meta( $post->ID, '_event_price_amount', true );

        // Conversion de centimes en euros pour l'affichage.
        $price_euros = $price_amount > 0 ? number_format( $price_amount / 100, 2, ',', '' ) : '';
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="event_capacity"><?php esc_html_e( 'Capacité maximale', 'theme-associatif' ); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        id="event_capacity"
                        name="event_capacity"
                        value="<?php echo esc_attr( $capacity ); ?>"
                        min="0"
                        class="small-text"
                    />
                    <p class="description"><?php esc_html_e( '0 = illimitée', 'theme-associatif' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e( 'Type de tarif', 'theme-associatif' ); ?></th>
                <td>
                    <label>
                        <input type="radio" name="event_pricing_type" value="free" <?php checked( $pricing_type, 'free' ); ?> />
                        <?php esc_html_e( 'Gratuit', 'theme-associatif' ); ?>
                    </label>
                    <br />
                    <label>
                        <input type="radio" name="event_pricing_type" value="paid" <?php checked( $pricing_type, 'paid' ); ?> />
                        <?php esc_html_e( 'Payant (montant fixe)', 'theme-associatif' ); ?>
                    </label>
                    <br />
                    <label>
                        <input type="radio" name="event_pricing_type" value="free_price" <?php checked( $pricing_type, 'free_price' ); ?> />
                        <?php esc_html_e( 'Prix libre', 'theme-associatif' ); ?>
                    </label>
                </td>
            </tr>
            <tr id="event_price_row" <?php echo $pricing_type === 'paid' ? '' : 'style="display:none"'; ?>>
                <th scope="row">
                    <label for="event_price_euros"><?php esc_html_e( 'Montant (€)', 'theme-associatif' ); ?></label>
                </th>
                <td>
                    <input
                        type="number"
                        id="event_price_euros"
                        name="event_price_euros"
                        value="<?php echo esc_attr( $price_euros ); ?>"
                        min="0"
                        step="0.01"
                        class="small-text"
                    />
                </td>
            </tr>
        </table>
        <script>
        (function() {
            var radios = document.querySelectorAll('input[name="event_pricing_type"]');
            var priceRow = document.getElementById('event_price_row');
            radios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    priceRow.style.display = (this.value === 'paid') ? '' : 'none';
                });
            });
        })();
        </script>
        <?php
    }

    /**
     * Affiche le contenu de la métabox "Récurrence".
     *
     * @param \WP_Post $post Post courant.
     *
     * @return void
     */
    public function renderRecurrenceMetaBox( \WP_Post $post ): void {
        $is_recurring  = (bool) get_post_meta( $post->ID, '_event_is_recurring', true );
        $parent_id     = (int)  get_post_meta( $post->ID, '_event_recurrence_parent_id', true );
        $raw_rule      = get_post_meta( $post->ID, '_event_recurrence_rule', true );
        $rule          = $raw_rule ? (array) maybe_unserialize( $raw_rule ) : [];
        $frequency     = $rule['frequency'] ?? 'weekly';
        $interval      = $rule['interval'] ?? 1;
        $end_date_rule = $rule['end_date'] ?? '';
        $count         = $rule['count'] ?? '';
        ?>
        <p>
            <label>
                <input type="checkbox" name="event_is_recurring" value="1" <?php checked( $is_recurring ); ?> id="event_is_recurring_cb" />
                <?php esc_html_e( 'Événement récurrent', 'theme-associatif' ); ?>
            </label>
        </p>
        <div id="event_recurrence_options" <?php echo $is_recurring ? '' : 'style="display:none"'; ?>>
            <p>
                <label for="event_recurrence_frequency"><?php esc_html_e( 'Fréquence', 'theme-associatif' ); ?></label><br />
                <select name="event_recurrence_frequency" id="event_recurrence_frequency">
                    <option value="daily"   <?php selected( $frequency, 'daily' ); ?>><?php esc_html_e( 'Quotidien', 'theme-associatif' ); ?></option>
                    <option value="weekly"  <?php selected( $frequency, 'weekly' ); ?>><?php esc_html_e( 'Hebdomadaire', 'theme-associatif' ); ?></option>
                    <option value="monthly" <?php selected( $frequency, 'monthly' ); ?>><?php esc_html_e( 'Mensuel', 'theme-associatif' ); ?></option>
                    <option value="yearly"  <?php selected( $frequency, 'yearly' ); ?>><?php esc_html_e( 'Annuel', 'theme-associatif' ); ?></option>
                </select>
            </p>
            <p>
                <label for="event_recurrence_interval"><?php esc_html_e( 'Tous les', 'theme-associatif' ); ?></label>
                <input type="number" name="event_recurrence_interval" id="event_recurrence_interval" value="<?php echo esc_attr( $interval ); ?>" min="1" class="tiny-text" />
            </p>
            <p>
                <label for="event_recurrence_end_date"><?php esc_html_e( 'Date de fin de récurrence', 'theme-associatif' ); ?></label><br />
                <input type="date" name="event_recurrence_end_date" id="event_recurrence_end_date" value="<?php echo esc_attr( $end_date_rule ); ?>" />
            </p>
            <p>
                <label for="event_recurrence_count"><?php esc_html_e( 'Ou nombre d\'occurrences', 'theme-associatif' ); ?></label>
                <input type="number" name="event_recurrence_count" id="event_recurrence_count" value="<?php echo esc_attr( $count ); ?>" min="1" class="small-text" />
            </p>
            <?php if ( $parent_id > 0 ) : ?>
                <p class="description">
                    <?php
                    printf(
                        /* translators: %s: lien vers l'événement parent */
                        esc_html__( 'Occurrence de la série : %s', 'theme-associatif' ),
                        '<a href="' . esc_url( get_edit_post_link( $parent_id ) ) . '">' . esc_html( get_the_title( $parent_id ) ) . '</a>'
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>
        <script>
        (function() {
            var cb = document.getElementById('event_is_recurring_cb');
            var opts = document.getElementById('event_recurrence_options');
            cb.addEventListener('change', function() {
                opts.style.display = this.checked ? '' : 'none';
            });
        })();
        </script>
        <?php
    }

    /**
     * Affiche les statistiques RSVP dans la métabox dédiée.
     *
     * @param \WP_Post $post Post courant.
     *
     * @return void
     */
    public function renderRSVPStatsMetaBox( \WP_Post $post ): void {
        global $wpdb;

        $table = $wpdb->prefix . 'event_rsvp';

        // Vérification de l'existence de la table avant requête.
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            esc_html_e( 'Statistiques indisponibles.', 'theme-associatif' );
            return;
        }

        // Récupération des comptages par statut en une seule requête.
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT status, COUNT(*) AS total
                 FROM {$table}
                 WHERE event_id = %d
                 GROUP BY status",
                $post->ID
            ),
            ARRAY_A
        );

        $counts = [
            'confirmed'  => 0,
            'interested' => 0,
            'absent'     => 0,
            'cancelled'  => 0,
            'waitlisted' => 0,
        ];

        foreach ( $results as $row ) {
            if ( isset( $counts[ $row['status'] ] ) ) {
                $counts[ $row['status'] ] = (int) $row['total'];
            }
        }

        $capacity = (int) get_post_meta( $post->ID, '_event_capacity', true );
        ?>
        <ul style="margin:0;padding:0;list-style:none;">
            <li><?php printf( esc_html__( 'Confirmés : %d', 'theme-associatif' ), $counts['confirmed'] ); ?></li>
            <li><?php printf( esc_html__( 'Intéressés : %d', 'theme-associatif' ), $counts['interested'] ); ?></li>
            <li><?php printf( esc_html__( 'Absents : %d', 'theme-associatif' ), $counts['absent'] ); ?></li>
            <li><?php printf( esc_html__( 'Annulés : %d', 'theme-associatif' ), $counts['cancelled'] ); ?></li>
            <li><?php printf( esc_html__( 'Liste d\'attente : %d', 'theme-associatif' ), $counts['waitlisted'] ); ?></li>
        </ul>
        <?php if ( $capacity > 0 ) : ?>
            <p><?php printf( esc_html__( 'Capacité : %d places', 'theme-associatif' ), $capacity ); ?></p>
            <p><?php printf( esc_html__( 'Places restantes : %d', 'theme-associatif' ), max( 0, $capacity - $counts['confirmed'] ) ); ?></p>
        <?php endif; ?>
        <?php
    }

    /**
     * Sauvegarde les métadonnées des métaboxes lors de l'enregistrement du post.
     *
     * Effectue les vérifications de sécurité (nonce, autosave, permissions)
     * avant tout traitement.
     *
     * @param int      $post_id Identifiant du post sauvegardé.
     * @param \WP_Post $post    Objet post WordPress.
     *
     * @return void
     */
    public function saveMetaBoxes( int $post_id, \WP_Post $post ): void {
        // Vérification du nonce de sécurité.
        if ( ! isset( $_POST['event_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['event_meta_nonce'] ) ), 'event_meta_save' ) ) {
            return;
        }

        // Ne pas sauvegarder lors d'une sauvegarde automatique.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Vérification des permissions de l'utilisateur.
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sauvegarde de la date de début.
        if ( isset( $_POST['event_start_date'] ) ) {
            $start_raw = sanitize_text_field( wp_unslash( $_POST['event_start_date'] ) );
            $start_ts  = strtotime( $start_raw );
            if ( $start_ts !== false ) {
                update_post_meta( $post_id, '_event_start_date', date( 'Y-m-d H:i:s', $start_ts ) );
            }
        }

        // Sauvegarde de la date de fin.
        if ( isset( $_POST['event_end_date'] ) ) {
            $end_raw = sanitize_text_field( wp_unslash( $_POST['event_end_date'] ) );
            $end_ts  = strtotime( $end_raw );
            if ( $end_ts !== false ) {
                update_post_meta( $post_id, '_event_end_date', date( 'Y-m-d H:i:s', $end_ts ) );
            }
        }

        // Sauvegarde du lieu.
        if ( isset( $_POST['event_location'] ) ) {
            update_post_meta( $post_id, '_event_location', sanitize_text_field( wp_unslash( $_POST['event_location'] ) ) );
        }

        // Sauvegarde de la capacité.
        if ( isset( $_POST['event_capacity'] ) ) {
            update_post_meta( $post_id, '_event_capacity', absint( $_POST['event_capacity'] ) );
        }

        // Sauvegarde du type de tarif.
        $allowed_pricing = [ 'free', 'paid', 'free_price' ];
        if ( isset( $_POST['event_pricing_type'] ) && in_array( $_POST['event_pricing_type'], $allowed_pricing, true ) ) {
            update_post_meta( $post_id, '_event_pricing_type', $_POST['event_pricing_type'] );
        }

        // Conversion euros en centimes avant stockage.
        if ( isset( $_POST['event_price_euros'] ) ) {
            $price_euros  = (float) str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['event_price_euros'] ) ) );
            $price_cents  = (int) round( $price_euros * 100 );
            update_post_meta( $post_id, '_event_price_amount', max( 0, $price_cents ) );
        }

        // Sauvegarde de la récurrence.
        $is_recurring = isset( $_POST['event_is_recurring'] ) && $_POST['event_is_recurring'] === '1';
        update_post_meta( $post_id, '_event_is_recurring', $is_recurring ? '1' : '0' );

        if ( $is_recurring ) {
            $allowed_frequencies = [ 'daily', 'weekly', 'monthly', 'yearly' ];
            $frequency           = isset( $_POST['event_recurrence_frequency'] ) && in_array( $_POST['event_recurrence_frequency'], $allowed_frequencies, true )
                ? $_POST['event_recurrence_frequency']
                : 'weekly';

            $rule = [
                'frequency' => $frequency,
                'interval'  => absint( $_POST['event_recurrence_interval'] ?? 1 ),
                'end_date'  => sanitize_text_field( wp_unslash( $_POST['event_recurrence_end_date'] ?? '' ) ),
                'count'     => absint( $_POST['event_recurrence_count'] ?? 0 ),
            ];

            update_post_meta( $post_id, '_event_recurrence_rule', maybe_serialize( $rule ) );
        }
    }

    /**
     * Ajoute des colonnes personnalisées dans la liste des événements en admin.
     *
     * @param array $columns Colonnes par défaut.
     *
     * @return array
     */
    public function addAdminColumns( array $columns ): array {
        // Insertion des colonnes après le titre.
        $new_columns = [];
        foreach ( $columns as $key => $label ) {
            $new_columns[ $key ] = $label;
            if ( $key === 'title' ) {
                $new_columns['event_start_date'] = __( 'Date de début', 'theme-associatif' );
                $new_columns['event_location']   = __( 'Lieu', 'theme-associatif' );
                $new_columns['event_capacity']   = __( 'Capacité', 'theme-associatif' );
                $new_columns['event_rsvp_count'] = __( 'Inscrits', 'theme-associatif' );
            }
        }
        return $new_columns;
    }

    /**
     * Affiche le contenu des colonnes personnalisées.
     *
     * @param string $column  Identifiant de la colonne.
     * @param int    $post_id Identifiant du post.
     *
     * @return void
     */
    public function renderAdminColumn( string $column, int $post_id ): void {
        switch ( $column ) {
            case 'event_start_date':
                $date = get_post_meta( $post_id, '_event_start_date', true );
                if ( $date ) {
                    echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $date ) ) );
                } else {
                    echo '&mdash;';
                }
                break;

            case 'event_location':
                $location = get_post_meta( $post_id, '_event_location', true );
                echo $location ? esc_html( $location ) : '&mdash;';
                break;

            case 'event_capacity':
                $capacity = (int) get_post_meta( $post_id, '_event_capacity', true );
                echo $capacity > 0 ? esc_html( $capacity ) : esc_html__( 'Illimitée', 'theme-associatif' );
                break;

            case 'event_rsvp_count':
                global $wpdb;
                $table = $wpdb->prefix . 'event_rsvp';
                if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
                    $count = (int) $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT COUNT(*) FROM {$table} WHERE event_id = %d AND status = 'confirmed'",
                            $post_id
                        )
                    );
                    echo esc_html( $count );
                } else {
                    echo '0';
                }
                break;
        }
    }

    /**
     * Déclare les colonnes triables dans l'administration.
     *
     * @param array $columns Colonnes triables existantes.
     *
     * @return array
     */
    public function makeSortableColumns( array $columns ): array {
        $columns['event_start_date'] = 'event_start_date';
        return $columns;
    }

    /**
     * Adapte la requête d'administration pour le tri par date de début.
     *
     * @param \WP_Query $query Objet de requête WordPress.
     *
     * @return void
     */
    public function handleAdminSorting( \WP_Query $query ): void {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        if ( $query->get( 'post_type' ) !== self::POST_TYPE ) {
            return;
        }

        if ( $query->get( 'orderby' ) === 'event_start_date' ) {
            $query->set( 'meta_key', '_event_start_date' );
            $query->set( 'orderby', 'meta_value' );
        }
    }
}
