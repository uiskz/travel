<?php
declare(strict_types=1);

namespace Uiskz\Travel;

/**
 * Base travel reservation class
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class Reservation
{
    const string STATUS_CREATED = 'booked';

    const string STATUS_ISSUED = 'issued';

    const string STATUS_CANCELLED = 'cancelled';

    const string STATUS_PARTIALLY_REFUNDED = 'partially-refunded';

    const string STATUS_REFUNDED = 'refunded';

    const string STATUS_VOID = 'void';

    const string STATUS_PAID = 'paid';

    const string STATUS_EXPIRED = 'expired';

    public string $id;

    public string $status;

    public string $currency;

    public float $total;

    /**
     * @var Traveler[] Passengers in current reservation
     */
    public array $passengers = [];

    public array $remarks = [];

    public array $errors = [];

    public array $warnings = [];

    public int $providerID = 0;

    public string $mode = Provider::MODE_TEST;

    public function getIdentity(): ReservationIdentity
    {
        $identity = new ReservationIdentity();
        $identity->id = $this->id;

        return $identity;
    }

    /**
     * Method fills all the necessary data internal data used in our product
     * @param CreateReservationParams $params
     */
    public function fillServiceData(CreateReservationParams $params): void
    {
        $this->providerID = $params->option->providerID;
        $this->mode = $params->option->mode;
    }
}