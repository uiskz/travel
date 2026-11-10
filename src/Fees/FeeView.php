<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Maps per-owner fees onto the fields a given viewer may see:
 *
 * | owner relative to viewer | service fee            | hidden fee (markup)    |
 * |--------------------------|------------------------|------------------------|
 * | own, viewer is the root  | consolidatorServiceFee | consolidatorHiddenFee  |
 * | own                      | agencyServiceFee       | agencyHiddenFee        |
 * | root, viewer is not root | as parent / above      | rootHiddenFee          |
 * | direct parent            | consolidatorServiceFee | parentHiddenFee        |
 * | above the parent         | providerServiceFee     | upperHiddenFee         |
 * | direct child of the root | agencyServiceFee       | agencyHiddenFee        |
 * | other agencies below     | not shown              | not shown              |
 *
 * A viewer who may not add service fees of its own ($parentServiceFeeShown = false) gets the parent's
 * service fee in providerServiceFee. Products decide how root/parent/upper hidden fees and provider
 * service fees are shown (air: root markup AD, provider service fees AW, other agencies' markups S2).
 */
final class FeeView
{
    public function __construct(
        public readonly AgencyChain $chain,
        public readonly int $viewerID,
        public readonly bool $parentServiceFeeShown = true,
    ) {
    }

    public function viewerIsRoot(): bool
    {
        return $this->viewerID === AgencyChain::ROOT_ID;
    }

    /**
     * Whether the chain has fee owners (agencies) below the viewer's agent level, i.e. whether
     * sub-agent fees (downstream* of {@see project()}) can exist at all: the root consolidator's
     * agent level is its first-level sub-agent, any other viewer is its own agent level. A viewer
     * looking at an order of its own company, or of its corporate client (which owns no fees), or
     * a viewer outside the chain (who sees the price as the buyer) has none.
     */
    public function hasSubAgentLevels(): bool
    {
        $viewerDepth = $this->chain->depthOf($this->viewerID) ?? count($this->chain->clients()) - 1;
        $agentDepth = $this->viewerIsRoot() ? $viewerDepth + 1 : $viewerDepth;
        foreach ($this->chain->owners() as $ownerID) {
            if (($this->chain->depthOf($ownerID) ?? -1) > $agentDepth) {
                return true;
            }
        }

        return false;
    }

    public function level(int $ownerID): FeeLevel
    {
        return $this->chain->relation($this->viewerID, $ownerID);
    }

    /**
     * @param array<int, array<string, float>> $ownerFees ownerID => [kind => amount]
     */
    public function project(
        array $ownerFees,
        string $serviceKind = FeeKind::SERVICE,
        string $markupKind = FeeKind::MARKUP
    ): FeeProjection {
        $projection = new FeeProjection();
        foreach ($ownerFees as $ownerID => $fees) {
            $service = (float)($fees[$serviceKind] ?? 0);
            $markup = (float)($fees[$markupKind] ?? 0);
            switch ($this->level((int)$ownerID)) {
                case FeeLevel::Own:
                    if ($this->viewerIsRoot()) {
                        $projection->consolidatorServiceFee += $service;
                        $projection->consolidatorHiddenFee += $markup;
                    } else {
                        $projection->agencyServiceFee += $service;
                        $projection->agencyHiddenFee += $markup;
                    }
                    break;
                case FeeLevel::Parent:
                    if ($this->parentServiceFeeShown) {
                        $projection->consolidatorServiceFee += $service;
                    } else {
                        $projection->providerServiceFee += $service;
                    }
                    if ((int)$ownerID === AgencyChain::ROOT_ID) {
                        $projection->rootHiddenFee += $markup;
                    } else {
                        $projection->parentHiddenFee += $markup;
                    }
                    break;
                case FeeLevel::Upper:
                    $projection->providerServiceFee += $service;
                    if ((int)$ownerID === AgencyChain::ROOT_ID) {
                        $projection->rootHiddenFee += $markup;
                    } else {
                        $projection->upperHiddenFee += $markup;
                    }
                    break;
                case FeeLevel::Child:
                    if ($this->viewerIsRoot()) {
                        $projection->agencyServiceFee += $service;
                        $projection->agencyHiddenFee += $markup;
                    } else {
                        $projection->downstreamServiceFee += $service;
                        $projection->downstreamHiddenFee += $markup;
                    }
                    break;
                case FeeLevel::Downstream:
                    $projection->downstreamServiceFee += $service;
                    $projection->downstreamHiddenFee += $markup;
                    break;
            }
        }

        return $projection;
    }

    /**
     * Owner amounts of a price line, completed from its legacy buckets. Lines saved before
     * per-owner tracking (or changed by legacy code) have amounts no owner accounts for: the
     * uncovered part of the consolidator bucket belongs to the root consolidator, of the agent
     * bucket to $legacyAgencyID (the order client, whose own rules produced agent fees before).
     * The root consolidator never owns agent amounts, so for its own orders the agent buckets
     * are left as they are.
     *
     * @param array<int, array<string, float>> $ownerFees
     * @return array<int, array<string, float>>
     */
    public static function reconcile(
        array $ownerFees,
        float $rootService,
        float $rootMarkup,
        float $agencyService,
        float $agencyMarkup,
        int $legacyAgencyID,
        string $serviceKind = FeeKind::SERVICE,
        string $markupKind = FeeKind::MARKUP
    ): array {
        $buckets = [
            [AgencyChain::ROOT_ID, $serviceKind, $rootService, true],
            [AgencyChain::ROOT_ID, $markupKind, $rootMarkup, true],
            [$legacyAgencyID, $serviceKind, $agencyService, false],
            [$legacyAgencyID, $markupKind, $agencyMarkup, false],
        ];
        foreach ($buckets as [$ownerID, $kind, $bucket, $isRoot]) {
            if (!$isRoot && $ownerID === AgencyChain::ROOT_ID) {
                continue;
            }
            $tracked = 0.0;
            foreach ($ownerFees as $trackedOwnerID => $fees) {
                if (((int)$trackedOwnerID === AgencyChain::ROOT_ID) === $isRoot) {
                    $tracked += (float)($fees[$kind] ?? 0);
                }
            }
            $missing = round($bucket - $tracked, 2);
            if ($missing != 0.0) {
                $ownerFees[$ownerID][$kind] = ($ownerFees[$ownerID][$kind] ?? 0.0) + $missing;
            }
        }

        return $ownerFees;
    }
}
