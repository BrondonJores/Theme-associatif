<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class MinLengthRule
 *
 * Regle de validation : le champ doit avoir une longueur minimale.
 * Fonctionne avec les chaines de caracteres et les tableaux.
 * Utilise mb_strlen pour le support Unicode.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class MinLengthRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field doit contenir au moins :min caractere(s).';

    /**
     * La longueur minimale requise.
     *
     * @var int
     */
    private int $min;

    /**
     * Constructeur avec longueur minimale.
     *
     * @param int         $min     La longueur minimale requise.
     * @param string|null $message Message personnalise.
     */
    public function __construct(int $min, ?string $message = null)
    {
        $this->min = $min;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la longueur est superieure ou egale au minimum.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_string($value)) {
            return mb_strlen($value) >= $this->min;
        }

        if (is_array($value)) {
            return count($value) >= $this->min;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        return str_replace(
            [':field', ':min'],
            [$field, (string) $this->min],
            $this->message
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'min_length';
    }
}
