<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests\Fees;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Fees\AgencyChain;
use Uiskz\Travel\Fees\FeeKind;
use Uiskz\Travel\Fees\FeeLevel;
use Uiskz\Travel\Fees\FeeView;

final class FeeViewTest extends TestCase
{
    private const FEES = [
        1 => [FeeKind::SERVICE => 10, FeeKind::MARKUP => 1, FeeKind::VOID_SERVICE => 100, FeeKind::VOID_MARKUP => 1000],
        5 => [FeeKind::SERVICE => 20, FeeKind::MARKUP => 2, FeeKind::VOID_SERVICE => 200, FeeKind::VOID_MARKUP => 2000],
        7 => [FeeKind::SERVICE => 30, FeeKind::MARKUP => 3, FeeKind::VOID_SERVICE => 300, FeeKind::VOID_MARKUP => 3000],
    ];

    private function view(int $viewerID, bool $parentServiceFeeShown = true): FeeView
    {
        return new FeeView(new AgencyChain([1, 5, 7, 9], [1, 5, 7]), $viewerID, $parentServiceFeeShown);
    }

    public function testViewerIsRoot(): void
    {
        $this->assertTrue($this->view(1)->viewerIsRoot());
        $this->assertFalse($this->view(5)->viewerIsRoot());
    }

    public function testSubAgentLevelsExistOnlyBelowTheViewersAgentLevel(): void
    {
        // Chain 1 -> 5 -> 7 -> corporate 9: the root's agent level is 5, so 7 is its sub-agent
        $this->assertTrue($this->view(1)->hasSubAgentLevels());
        $this->assertTrue($this->view(5)->hasSubAgentLevels());
        // Below 7 there is only its corporate client, which owns no fees
        $this->assertFalse($this->view(7)->hasSubAgentLevels());
        // Order of the viewer's own company
        $this->assertFalse($this->view(9)->hasSubAgentLevels());
        $this->assertFalse((new FeeView(new AgencyChain([1, 5, 7], [1, 5, 7]), 7))->hasSubAgentLevels());
        $this->assertTrue((new FeeView(new AgencyChain([1, 5, 7], [1, 5, 7]), 5))->hasSubAgentLevels());
        // A viewer outside the chain sees the price as the buyer
        $this->assertFalse($this->view(42)->hasSubAgentLevels());
        // The root's own order and an order of its first-level sub-agent
        $this->assertFalse((new FeeView(new AgencyChain([1], [1]), 1))->hasSubAgentLevels());
        $this->assertFalse((new FeeView(new AgencyChain([1, 5], [1, 5]), 1))->hasSubAgentLevels());
        $this->assertFalse((new FeeView(new AgencyChain([1, 5], [1, 5]), 5))->hasSubAgentLevels());
    }

    public function testLevel(): void
    {
        $this->assertSame(FeeLevel::Parent, $this->view(7)->level(5));
    }

    public function testProjectForRoot(): void
    {
        $projection = $this->view(1)->project(self::FEES);

        $this->assertSame(10.0, $projection->consolidatorServiceFee);
        $this->assertSame(1.0, $projection->consolidatorHiddenFee);
        $this->assertSame(20.0, $projection->agencyServiceFee);
        $this->assertSame(2.0, $projection->agencyHiddenFee);
        $this->assertSame(0.0, $projection->providerServiceFee);
        $this->assertSame(0.0, $projection->parentHiddenFee);
        $this->assertSame(0.0, $projection->upperHiddenFee);
        $this->assertSame(0.0, $projection->rootHiddenFee);
        $this->assertSame(30.0, $projection->downstreamServiceFee);
        $this->assertSame(3.0, $projection->downstreamHiddenFee);
    }

    public function testProjectForSubAgentWithoutServiceFees(): void
    {
        $projection = $this->view(5, false)->project(self::FEES);

        $this->assertSame(0.0, $projection->consolidatorServiceFee);
        $this->assertSame(10.0, $projection->providerServiceFee);
        $this->assertSame(1.0, $projection->rootHiddenFee);
        $this->assertSame(0.0, $projection->parentHiddenFee);
        $this->assertSame(20.0, $projection->agencyServiceFee);
        $this->assertSame(2.0, $projection->agencyHiddenFee);
    }

    public function testProjectForSubAgent(): void
    {
        $projection = $this->view(5)->project(self::FEES);

        $this->assertSame(10.0, $projection->consolidatorServiceFee);
        // The root consolidator's markup is kept apart even when the root is the parent
        $this->assertSame(1.0, $projection->rootHiddenFee);
        $this->assertSame(0.0, $projection->parentHiddenFee);
        $this->assertSame(20.0, $projection->agencyServiceFee);
        $this->assertSame(2.0, $projection->agencyHiddenFee);
        $this->assertSame(0.0, $projection->consolidatorHiddenFee);
        $this->assertSame(0.0, $projection->providerServiceFee);
        $this->assertSame(0.0, $projection->upperHiddenFee);
        $this->assertSame(30.0, $projection->downstreamServiceFee);
        $this->assertSame(3.0, $projection->downstreamHiddenFee);
    }

    public function testProjectForBuyer(): void
    {
        $projection = $this->view(9)->project(self::FEES);

        // Service fees of the root (10) and of sub-agent 5 (20) are above the parent 7
        $this->assertSame(30.0, $projection->providerServiceFee);
        $this->assertSame(1.0, $projection->rootHiddenFee);
        $this->assertSame(2.0, $projection->upperHiddenFee);
        $this->assertSame(30.0, $projection->consolidatorServiceFee);
        $this->assertSame(3.0, $projection->parentHiddenFee);
        $this->assertSame(0.0, $projection->agencyServiceFee);
        $this->assertSame(0.0, $projection->agencyHiddenFee);
        $this->assertSame(0.0, $projection->downstreamServiceFee);
    }

