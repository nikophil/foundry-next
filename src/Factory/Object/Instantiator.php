<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Object;

use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type Parameters from Factory
 */
final class Instantiator
{
    /**
     * @template T of object
     *
     * @param Parameters      $parameters
     * @param class-string<T> $class
     *
     * @return T
     */
    public function __invoke(array $parameters, string $class): object
    {
        return new $class(...$parameters);
    }
}
