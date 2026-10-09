<?php

declare(strict_types=1);

namespace App\Pricing\Steps;

use App\Pricing\Data\TaxRule;
use App\Pricing\Enums\TaxBase;
use App\Pricing\PricingContext;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

/**
 * Taxes et contributions, dans l'ordre du barème. Chaque taxe est arrondie au franc séparément.
 */
final class TaxesStep implements PricingStep
{
    public function apply(PricingContext $context): void
    {
        foreach ($context->grid->taxes as $tax) {
            if ($tax->fixedAmount !== null) {
                $context->addTax($tax->code, $tax->label, $tax->fixedAmount);

                continue;
            }

            $base = $this->baseAmount($tax, $context);
            $amount = BigDecimal::of($base)->multipliedBy($tax->rate)->toScale(0, RoundingMode::HalfUp)->toInt();

            $context->addTax(
                $tax->code,
                sprintf('%s (%s %% de %s %s)', $tax->label, $this->percent($tax->rate), $this->money($base), $context->grid->currency),
                $amount,
            );
        }
    }

    private function baseAmount(TaxRule $tax, PricingContext $context): int
    {
        return match ($tax->base) {
            TaxBase::NetPremium => $context->netPremium(),
            TaxBase::NetPremiumAndFees => $context->netPremium() + $context->fees(),
        };
    }

    private function percent(BigDecimal $rate): string
    {
        return str_replace('.', ',', (string) $rate->multipliedBy(100)->strippedOfTrailingZeros());
    }

    private function money(int $amount): string
    {
        return number_format($amount, 0, ',', ' ');
    }
}
