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

    /** agencyServiceFee (agentFee) / agency markup */
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
            FeeLevel::Downstream => self::DOWNSTREAM,
        };
    }
}
