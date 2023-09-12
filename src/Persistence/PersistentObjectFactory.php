<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Persistence;

use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Exception\PersistenceNotAvailable;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\ObjectFactory;
use Zenstruck\Foundry\Persistence\Exception\NotEnoughObjects;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends ObjectFactory<T>
 *
 * @phpstan-import-type Parameters from Factory
 */
abstract class PersistentObjectFactory extends ObjectFactory
{
    private bool $persist;

    /** @var list<callable(T):void> */
    private array $afterPersist = [];

    /**
     * @param mixed|Parameters $criteriaOrId
     *
     * @return T
     *
     * @throws \RuntimeException If no object found
     */
    public static function find(mixed $criteriaOrId): object
    {
        return static::repository()->find($criteriaOrId) ?? throw new \RuntimeException(\sprintf('No "%s" object found for "%s".', static::class(), \get_debug_type($criteriaOrId)));
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public static function findOrCreate(array $criteria): object
    {
        try {
            $object = static::repository()->findOneBy($criteria);
        } catch (PersistenceNotAvailable) {
            $object = null;
        }

        return $object ?? static::createOne($criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public static function randomOrCreate(array $criteria = []): object
    {
        try {
            return static::repository()->random($criteria);
        } catch (NotEnoughObjects|PersistenceNotAvailable) {
            return static::createOne($criteria);
        }
    }

    /**
     * @param positive-int $count
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public static function randomSet(int $count, array $criteria = []): array
    {
        return static::repository()->randomSet($count, $criteria);
    }

    /**
     * @param positive-int $min
     * @param positive-int $max
     * @param Parameters   $criteria
     *
     * @return T[]
     */
    public static function randomRange(int $min, int $max, array $criteria = []): array
    {
        return static::repository()->randomRange($min, $max, $criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T[]
     */
    public static function findBy(array $criteria): array
    {
        return static::repository()->findBy($criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    public static function random(array $criteria = []): object
    {
        return static::repository()->random($criteria);
    }

    /**
     * @return T
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function first(string $sortBy = 'id'): object
    {
        return static::repository()->first($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
    }

    /**
     * @return T
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function last(string $sortBy = 'id'): object
    {
        return static::repository()->last($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
    }

    /**
     * @return T[]
     */
    final public static function all(): array
    {
        return static::repository()->findAll();
    }

    /**
     * @return RepositoryDecorator<T>
     */
    final public static function repository(): RepositoryDecorator
    {
        return new RepositoryDecorator(static::class());
    }

    /**
     * @param Parameters $criteria
     */
    final public static function count(array $criteria = []): int
    {
        return static::repository()->count($criteria);
    }

    final public static function truncate(): void
    {
        static::repository()->truncate();
    }

    final public function create(callable|array $attributes = []): object
    {
        $object = parent::create($attributes);
        $configuration = Configuration::instance();
        $persist = $this->persist ?? $configuration->isPersistenceEnabled() && $configuration->persistence()->autoPersist(static::class());

        if (!$persist) {
            return $object;
        }

        if (!$configuration->isPersistenceEnabled()) {
            throw new \LogicException('Persistence cannot be used in unit tests.');
        }

        $configuration->persistence()->save($object);

        foreach ($this->afterPersist as $callback) {
            $callback($object);
        }

        return $object;
    }

    final public function andPersist(): static
    {
        $clone = clone $this;
        $clone->persist = true;

        return $clone;
    }

    final public function withoutPersisting(): static
    {
        $clone = clone $this;
        $clone->persist = false;

        return $clone;
    }

    /**
     * @param callable(T):void $callback
     */
    final public function afterPersist(callable $callback): static
    {
        $clone = clone $this;
        $clone->afterPersist[] = $callback;

        return $clone;
    }

    /**
     * @return class-string<T>
     */
    abstract public static function class(): string;
}
