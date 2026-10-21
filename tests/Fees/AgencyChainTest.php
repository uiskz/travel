<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests\Fees;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Fees\AgencyChain;
use Uiskz\Travel\Fees\FeeLevel;

final class AgencyChainTest extends TestCase
{
    private function chain(): AgencyChain
    {
        // root consolidator 1 -> sub-agent 5 -> sub-agent 7 -> corporate buyer 9
        return new AgencyChain([1, 5, 7, 9], [1, 5, 7]);
    }

    public function testLegacyChainForRoot(): void
    {
        $chain = AgencyChain::legacy(AgencyChain::ROOT_ID);

        $this->assertSame([1], $chain->clients());
        $this->assertSame([1], $chain->owners());
        $this->assertSame(1, $chain->buyerID());
    }

    public function testLegacyChainForAgency(): void
    {
        $chain = AgencyChain::legacy(42);

        $this->assertSame([1, 42], $chain->clients());
        $this->assertSame([1, 42], $chain->owners());
        $this->assertSame(42, $chain->buyerID());
    }

    public function testAccessors(): void
    {
        $chain = $this->chain();

        $this->assertSame([1, 5, 7, 9], $chain->clients());
        $this->assertSame([1, 5, 7], $chain->owners());
        $this->assertSame(9, $chain->buyerID());
        $this->assertSame(0, $chain->depthOf(1));
        $this->assertSame(3, $chain->depthOf(9));
        $this->assertNull($chain->depthOf(100));
        $this->assertTrue($chain->contains(7));
        $this->assertFalse($chain->contains(100));
    }

    public static function relationProvider(): array
    {
        return [
            'own root' => [1, 1, FeeLevel::Own],
            'own agency' => [7, 7, FeeLevel::Own],
            'parent' => [7, 5, FeeLevel::Parent],
            'parent of buyer' => [9, 7, FeeLevel::Parent],
            'upper' => [7, 1, FeeLevel::Upper],
            'upper of buyer' => [9, 1, FeeLevel::Upper],
            'downstream' => [5, 7, FeeLevel::Downstream],
            'downstream of root' => [1, 5, FeeLevel::Downstream],
            'viewer outside sees as buyer' => [100, 7, FeeLevel::Parent],
            'viewer outside, buyer own fee' => [100, 9, FeeLevel::Own],
            'owner outside is upper' => [5, 100, FeeLevel::Upper],
            'owner outside is upper for root' => [1, 100, FeeLevel::Upper],
        ];
    }

    #[DataProvider('relationProvider')]
    public function testRelation(int $viewerID, int $ownerID, FeeLevel $expected): void
    {
        $this->assertSame($expected, $this->chain()->relation($viewerID, $ownerID));
    }

    public function testIdsFromDatabaseStringsAreNormalized(): void
    {
        $chain = new AgencyChain(['1', '5'], ['1', '5']);

        $this->assertSame([1, 5], $chain->clients());
        $this->assertSame([1, 5], $chain->owners());
        $this->assertSame(5, $chain->buyerID());
        $this->assertSame(FeeLevel::Parent, $chain->relation(5, 1));
    }

    public function testEmptyChainIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new AgencyChain([], []);
    }
}
