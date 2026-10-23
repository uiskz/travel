<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Field a fee lands in on a search option, which is priced for one viewer.
 * Products decide how a hidden fee of each slot is shown (e.g. as a tax for air).
 */
final class FeeSlot
{
    /** consolidatorServiceFee / consolidator markup */
    public const CONSOLIDATOR = 'consolidator';

    /** agencyServiceFee (agentFee) / agency markup; for the root also its child agency's fees */
    public const AGENCY = 'agency';

    /** providerServiceFee / markups of upper agencies */
    public const PROVIDER = 'provider';

    /** Included in the price, not shown */
    public const DOWNSTREAM = 'downstream';

    public static function forLevel(FeeLevel $level, bool $viewerIsRoot): string
    {
        return match ($level) {
            FeeLevel::Own => $viewerIsRoot ? self::CONSOLIDATOR : self::AGENCY,
            FeeLevel::Parent => self::CONSOLIDATOR,
            FeeLevel::Upper => self::PROVIDER,
            FeeLevel::Child => $viewerIsRoot ? self::AGENCY : self::DOWNSTREAM,
            FeeLevel::Downstream => self::DOWNSTREAM,
        };
    }

    /**
     * Slot of a service fee. Same as {@see forLevel()}, except that a viewer who may not add service
     * fees of its own sees the parent's service fee in the provider slot (air: tax SFS).
     */
    public static function forServiceFee(FeeLevel $level, bool $viewerIsRoot, bool $parentServiceFeeShown): string
    {
        if ($level === FeeLevel::Parent && !$viewerIsRoot && !$parentServiceFeeShown) {
            return self::PROVIDER;
        }

        return self::forLevel($level, $viewerIsRoot);
    }
}
