<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use App\Pricing\Enums\Usage;
use App\Pricing\Enums\VehicleCategory;
use Brick\Math\BigDecimal;
use InvalidArgumentException;

/**
 * Une version complète du barème : tout ce dont le moteur a besoin pour calculer une prime.
 *
 * Le moteur ne contient aucun montant ni coefficient : tout vient d'ici.
 */
final readonly class TariffGrid
{
    /**
     * @param  list<BasePremiumBand>  $basePremiums
     * @param  array<string, KeyedCoefficient>  $usageCoefficients  clé = valeur de l'enum Usage
     * @param  list<Band>  $ageBands  âge du conducteur en années révolues
     * @param  list<Band>  $licenseBands  ancienneté du permis en années révolues
     * @param  array<string, KeyedCoefficient>  $zoneCoefficients  clé = code de zone
     * @param  list<TaxRule>  $taxes  appliquées dans cet ordre
     */
    public function __construct(
        public string $version,
        public string $currency,
        public array $basePremiums,
        public array $usageCoefficients,
        public array $ageBands,
        public array $licenseBands,
        public array $zoneCoefficients,
        public BigDecimal $bonusMalusMin,
        public BigDecimal $bonusMalusMax,
        public int $policyFee,
        public array $taxes,
        public int $minimumDriverAge = 18,
        public int $minimumLicenseAge = 18,
    ) {
        if ($bonusMalusMin->isGreaterThan($bonusMalusMax)) {
            throw new InvalidArgumentException('Bornes de bonus-malus incohérentes.');
        }
        if ($policyFee < 0) {
            throw new InvalidArgumentException('Le coût de police ne peut pas être négatif.');
        }
    }

    public function basePremiumFor(VehicleCategory $category, int $fiscalPower): ?BasePremiumBand
    {
        foreach ($this->basePremiums as $band) {
            if ($band->matches($category, $fiscalPower)) {
                return $band;
            }
        }

        return null;
    }

    public function usageCoefficientFor(Usage $usage): ?KeyedCoefficient
    {
        return $this->usageCoefficients[$usage->value] ?? null;
    }

    public function ageBandFor(int $age): ?Band
    {
        return self::findBand($this->ageBands, $age);
    }

    public function licenseBandFor(int $years): ?Band
    {
        return self::findBand($this->licenseBands, $years);
    }

    public function zoneCoefficientFor(string $zone): ?KeyedCoefficient
    {
        return $this->zoneCoefficients[$zone] ?? null;
    }

    /**
     * @param  list<Band>  $bands
     */
    private static function findBand(array $bands, int $value): ?Band
    {
        foreach ($bands as $band) {
            if ($band->contains($value)) {
                return $band;
            }
        }

        return null;
    }
}
