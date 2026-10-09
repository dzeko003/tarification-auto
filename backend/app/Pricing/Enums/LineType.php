<?php

declare(strict_types=1);

namespace App\Pricing\Enums;

/**
 * Nature d'une ligne du détail de calcul.
 */
enum LineType: string
{
    /** Prime de base, point de départ du calcul. */
    case Base = 'base';

    /** Coefficient multiplicateur appliqué au sous-total. */
    case Coefficient = 'coefficient';

    /** Arrondi au franc de la prime nette. */
    case Rounding = 'arrondi';

    /** Frais accessoires ajoutés (coût de police). */
    case Fee = 'accessoire';

    /** Taxe ou contribution ajoutée. */
    case Tax = 'taxe';
}
