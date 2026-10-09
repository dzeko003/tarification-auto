<?php

declare(strict_types=1);

use App\Pricing\Data\PricingResult;
use App\Pricing\Data\QuoteInput;
use App\Pricing\Enums\LineType;
use App\Pricing\Enums\Usage;
use App\Pricing\Enums\VehicleCategory;
use App\Pricing\Pricer;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Tests\Fixtures\QuoteInputBuilder;
use Tests\Fixtures\SahelTariff2025;

/*
 * Propriétés vérifiées sur des profils tirés au hasard (graine fixe : les tests restent reproductibles).
 */

/**
 * @return list<QuoteInput>
 */
function randomProfiles(int $count, int $seed = 2025): array
{
    mt_srand($seed);
    $zones = ['douala', 'yaounde', 'autres_villes', 'zone_rurale'];
    $categories = VehicleCategory::cases();
    $usages = Usage::cases();
    $profiles = [];

    for ($i = 0; $i < $count; $i++) {
        $age = mt_rand(18, 90);
        $birth = (new DateTimeImmutable('2025-07-01'))->modify("-{$age} years")->modify('-'.mt_rand(0, 364).' days');
        $licenseAge = mt_rand(18, max(18, $age));
        $license = min($birth->modify("+{$licenseAge} years")->modify('+'.mt_rand(0, 364).' days'), new DateTimeImmutable('2025-07-01'));

        $profiles[] = QuoteInputBuilder::make([
            'category' => $categories[array_rand($categories)],
            'fiscal_power' => mt_rand(1, 40),
            'usage' => $usages[array_rand($usages)],
            'birth_date' => $birth->format('Y-m-d'),
            'license_date' => $license->format('Y-m-d'),
            'zone' => $zones[array_rand($zones)],
            'bonus_malus' => (string) BigDecimal::of(mt_rand(50, 350))->dividedBy(100, 2),
        ]);
    }

    return $profiles;
}

function priceWith(QuoteInput $input): PricingResult
{
    return (new Pricer)->price($input, SahelTariff2025::grid());
}

function withBonusMalus(QuoteInput $input, string $bonusMalus): QuoteInput
{
    return new QuoteInput(
        $input->vehicleCategory, $input->fiscalPower, $input->usage, $input->driverBirthDate,
        $input->licenseDate, $input->zone, BigDecimal::of($bonusMalus), $input->effectiveDate,
    );
}

it('donne un total égal à prime nette + accessoires + taxes, et égal au dernier sous-total', function () {
    foreach (randomProfiles(200) as $input) {
        $result = priceWith($input);
        $last = $result->lines[array_key_last($result->lines)];

        expect($result->total)->toBe($result->netPremium + $result->fees + $result->taxes)
            ->and($last->subtotal->isEqualTo($result->total))->toBeTrue();
    }
});

it('donne des sous-totaux qui suivent exactement les lignes du détail', function () {
    foreach (randomProfiles(100) as $input) {
        $running = null;
        foreach (priceWith($input)->lines as $line) {
            $running = match ($line->type) {
                LineType::Base => $line->amount,
                LineType::Coefficient => $running->multipliedBy($line->coefficient),
                LineType::Rounding => $running->toScale(0, RoundingMode::HalfUp),
                LineType::Fee, LineType::Tax => $running->plus($line->amount),
            };

            expect($line->subtotal->isEqualTo($running))->toBeTrue("Ligne {$line->code} incohérente");
        }
    }
});

it('ne baisse jamais la prime quand le bonus-malus augmente', function () {
    foreach (randomProfiles(100) as $input) {
        $previous = 0;
        foreach (['0.50', '0.75', '1.00', '1.25', '2.00', '3.50'] as $bonusMalus) {
            $total = priceWith(withBonusMalus($input, $bonusMalus))->total;

            expect($total)->toBeGreaterThanOrEqual($previous);
            $previous = $total;
        }
    }
});

it('donne toujours une prime strictement positive', function () {
    foreach (randomProfiles(200) as $input) {
        expect(priceWith($input)->netPremium)->toBeGreaterThan(0);
    }
});

it('est déterministe : le même devis donne exactement le même résultat', function () {
    foreach (randomProfiles(50) as $input) {
        expect(priceWith($input))->toEqual(priceWith($input));
    }
});
