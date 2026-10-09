<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use App\Pricing\Enums\Usage;
use App\Pricing\Enums\VehicleCategory;
use Brick\Math\BigDecimal;
use DateTimeImmutable;

/**
 * Informations saisies pour un devis : le véhicule, le conducteur et le contrat.
 */
final readonly class QuoteInput
{
    public DateTimeImmutable $driverBirthDate;

    public DateTimeImmutable $licenseDate;

    public DateTimeImmutable $effectiveDate;

    public function __construct(
        public VehicleCategory $vehicleCategory,
        public int $fiscalPower,
        public Usage $usage,
        DateTimeImmutable $driverBirthDate,
        DateTimeImmutable $licenseDate,
        public string $zone,
        public BigDecimal $bonusMalus,
        DateTimeImmutable $effectiveDate,
    ) {
        // Seule la date compte : on ignore l'heure pour que les calculs d'âge soient stables.
        $this->driverBirthDate = $driverBirthDate->setTime(0, 0);
        $this->licenseDate = $licenseDate->setTime(0, 0);
        $this->effectiveDate = $effectiveDate->setTime(0, 0);
    }
}
