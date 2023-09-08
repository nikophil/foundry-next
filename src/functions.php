<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry;

use Faker;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

function faker(): Faker\Generator
{
    return Configuration::instance()->faker;
}

/**
 * Create an anonymous factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                    $class
 * @param array<string,mixed>|callable():array<string,mixed> $attributes
 *
 * @return ObjectFactory<T>
 */
function factory(string $class, array|callable $attributes = []): ObjectFactory
{
    return (ProxyGenerator::anonymousFactoryFor($class, persistent: false))::new($attributes);
}

/**
 * Create an object with an anonymous factory.
 *
 * @template T of object
 *
 * @param class-string<T>                                    $class
 * @param array<string,mixed>|callable():array<string,mixed> $attributes
 *
 * @return T
 */
function object(string $class, array|callable $attributes = []): object
{
    return factory($class, $attributes)->create();
}

/**
 * Create an anonymous "persistent" factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                    $class
 * @param array<string,mixed>|callable():array<string,mixed> $attributes
 *
 * @return PersistentObjectFactory<T>
 */
function persistent_factory(string $class, array|callable $attributes = []): PersistentObjectFactory
{
    return (ProxyGenerator::anonymousFactoryFor($class, persistent: true))::new($attributes);
}

/**
 * Create a "persistent" object with an anonymous factory.
 *
 * @template T of object
 *
 * @param class-string<T>                                    $class
 * @param array<string,mixed>|callable():array<string,mixed> $attributes
 *
 * @return T&Proxy
 */
function persistent_object(string $class, array|callable $attributes = []): object
{
    return persistent_factory($class, $attributes)->create();
}

/**
 * @template T of object
 *
 * @param class-string<T> $class
 *
 * @return RepositoryDecorator<T>
 */
function repo(string $class): RepositoryDecorator
{
    return Configuration::instance()->persistence()->repositoryFor($class);
}
