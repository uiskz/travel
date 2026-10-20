<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Agency hierarchy of a buyer client: root consolidator (id 1) -> sub-agent -> ... -> buyer.
 *
 * Owners are the members of the chain that set their own fees, markups and restrictions
 * (the consolidator and sub-agents; a corporate client has none). Loading the chain is up
 * to the application.
 */
final class AgencyChain
{
    public const ROOT_ID = 1;

    /**
     * @param int[] $clients Client ids from the root consolidator down to the buyer
     * @param int[] $owners Client ids of the chain that own rules, in the same order
     */
    public function __construct(
        private readonly array $clients,
        private readonly array $owners,
    ) {
    }

    /**
     * Two-level chain used when no hierarchy is known (tests, legacy callers).
     */
    public static function legacy(int $clientID): self
    {
        if ($clientID === self::ROOT_ID) {
            return new self([self::ROOT_ID], [self::ROOT_ID]);
        }

        return new self([self::ROOT_ID, $clientID], [self::ROOT_ID, $clientID]);
    }

    /**
     * @return int[]
     */
    public function clients(): array
    {
        return $this->clients;
    }

    /**
     * Owners from the root consolidator down to the buyer's own agency.
     *
     * @return int[]
     */
    public function owners(): array
    {
        return $this->owners;
    }

    public function buyerID(): int
    {
        return $this->clients[count($this->clients) - 1];
    }

    public function depthOf(int $clientID): ?int
    {
        $depth = array_search($clientID, $this->clients, true);

        return $depth === false ? null : $depth;
    }

    public function contains(int $clientID): bool
    {
        return $this->depthOf($clientID) !== null;
    }

    /**
     * Where the viewer sees a fee of the given owner. A viewer outside the chain sees
     * the price as the buyer does; an owner outside the chain counts as an upper agency.
     */
    public function relation(int $viewerID, int $ownerID): FeeLevel
    {
        $viewerDepth = $this->depthOf($viewerID) ?? count($this->clients) - 1;
        $ownerDepth = $this->depthOf($ownerID);
        if ($ownerDepth === null) {
            return FeeLevel::Upper;
        }

        $distance = $viewerDepth - $ownerDepth;

        return match (true) {
            $distance === 0 => FeeLevel::Own,
            $distance === 1 => FeeLevel::Parent,
            $distance > 1 => FeeLevel::Upper,
            default => FeeLevel::Downstream,
        };
    }
}
