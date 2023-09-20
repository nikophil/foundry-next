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
use Zenstruck\Foundry\FactoryCollection;
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

    /** @var list<callable(T):void> */
    private array $tempAfterPersist = [];

    /**
     * @param mixed|Parameters $criteriaOrId
     *
     * @return T
     *
     * @throws \RuntimeException If no object found
     */
    final public static function find(mixed $criteriaOrId): object
    {
        return static::repository()->find($criteriaOrId) ?? throw new \RuntimeException(\sprintf('No "%s" object found for "%s".', static::class(), \get_debug_type($criteriaOrId)));
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    final public static function findOrCreate(array $criteria): object
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
    final public static function randomOrCreate(array $criteria = []): object
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
    final public static function randomSet(int $count, array $criteria = []): array
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
    final public static function randomRange(int $min, int $max, array $criteria = []): array
    {
        return static::repository()->randomRange($min, $max, $criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T[]
     */
    final public static function findBy(array $criteria): array
    {
        return static::repository()->findBy($criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return T
     */
    final public static function random(array $criteria = []): object
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
        return new RepositoryDecorator(static::class(), \is_a(static::class, PersistentProxyObjectFactory::class, true));
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

        if ($this instanceof PersistentProxyObjectFactory) {
            $object = ProxyGenerator::create($object);
        }

        if ($object instanceof Proxy) {
            $object->_disableAutoRefresh();
        }

        if (!$this->isPersisting()) {
            return $object;
        }

        $configuration = Configuration::instance();

        if (!$configuration->isPersistenceEnabled()) {
            throw new \LogicException('Persistence cannot be used in unit tests.');
        }

        $configuration->persistence()->save($object);

        foreach ($this->tempAfterPersist as $callback) {
            $callback($object);
        }

        $this->tempAfterPersist = [];

        foreach ($this->afterPersist as $callback) {
            $callback($object);
        }

        if ($object instanceof Proxy) {
            $object->_resetAutoRefresh(); // @phpstan-ignore-line
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

    protected function normalizeParameter(string $name, mixed $value): mixed
    {
        if (!Configuration::instance()->isPersistenceEnabled()) {
            return parent::normalizeParameter($name, $value);
        }

        if ($value instanceof self && isset($this->persist)) {
            $value->persist = $this->persist;
        }

        if ($value instanceof self && Configuration::instance()->persistence()->relationshipMetadata(static::class(), $value::class())?->isCascadePersist) {
            $value->persist = false;
        }

        return parent::normalizeParameter($name, $value);
    }

    protected function normalizeCollection(string $name, FactoryCollection $collection): array
    {
        if (!$this->isPersisting() || !$collection->factory instanceof self) {
            return parent::normalizeCollection($name, $collection);
        }

        $pm = Configuration::instance()->persistence();

        if ($field = $pm->relationshipMetadata($collection->factory::class(), static::class())?->inverseField) {
            $this->tempAfterPersist[] = static function(object $object) use ($collection, $field, $pm) {
                $collection->create([$field => $object]);
                $pm->refresh($object);
            };

            // creation delegated to afterPersist hook - return empty array here
            return [];
        }

        return parent::normalizeCollection($name, $collection);
    }

    private function isPersisting(): bool
    {
        $config = Configuration::instance();

        return $this->persist ?? $config->isPersistenceEnabled() && $config->persistence()->autoPersist(static::class());
    }
}
