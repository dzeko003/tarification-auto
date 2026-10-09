<?php

declare(strict_types=1);

use App\Pricing\Steps\LicenseFactorStep;

it("applique le coefficient d'ancienneté du permis à la date d'effet", function (string $licenseDate, string $coefficient) {
    $context = startedContext(['license_date' => $licenseDate, 'effective_date' => '2025-07-01']);

    (new LicenseFactorStep)->apply($context);

    expect(lastLine($context)->coefficient)->toEqualDecimal($coefficient);
})->with([
    'obtenu le jour même' => ['2025-07-01', '1.30'],
    '364 jours' => ['2024-07-02', '1.30'],
    '1 an pile' => ['2024-07-01', '1.15'],
    '2 ans (veille des 3)' => ['2022-07-02', '1.15'],
    '3 ans pile' => ['2022-07-01', '1.00'],
    '20 ans' => ['2005-07-01', '1.00'],
]);

it('accorde « an » au singulier et au pluriel dans le libellé', function (string $licenseDate, string $label) {
    $context = startedContext(['license_date' => $licenseDate, 'effective_date' => '2025-07-01']);

    (new LicenseFactorStep)->apply($context);

    expect(lastLine($context)->label)->toBe($label);
})->with([
    ['2024-07-01', 'Ancienneté du permis : 1 an (1 à 2 ans)'],
    ['2023-07-01', 'Ancienneté du permis : 2 ans (1 à 2 ans)'],
]);
