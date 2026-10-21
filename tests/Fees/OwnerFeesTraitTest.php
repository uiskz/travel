<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests\Fees;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Fees\FeeKind;
use Uiskz\Travel\Fees\OwnerFeesTrait;

final class OwnerFeesTraitTest extends TestCase
{
    private function line(): object
    {
        return new class {
            use OwnerFeesTrait;

            public float $consolidatorServiceFee = 0.0;

            public float $agentServiceFee = 0.0;

            public function set(?int $ownerID, string $ownerType, float $amount): void
            {
                $this->setOwnerAmount(
                    $ownerID,
                    $ownerType,
                    FeeKind::SERVICE,
                    $amount,
                    $this->consolidatorServiceFee,
                    $this->agentServiceFee
                );
            }

            public function put(int $ownerID, string $kind, float $amount): float
            {
                return $this->putOwnerFee($ownerID, $kind, $amount);
            }

            public function agencyTotal(string $kind): float
            {
                return $this->agencyOwnerFeesTotal($kind);
            }

            public function scale(callable $convert): void
            {
                $this->scaleOwnerFees($convert);
            }
        };
    }

    public function testSetOwnerFeesNormalizesTypes(): void
    {
        $line = $this->line();
        $line->setOwnerFees(['1' => ['service' => '10.5', 'markup' => 2], 5 => ['service' => 3]]);

        $this->assertSame([1 => ['service' => 10.5, 'markup' => 2.0], 5 => ['service' => 3.0]], $line->getOwnerFees());
    }

    public function testSetOwnerFeesReplacesMap(): void
    {
        $line = $this->line();
        $line->setOwnerFees([5 => ['service' => 3]]);
        $line->setOwnerFees([7 => ['service' => 4]]);

        $this->assertSame([7 => ['service' => 4.0]], $line->getOwnerFees());
    }

    public function testGetOwnerFeeDefaultsToZero(): void
    {
        $line = $this->line();
        $line->setOwnerFees([5 => ['service' => 3]]);

        $this->assertSame(3.0, $line->getOwnerFee(5, 'service'));
        $this->assertSame(0.0, $line->getOwnerFee(5, 'markup'));
        $this->assertSame(0.0, $line->getOwnerFee(7, 'service'));
    }

    public function testPutOwnerFeeReturnsPreviousAmount(): void
    {
        $line = $this->line();

        $this->assertSame(0.0, $line->put(5, 'service', 3));
        $this->assertSame(3.0, $line->put(5, 'service', 4));
        $this->assertSame(4.0, $line->getOwnerFee(5, 'service'));
    }

    public function testSetOwnerAmountForRoot(): void
    {
        $line = $this->line();
        $line->set(1, 'consolidator', 10);

        $this->assertSame(10.0, $line->consolidatorServiceFee);
        $this->assertSame(0.0, $line->agentServiceFee);
        $this->assertSame([1 => ['service' => 10.0]], $line->getOwnerFees());
    }

    public function testSetOwnerAmountWithoutOwnerFallsBackToRoot(): void
    {
        $line = $this->line();
        $line->set(null, 'consolidator', 10);

        $this->assertSame(10.0, $line->consolidatorServiceFee);
        $this->assertSame([1 => ['service' => 10.0]], $line->getOwnerFees());
    }

    public function testSetOwnerAmountSumsAgencies(): void
    {
        $line = $this->line();
        $line->set(1, 'consolidator', 10);
        $line->set(5, 'agent', 2);
        $line->set(7, 'agent', 3);
        $line->set(5, 'agent', 4);

        $this->assertSame(10.0, $line->consolidatorServiceFee);
        $this->assertSame(7.0, $line->agentServiceFee);
        $this->assertSame(7.0, $line->agencyTotal('service'));
    }

    public function testLegacyAgentAmountOnlyOverwritesBucket(): void
    {
        $line = $this->line();
        $line->set(5, 'agent', 2);
        $line->set(null, 'agent', 9);

        $this->assertSame(9.0, $line->agentServiceFee);
        $this->assertSame([5 => ['service' => 2.0]], $line->getOwnerFees());
    }

    public function testScaleOwnerFees(): void
    {
        $line = $this->line();
        $line->setOwnerFees([1 => ['service' => 10, 'markup' => 1], 5 => ['service' => 3]]);
        $line->scale(fn(float $amount): float => $amount * 2);

        $this->assertSame([1 => ['service' => 20.0, 'markup' => 2.0], 5 => ['service' => 6.0]], $line->getOwnerFees());
    }
}
