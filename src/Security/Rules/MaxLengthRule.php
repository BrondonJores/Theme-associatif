<?php

declare(strict_types=1);

namespace ThemeAssociatif\Security\Rules;

/**
 * Class MaxLengthRule
 *
 * Regle de validation : le champ ne doit pas depasser une longueur maximale.
 * Fonctionne avec les chaines de caracteres et les tableaux.
 * Utilise mb_strlen pour le support Unicode.
 *
 * @package ThemeAssociatif\Security\Rules
 * @since   1.0.0
 */
class MaxLengthRule extends AbstractValidationRule
{
    /**
     * Message d'erreur par defaut pour cette regle.
     *
     * @var string
     */
    protected string $message = 'Le champ :field ne peut pas depasser :max caractere(s).';

    /**
     * La longueur maximale autorisee.
     *
     * @var int
     */
    private int $max;

    /**
     * Constructeur avec longueur maximale.
     *
     * @param int         $max     La longueur maximale autorisee.
     * @param string|null $message Message personnalise.
     */
    public function __construct(int $max, ?string $message = null)
    {
        $this->max = $max;
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     *
     * @param  mixed  $value La valeur a valider.
     * @param  string $field Le nom du champ.
     * @return bool   True si la longueur est inferieure ou egale au maximum.
     */
    public function passes(mixed $value, string $field): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_string($value)) {
            return mb_strlen($value) <= $this->max;
        }

        if (is_array($value)) {
            return count($value) <= $this->max;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage(string $field): string
    {
        return str_replace(
            [':field', ':max'],
            [$field, (string) $this->max],
            $this->message
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'max_length';
    }
}
