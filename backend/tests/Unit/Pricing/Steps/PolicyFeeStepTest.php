<?php

declare(strict_types=1);

use App\Pricing\Enums\LineType;
use App\Pricing\Steps\PolicyFeeStep;

it('ajoute le coût de police après la prime nette', function () {
    $context = startedContext();
    $context->roundNetPremium('net', 'Net');

    (new PolicyFeeStep)->apply($context);

    expect(lastLine($context)->type)->toBe(LineType::Fee)
        ->and(lastLine($context)->amount)->toEqualDecimal('5000')
        ->and(lastLine($context)->subtotal)->toEqualDecimal('105000')
        ->and($context->fees())->toBe(5_000);
});
