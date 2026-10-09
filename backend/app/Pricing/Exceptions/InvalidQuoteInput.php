<?php

declare(strict_types=1);

namespace App\Pricing\Exceptions;

use DomainException;

/**
 * Les données du devis sont incohérentes avec elles-mêmes ou avec le barème.
 *
 * Contient toutes les erreurs trouvées, par champ, pour pouvoir les afficher d'un coup.
 */
final class InvalidQuoteInput extends DomainException
{
    /**
     * @param  array<string, string>  $errors  champ => message
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Données de devis invalides : '.implode(' ', $errors));
    }
}
