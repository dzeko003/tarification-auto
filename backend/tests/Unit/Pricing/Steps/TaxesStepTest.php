<?php

declare(strict_types=1);

use App\Pricing\Data\TariffGrid;
use App\Pricing\Data\TaxRule;
use App\Pricing\Enums\LineType;
use App\Pricing\PricingContext;
use App\Pricing\Steps\TaxesStep;
use Tests\Fixtures\QuoteInputBuilder;
use Tests\Fixtures\SahelTariff2025;

/**
 * Contexte arrêté juste avant les taxes : prime nette $net, coût de police 5 000.
 *
 * @param  list<TaxRule>|null  $taxes  null = taxes du barème Sahel 2025
 */
function contextBeforeTaxes(int $net, ?array $taxes = null): PricingContext
{
    $grid = SahelTariff2025::grid();
    if ($taxes !== null) {
        $grid = new TariffGrid(...[...get_object_vars($grid), 'taxes' => $taxes]);
    }

    $context = new PricingContext(QuoteInputBuilder::make(), $grid);
    $context->start('base', 'Base', $net);
    $context->roundNetPremium('net', 'Net');
    $context->addFee('policy_fee', 'Frais', 5_000);

    return $context;
}

it('calcule chaque taxe sur son assiette et arrondit au franc', function () {
    // TCA : 14,5 % de (148 781 + 5 000) = 22 298,245 → 22 298
    // FGA :  2,5 % de 148 781           =  3 719,525 →  3 720 (demi-unité vers le haut)
    $context = contextBeforeTaxes(148_781);

    (new TaxesStep)->apply($context);

    [$tca, $fga] = array_slice($context->lines(), -2);
    expect($tca->type)->toBe(LineType::Tax)
        ->and($tca->amount)->toEqualDecimal('22298')
        ->and($tca->label)->toBe("Taxe sur les contrats d'assurance (14,5 % de 153 781 XAF)")
        ->and($fga->amount)->toEqualDecimal('3720')
        ->and($fga->label)->toBe('Contribution au fonds de garantie automobile (2,5 % de 148 781 XAF)')
        ->and($fga->subtotal)->toEqualDecimal('179799');
});

it('ajoute une taxe à montant fixe telle quelle', function () {
    $context = contextBeforeTaxes(100_000, [new TaxRule('timbre', 'Timbre', fixedAmount: 1_000)]);

    (new TaxesStep)->apply($context);

    expect(lastLine($context)->amount)->toEqualDecimal('1000')
        ->and(lastLine($context)->label)->toBe('Timbre')
        ->and($context->toResult()->taxes)->toBe(1_000);
});

it("n'ajoute aucune ligne si le barème n'a pas de taxe", function () {
    $context = contextBeforeTaxes(100_000, []);
    $before = count($context->lines());

    (new TaxesStep)->apply($context);

    expect($context->lines())->toHaveCount($before);
});
