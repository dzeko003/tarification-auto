<?php

declare(strict_types=1);

namespace App\Pricing\Support;

use DateTimeImmutable;

final class Age
{
    /**
     * Nombre d'années révolues entre deux dates. Négatif si $to est antérieure à $from.
     *
     * Une personne née un 29 février prend un an le 1er mars des années non bissextiles.
     */
    public static function yearsBetween(DateTimeImmutable $from, DateTimeImmutable $to): int
    {
        $diff = $from->diff($to);

        return $diff->invert === 1 ? -$diff->y : $diff->y;
    }
}
