<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use App\Pricing\Data\Band;
use App\Pricing\Data\BasePremiumBand;
use App\Pricing\Data\KeyedCoefficient;
use App\Pricing\Data\TariffGrid;
use App\Pricing\Data\TaxRule;
use App\Pricing\Enums\TaxBase;
use App\Pricing\Enums\Usage;
use App\Pricing\Enums\VehicleCategory as Cat;
use Brick\Math\BigDecimal;

/**
 * Barème RC auto 2025 de « Sahel Assurances », compagnie FICTIVE (Cameroun, XAF).
 *
 * Tous les montants, coefficients et taux sont inventés à des fins de démonstration.
 * Ils ne correspondent ni au tarif minimum CIMA ni aux tarifs d'un assureur réel.
 *
 * Tranches : [min, max), max = null = sans limite.
 */
final class SahelTariff2025
{
    public static function grid(): TariffGrid
    {
        $d = static fn (string $v): BigDecimal => BigDecimal::of($v);

        return new TariffGrid(
            version: 'SAHEL-RC-2025',
            currency: 'XAF',
            basePremiums: [
                new BasePremiumBand(Cat::PrivateAndBusiness, 1, 3, 55_000),
                new BasePremiumBand(Cat::PrivateAndBusiness, 3, 7, 72_000),
                new BasePremiumBand(Cat::PrivateAndBusiness, 7, 11, 90_000),
                new BasePremiumBand(Cat::PrivateAndBusiness, 11, 15, 118_000),
                new BasePremiumBand(Cat::PrivateAndBusiness, 15, 24, 145_000),
                new BasePremiumBand(Cat::PrivateAndBusiness, 24, null, 180_000),

                new BasePremiumBand(Cat::OwnAccountTransport, 1, 7, 95_000),
                new BasePremiumBand(Cat::OwnAccountTransport, 7, 11, 120_000),
                new BasePremiumBand(Cat::OwnAccountTransport, 11, null, 160_000),

                new BasePremiumBand(Cat::PublicGoodsTransport, 1, 11, 150_000),
                new BasePremiumBand(Cat::PublicGoodsTransport, 11, null, 210_000),

                new BasePremiumBand(Cat::PublicPassengerTransport, 1, 7, 165_000),
                new BasePremiumBand(Cat::PublicPassengerTransport, 7, 11, 195_000),
                new BasePremiumBand(Cat::PublicPassengerTransport, 11, null, 240_000),

                new BasePremiumBand(Cat::TwoWheeler, 1, 3, 18_000),
                new BasePremiumBand(Cat::TwoWheeler, 3, null, 30_000),
            ],
            usageCoefficients: [
                Usage::Personal->value => new KeyedCoefficient($d('1.00'), 'usage personnel'),
                Usage::Professional->value => new KeyedCoefficient($d('1.15'), 'usage professionnel'),
            ],
            ageBands: [
                new Band(18, 21, $d('1.40'), '18 à 20 ans'),
                new Band(21, 25, $d('1.25'), '21 à 24 ans'),
                new Band(25, 65, $d('1.00'), '25 à 64 ans'),
                new Band(65, 75, $d('1.10'), '65 à 74 ans'),
                new Band(75, null, $d('1.25'), '75 ans et plus'),
            ],
            licenseBands: [
                new Band(0, 1, $d('1.30'), "moins d'1 an"),
                new Band(1, 3, $d('1.15'), '1 à 2 ans'),
                new Band(3, null, $d('1.00'), '3 ans et plus'),
            ],
            zoneCoefficients: [
                'douala' => new KeyedCoefficient($d('1.15'), 'Douala'),
                'yaounde' => new KeyedCoefficient($d('1.10'), 'Yaoundé'),
                'autres_villes' => new KeyedCoefficient($d('1.00'), 'autres villes'),
                'zone_rurale' => new KeyedCoefficient($d('0.90'), 'zone rurale'),
            ],
            bonusMalusMin: $d('0.50'),
            bonusMalusMax: $d('3.50'),
            policyFee: 5_000,
            taxes: [
                new TaxRule('tca', "Taxe sur les contrats d'assurance", rate: $d('0.145'), base: TaxBase::NetPremiumAndFees),
                new TaxRule('fga', 'Contribution au fonds de garantie automobile', rate: $d('0.025'), base: TaxBase::NetPremium),
            ],
        );
    }
}
