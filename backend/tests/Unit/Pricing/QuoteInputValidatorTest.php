<?php

declare(strict_types=1);

use App\Pricing\Enums\VehicleCategory;
use App\Pricing\Exceptions\InvalidQuoteInput;
use App\Pricing\QuoteInputValidator;
use Tests\Fixtures\QuoteInputBuilder;
use Tests\Fixtures\SahelTariff2025;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, string> erreurs par champ ([] si valide)
 */
function validationErrors(array $overrides): array
{
    try {
        (new QuoteInputValidator)->validate(QuoteInputBuilder::make($overrides), SahelTariff2025::grid());
    } catch (InvalidQuoteInput $e) {
        return $e->errors;
    }

    return [];
}

it('accepte un devis cohérent', function () {
    expect(validationErrors([]))->toBe([]);
});

it('refuse un devis incohérent, avec le bon champ en erreur', function (array $overrides, string $field) {
    expect(validationErrors($overrides))->toHaveKey($field);
})->with([
    'puissance nulle' => [['fiscal_power' => 0], 'fiscal_power'],
    'puissance négative' => [['fiscal_power' => -3], 'fiscal_power'],
    'conducteur de 17 ans' => [['birth_date' => '2007-07-02', 'license_date' => '2025-07-01'], 'driver_birth_date'],
    'date de naissance après la date d\'effet' => [['birth_date' => '2026-01-01'], 'driver_birth_date'],
    'permis après la date d\'effet' => [['license_date' => '2025-07-02'], 'license_date'],
    'permis obtenu à 17 ans' => [['birth_date' => '1990-01-01', 'license_date' => '2007-12-31'], 'license_date'],
    'zone inconnue' => [['zone' => 'abidjan'], 'zone'],
    'bonus-malus sous le minimum' => [['bonus_malus' => '0.49'], 'bonus_malus'],
    'bonus-malus au-dessus du maximum' => [['bonus_malus' => '3.51'], 'bonus_malus'],
]);

it('accepte les valeurs pile aux limites', function (array $overrides) {
    expect(validationErrors($overrides))->toBe([]);
})->with([
    'conducteur de 18 ans pile, permis le jour même' => [['birth_date' => '2007-07-01', 'license_date' => '2025-07-01']],
    'permis obtenu le jour des 18 ans' => [['birth_date' => '1990-01-01', 'license_date' => '2008-01-01']],
    'bonus-malus minimum' => [['bonus_malus' => '0.50']],
    'bonus-malus maximum' => [['bonus_malus' => '3.50']],
    'puissance 1 CV' => [['fiscal_power' => 1, 'category' => VehicleCategory::TwoWheeler]],
]);

it('remonte toutes les erreurs en une seule fois', function () {
    $errors = validationErrors(['fiscal_power' => 0, 'zone' => 'abidjan', 'bonus_malus' => '9']);

    expect(array_keys($errors))->toEqualCanonicalizing(['fiscal_power', 'zone', 'bonus_malus']);
});

it('donne des messages compréhensibles en français', function () {
    expect(validationErrors(['bonus_malus' => '4'])['bonus_malus'])
        ->toBe('Le coefficient bonus-malus doit être compris entre 0.50 et 3.50.');
});
