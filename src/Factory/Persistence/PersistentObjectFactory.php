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
use Zenstruck\Foundry\Factory\ObjectFactory;

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

    final public function create(callable|array $attributes = []): object
    {
        $object = parent::create($attributes);
        $proxy = ProxyGenerator::wrap($object);
        $persist = $this->persist ?? Configuration::instance()->isPersistenceEnabled();

        if (!$persist) {
            return $proxy;
        }

        if (!Configuration::instance()->isPersistenceEnabled()) {
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
}
