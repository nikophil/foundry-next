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
use Zenstruck\Foundry\Factory\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @template T
 * @phpstan-type Parameters = array<string,mixed>
 * @phpstan-type Attributes = Parameters|callable():Parameters
 */
abstract class Factory
{
    /** @var list<Attributes> */
    private array $attributes;

    /**
     * @param Attributes $attributes
     */
    final public static function new(array|callable $attributes = []): static
    {
        return Configuration::instance()->factories->new(static::class)->initialize()->with($attributes);
    }

    /**
     * @param Attributes $attributes
     *
     * @return T
     */
    final public static function createOne(array|callable $attributes = []): mixed
    {
        return static::new()->create($attributes);
    }

    /**
     * @param Parameters|callable(int):Parameters $attributes
     *
     * @return T[]
     */
    final public static function createMany(int $number, array|callable $attributes = []): array
    {
        return static::new()->many($number)->create($attributes);
    }

    /**
     * @param Attributes $attributes
     *
     * @return T
     */
    abstract public function create(array|callable $attributes = []): mixed;

    /**
     * @return Collection<T>
     */
    final public function many(int $min, ?int $max = null): Collection
    {
        if (!$max) {
            return Collection::set($this, $min);
        }

        return Collection::range($this, $min, $max);
    }

    /**
     * @param Attributes $attributes
     */
    final public function with(array|callable $attributes = []): static
    {
        $clone = clone $this;
        $clone->attributes[] = $attributes;

        return $clone;
    }

    final protected static function faker(): Faker\Generator
    {
        return Configuration::instance()->faker;
    }

    /**
     * @internal
     *
     * @param Attributes $attributes
     *
     * @return Parameters
     */
    final protected function normalizeAttributes(array|callable $attributes = []): array
    {
        $attributes = [$this->defaults(), ...$this->attributes, $attributes];

        $parameters = \array_merge(
            ...\array_map(static fn(array|callable $attr) => \is_callable($attr) ? $attr() : $attr, $attributes)
        );

        // convert lazy values
        $parameters = \array_map(
            static fn(mixed $v) => $v instanceof LazyValue ? $v() : $v,
            $parameters,
        );

        // normalize values
        foreach ($parameters as $key => &$value) {
            $value = static::normalizeParameter($key, $value);
        }

        return $parameters;
    }

    /**
     * Override to adjust default attributes & config.
     */
    protected function initialize(): static
    {
        return $this;
    }

    /**
     * @internal
     */
    protected static function normalizeParameter(string $name, mixed $value): mixed
    {
        if ($value instanceof self) {
            $value = $value->create();
        }

        return $value;
    }

    /**
     * @return Parameters
     */
    abstract protected function defaults(): array;
}
