<?php

declare(strict_types=1);

use App\Pricing\Data\TaxRule;
use App\Pricing\Enums\TaxBase;
use Brick\Math\BigDecimal;

it('accepte un taux avec une assiette', function () {
    expect(new TaxRule('tca', 'TCA', rate: BigDecimal::of('0.145'), base: TaxBase::NetPremium))
        ->toBeInstanceOf(TaxRule::class);
});

it('accepte un montant fixe', function () {
    expect(new TaxRule('timbre', 'Timbre', fixedAmount: 1_000))->toBeInstanceOf(TaxRule::class);
});

it('refuse une définition ambiguë ou incomplète', function (array $args) {
    new TaxRule('x', 'X', ...$args);
})->throws(InvalidArgumentException::class)->with([
    'rien' => [[]],
    'taux sans assiette' => [['rate' => BigDecimal::of('0.1')]],
    'taux et montant fixe' => [['rate' => BigDecimal::of('0.1'), 'base' => TaxBase::NetPremium, 'fixedAmount' => 100]],
]);
