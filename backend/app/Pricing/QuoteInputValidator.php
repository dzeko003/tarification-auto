<?php

declare(strict_types=1);

namespace App\Pricing;

use App\Pricing\Data\QuoteInput;
use App\Pricing\Data\TariffGrid;
use App\Pricing\Exceptions\InvalidQuoteInput;
use App\Pricing\Support\Age;

/**
 * Vérifie qu'un devis est cohérent et entièrement couvert par le barème, avant tout calcul.
 *
 * Les clés d'erreur correspondent aux champs de l'API (snake_case).
 */
final class QuoteInputValidator
{
    /**
     * @throws InvalidQuoteInput
     */
    public function validate(QuoteInput $input, TariffGrid $grid): void
    {
        $errors = [];

        if ($input->fiscalPower <= 0) {
            $errors['fiscal_power'] = 'La puissance fiscale doit être supérieure à 0.';
        } elseif ($grid->basePremiumFor($input->vehicleCategory, $input->fiscalPower) === null) {
            $errors['fiscal_power'] = "Aucun tarif pour la catégorie « {$input->vehicleCategory->label()} » à {$input->fiscalPower} CV.";
        }

        if ($grid->usageCoefficientFor($input->usage) === null) {
            $errors['usage'] = "Usage non couvert par le barème : {$input->usage->label()}.";
        }

        $driverAge = Age::yearsBetween($input->driverBirthDate, $input->effectiveDate);
        if ($driverAge < $grid->minimumDriverAge) {
            $errors['driver_birth_date'] = "Le conducteur doit avoir au moins {$grid->minimumDriverAge} ans à la date d'effet.";
        } elseif ($grid->ageBandFor($driverAge) === null) {
            $errors['driver_birth_date'] = "Âge non couvert par le barème : {$driverAge} ans.";
        }

        if ($input->licenseDate > $input->effectiveDate) {
            $errors['license_date'] = "Le permis doit être obtenu avant la date d'effet du contrat.";
        } elseif (Age::yearsBetween($input->driverBirthDate, $input->licenseDate) < $grid->minimumLicenseAge) {
            $errors['license_date'] = "Le permis ne peut pas avoir été obtenu avant {$grid->minimumLicenseAge} ans.";
        } elseif ($grid->licenseBandFor(Age::yearsBetween($input->licenseDate, $input->effectiveDate)) === null) {
            $errors['license_date'] = 'Ancienneté de permis non couverte par le barème.';
        }

        if ($grid->zoneCoefficientFor($input->zone) === null) {
            $errors['zone'] = "Zone inconnue : {$input->zone}.";
        }

        if ($input->bonusMalus->isLessThan($grid->bonusMalusMin) || $input->bonusMalus->isGreaterThan($grid->bonusMalusMax)) {
            $errors['bonus_malus'] = "Le coefficient bonus-malus doit être compris entre {$grid->bonusMalusMin} et {$grid->bonusMalusMax}.";
        }

        if ($errors !== []) {
            throw new InvalidQuoteInput($errors);
        }
    }
}
