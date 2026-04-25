<?php
/**
 * Classe Event
 *
 * Modèle de données représentant un événement associatif.
 * Implémente EventInterface et encapsule toutes les métadonnées
 * stockées dans les champs personnalisés du Custom Post Type 'event'.
 *
 * Respecte le principe de responsabilité unique (SRP) :
 * cette classe ne fait que représenter et exposer des données ;
 * elle ne contient aucune logique métier ni interaction avec la base de données.
 *
 * @package ThemeAssociatif\Models
 * @since   1.0.0
 */

namespace ThemeAssociatif\Models;

use ThemeAssociatif\Interfaces\EventInterface;

/**
 * Classe Event
 */
class Event implements EventInterface {

    /**
     * Identifiant WordPress du post.
     *
     * @var int
     */
    private int $id;

    /**
     * Titre de l'événement.
     *
     * @var string
     */
    private string $title;

    /**
     * Description complète (contenu du post).
     *
     * @var string
     */
    private string $description;

    /**
     * Date et heure de début (format ISO 8601 : Y-m-d H:i:s).
     *
     * @var string
     */
    private string $start_date;

    /**
     * Date et heure de fin (format ISO 8601 : Y-m-d H:i:s).
     *
     * @var string
     */
    private string $end_date;

    /**
     * Lieu de l'événement (adresse textuelle ou nom de salle).
     *
     * @var string
     */
    private string $location;

    /**
     * Capacité maximale de participants. 0 signifie illimitée.
     *
     * @var int
     */
    private int $capacity;

    /**
     * Type de tarif : 'free', 'paid' ou 'free_price'.
     *
     * @var string
     */
    private string $pricing_type;

    /**
     * Montant du tarif en centimes. 0 si gratuit.
     *
     * @var int
     */
    private int $price_amount;

    /**
     * Statut de publication WordPress (publish, draft, private, trash, cancelled).
     *
     * @var string
     */
    private string $status;

    /**
     * Indique si l'événement appartient à une série récurrente.
     *
     * @var bool
     */
    private bool $is_recurring;

    /**
     * Identifiant de l'événement parent pour les occurrences récurrentes. 0 si aucun.
     *
     * @var int
     */
    private int $recurrence_parent_id;

    /**
     * Tableau des identifiants de catégories d'événements.
     *
     * @var int[]
     */
    private array $category_ids;

    /**
     * URL de l'image mise en avant.
     *
     * @var string
     */
    private string $thumbnail_url;

    /**
     * Règle de récurrence sérialisée (tableau de configuration).
     *
     * @var array
     */
    private array $recurrence_rule;

    /**
     * Constructeur.
     *
     * Initialise le modèle à partir d'un post WordPress et de ses métadonnées.
     *
     * @param \WP_Post $post      Objet post WordPress de type 'event'.
     * @param array    $meta      Tableau de métadonnées post (issu de get_post_meta).
     * @param int[]    $term_ids  Identifiants des termes de la taxonomie event_category.
     */
    public function __construct( \WP_Post $post, array $meta = [], array $term_ids = [] ) {
        $this->id                   = (int) $post->ID;
        $this->title                = (string) $post->post_title;
        $this->description          = (string) $post->post_content;
        $this->status               = (string) $post->post_status;
        $this->start_date           = (string) ( $meta['_event_start_date'][0] ?? '' );
        $this->end_date             = (string) ( $meta['_event_end_date'][0] ?? '' );
        $this->location             = (string) ( $meta['_event_location'][0] ?? '' );
        $this->capacity             = (int) ( $meta['_event_capacity'][0] ?? 0 );
        $this->pricing_type         = (string) ( $meta['_event_pricing_type'][0] ?? 'free' );
        $this->price_amount         = (int) ( $meta['_event_price_amount'][0] ?? 0 );
        $this->is_recurring         = (bool) ( $meta['_event_is_recurring'][0] ?? false );
        $this->recurrence_parent_id = (int) ( $meta['_event_recurrence_parent_id'][0] ?? 0 );
        $this->thumbnail_url        = (string) ( $meta['_event_thumbnail_url'][0] ?? '' );
        $this->category_ids         = array_map( 'intval', $term_ids );

        // Désérialisation de la règle de récurrence si elle est stockée.
        $raw_rule              = $meta['_event_recurrence_rule'][0] ?? '';
        $this->recurrence_rule = $raw_rule ? (array) maybe_unserialize( $raw_rule ) : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getStartDate(): string {
        return $this->start_date;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndDate(): string {
        return $this->end_date;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocation(): string {
        return $this->location;
    }

    /**
     * {@inheritdoc}
     */
    public function getCapacity(): int {
        return $this->capacity;
    }

    /**
     * {@inheritdoc}
     */
    public function getPricingType(): string {
        return $this->pricing_type;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriceAmount(): int {
        return $this->price_amount;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): string {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isRecurring(): bool {
        return $this->is_recurring;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecurrenceParentId(): int {
        return $this->recurrence_parent_id;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIds(): array {
        return $this->category_ids;
    }

    /**
     * Retourne l'URL de l'image mise en avant de l'événement.
     *
     * @return string
     */
    public function getThumbnailUrl(): string {
        return $this->thumbnail_url;
    }

    /**
     * Retourne la règle de récurrence sous forme de tableau.
     *
     * @return array
     */
    public function getRecurrenceRule(): array {
        return $this->recurrence_rule;
    }

    /**
     * Retourne une représentation en tableau du modèle, utile pour
     * la sérialisation JSON (ex : réponses API REST ou données JavaScript).
     *
     * @return array
     */
    public function toArray(): array {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => $this->description,
            'start_date'            => $this->start_date,
            'end_date'              => $this->end_date,
            'location'              => $this->location,
            'capacity'              => $this->capacity,
            'pricing_type'          => $this->pricing_type,
            'price_amount'          => $this->price_amount,
            'status'                => $this->status,
            'is_recurring'          => $this->is_recurring,
            'recurrence_parent_id'  => $this->recurrence_parent_id,
            'category_ids'          => $this->category_ids,
            'thumbnail_url'         => $this->thumbnail_url,
            'recurrence_rule'       => $this->recurrence_rule,
        ];
    }
}
