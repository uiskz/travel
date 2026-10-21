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
                $this->$key = static::castPropertyValue(static::class, $key, $value);
            }
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Casts a config value to the type of the class property. Null stays null for nullable properties;
     * untyped and union-typed properties get the value as is.
     * @param string $className
     * @param string $property
     * @param mixed $value
     * @return mixed
     * @throws \ReflectionException
     */
    protected static function castPropertyValue(string $className, string $property, mixed $value): mixed
    {
        $type = (new ReflectionProperty($className, $property))->getType();
        if ($value === null && $type?->allowsNull()) {
            return null;
        }

        return match (static::getPropertyType($className, $property)) {
            'int' => (int)$value,
            'float' => (float)$value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    /**
     * Method returns Class property's data type, or null for untyped and union-typed properties
     * @param string $className
     * @param string $property
     * @return string|null
     * @throws \ReflectionException
     */
    protected static function getPropertyType(string $className, string $property): ?string
    {
        $type = (new ReflectionProperty($className, $property))->getType();
        return $type instanceof \ReflectionNamedType ? $type->getName() : null;
    }
}