<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class RequiredRule
 *
 * Regle de validation : le champ est obligatoire et ne peut pas etre vide.
 * Retourne false si la valeur est null, une chaine vide, ou un tableau vide.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class RequiredRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field est obligatoire.';

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la valeur est presente et non vide.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_array($value) && count($value) === 0) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'required';
    }
}
