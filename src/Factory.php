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
     * @param Attributes $attributes
     *
     * @return T
     */
    abstract public function create(array|callable $attributes = []): mixed;

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

        \array_walk_recursive($parameters, static function(mixed &$v): void {
            if ($v instanceof LazyValue) {
                $v = $v();
            }
        });

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
     * @return Parameters
     */
    abstract protected function defaults(): array;
}
