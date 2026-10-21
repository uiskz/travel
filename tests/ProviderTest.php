<?php
declare(strict_types=1);

namespace Uiskz\Travel\Tests;

use PHPUnit\Framework\TestCase;
use Uiskz\Travel\Provider;

final class ProviderTest extends TestCase
{
    private function provider(array $config): Provider
    {
        return new class($config) extends Provider {
            public $untyped;

            public int|string $union = 0;

            public bool $flag = false;

            public float $rate = 0.0;

            public function get(string $property): mixed
            {
                return $this->$property;
            }
        };
    }

    public function testDefaults(): void
    {
        $provider = $this->provider([]);

        $this->assertNull($provider->getName());
        $this->assertNull($provider->get('providerID'));
        $this->assertSame(Provider::MODE_TEST, $provider->get('mode'));
        $this->assertSame('', $provider->get('logsPath'));
    }

    public function testCastsConfigValues(): void
    {
        $provider = $this->provider([
            'providerID' => '12',
            'rate' => '1.5',
            'name' => 'Amadeus',
            'mode' => Provider::MODE_LIVE,
            'currency' => 'KZT',
            'unknown' => 'ignored',
        ]);

        $this->assertSame(12, $provider->get('providerID'));
        $this->assertSame(1.5, $provider->get('rate'));
        $this->assertSame('Amadeus', $provider->getName());
        $this->assertSame(Provider::MODE_LIVE, $provider->get('mode'));
        $this->assertSame('KZT', $provider->get('currency'));
    }

    public function testKeepsNullForNullableProperty(): void
    {
        $this->assertNull($this->provider(['providerID' => null])->get('providerID'));
    }

    public function testAssignsUntypedAndUnionPropertiesAsIs(): void
    {
        $provider = $this->provider(['untyped' => [1], 'union' => 'abc']);

        $this->assertSame([1], $provider->get('untyped'));
        $this->assertSame('abc', $provider->get('union'));
    }

    public function testCastsBoolValues(): void
    {
        $this->assertTrue($this->provider(['flag' => '1'])->get('flag'));
        $this->assertTrue($this->provider(['flag' => 'true'])->get('flag'));
        $this->assertFalse($this->provider(['flag' => '0'])->get('flag'));
        $this->assertFalse($this->provider(['flag' => 'false'])->get('flag'));
        $this->assertFalse($this->provider(['flag' => ''])->get('flag'));
        $this->assertTrue($this->provider(['flag' => true])->get('flag'));
    }
}
