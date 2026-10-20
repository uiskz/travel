<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Kinds of per-owner amounts stored by {@see OwnerFeesTrait}.
 */
final class FeeKind
{
    public const SERVICE = 'service';

    public const MARKUP = 'markup';

    public const VOID_SERVICE = 'voidService';

    public const VOID_MARKUP = 'voidMarkup';
}
