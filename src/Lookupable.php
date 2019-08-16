<?php

namespace Lookupable;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use ReflectionClass;

trait Lookupable
{
    /** @var string */
    protected static $lookupableColumnPropertyName = 'lookupColumn';

    /** @var string */
    protected static $defaultLookupColumn = 'identifier';

    /** @var \Illuminate\Database\Eloquent\Collection */
    protected static $lookupableInstances;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected static $lookupableClass;

    /**
     * Lookup an instance by it's identifier
     *
     * @param string $identifier
     * @param bool $withTrashed
     *
     * @return $this
     */
    public static function lookup(string $identifier, bool $withTrashed = false): self
    {
        return static::getInstances()->first(function ($instance) use ($identifier, $withTrashed) {
            // Exclude withTrashed
            if (!$withTrashed && $instance->trashed()) {
                return false;
            }

            return data_get($instance, self::getLookupableColumn()) == $identifier;
        });
    }

    /**
     * Lookup an instance by it's identifier, or fail when not found
     *
     * @param string $identifier
     * @param bool $withTrashed
     *
     * @return $this
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public static function lookupOrFail(string $identifier, bool $withTrashed = false): self
    {
        if ($result = static::lookup($identifier, $withTrashed)) {
            return $result;
        }
        throw (new ModelNotFoundException)->setModel(
            get_class(with(new static)), $identifier
        );
    }

    /**
     * Lookup many instances by their identifiers
     *
     * @param array $identifiers
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function lookupMany(array $identifiers, bool $withTrashed = false): Collection
    {
        return static::getInstances()->filter(function ($instance) use ($identifiers, $withTrashed) {
            // Exclude withTrashed
            if (!$withTrashed && $instance->trashed()) {
                return false;
            }

            return in_array(data_get($instance, self::getLookupableColumn()), $identifiers);
        });
    }

    /**
     * Lookup many instances by their identifiers, or fail when any of them cannot be found
     *
     * @param array $identifiers
     * @param bool $withTrashed
     *
     * @return \Illuminate\Database\Eloquent\Collection
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException;
     */
    public static function lookupManyOrFail(array $identifiers, bool $withTrashed = false): Collection
    {
        if ($result = static::lookupMany($identifiers, $withTrashed)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(
            get_class(with(new static)), $identifiers
        );
    }

    /**
     * Get all instances of the lookupable class and put in memory to save queries.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private static function getInstances(): Collection
    {
        if (!static::$lookupableInstances) {
            $builder = static::query();

            // Include trashed values when soft deletes trait is implemented
            if (static::getLookupableClass() instanceof SoftDeletes) {
                $builder->withTrashed();
            }

            static::$lookupableInstances = $builder->get();
        }

        return static::$lookupableInstances;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model
     * @throws Exception
     */
    private static function getLookupableClass(): \Illuminate\Database\Eloquent\Model
    {
        static::$lookupableClass = app(get_called_class());

        if (!static::$lookupableClass instanceof Model) {
            throw new Exception('Lookupable only works with Eloquent models');
        }

        return static::$lookupableClass;
    }

    /**
     * @return string
     */
    private static function getLookupableColumn(): string
    {
        try {
            $reflection = new ReflectionClass(static::getLookupableClass());

            $property = $reflection->getProperty(static::$lookupableColumnPropertyName);

            $property->setAccessible(true);

            return $property->getValue(static::getLookupableClass());
        } catch (Exception $e) {
            return static::$defaultLookupColumn;
        }
    }
}
