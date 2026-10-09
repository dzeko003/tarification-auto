<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;
use LogicException;

final class UsageFactorStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $usage = $context->grid->usageCoefficientFor($context->input->usage)
            ?? throw new LogicException("Aucun coefficient pour l'usage {$context->input->usage->value}.");

        $context->applyCoefficient('usage', "Usage : {$usage->label}", $usage->coefficient);
    }
}
