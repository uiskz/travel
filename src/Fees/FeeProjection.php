<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Fees of a price line as one viewer sees them.
 *
 * Hidden fees of the parent agency are shown as tax AD, hidden fees of upper agencies as tax AW.
 * Downstream amounts are kept for completeness and never shown.
 */
final class FeeProjection
{
    public float $consolidatorServiceFee = 0.0;

    public float $consolidatorHiddenFee = 0.0;

    public float $agencyServiceFee = 0.0;

    public float $agencyHiddenFee = 0.0;

    public float $providerServiceFee = 0.0;

    /** Parent agency markup, shown as tax AD */
    public float $parentHiddenFee = 0.0;

    /** Markups of the agencies above the parent, shown as tax AW */
    public float $upperHiddenFee = 0.0;

    public float $downstreamServiceFee = 0.0;

    public float $downstreamHiddenFee = 0.0;

    public function add(self $other, float $qty = 1): void
    {
        foreach (get_object_vars($other) as $field => $amount) {
            $this->$field += $amount * $qty;
        }
    }

    /**
     * Fee fields for API output.
     */
    public function toArray(): array
    {
        return [
            'consolidatorServiceFee' => $this->consolidatorServiceFee,
            'consolidatorHiddenFee' => $this->consolidatorHiddenFee,
            'agencyServiceFee' => $this->agencyServiceFee,
            'agencyHiddenFee' => $this->agencyHiddenFee,
            'providerServiceFee' => $this->providerServiceFee,
        ];
    }
}
