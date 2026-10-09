<?php

declare(strict_types=1);

namespace App\Pricing\Enums;

/**
 * Assiette d'une taxe : le montant sur lequel s'applique son taux.
 */
enum TaxBase: string
{
    /** Prime nette seule. */
    case NetPremium = 'prime_nette';

    /** Prime nette + accessoires (coût de police). */
    case NetPremiumAndFees = 'prime_nette_et_accessoires';
}
