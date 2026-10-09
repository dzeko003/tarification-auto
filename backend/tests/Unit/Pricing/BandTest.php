<?php

declare(strict_types=1);

use App\Pricing\Data\Band;
use App\Pricing\Data\BasePremiumBand;
use App\Pricing\Enums\VehicleCategory;
use Brick\Math\BigDecimal;

describe('Band [min, max)', function () {
    $band = new Band(21, 25, BigDecimal::of('1.25'), '21 à 24 ans');

    it('inclut la borne min et exclut la borne max', function (int $value, bool $expected) use ($band) {
        expect($band->contains($value))->toBe($expected);
    })->with([
        'juste avant min' => [20, false],
        'min' => [21, true],
        'milieu' => [23, true],
        'max - 1' => [24, true],
        'max' => [25, false],
    ]);

    it('accepte une borne max ouverte', function () {
        $open = new Band(75, null, BigDecimal::of('1.25'), '75 ans et plus');

        expect($open->contains(75))->toBeTrue()
            ->and($open->contains(120))->toBeTrue()
            ->and($open->contains(74))->toBeFalse();
    });

    it('refuse une tranche vide ou inversée', function () {
        new Band(25, 25, BigDecimal::one(), 'vide');
    })->throws(InvalidArgumentException::class);
});

describe('BasePremiumBand', function () {
    $band = new BasePremiumBand(VehicleCategory::PrivateAndBusiness, 7, 11, 90_000);

    it('correspond à sa catégorie et à sa tranche de puissance', function () use ($band) {
        expect($band->matches(VehicleCategory::PrivateAndBusiness, 7))->toBeTrue()
            ->and($band->matches(VehicleCategory::PrivateAndBusiness, 10))->toBeTrue()
            ->and($band->matches(VehicleCategory::PrivateAndBusiness, 11))->toBeFalse()
            ->and($band->matches(VehicleCategory::PrivateAndBusiness, 6))->toBeFalse()
            ->and($band->matches(VehicleCategory::TwoWheeler, 7))->toBeFalse();
    });

    it('affiche la tranche en CV inclusifs', function () use ($band) {
        expect($band->label())->toBe('Prime de base RC — Promenade et affaires, 7 à 10 CV');
        expect((new BasePremiumBand(VehicleCategory::TwoWheeler, 3, null, 30_000))->label())
            ->toBe('Prime de base RC — Deux-roues, 3 CV et plus');
    });

    it('refuse une prime nulle', function () {
        new BasePremiumBand(VehicleCategory::TwoWheeler, 1, 3, 0);
    })->throws(InvalidArgumentException::class);
});
