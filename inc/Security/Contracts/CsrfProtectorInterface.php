<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Contracts;

/**
 * Interface CsrfProtectorInterface
 *
 * Contrat pour la protection automatique CSRF sur les formulaires.
 * Le CSRF (Cross-Site Request Forgery) est une attaque qui force un utilisateur
 * authentifie a executer des actions non desirees.
 *
 * Ce service s'appuie sur le systeme de nonces WordPress et l'ajoute
 * automatiquement a tous les formulaires du theme.
 *
 * @package ThemeAssociatif\Security\Contracts
 * @since   1.0.0
 */
interface CsrfProtectorInterface
{
    /**
     * Initialise la protection CSRF sur les formulaires.
     * Doit etre appele lors de l'initialisation du theme via les hooks WordPress.
     *
     * @return void
     */
    public function initialize(): void;

    /**
     * Protege un formulaire en injectant le champ nonce CSRF.
     *
     * @param  string $action     L'identifiant de l'action du formulaire.
     * @param  string $fieldName  Le nom du champ nonce (par defaut '_csrf_token').
     * @return string Le champ hidden HTML a inserer dans le formulaire.
     */
    public function protectForm(string $action, string $fieldName = '_csrf_token'): string;

    /**
     * Valide le token CSRF d'une requete entrante.
     *
     * @param  string $action    L'action attendue.
     * @param  string $fieldName Le nom du champ nonce dans la requete.
     * @return bool True si le token est valide.
     */
    public function validateRequest(string $action, string $fieldName = '_csrf_token'): bool;

    /**
     * Valide et interrompt l'execution si le token CSRF est invalide.
     * A appeler en debut de traitement de tout formulaire soumis.
     *
     * @param  string $action    L'action attendue.
     * @param  string $fieldName Le nom du champ nonce dans la requete.
     * @return void
     */
    public function assertValid(string $action, string $fieldName = '_csrf_token'): void;

    /**
     * Genere une action CSRF unique basee sur le contexte courant.
     * Combine le nom du formulaire avec l'identifiant de l'utilisateur
     * pour creer une action specifique et non predictible.
     *
     * @param  string $formName Le nom semantique du formulaire.
     * @return string L'identifiant d'action CSRF genere.
     */
    public function generateAction(string $formName): string;
}
