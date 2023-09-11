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
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\ObjectFactory;
use Zenstruck\Foundry\ProxyGenerator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends ObjectFactory<T&Proxy>
 *
 * @phpstan-import-type Parameters from Factory
 */
abstract class PersistentObjectFactory extends ObjectFactory
{
    private bool $persist;

    /** @var list<callable(T&Proxy):void> */
    private array $afterPersist = [];

    /**
     * @param mixed|Parameters $criteriaOrId
     *
     * @return T&Proxy
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
     * @return T&Proxy
     */
    public static function findOrCreate(array $criteria): object
    {
        if ($object = static::repository()->findOneBy($criteria)) {
            return $object;
        }

        return static::createOne($criteria);
    }

    /**
     * @param Parameters $criteria
     *
     * @return list<T&Proxy>
     */
    public static function findBy(array $criteria): array
    {
        return static::repository()->findBy($criteria);
    }

    /**
     * @return T&Proxy
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function first(string $sortBy = 'id'): object
    {
        return static::repository()->first($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
    }

    /**
     * @return T&Proxy
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function last(string $sortBy = 'id'): Proxy
    {
        return static::repository()->last($sortBy) ?? throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
    }

    /**
     * @return list<T&Proxy>
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
        return Configuration::instance()->persistence()->repositoryFor(static::class());
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
        $proxy = ProxyGenerator::wrap($object);
        $persist = $this->persist ?? $configuration->isPersistenceEnabled() && $configuration->persistence()->managerFor(static::class())->autoPersist();

        if (!$persist) {
            return $proxy->_disableAutoRefresh();
        }

        if (!$configuration->isPersistenceEnabled()) {
            throw new \LogicException('Persistence cannot be used in unit tests.');
        }

        $proxy->_save();

        foreach ($this->afterPersist as $callback) {
            $callback($proxy);
        }

        return $proxy;
    }

    final public function withoutPersisting(): static
    {
        $clone = clone $this;
        $clone->persist = false;

        return $clone;
    }

    /**
     * @param callable(T&Proxy):void $callback
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

    final protected static function normalizeParameter(string $name, mixed $value): mixed
    {
        $value = parent::normalizeParameter($name, $value);

        if ($value instanceof Proxy) {
            $value = $value->_object();
        }

        return $value;
    }
}
