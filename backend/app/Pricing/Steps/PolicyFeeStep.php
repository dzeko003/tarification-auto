<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;

final class PolicyFeeStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        if ($context->grid->policyFee > 0) {
            $context->addFee('policy_fee', 'Accessoires (coût de police)', $context->grid->policyFee);
        }
    }
}
