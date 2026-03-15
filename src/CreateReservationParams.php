<?php
declare(strict_types=1);

namespace Uiskz\Travel;

/**
 * Base create reservation parameters class
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class CreateReservationParams
{
    public ContactInformation $contacts;

    public array $passengers = [];

    public array $remarks = [];

    /**
     * Travel option selected by a passenger
     * @var mixed
     */
    public mixed $option;
}