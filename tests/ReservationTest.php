<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\CreateReservationParams;
use Uiskz\Travel\Provider;
use Uiskz\Travel\Reservation;
use Uiskz\Travel\Traveler;

final class ReservationTest extends TestCase
{
    public function testIdentity(): void
    {
        $reservation = new Reservation();
        $reservation->id = 'PNR123';

        $identity = $reservation->getIdentity();

        $this->assertSame('PNR123', $identity->id);
        $this->assertSame([], $identity->params);
    }

    public function testFillServiceData(): void
    {
        $params = new CreateReservationParams();
        $params->option = (object)['providerID' => 3, 'mode' => Provider::MODE_LIVE];

        $reservation = new Reservation();
        $reservation->fillServiceData($params);

        $this->assertSame(3, $reservation->providerID);
        $this->assertSame(Provider::MODE_LIVE, $reservation->mode);
    }

    public function testTravelerOptionalFieldsDefaultToNull(): void
    {
        $traveler = new Traveler();

        $this->assertNull($traveler->middleName);
        $this->assertNull($traveler->document);
        $this->assertNull($traveler->contacts);
    }
}
