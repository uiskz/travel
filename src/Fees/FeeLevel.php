<?php
declare(strict_types=1);

namespace Uiskz\Travel\Fees;

/**
 * Position of a fee owner relative to the client who looks at the price.
 *
 * - Own: the viewer's own fee (agency* fields, or consolidator* fields for the root consolidator);
 * - Parent: the viewer's direct agency (consolidatorServiceFee, markup hidden in tax AD);
 * - Upper: any agency above the parent (providerServiceFee, markup hidden in tax SFS);
 * - Downstream: an agency below the viewer. Stored, but not shown.
 */
enum FeeLevel: string
{
    case Own = 'own';
    case Parent = 'parent';
    case Upper = 'upper';
    case Downstream = 'downstream';
}
