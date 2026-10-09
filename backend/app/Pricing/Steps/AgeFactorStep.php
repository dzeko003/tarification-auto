<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;
use LogicException;

/**
 * Coefficient selon l'âge du conducteur, en années révolues à la date d'effet.
 */
final class AgeFactorStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $age = $context->driverAge;
        $band = $context->grid->ageBandFor($age)
            ?? throw new LogicException("Aucune tranche d'âge pour {$age} ans.");

        $context->applyCoefficient('driver_age', "Âge du conducteur : {$age} ans ({$band->label})", $band->coefficient);
    }
}
