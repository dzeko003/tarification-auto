<?php

declare(strict_types=1);

namespace App\Pricing;

use App\Pricing\Data\PricingResult;
use App\Pricing\Data\QuoteInput;
use App\Pricing\Data\TariffGrid;
use App\Pricing\Exceptions\InvalidQuoteInput;
use App\Pricing\Steps\AgeFactorStep;
use App\Pricing\Steps\BasePremiumStep;
use App\Pricing\Steps\BonusMalusStep;
use App\Pricing\Steps\LicenseFactorStep;
use App\Pricing\Steps\NetPremiumRoundingStep;
use App\Pricing\Steps\PolicyFeeStep;
use App\Pricing\Steps\PricingStep;
use App\Pricing\Steps\TaxesStep;
use App\Pricing\Steps\UsageFactorStep;
use App\Pricing\Steps\ZoneFactorStep;

/**
 * Point d'entrée du moteur : valide le devis puis applique les étapes du barème dans l'ordre.
 *
 * Fonction pure : mêmes données + même barème = même résultat, à chaque fois.
 */
final class Pricer
{
    /** @var list<PricingStep> */
    private readonly array $steps;

    /**
     * @param  list<PricingStep>|null  $steps  null = étapes par défaut
     */
    public function __construct(
        private readonly QuoteInputValidator $validator = new QuoteInputValidator,
        ?array $steps = null,
    ) {
        $this->steps = $steps ?? self::defaultSteps();
    }

    /**
     * @return list<PricingStep>
     */
    public static function defaultSteps(): array
    {
        return [
            new BasePremiumStep,
            new UsageFactorStep,
            new AgeFactorStep,
            new LicenseFactorStep,
            new ZoneFactorStep,
            new BonusMalusStep,
            new NetPremiumRoundingStep,
            new PolicyFeeStep,
            new TaxesStep,
        ];
    }

    /**
     * @throws InvalidQuoteInput
     */
    public function price(QuoteInput $input, TariffGrid $grid): PricingResult
    {
        $this->validator->validate($input, $grid);

        $context = new PricingContext($input, $grid);

        foreach ($this->steps as $step) {
            $step->apply($context);
        }

        return $context->toResult();
    }
}
