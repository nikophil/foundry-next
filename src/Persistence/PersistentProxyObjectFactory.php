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

use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends PersistentObjectFactory<T&Proxy<T>>
 *
 * @phpstan-import-type Parameters from Factory
 */
abstract class PersistentProxyObjectFactory extends PersistentObjectFactory
{
    /**
     * @return class-string<T>
     */
    abstract public static function class(): string;

    /**
     * @param callable(T,Parameters):void $callback
     */
    final public function afterInstantiate(callable $callback): static
    {
        return parent::afterInstantiate($callback);
    }
}
