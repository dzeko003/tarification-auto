<?php

declare(strict_types=1);

namespace App\Pricing\Data;

/**
 * Résultat d'un calcul de prime. Tous les montants sont en francs CFA entiers.
 */
final readonly class PricingResult
{
    /**
     * @param  list<BreakdownLine>  $lines
     */
    public function __construct(
        public string $tariffVersion,
        public string $currency,
        public int $driverAge,
        public int $licenseYears,
        public int $netPremium,
        public int $fees,
        public int $taxes,
        public int $total,
        public array $lines,
    ) {}
}
