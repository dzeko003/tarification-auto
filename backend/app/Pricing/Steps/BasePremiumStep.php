<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;
use LogicException;

/**
 * Prime de base RC selon la catégorie du véhicule et sa puissance fiscale.
 */
final class BasePremiumStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $input = $context->input;
        $band = $context->grid->basePremiumFor($input->vehicleCategory, $input->fiscalPower)
            ?? throw new LogicException('Aucune prime de base pour ce véhicule.');

        $context->start('base_premium', $band->label(), $band->amount);
    }
}
