<?php

use App\Pricing\Data\BreakdownLine;
use App\Pricing\Data\TariffGrid;
use App\Pricing\PricingContext;
use Brick\Math\BigDecimal;
use Tests\Fixtures\QuoteInputBuilder;
use Tests\Fixtures\SahelTariff2025;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Les tests Feature utilisent l'application Laravel. Les tests Unit du moteur (tests/Unit/Pricing)
| restent en PHP pur : ils n'ont besoin ni de Laravel ni de base de données.
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toEqualDecimal', function (string $expected) {
    expect($this->value)->toBeInstanceOf(BigDecimal::class);
    expect($this->value->isEqualTo($expected))
        ->toBeTrue("{$this->value} n'est pas égal à {$expected}");

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

/**
 * Contexte de calcul sur le barème Sahel 2025, avec un devis neutre modifié par $overrides.
 *
 * @param  array<string, mixed>  $overrides  voir QuoteInputBuilder::make()
 */
function pricingContext(array $overrides = [], ?TariffGrid $grid = null): PricingContext
{
    return new PricingContext(QuoteInputBuilder::make($overrides), $grid ?? SahelTariff2025::grid());
}

/**
 * Contexte déjà démarré avec une prime de base de 100 000, pour tester un coefficient isolément.
 *
 * @param  array<string, mixed>  $overrides
 */
function startedContext(array $overrides = []): PricingContext
{
    $context = pricingContext($overrides);
    $context->start('base_premium', 'Base de test', 100_000);

    return $context;
}

/**
 * Dernière ligne du détail d'un contexte.
 */
function lastLine(PricingContext $context): BreakdownLine
{
    $lines = $context->lines();

    return $lines[array_key_last($lines)];
}
