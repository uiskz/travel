<?php
declare(strict_types=1);

namespace Uiskz\Travel;

/**
 * Base travel provider with config loading feature
 * @author Dmitriy Gritsenko <dg@uis.kz>
 * @package Uiskz\Travel
 * @version 1.0.0
 */
class Provider
{
    const MODE_TEST = 'test';

    const MODE_LIVE = 'live';

    const PROVIDER_TYPE_AIR = 'air';

    const PROVIDER_TYPE_ANY = 'any';

    const PROVIDER_TYPE_RAIL = 'rail';

    const PROVIDER_TYPE_HOTEL = 'hotel';

    public int $providerID;

    public string $name;

    public string $mode = self::MODE_TEST;

    public function __construct(string $mode, int $providerID, array $config = [])
    {
        $this->mode = $mode;
        $this->providerID = $providerID;
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}