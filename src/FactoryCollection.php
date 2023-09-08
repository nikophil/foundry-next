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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T
 *
 * @phpstan-import-type Parameters from Factory
 */
final class FactoryCollection
{
    /**
     * @param Factory<T> $factory
     */
    private function __construct(private Factory $factory, private int $min, private int $max)
    {
        if ($min > $max) {
            throw new \InvalidArgumentException('Min must be less than max.');
        }
    }

    /**
     * @param Factory<T> $factory
     *
     * @return self<T>
     */
    public static function set(Factory $factory, int $count): self
    {
        return new self($factory, $count, $count);
    }

    /**
     * @param Factory<T> $factory
     *
     * @return self<T>
     */
    public static function range(Factory $factory, int $min, int $max): self
    {
        return new self($factory, $min, $max);
    }

    /**
     * @param Parameters|callable(int):Parameters $attributes
     *
     * @return list<T>
     */
    public function create(array|callable $attributes = []): array
    {
        $objects = [];

        foreach ($this->all() as $i => $factory) {
            $objects[] = $factory->create(
                \is_callable($attributes) ? $attributes($i + 1) : $attributes
            );
        }

        return $objects;
    }

    /**
     * @return list<Factory<T>>
     */
    public function all(): array
    {
        return \array_map(
            fn(): Factory => clone $this->factory,
            \array_fill(0, \random_int($this->min, $this->max), null)
        );
    }
}
