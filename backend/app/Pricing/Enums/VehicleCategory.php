<?php

declare(strict_types=1);

namespace App\Pricing\Enums;

/**
 * Catégories de véhicules inspirées de la classification CIMA (usage du véhicule).
 */
enum VehicleCategory: string
{
    case PrivateAndBusiness = 'promenade_affaires';
    case OwnAccountTransport = 'transport_propre_compte';
    case PublicGoodsTransport = 'transport_public_marchandises';
    case PublicPassengerTransport = 'transport_public_voyageurs';
    case TwoWheeler = 'deux_roues';

    public function label(): string
    {
        return match ($this) {
            self::PrivateAndBusiness => 'Promenade et affaires',
            self::OwnAccountTransport => 'Transport pour propre compte',
            self::PublicGoodsTransport => 'Transport public de marchandises',
            self::PublicPassengerTransport => 'Transport public de voyageurs',
            self::TwoWheeler => 'Deux-roues',
        };
    }
}
