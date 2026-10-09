<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;
use LogicException;

final class ZoneFactorStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $zone = $context->grid->zoneCoefficientFor($context->input->zone)
            ?? throw new LogicException("Zone inconnue : {$context->input->zone}.");

        $context->applyCoefficient('zone', "Zone : {$zone->label}", $zone->coefficient);
    }
}
