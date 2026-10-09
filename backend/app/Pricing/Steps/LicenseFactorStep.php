<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;
use LogicException;

/**
 * Coefficient selon l'ancienneté du permis, en années révolues à la date d'effet.
 */
final class LicenseFactorStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $years = $context->licenseYears;
        $band = $context->grid->licenseBandFor($years)
            ?? throw new LogicException("Aucune tranche d'ancienneté de permis pour {$years} ans.");

        $context->applyCoefficient(
            'license_seniority',
            "Ancienneté du permis : {$years} an".($years > 1 ? 's' : '')." ({$band->label})",
            $band->coefficient,
        );
    }
}
