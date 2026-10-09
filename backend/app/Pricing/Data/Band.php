<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use Brick\Math\BigDecimal;
use InvalidArgumentException;

/**
 * Tranche de valeurs entières associée à un coefficient (âge, ancienneté du permis…).
 *
 * Convention unique pour toutes les tranches : intervalle semi-ouvert [min, max).
 * La borne min est incluse, la borne max est exclue ; max = null signifie « sans limite ».
 */
final readonly class Band
{
    public function __construct(
        public int $min,
        public ?int $max,
        public BigDecimal $coefficient,
        public string $label,
    ) {
        if ($max !== null && $max <= $min) {
            throw new InvalidArgumentException("Tranche invalide : max ({$max}) doit être supérieur à min ({$min}).");
        }
    }

    public function contains(int $value): bool
    {
        return $value >= $this->min && ($this->max === null || $value < $this->max);
    }
}
