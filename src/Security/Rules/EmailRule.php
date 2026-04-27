<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class EmailRule
 *
 * Regle de validation : le champ doit etre une adresse email valide.
 * Utilise filter_var() avec FILTER_VALIDATE_EMAIL pour la validation.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class EmailRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field doit etre une adresse email valide.';

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la valeur est une adresse email valide.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'email';
    }
}
