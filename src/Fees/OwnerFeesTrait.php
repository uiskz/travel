<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Per-owner fee amounts of one price line (passenger fare, ticket, room rate).
 *
 * The legacy two-bucket fields (consolidator = client 1, agent = all other agencies) stay the
 * source of totals and DB columns; this map keeps who exactly charged what, so a price can be
 * shown to every level of the agency hierarchy. The map is protected on purpose: it must never
 * reach API output as is.
 */
trait OwnerFeesTrait
{
    /** @var array<int, array<string, float>> ownerID => [kind => amount] */
    protected array $ownerFees = [];

    /**
     * @return array<int, array<string, float>>
     */
    public function getOwnerFees(): array
    {
        return $this->ownerFees;
    }

    /**
     * @param array<int|string, array<string, float|int|string>> $ownerFees
     */
    public function setOwnerFees(array $ownerFees): void
    {
        $this->ownerFees = [];
        foreach ($ownerFees as $ownerID => $fees) {
            foreach ((array)$fees as $kind => $amount) {
                $this->ownerFees[(int)$ownerID][(string)$kind] = (float)$amount;
            }
        }
    }

    public function getOwnerFee(int $ownerID, string $kind): float
    {
        return $this->ownerFees[$ownerID][$kind] ?? 0.0;
    }

    /**
     * Stores the owner's amount and returns the amount it replaces.
     */
    protected function putOwnerFee(int $ownerID, string $kind, float $amount): float
    {
        $previous = $this->getOwnerFee($ownerID, $kind);
        $this->ownerFees[$ownerID][$kind] = $amount;

        return $previous;
    }

    /**
     * Sets an owner's amount and refreshes the legacy buckets: the root consolidator's amount
     * goes to $rootBucket, the sum of all agencies' amounts to $agencyBucket.
     * Without an owner (legacy two-level callers) the bucket of $ownerType is overwritten.
     */
    protected function setOwnerAmount(
        ?int $ownerID,
        string $ownerType,
        string $kind,
        float $amount,
        float &$rootBucket,
        float &$agencyBucket
    ): void {
        if ($ownerID === null && 'agent' === $ownerType) {
            $agencyBucket = $amount;
            return;
        }

        $ownerID ??= AgencyChain::ROOT_ID;
        $this->putOwnerFee($ownerID, $kind, $amount);
        if ($ownerID === AgencyChain::ROOT_ID) {
            $rootBucket = $amount;
        } else {
            $agencyBucket = $this->agencyOwnerFeesTotal($kind);
        }
    }

    /**
     * Sum of agency (non-root) amounts of the kind.
     */
    protected function agencyOwnerFeesTotal(string $kind): float
    {
        $total = 0.0;
        foreach ($this->ownerFees as $ownerID => $fees) {
            if ($ownerID !== AgencyChain::ROOT_ID) {
                $total += $fees[$kind] ?? 0.0;
            }
        }

        return $total;
    }

    /**
     * Converts every stored amount, e.g. into another currency.
     */
    protected function scaleOwnerFees(callable $convert): void
    {
        foreach ($this->ownerFees as $ownerID => $fees) {
            foreach ($fees as $kind => $amount) {
                $this->ownerFees[$ownerID][$kind] = $convert($amount);
            }
        }
    }
}
