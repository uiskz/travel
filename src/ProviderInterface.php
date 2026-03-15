<?php
declare(strict_types=1);

namespace Uiskz\Travel;

/**
 * Base travel provider interface
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
interface ProviderInterface
{
    public function createReservation(CreateReservationParams $parms): Reservation;
}