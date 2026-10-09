<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;

/**
 * Arrondi au franc (demi-unité vers le haut) de la prime nette, une fois tous les coefficients appliqués.
 */
final class NetPremiumRoundingStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        $context->roundNetPremium('net_premium', 'Prime RC nette (arrondie au franc)');
    }
}
