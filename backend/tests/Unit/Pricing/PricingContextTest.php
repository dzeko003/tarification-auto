<?php

declare(strict_types=1);

use App\Pricing\Enums\LineType;
use Brick\Math\BigDecimal;

it("calcule l'âge et l'ancienneté du permis à la date d'effet, pas à la date du jour", function () {
    $context = pricingContext([
        'birth_date' => '2000-07-01',
        'license_date' => '2022-07-02',
        'effective_date' => '2025-07-01',
    ]);

    expect($context->driverAge)->toBe(25)
        ->and($context->licenseYears)->toBe(2);
});

it('enregistre une ligne pour chaque opération, avec le sous-total cumulé', function () {
    $context = pricingContext();
    $context->start('base', 'Base', 100_000);
    $context->applyCoefficient('coef', 'Coef', BigDecimal::of('1.255'));
    $context->roundNetPremium('net', 'Net');
    $context->addFee('fee', 'Frais', 5_000);
    $context->addTax('tax', 'Taxe', 1_234);

    $lines = $context->lines();

    expect(array_map(fn ($l) => $l->type, $lines))->toBe([
        LineType::Base, LineType::Coefficient, LineType::Rounding, LineType::Fee, LineType::Tax,
    ]);
    expect($lines[1]->subtotal)->toEqualDecimal('125500.000')
        ->and($lines[2]->subtotal)->toEqualDecimal('125500')
        ->and($lines[4]->subtotal)->toEqualDecimal('131734');

    $result = $context->toResult();
    expect($result->netPremium)->toBe(125_500)
        ->and($result->fees)->toBe(5_000)
        ->and($result->taxes)->toBe(1_234)
        ->and($result->total)->toBe(131_734);
});

it('arrondit la prime nette au franc, demi-unité vers le haut', function (string $coefficient, int $expected) {
    $context = pricingContext();
    $context->start('base', 'Base', 1_000);
    $context->applyCoefficient('coef', 'Coef', BigDecimal::of($coefficient));
    $context->roundNetPremium('net', 'Net');

    expect($context->netPremium())->toBe($expected);
})->with([
    'x,4 arrondi en dessous' => ['1.0004', 1_000],
    'x,5 arrondi au-dessus' => ['1.0005', 1_001],
    'x,6 arrondi au-dessus' => ['1.0006', 1_001],
]);

it('interdit un coefficient avant la prime de base', function () {
    pricingContext()->applyCoefficient('coef', 'Coef', BigDecimal::one());
})->throws(LogicException::class);

it('interdit de démarrer deux fois', function () {
    $context = startedContext();
    $context->start('base', 'Base', 1);
})->throws(LogicException::class);

it("interdit un coefficient après l'arrondi de la prime nette", function () {
    $context = startedContext();
    $context->roundNetPremium('net', 'Net');
    $context->applyCoefficient('coef', 'Coef', BigDecimal::one());
})->throws(LogicException::class);

it("interdit frais et taxes avant l'arrondi de la prime nette", function () {
    startedContext()->addTax('tax', 'Taxe', 100);
})->throws(LogicException::class);
