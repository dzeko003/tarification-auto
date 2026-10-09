<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use App\Pricing\Enums\VehicleCategory;
use InvalidArgumentException;

/**
 * Prime de base annuelle pour une catégorie de véhicule et une tranche de puissance fiscale.
 *
 * Même convention que Band : tranche de puissance [cvMin, cvMax), cvMax = null = sans limite.
 */
final readonly class BasePremiumBand
{
    public function __construct(
        public VehicleCategory $category,
        public int $cvMin,
        public ?int $cvMax,
        public int $amount,
    ) {
        if ($cvMax !== null && $cvMax <= $cvMin) {
            throw new InvalidArgumentException("Tranche de puissance invalide : {$cvMin} à {$cvMax} CV.");
        }
        if ($amount <= 0) {
            throw new InvalidArgumentException('La prime de base doit être positive.');
        }
    }

    public function matches(VehicleCategory $category, int $fiscalPower): bool
    {
        return $this->category === $category
            && $fiscalPower >= $this->cvMin
            && ($this->cvMax === null || $fiscalPower < $this->cvMax);
    }

    public function label(): string
    {
        $range = $this->cvMax === null
            ? "{$this->cvMin} CV et plus"
            : "{$this->cvMin} à ".($this->cvMax - 1).' CV';

        return "Prime de base RC — {$this->category->label()}, {$range}";
    }
}
