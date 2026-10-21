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

    private function view(int $viewerID): FeeView
    {
        return new FeeView(new AgencyChain([1, 5, 7, 9], [1, 5, 7]), $viewerID);
    }

    public function testViewerIsRoot(): void
    {
        $this->assertTrue($this->view(1)->viewerIsRoot());
        $this->assertFalse($this->view(5)->viewerIsRoot());
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
        $this->assertSame(0.0, $projection->agencyServiceFee);
        $this->assertSame(0.0, $projection->agencyHiddenFee);
        $this->assertSame(0.0, $projection->providerServiceFee);
        $this->assertSame(0.0, $projection->parentHiddenFee);
        $this->assertSame(0.0, $projection->upperHiddenFee);
        $this->assertSame(50.0, $projection->downstreamServiceFee);
        $this->assertSame(5.0, $projection->downstreamHiddenFee);
    }

    public function testProjectForSubAgent(): void
    {
        $projection = $this->view(5)->project(self::FEES);

        $this->assertSame(10.0, $projection->consolidatorServiceFee);
        $this->assertSame(1.0, $projection->parentHiddenFee);
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

        $this->assertSame(30.0, $projection->providerServiceFee);
        $this->assertSame(3.0, $projection->upperHiddenFee);
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
    }

    public function testProjectUsesRequestedKinds(): void
    {
        $projection = $this->view(5)->project(self::FEES, FeeKind::VOID_SERVICE, FeeKind::VOID_MARKUP);

        $this->assertSame(100.0, $projection->consolidatorServiceFee);
        $this->assertSame(1000.0, $projection->parentHiddenFee);
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
