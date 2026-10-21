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
            [FeeLevel::Downstream, true, FeeSlot::DOWNSTREAM],
            [FeeLevel::Downstream, false, FeeSlot::DOWNSTREAM],
        ];
    }

    #[DataProvider('levelProvider')]
    public function testForLevel(FeeLevel $level, bool $viewerIsRoot, string $expected): void
    {
        $this->assertSame($expected, FeeSlot::forLevel($level, $viewerIsRoot));
    }
}
