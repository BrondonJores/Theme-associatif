<?php
/**
 * Interface Hookable - Capacite d'enregistrement des hooks WordPress
 *
 * Definit le contrat pour tout objet capable d'enregistrer ses propres
 * hooks WordPress (actions et filtres). Cette interface permet de standardiser
 * la facon dont les services s'integrent dans le cycle de vie WordPress.
 *
 * Principe SOLID applique :
 * - Single Responsibility : L'objet est seul responsable de ses hooks.
 * - Interface Segregation : Cette interface ne definit qu'une seule capacite.
 * - Open/Closed           : Nouveaux services peuvent s'enregistrer sans modifier le code existant.
 *
 * @package ThemeAssociatif\Contracts
 * @since   1.0.0
 */

declare(strict_types=1);

namespace ThemeAssociatif\Contracts;

/**
 * HookableInterface
 *
 * Contrat pour les services qui enregistrent des hooks WordPress.
 * Tout service implementant cette interface declare explicitement
 * qu'il interagit avec le systeme d'evenements de WordPress.
 */
interface HookableInterface
{
    /**
     * Enregistrer toutes les actions et filtres WordPress du service.
     *
     * Cette methode est le point d'entree unique pour l'enregistrement
     * des hooks. Elle est appelee par le ThemeManager lors de la phase boot.
     *
     * @return void
     */
    public function registerHooks(): void;
}
