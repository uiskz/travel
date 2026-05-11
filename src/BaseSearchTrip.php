<?php
declare(strict_types=1);
namespace Uiskz\Travel;

/**
 * Base object to use in search requests. Contains common properties and methods for search trips.
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class BaseSearchTrip
{
    /**
     * @var string Origin city IATA code
     */
    public string $origin;

    /**
     * @var string Destination city IATA code
     */
    public string $destination;

    /**
     * @var \DateTimeInterface Departure date
     */
    public \DateTimeInterface $date;

    /**
     * @throws \DateMalformedStringException
     */
    public function __construct(array $data)
    {
        $this->origin = mb_strtoupper($data['origin']);
        $this->destination = mb_strtoupper($data['destination']);
        $this->parseDate($data['date']);
    }

    /**
     * Function sets the departure date from string in format Y-m-d. Sets hours, minutes, and seconds to 0.
     * Throws InvalidArgumentException if the date format is incorrect or the date is in the past.
     *
     * @param string $dateString
     * @return void
     * @throws \InvalidArgumentException|\DateMalformedStringException
     */
    private function parseDate(string $dateString): void
    {
        $dt = \DateTime::createFromFormat('Y-m-d', $dateString);
        if ($dt === false) {
            throw new \InvalidArgumentException(sprintf('Incorrect date format: %s. Required format "yyyy-mm-dd"', $data['departure']));
        }
        $dt->modify('today');
        $now = (new \DateTime())->modify('today');
        if ($dt->getTimestamp() < $now->getTimestamp()) {
            throw new \InvalidArgumentException('Date cannot be in the past');
        }
        $this->date = $dt;
    }

    /**
     * Method converts the current trip into an array
     * @return array
     */
    public function toArray(): array
    {
        return [
            'from' => $this->origin,
            'to' => $this->destination,
            'date' => $this->date->format('Y-m-d'),
        ];
    }

}