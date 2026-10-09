<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use App\Pricing\Enums\TaxBase;
use Brick\Math\BigDecimal;
use InvalidArgumentException;

/**
 * Taxe ou contribution : soit un taux appliqué à une assiette, soit un montant fixe.
 */
final readonly class TaxRule
{
    public function __construct(
        public string $code,
        public string $label,
        public ?BigDecimal $rate = null,
        public ?TaxBase $base = null,
        public ?int $fixedAmount = null,
    ) {
        $isRate = $rate !== null && $base !== null && $fixedAmount === null;
        $isFixed = $rate === null && $base === null && $fixedAmount !== null;

        if (! $isRate && ! $isFixed) {
            throw new InvalidArgumentException(
                "Taxe {$code} : il faut soit un taux et une assiette, soit un montant fixe."
            );
        }
    }
}
