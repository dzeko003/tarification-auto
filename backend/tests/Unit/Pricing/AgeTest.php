<?php

declare(strict_types=1);

use App\Pricing\Support\Age;

function yearsBetweenDates(string $from, string $to): int
{
    return Age::yearsBetween(new DateTimeImmutable($from), new DateTimeImmutable($to));
}

it('compte les années révolues', function (string $from, string $to, int $expected) {
    expect(yearsBetweenDates($from, $to))->toBe($expected);
})->with([
    'veille de l\'anniversaire' => ['2000-07-01', '2025-06-30', 24],
    'jour de l\'anniversaire' => ['2000-07-01', '2025-07-01', 25],
    'lendemain de l\'anniversaire' => ['2000-07-01', '2025-07-02', 25],
    'même jour' => ['2025-07-01', '2025-07-01', 0],
    'né un 29/02, le 28/02 d\'une année non bissextile' => ['2004-02-29', '2022-02-28', 17],
    'né un 29/02, le 01/03 d\'une année non bissextile' => ['2004-02-29', '2022-03-01', 18],
    'né un 29/02, le 29/02 d\'une année bissextile' => ['2004-02-29', '2024-02-29', 20],
]);

it('renvoie un nombre négatif quand la date de fin est antérieure', function () {
    expect(yearsBetweenDates('2025-07-01', '2023-07-01'))->toBe(-2);
});
