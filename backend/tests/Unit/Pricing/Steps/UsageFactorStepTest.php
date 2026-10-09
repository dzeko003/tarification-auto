<?php

declare(strict_types=1);

use App\Pricing\Enums\Usage;
use App\Pricing\Steps\UsageFactorStep;

it("applique le coefficient d'usage", function (Usage $usage, string $coefficient, string $subtotal) {
    $context = startedContext(['usage' => $usage]);

    (new UsageFactorStep)->apply($context);

    expect(lastLine($context)->coefficient)->toEqualDecimal($coefficient)
        ->and(lastLine($context)->subtotal)->toEqualDecimal($subtotal);
})->with([
    'personnel' => [Usage::Personal, '1.00', '100000'],
    'professionnel' => [Usage::Professional, '1.15', '115000'],
]);
