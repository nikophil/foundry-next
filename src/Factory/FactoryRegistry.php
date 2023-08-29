<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory;

use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class FactoryRegistry
{
    /**
     * @param Factory<mixed>[] $factories
     */
    public function __construct(private iterable $factories)
    {
    }

    /**
     * @template T of Factory
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    public function new(string $class): Factory
    {
        foreach ($this->factories as $factory) {
            if ($class === $factory::class) {
                return $factory; // @phpstan-ignore-line
            }
        }

        if (!\class_exists($class)) {
            throw new \InvalidArgumentException(\sprintf('Factory "%s" does not exist.', $class));
        }

        if (!\is_a($class, Factory::class, true)) {
            throw new \LogicException(\sprintf('"%s" is not a factory.', $class));
        }

        try {
            return new $class();
        } catch (\ArgumentCountError) { // @phpstan-ignore-line
            throw new \LogicException('Factories with dependencies (services) cannot be created before foundry is booted.');
        }
    }
}
