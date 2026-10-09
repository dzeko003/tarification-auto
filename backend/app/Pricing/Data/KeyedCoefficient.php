<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use Brick\Math\BigDecimal;

/**
 * Coefficient associé à une valeur précise (une zone, un usage…).
 */
final readonly class KeyedCoefficient
{
    public function __construct(
        public BigDecimal $coefficient,
        public string $label,
    ) {}
}
