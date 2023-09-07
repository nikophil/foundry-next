<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Persistence;

use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory;
use Zenstruck\Foundry\Factory\ObjectFactory;
use Zenstruck\Foundry\Factory\ProxyGenerator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends ObjectFactory<T&Proxy>
 */
abstract class PersistentObjectFactory extends ObjectFactory
{
    private bool $persist;

    /** @var list<callable(T&Proxy):void> */
    private array $afterPersist = [];

    /**
     * @return T&Proxy
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function first(string $sortBy = 'id'): object
    {
        if (null === $proxy = static::repository()->first($sortBy)) {
            throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
        }

        return $proxy;
    }

    /**
     * @return T&Proxy
     *
     * @throws \RuntimeException If no objects exist
     */
    final public static function last(string $sortBy = 'id'): Proxy
    {
        if (null === $proxy = static::repository()->last($sortBy)) {
            throw new \RuntimeException(\sprintf('No "%s" objects persisted.', static::class()));
        }

        return $proxy;
    }

    /**
     * @return RepositoryDecorator<T>
     */
    final public static function repository(): RepositoryDecorator
    {
        return Configuration::instance()->persistence()->repositoryFor(static::class());
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

    final protected static function createNested(string $parameter, Factory $factory): mixed
    {
        $nested = parent::createNested($parameter, $factory);

        if ($nested instanceof Proxy) {
            $nested = $nested->_object();
        }

        return $nested; // @phpstan-ignore-line
    }
}
