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

    public string $passengerType;

    public float $amount = 0.0;

    public string $subject;

    public function __construct(string $type, float $amount = 0.0, string $passengerType = self::PASSENGER_TYPE_ANY, string $subject = self::SUBJECT_ALL)
    {
        if (!in_array($type, [self::TYPE_SERVICE_FEE, self::TYPE_MARKUP, self::TYPE_DISCOUNT, self::TYPE_COMMISSION])) {
            throw new \InvalidArgumentException('Invalid price modifier type');
        }
        if (!in_array($passengerType, [self::PASSENGER_TYPE_ANY, self::PASSENGER_TYPE_ADULT, self::PASSENGER_TYPE_CHILD, self::PASSENGER_TYPE_INFANT])) {
            throw new \InvalidArgumentException('Invalid passenger type');
        }
        if (!in_array($subject, [self::SUBJECT_ALL, self::SUBJECT_TICKET, self::SUBJECT_EMD])) {
            throw new \InvalidArgumentException('Invalid subject');
        }
        $this->type = $type;
        $this->amount = $amount;
    }
}