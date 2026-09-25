<?php
declare(strict_types=1);

namespace Uiskz\Travel;


use ReflectionProperty;

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

    protected string $currency;

    /**
     * @var int|null Provider ID
     */
    protected int|null $providerID = null;

    /**
     * @var string Provider mode
     */
    protected string $mode = self::MODE_TEST;

    /**
     * @var string|null Provider name
     */
    protected string|null $name = null;

    /**
     * Constructor method to initialize the class with the provided configuration.
     *
     * @param array $config An associative array of configuration options to set class properties.
     *
     * @return void
     * @throws \ReflectionException
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                if ('int' == static::getPropertyType(static::class, $key)) {
                    $this->$key = (int)$value;
                } elseif ('float' == static::getPropertyType(static::class, $key)) {
                    $this->$key = (float)$value;
                } else {
                    $this->$key = $value;
                }
            }
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Method returns Class property's data type
     * @param string $className
     * @param string $property
     * @return string
     * @throws \ReflectionException
     */
    protected static function getPropertyType(string $className, string $property): string
    {
        $rp = new ReflectionProperty($className, $property);
        return $rp->getType()->getName();
    }
}