    public function testProjectSumsSeveralUpperOwners(): void
    {
        $projection = $this->view(9)->project([
            1 => [FeeKind::SERVICE => 10],
            5 => [FeeKind::SERVICE => 20, FeeKind::MARKUP => 2],
        ]);

        $this->assertSame(30.0, $projection->providerServiceFee);
        $this->assertSame(2.0, $projection->upperHiddenFee);
        $this->assertSame(0.0, $projection->rootHiddenFee);
    }

    /**
     * Consolidator 1 -> KTI 5 -> sub-agent 7 (order 10414): the sub-agent sees KTI's service fee as a fee,
     * the consolidator's markup apart from KTI's markup and the consolidator's service fee as hidden.
     */
    public function testProjectForSubAgentOfSubAgentSeparatesRootMarkup(): void
    {
        $projection = (new FeeView(new AgencyChain([1, 5, 7], [1, 5, 7]), 7))->project([
            1 => [FeeKind::SERVICE => 3000, FeeKind::MARKUP => 4000],
            5 => [FeeKind::SERVICE => 1000, FeeKind::MARKUP => 2000],
            7 => [FeeKind::SERVICE => 20000],
        ]);

        $this->assertSame(1000.0, $projection->consolidatorServiceFee);
        $this->assertSame(20000.0, $projection->agencyServiceFee);
        $this->assertSame(4000.0, $projection->rootHiddenFee);
        $this->assertSame(3000.0, $projection->providerServiceFee);
        $this->assertSame(2000.0, $projection->parentHiddenFee);
        $this->assertSame(0.0, $projection->upperHiddenFee);
    }

    public function testProjectUsesRequestedKinds(): void
    {
        $projection = $this->view(5)->project(self::FEES, FeeKind::VOID_SERVICE, FeeKind::VOID_MARKUP);

        $this->assertSame(100.0, $projection->consolidatorServiceFee);
        $this->assertSame(1000.0, $projection->rootHiddenFee);
        $this->assertSame(200.0, $projection->agencyServiceFee);
        $this->assertSame(2000.0, $projection->agencyHiddenFee);
    }

    public function testProjectAcceptsStringKeysAndMissingKinds(): void
    {
        $projection = $this->view(5)->project(['1' => [FeeKind::SERVICE => '7.5'], '5' => []]);

        $this->assertSame(7.5, $projection->consolidatorServiceFee);
        $this->assertSame(0.0, $projection->agencyServiceFee);
    }

    public function testReconcileFillsEmptyMapFromBuckets(): void
    {
        $this->assertSame([
            1 => [FeeKind::SERVICE => 10.0, FeeKind::MARKUP => 1.0],
            7 => [FeeKind::SERVICE => 3.0, FeeKind::MARKUP => 0.5],
        ], FeeView::reconcile([], 10, 1, 3, 0.5, 7));
    }

    public function testReconcileKeepsFullyTrackedFees(): void
    {
        $fees = [
            1 => [FeeKind::SERVICE => 10.0, FeeKind::MARKUP => 1.0],
            5 => [FeeKind::SERVICE => 2.0],
            7 => [FeeKind::SERVICE => 1.0, FeeKind::MARKUP => 0.5],
        ];

        $this->assertSame($fees, FeeView::reconcile($fees, 10, 1, 3, 0.5, 7));
    }

    public function testReconcileAddsUncoveredPartToLegacyOwners(): void
    {
        $fees = [
            1 => [FeeKind::SERVICE => 4.0],
            5 => [FeeKind::SERVICE => 2.0],
        ];

        $this->assertSame([
            1 => [FeeKind::SERVICE => 10.0],
            5 => [FeeKind::SERVICE => 2.0],
            7 => [FeeKind::SERVICE => 3.0],
        ], FeeView::reconcile($fees, 10, 0, 5, 0, 7));
    }

    public function testReconcileTakesBackOvertrackedAmount(): void
    {
        $fees = [5 => [FeeKind::SERVICE => 2.0], 7 => [FeeKind::SERVICE => 4.0]];

        $this->assertSame(
            [5 => [FeeKind::SERVICE => 2.0], 7 => [FeeKind::SERVICE => 3.0]],
            FeeView::reconcile($fees, 0, 0, 5, 0, 7)
        );
    }

    public function testReconcileIgnoresFloatNoise(): void
    {
        $fees = [7 => [FeeKind::SERVICE => 0.1 + 0.2]];

        $this->assertSame($fees, FeeView::reconcile($fees, 0, 0, 0.3, 0, 7));
    }

    public function testReconcileUsesRequestedKinds(): void
    {
        $this->assertSame([
            1 => [FeeKind::VOID_SERVICE => 10.0],
            7 => [FeeKind::VOID_MARKUP => 2.0],
        ], FeeView::reconcile([], 10, 0, 0, 2, 7, FeeKind::VOID_SERVICE, FeeKind::VOID_MARKUP));
    }

    public function testReconcileNeverMovesAgencyBucketsToRoot(): void
    {
        $fees = FeeView::reconcile([], 10, 1, 3, 0.5, AgencyChain::ROOT_ID);

        $this->assertSame([1 => [FeeKind::SERVICE => 10.0, FeeKind::MARKUP => 1.0]], $fees);
    }
}
