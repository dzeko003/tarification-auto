<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Pricing\Data\QuoteInput;
use App\Pricing\Enums\Usage;
use App\Pricing\Enums\VehicleCategory;
use Brick\Math\BigDecimal;
use DateTimeImmutable;

/**
 * Construit un QuoteInput « neutre » (tous les coefficients à 1,00 dans le barème 2025)
 * dont on ne change que ce qui intéresse le test.
 */
final class QuoteInputBuilder
{
    /**
     * @param  array{
     *     category?: VehicleCategory,
     *     fiscal_power?: int,
     *     usage?: Usage,
     *     birth_date?: string,
     *     license_date?: string,
     *     zone?: string,
     *     bonus_malus?: string,
     *     effective_date?: string,
     * }  $overrides
     */
    public static function make(array $overrides = []): QuoteInput
    {
        return new QuoteInput(
            vehicleCategory: $overrides['category'] ?? VehicleCategory::PrivateAndBusiness,
            fiscalPower: $overrides['fiscal_power'] ?? 7,
            usage: $overrides['usage'] ?? Usage::Personal,
            driverBirthDate: new DateTimeImmutable($overrides['birth_date'] ?? '1985-06-15'),
            licenseDate: new DateTimeImmutable($overrides['license_date'] ?? '2005-09-01'),
            zone: $overrides['zone'] ?? 'autres_villes',
            bonusMalus: BigDecimal::of($overrides['bonus_malus'] ?? '1.00'),
            effectiveDate: new DateTimeImmutable($overrides['effective_date'] ?? '2025-07-01'),
        );
    }
}
