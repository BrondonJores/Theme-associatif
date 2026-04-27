<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class IntegerRule
 *
 * Regle de validation : le champ doit etre un entier valide.
 * Accepte les chaines de caracteres representant des entiers.
 * Supporte optionnellement la definition d'une plage min/max.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class IntegerRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field doit etre un nombre entier.';

    /**
     * Valeur minimale autorisee (null = pas de minimum).
     *
     * @var int|null
     */
    private ?int $min;

    /**
     * Valeur maximale autorisee (null = pas de maximum).
     *
     * @var int|null
     */
    private ?int $max;

    /**
     * Constructeur avec plage optionnelle.
     *
     * @param int|null    $min     Valeur minimale (null = sans limite).
     * @param int|null    $max     Valeur maximale (null = sans limite).
     * @param string|null $message Message personnalise.
     */
    public function __construct(?int $min = null, ?int $max = null, ?string $message = null)
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la valeur est un entier dans la plage autorisee.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return false;
        }

        $intValue = (int) $value;

        if ($this->min !== null && $intValue < $this->min) {
            return false;
        }

        if ($this->max !== null && $intValue > $this->max) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'integer';
    }
}
