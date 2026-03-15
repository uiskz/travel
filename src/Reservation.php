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

    public string $id;

    public string $status;

    public string $currency;

    public float $total;

    public array $remarks = [];

    public array $errors = [];

    public array $warnings = [];
}