<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Position of a fee owner relative to the client who looks at the price.
 *
 * - Own: the viewer's own fee (agency* fields, or consolidator* fields for the root consolidator);
 * - Parent: the viewer's direct agency (consolidatorServiceFee, markup hidden in a tax);
 * - Upper: any agency above the parent (providerServiceFee, markup hidden in a tax);
 *   air shows the root consolidator's markup as AD, other agencies' markups as S2, hidden service fees as AW;
 * - Child: the viewer's direct client agency; the root consolidator sees its fees as agency* fields;
 * - Downstream: an agency further below the viewer (or a child of a non-root viewer). Stored, but not shown.
 */
enum FeeLevel: string
{
    case Own = 'own';
    case Parent = 'parent';
    case Upper = 'upper';
    case Child = 'child';
    case Downstream = 'downstream';
}
