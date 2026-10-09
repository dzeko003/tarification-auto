<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;

/**
 * Coefficient de réduction-majoration (bonus-malus) fourni dans le devis.
 * Ses bornes sont vérifiées en amont par le QuoteInputValidator.
 */
final class BonusMalusStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $context->applyCoefficient('bonus_malus', 'Coefficient bonus-malus', $context->input->bonusMalus);
    }
}
