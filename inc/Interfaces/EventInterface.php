<?php
/**
 * Interface EventInterface
 *
 * Contrat définissant la structure d'un événement associatif.
 * Toute implémentation de modèle d'événement doit respecter ce contrat.
 *
 * @package ThemeAssociatif\Interfaces
 * @since   1.0.0
 */

namespace ThemeAssociatif\Interfaces;

/**
 * Interface EventInterface
 *
 * Définit les accesseurs obligatoires pour un objet événement.
 * Respecte le principe d'interface segregation (ISP de SOLID) :
 * seules les méthodes strictement nécessaires à la représentation
 * d'un événement sont incluses ici.
 */
interface EventInterface {

    /**
     * Retourne l'identifiant unique de l'événement.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Retourne le titre de l'événement.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Retourne la description complète de l'événement.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Retourne la date et heure de début (format ISO 8601).
     *
     * @return string
     */
    public function getStartDate(): string;

    /**
     * Retourne la date et heure de fin (format ISO 8601).
     *
     * @return string
     */
    public function getEndDate(): string;

    /**
     * Retourne le lieu de l'événement.
     *
     * @return string
     */
    public function getLocation(): string;

    /**
     * Retourne la capacité maximale de participants (0 = illimitée).
     *
     * @return int
     */
    public function getCapacity(): int;

    /**
     * Retourne le type de tarif : 'free', 'paid' ou 'free_price'.
     *
     * @return string
     */
    public function getPricingType(): string;

    /**
     * Retourne le montant du tarif en centimes (0 si gratuit).
     *
     * @return int
     */
    public function getPriceAmount(): int;

    /**
     * Retourne le statut de publication WordPress de l'événement.
     *
     * @return string
     */
    public function getStatus(): string;

    /**
     * Indique si l'événement fait partie d'une série récurrente.
     *
     * @return bool
     */
    public function isRecurring(): bool;

    /**
     * Retourne l'identifiant de la série récurrente parente (0 si aucune).
     *
     * @return int
     */
    public function getRecurrenceParentId(): int;

    /**
     * Retourne les identifiants des catégories associées à l'événement.
     *
     * @return int[]
     */
    public function getCategoryIds(): array;
}
