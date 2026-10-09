<?php

declare(strict_types=1);

use App\Pricing\Steps\BonusMalusStep;

it('applique le coefficient bonus-malus saisi', function (string $bonusMalus, string $subtotal) {
    $context = startedContext(['bonus_malus' => $bonusMalus]);

    (new BonusMalusStep)->apply($context);

    expect(lastLine($context)->coefficient)->toEqualDecimal($bonusMalus)
        ->and(lastLine($context)->subtotal)->toEqualDecimal($subtotal);
})->with([
    'bonus maximum' => ['0.50', '50000'],
    'neutre' => ['1.00', '100000'],
    'malus' => ['1.75', '175000'],
]);
