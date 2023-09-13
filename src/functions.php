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

function faker(): Faker\Generator
{
    return Configuration::instance()->faker;
}

/**
 * Create an anonymous factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return ObjectFactory<T>
 */
function factory(string $class, array|callable $attributes = []): ObjectFactory
{
    return (AnonymousFactoryGenerator::create($class, persistent: false))::new($attributes);
}

/**
 * Create an object with an anonymous factory.
 *
 * @template T of object
 *
 * @param class-string<T>                                       $class
 * @param array<string,mixed>|callable(int):array<string,mixed> $attributes
 *
 * @return T
 */
function object(string $class, array|callable $attributes = []): object
{
    return factory($class, $attributes)->create();
}
