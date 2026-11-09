<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests\Fees;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Fees\FeeProjection;

final class FeeProjectionTest extends TestCase
{
    public function testAddSumsEveryFieldWithQuantity(): void
    {
        $other = new FeeProjection();
        $other->consolidatorServiceFee = 1;
        $other->consolidatorHiddenFee = 2;
        $other->agencyServiceFee = 3;
        $other->agencyHiddenFee = 4;
        $other->providerServiceFee = 5;
        $other->parentHiddenFee = 6;
        $other->upperHiddenFee = 7;
        $other->rootHiddenFee = 10;
        $other->downstreamServiceFee = 8;
        $other->downstreamHiddenFee = 9;

        $total = new FeeProjection();
        $total->consolidatorServiceFee = 10;
        $total->add($other, 2);
        $total->add($other);

        $this->assertSame(13.0, $total->consolidatorServiceFee);
        $this->assertSame(6.0, $total->consolidatorHiddenFee);
        $this->assertSame(9.0, $total->agencyServiceFee);
        $this->assertSame(12.0, $total->agencyHiddenFee);
        $this->assertSame(15.0, $total->providerServiceFee);
        $this->assertSame(18.0, $total->parentHiddenFee);
        $this->assertSame(21.0, $total->upperHiddenFee);
        $this->assertSame(30.0, $total->rootHiddenFee);
        $this->assertSame(24.0, $total->downstreamServiceFee);
        $this->assertSame(27.0, $total->downstreamHiddenFee);
    }

    public function testToArrayExposesOnlyApiFields(): void
    {
        $projection = new FeeProjection();
        $projection->consolidatorServiceFee = 1;
        $projection->consolidatorHiddenFee = 2;
        $projection->agencyServiceFee = 3;
        $projection->agencyHiddenFee = 4;
        $projection->providerServiceFee = 5;
        $projection->parentHiddenFee = 6;
        $projection->upperHiddenFee = 7;
        $projection->downstreamServiceFee = 8;

        $this->assertSame([
            'consolidatorServiceFee' => 1.0,
            'consolidatorHiddenFee' => 2.0,
            'agencyServiceFee' => 3.0,
            'agencyHiddenFee' => 4.0,
            'providerServiceFee' => 5.0,
        ], $projection->toArray());
    }
}
