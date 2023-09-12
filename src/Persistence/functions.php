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
    return Configuration::instance()->persistence()->repositoryFor($class);
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
    return (AnonymousFactoryGenerator::create($class, persistent: true))::new($attributes);
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
function persist_object(string $class, array|callable $attributes = []): object
{
    return persistent_factory($class, $attributes)->create();
}
