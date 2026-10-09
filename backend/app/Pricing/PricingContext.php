<?php

declare(strict_types=1);

namespace App\Pricing;

use App\Pricing\Data\BreakdownLine;
use App\Pricing\Data\PricingResult;
use App\Pricing\Data\QuoteInput;
use App\Pricing\Data\TariffGrid;
use App\Pricing\Enums\LineType;
use App\Pricing\Support\Age;
use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use LogicException;

/**
 * État du calcul transporté d'une étape à l'autre.
 *
 * Toute modification du montant passe par une méthode de cette classe, qui ajoute la ligne
 * correspondante au détail : aucune étape ne peut changer le prix sans laisser de trace.
 *
 * Deux phases :
 *  1. prime nette (base puis coefficients, montant exact non arrondi) ;
 *  2. après l'arrondi au franc : ajout des accessoires et des taxes (montants entiers).
 */
final class PricingContext
{
    public readonly int $driverAge;

    public readonly int $licenseYears;

    private ?BigDecimal $subtotal = null;

    private ?int $netPremium = null;

    private int $fees = 0;

    private int $taxes = 0;

    /** @var list<BreakdownLine> */
    private array $lines = [];

    public function __construct(
        public readonly QuoteInput $input,
        public readonly TariffGrid $grid,
    ) {
        $this->driverAge = Age::yearsBetween($input->driverBirthDate, $input->effectiveDate);
        $this->licenseYears = Age::yearsBetween($input->licenseDate, $input->effectiveDate);
    }

    public function start(string $code, string $label, int $amount): void
    {
        if ($this->subtotal !== null) {
            throw new LogicException('Le calcul a déjà commencé.');
        }

        $this->subtotal = BigDecimal::of($amount);
        $this->addLine($code, $label, LineType::Base, amount: BigDecimal::of($amount));
    }

    public function applyCoefficient(string $code, string $label, BigDecimal $coefficient): void
    {
        $this->assertNetPremiumPhase();

        $this->subtotal = $this->subtotal()->multipliedBy($coefficient);
        $this->addLine($code, $label, LineType::Coefficient, coefficient: $coefficient);
    }

    public function roundNetPremium(string $code, string $label): void
    {
        $this->assertNetPremiumPhase();

        $this->netPremium = $this->subtotal()->toScale(0, RoundingMode::HalfUp)->toInt();
        $this->subtotal = BigDecimal::of($this->netPremium);
        $this->addLine($code, $label, LineType::Rounding);
    }

    public function addFee(string $code, string $label, int $amount): void
    {
        $this->fees += $amount;
        $this->addAmount($code, $label, LineType::Fee, $amount);
    }

    public function addTax(string $code, string $label, int $amount): void
    {
        $this->taxes += $amount;
        $this->addAmount($code, $label, LineType::Tax, $amount);
    }

    public function netPremium(): int
    {
        return $this->netPremium ?? throw new LogicException("La prime nette n'est pas encore arrondie.");
    }

    public function fees(): int
    {
        return $this->fees;
    }

    /**
     * @return list<BreakdownLine>
     */
    public function lines(): array
    {
        return $this->lines;
    }

    public function toResult(): PricingResult
    {
        $netPremium = $this->netPremium();

        return new PricingResult(
            tariffVersion: $this->grid->version,
            currency: $this->grid->currency,
            driverAge: $this->driverAge,
            licenseYears: $this->licenseYears,
            netPremium: $netPremium,
            fees: $this->fees,
            taxes: $this->taxes,
            total: $netPremium + $this->fees + $this->taxes,
            lines: $this->lines,
        );
    }

    private function addAmount(string $code, string $label, LineType $type, int $amount): void
    {
        $this->netPremium(); // accessoires et taxes uniquement après l'arrondi de la prime nette

        $this->subtotal = $this->subtotal()->plus($amount);
        $this->addLine($code, $label, $type, amount: BigDecimal::of($amount));
    }

    private function addLine(
        string $code,
        string $label,
        LineType $type,
        ?BigDecimal $coefficient = null,
        ?BigDecimal $amount = null,
    ): void {
        $this->lines[] = new BreakdownLine($code, $label, $type, $this->subtotal(), $coefficient, $amount);
    }

    private function subtotal(): BigDecimal
    {
        return $this->subtotal ?? throw new LogicException("Le calcul n'a pas commencé : aucune prime de base.");
    }

    private function assertNetPremiumPhase(): void
    {
        $this->subtotal();

        if ($this->netPremium !== null) {
            throw new LogicException('La prime nette est déjà arrondie : plus de coefficient possible.');
        }
    }
}
