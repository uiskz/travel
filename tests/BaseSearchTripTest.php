<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\BaseSearchTrip;

final class BaseSearchTripTest extends TestCase
{
    private function trip(string $date): BaseSearchTrip
    {
        return new BaseSearchTrip(['origin' => 'ala', 'destination' => 'nqz', 'date' => $date]);
    }

    public function testParsesData(): void
    {
        $date = (new \DateTime('+10 days'))->format('Y-m-d');
        $trip = $this->trip($date);

        $this->assertSame('ALA', $trip->origin);
        $this->assertSame('NQZ', $trip->destination);
        $this->assertSame($date . ' 00:00:00', $trip->date->format('Y-m-d H:i:s'));
        $this->assertSame(['from' => 'ALA', 'to' => 'NQZ', 'date' => $date], $trip->toArray());
        $this->assertSame('ALA_NQZ_' . $date, (string)$trip);
    }

    public function testUppercasesMultibyteCodes(): void
    {
        $trip = new BaseSearchTrip(['origin' => 'алм', 'destination' => 'nqz', 'date' => date('Y-m-d')]);

        $this->assertSame('АЛМ', $trip->origin);
    }

    public function testTodayIsAllowed(): void
    {
        $this->assertSame(date('Y-m-d'), $this->trip(date('Y-m-d'))->date->format('Y-m-d'));
    }

    public function testPastDateIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Date cannot be in the past');
        $this->trip((new \DateTime('-1 day'))->format('Y-m-d'));
    }

    public function testWrongFormatMessageContainsInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect date format: 21.10.2030.');
        $this->trip('21.10.2030');
    }

    public function testNonExistentDateIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Incorrect date format: 2030-02-31.');
        $this->trip('2030-02-31');
    }
}
