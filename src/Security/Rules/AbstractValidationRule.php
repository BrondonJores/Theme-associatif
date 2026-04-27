<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class AbstractValidationRule
 *
 * Classe abstraite de base pour toutes les regles de validation.
 * Definit le contrat commun et fournit les utilitaires partages
 * entre les regles (gestion des parametres, message d'erreur).
 *
 * Principe de responsabilite unique : chaque sous-classe ne gere
 * qu'une seule regle de validation.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
abstract class AbstractValidationRule
{
    /**
     * Le message d'erreur de la regle.
     * Les sous-classes peuvent le surcharger via le constructeur.
     *
     * @var string
     */
    protected string $message;

    /**
     * Les parametres de la regle (ex: longueur minimale, pattern regex, etc.).
     *
     * @var array<int, mixed>
     */
    protected array $parameters = [];

    /**
     * Constructeur optionnel pour surcharger le message d'erreur.
     *
     * @param string|null        $message    Message personnalise (null = utiliser le defaut).
     * @param array<int, mixed>  $parameters Parametres de la regle.
     */
    public function __construct(?string $message = null, array $parameters = [])
    {
        if ($message !== null) {
            $this->message = $message;
        }

        $this->parameters = $parameters;
    }

    /**
     * Execute la regle de validation sur une valeur.
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ (pour les messages d'erreur).
     * @return bool   True si la valeur est valide.
     */
    abstract public function passes(mixed $value, string $field): bool;

    /**
     * Retourne le message d'erreur de la regle.
     * Le placeholder ':field' est remplace par le nom du champ.
     *
     * @param  string $field Le nom du champ valide.
     * @return string Le message d'erreur formate.
     */
    public function getMessage(string $field): string
    {
        return str_replace(':field', $field, $this->message);
    }

    /**
     * Retourne le nom unique de la regle (utilise pour l'enregistrement).
     *
     * @return string Le nom de la regle en snake_case.
     */
    abstract public function getName(): string;
}
