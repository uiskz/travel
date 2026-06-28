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

    use LoggingTrait;

    const string MODE_TEST = 'test';

    const string MODE_LIVE = 'live';

    const string PROVIDER_TYPE_AIR = 'air';

    const string PROVIDER_TYPE_ANY = 'any';

    const string PROVIDER_TYPE_RAIL = 'rail';

    const string PROVIDER_TYPE_HOTEL = 'hotel';

    protected string $mode = self::MODE_TEST;

    /**
     * Constructor method to initialize the class with the provided configuration.
     *
     * @param array $config An associative array of configuration options to set class properties.
     *
     * @return void
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

}