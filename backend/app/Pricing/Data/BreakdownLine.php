<?php

declare(strict_types=1);

namespace App\Pricing\Data;

use App\Pricing\Enums\LineType;
use Brick\Math\BigDecimal;

/**
 * Une ligne du détail de calcul.
 *
 * - coefficient : renseigné pour les lignes de type Coefficient ;
 * - amount : montant ajouté par la ligne (Base, Fee, Tax) ;
 * - subtotal : total cumulé après cette ligne. Avant l'arrondi il peut contenir des décimales,
 *   on le garde exact pour que le détail soit vérifiable à la main.
 */
final readonly class BreakdownLine
{
    public function __construct(
        public string $code,
        public string $label,
        public LineType $type,
        public BigDecimal $subtotal,
        public ?BigDecimal $coefficient = null,
        public ?BigDecimal $amount = null,
    ) {}
}
