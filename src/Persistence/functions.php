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

use Zenstruck\Foundry\AnonymousFactoryGenerator;
use Zenstruck\Foundry\Configuration;

/**
 * @template T of object
 *
 * @param class-string<T> $class
 *
 * @return RepositoryDecorator<T>
 */
function repository(string $class): RepositoryDecorator
{
    return new RepositoryDecorator($class, false);
}

/**
 * @template T of object
 *
 * @param class-string<T> $class
 *
 * @return RepositoryDecorator<T&Proxy<T>>
 */
function proxy_repository(string $class): RepositoryDecorator
{
    return new RepositoryDecorator($class, true); // @phpstan-ignore-line
}

/**
 * Create an anonymous "persistent" factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return PersistentObjectFactory<T>
 */
function persistent_factory(string $class, array|callable $attributes = []): PersistentObjectFactory
{
    return AnonymousFactoryGenerator::create($class, PersistentObjectFactory::class)::new($attributes);
}

/**
 * Create a "persistent" object with an anonymous factory.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return T
 */
function persist(string $class, array|callable $attributes = []): object
{
    return persistent_factory($class, $attributes)->andPersist()->create();
}

/**
 * Create an anonymous "persistent proxy" factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return PersistentProxyObjectFactory<T>
 */
function proxy_factory(string $class, array|callable $attributes = []): PersistentProxyObjectFactory
{
    return AnonymousFactoryGenerator::create($class, PersistentProxyObjectFactory::class)::new($attributes);
}

/**
 * Create a "persistent proxy" object with an anonymous factory.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return T&Proxy<T>
 */
function proxy_persist(string $class, array|callable $attributes = []): object
{
    return proxy_factory($class, $attributes)->andPersist()->create();
}

/**
 * @template T of object
 *
 * @param T $object
 *
 * @return T
 */
function save(object $object): object
{
    return Configuration::instance()->persistence()->save($object);
}

/**
 * @template T of object
 *
 * @param T $object
 *
 * @return T
 */
function refresh(object &$object): object
{
    return Configuration::instance()->persistence()->refresh($object);
}

/**
 * @template T of object
 *
 * @param T $object
 *
 * @return T
 */
function delete(object $object): object
{
    return Configuration::instance()->persistence()->delete($object);
}

/**
 * @param callable():void $callback
 */
function flush_after(callable $callback): void
{
    Configuration::instance()->persistence()->flushAfter($callback);
}
