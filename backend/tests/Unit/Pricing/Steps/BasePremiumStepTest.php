<?php

declare(strict_types=1);

use App\Pricing\Enums\LineType;
use App\Pricing\Enums\VehicleCategory;
use App\Pricing\Steps\BasePremiumStep;

it('prend la prime de base de la catégorie et de la tranche de puissance', function (VehicleCategory $category, int $cv, int $expected) {
    $context = pricingContext(['category' => $category, 'fiscal_power' => $cv]);

    (new BasePremiumStep)->apply($context);

    $line = lastLine($context);
    expect($line->type)->toBe(LineType::Base)
        ->and($line->amount)->toEqualDecimal((string) $expected)
        ->and($line->subtotal)->toEqualDecimal((string) $expected);
})->with([
    '2 CV promenade' => [VehicleCategory::PrivateAndBusiness, 2, 55_000],
    '3 CV = début de tranche' => [VehicleCategory::PrivateAndBusiness, 3, 72_000],
    '6 CV = fin de tranche' => [VehicleCategory::PrivateAndBusiness, 6, 72_000],
    '7 CV = tranche suivante' => [VehicleCategory::PrivateAndBusiness, 7, 90_000],
    '40 CV = tranche ouverte' => [VehicleCategory::PrivateAndBusiness, 40, 180_000],
    'taxi 8 CV' => [VehicleCategory::PublicPassengerTransport, 8, 195_000],
    'deux-roues 2 CV' => [VehicleCategory::TwoWheeler, 2, 18_000],
]);

it('lève une erreur si aucune tranche ne correspond', function () {
    (new BasePremiumStep)->apply(pricingContext(['fiscal_power' => 0]));
})->throws(LogicException::class);
