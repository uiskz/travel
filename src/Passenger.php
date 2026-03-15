<?php
declare(strict_types=1);

namespace Uiskz\Travel;

/**
 * Base passenger class
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class Passenger
{
    const TYPE_ADULT = 'ADT';

    const TYPE_CHILD = 'CHD';

    const TYPE_INFANT = 'INF';

    const GENDER_MALE = 'M';

    const GENDER_FEMALE = 'F';

    const TITLE_MISTER = 'MR';

    const TITLE_MISSES = 'MRS';

    const TITLE_MISS = 'MS';

    public string $firstName;

    public ?string $middleName;

    public string $lastName;

    public string $gender;

    public \DateTimeInterface $birthDate;

    public string $type;

    public ?PassengerDocument $document = null;

    public ?ContactInformation $contacts = null;

}