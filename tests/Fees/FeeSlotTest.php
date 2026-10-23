<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests\Fees;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Fees\FeeLevel;
use Uiskz\Travel\Fees\FeeSlot;

final class FeeSlotTest extends TestCase
{
    public static function levelProvider(): array
    {
        return [
            [FeeLevel::Own, true, FeeSlot::CONSOLIDATOR],
            [FeeLevel::Own, false, FeeSlot::AGENCY],
            [FeeLevel::Parent, true, FeeSlot::CONSOLIDATOR],
            [FeeLevel::Parent, false, FeeSlot::CONSOLIDATOR],
            [FeeLevel::Upper, true, FeeSlot::PROVIDER],
            [FeeLevel::Upper, false, FeeSlot::PROVIDER],
            [FeeLevel::Child, true, FeeSlot::AGENCY],
            [FeeLevel::Child, false, FeeSlot::DOWNSTREAM],
            [FeeLevel::Downstream, true, FeeSlot::DOWNSTREAM],
            [FeeLevel::Downstream, false, FeeSlot::DOWNSTREAM],
        ];
    }

    public static function serviceFeeProvider(): array
    {
        return [
            'parent fee shown' => [FeeLevel::Parent, false, true, FeeSlot::CONSOLIDATOR],
            'parent fee hidden' => [FeeLevel::Parent, false, false, FeeSlot::PROVIDER],
            'own fee' => [FeeLevel::Own, false, false, FeeSlot::AGENCY],
            'own fee of root' => [FeeLevel::Own, true, false, FeeSlot::CONSOLIDATOR],
            'upper fee' => [FeeLevel::Upper, false, true, FeeSlot::PROVIDER],
            'child fee for root' => [FeeLevel::Child, true, false, FeeSlot::AGENCY],
        ];
    }

    #[DataProvider('serviceFeeProvider')]
    public function testForServiceFee(FeeLevel $level, bool $viewerIsRoot, bool $parentFeeShown, string $expected): void
    {
        $this->assertSame($expected, FeeSlot::forServiceFee($level, $viewerIsRoot, $parentFeeShown));
    }

    #[DataProvider('levelProvider')]
    public function testForLevel(FeeLevel $level, bool $viewerIsRoot, string $expected): void
    {
        $this->assertSame($expected, FeeSlot::forLevel($level, $viewerIsRoot));
    }
}
