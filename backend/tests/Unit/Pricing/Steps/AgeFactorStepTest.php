<?php

declare(strict_types=1);

use App\Pricing\Steps\AgeFactorStep;

it("applique le coefficient de la tranche d'âge à la date d'effet", function (string $birthDate, string $coefficient) {
    $context = startedContext(['birth_date' => $birthDate, 'effective_date' => '2025-07-01']);

    (new AgeFactorStep)->apply($context);

    expect(lastLine($context)->coefficient)->toEqualDecimal($coefficient);
})->with([
    '18 ans pile' => ['2007-07-01', '1.40'],
    '20 ans (veille des 21)' => ['2004-07-02', '1.40'],
    '21 ans pile' => ['2004-07-01', '1.25'],
    '24 ans (veille des 25)' => ['2000-07-02', '1.25'],
    '25 ans pile' => ['2000-07-01', '1.00'],
    '64 ans' => ['1961-01-01', '1.00'],
    '65 ans pile' => ['1960-07-01', '1.10'],
    '75 ans pile' => ['1950-07-01', '1.25'],
]);

it("indique l'âge et la tranche dans le libellé", function () {
    $context = startedContext(['birth_date' => '2001-03-10', 'effective_date' => '2025-07-01']);

    (new AgeFactorStep)->apply($context);

    expect(lastLine($context)->label)->toBe('Âge du conducteur : 24 ans (21 à 24 ans)');
});
