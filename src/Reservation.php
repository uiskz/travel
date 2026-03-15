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
    const STATUS_CREATED = 'booked';

    const STATUS_ISSUED = 'issued';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_PARTIALLY_REFUNDED = 'partially-refunded';

    const STATUS_REFUNDED = 'refunded';

    const STATUS_VOID = 'void';

    const STATUS_PAID = 'paid';

    public string $id;

    public string $status;

    public string $currency;

    public float $total;

    public array $remarks = [];

    public array $errors = [];

    public array $warnings = [];
}