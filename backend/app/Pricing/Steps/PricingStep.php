<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\PricingContext;

/**
 * Une règle du barème. Chaque étape lit le contexte et y ajoute sa contribution au calcul.
 */
interface PricingStep
{
    public function apply(PricingContext $context): void;
}
