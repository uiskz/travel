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
    const string TYPE_ADULT = 'ADT';

    const string TYPE_CHILD = 'CHD';

    const string TYPE_INFANT = 'INF';

    const string GENDER_MALE = 'M';

    const string GENDER_FEMALE = 'F';

    const string TITLE_MISTER = 'MR';

    const string TITLE_MISSES = 'MRS';

    const string TITLE_MISS = 'MS';

    public string $firstName;

    public ?string $middleName;

    public string $lastName;

    public string $gender;

    public \DateTimeInterface $birthDate;

    public string $type;

    public ?PassengerDocument $document = null;

    public ?ContactInformation $contacts = null;

}