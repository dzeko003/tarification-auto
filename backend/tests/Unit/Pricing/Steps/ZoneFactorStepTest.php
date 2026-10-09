<?php

declare(strict_types=1);

use App\Pricing\Steps\ZoneFactorStep;

it('applique le coefficient de zone', function (string $zone, string $coefficient, string $label) {
    $context = startedContext(['zone' => $zone]);

    (new ZoneFactorStep)->apply($context);

    expect(lastLine($context)->coefficient)->toEqualDecimal($coefficient)
        ->and(lastLine($context)->label)->toBe("Zone : {$label}");
})->with([
    ['douala', '1.15', 'Douala'],
    ['yaounde', '1.10', 'Yaoundé'],
    ['autres_villes', '1.00', 'autres villes'],
    ['zone_rurale', '0.90', 'zone rurale'],
]);

it('lève une erreur pour une zone inconnue', function () {
    (new ZoneFactorStep)->apply(startedContext(['zone' => 'paris']));
})->throws(LogicException::class);
