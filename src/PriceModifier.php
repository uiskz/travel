<?php
declare(strict_types = 1);
namespace Uiskz\Travel;

class PriceModifier
{
    const string TYPE_SERVICE_FEE = 'SF';

    const string TYPE_MARKUP = 'HF';

    const string TYPE_DISCOUNT = 'DS';

    const string TYPE_COMMISSION = 'FM';

    const string PASSENGER_TYPE_ANY = 'ANY';

    const string PASSENGER_TYPE_ADULT = 'ADT';

    const string PASSENGER_TYPE_CHILD = 'CHD';

    const string PASSENGER_TYPE_INFANT = 'INF';

    const string SUBJECT_ALL = 'ALL';

    const string SUBJECT_TICKET = 'TICKET';

    const string SUBJECT_EMD = 'EMD';

    public string $type;

    public string $passengerType = self::PASSENGER_TYPE_ANY;

    public float $amount = 0.0;

    public string $subject = self::SUBJECT_ALL;
}