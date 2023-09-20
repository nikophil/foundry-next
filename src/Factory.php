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
 * @phpstan-type Attributes = Parameters|callable(int):Parameters
 */
abstract class Factory
{
    /** @var Attributes[] */
    private array $attributes;

    /**
     * @param Attributes $attributes
     */
    final public static function new(array|callable $attributes = []): static
    {
        if (Configuration::isBooted()) {
            $factory = Configuration::instance()->factories->get(static::class);
        }

        try {
            $factory ??= new static(); // @phpstan-ignore-line
        } catch (\ArgumentCountError $e) { // @phpstan-ignore-line
            throw new \LogicException('Factories with dependencies (services) cannot be created before foundry is booted.', previous: $e);
        }

        return $factory->initialize()->with($attributes);
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
     * @return FactoryCollection<T>
     */
    final public function many(int $min, ?int $max = null): FactoryCollection
    {
        if (!$max) {
            return FactoryCollection::set($this, $min);
        }

        return FactoryCollection::range($this, $min, $max);
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
        $index = 1;

        // find if an index was set by factory collection
        foreach ($attributes as $i => $attr) {
            if (\is_array($attr) && isset($attr['__index'])) {
                $index = $attr['__index'];
                unset($attributes[$i]);
                break;
            }
        }

        $parameters = \array_merge(
            ...\array_map(static fn(array|callable $attr) => \is_callable($attr) ? $attr($index) : $attr, $attributes)
        );

        // convert lazy values
        $parameters = \array_map(
            static fn(mixed $v) => $v instanceof LazyValue ? $v() : $v,
            $parameters,
        );

        // normalize values
        foreach ($parameters as $key => &$value) {
            $value = $this->normalizeParameter($key, $value);
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
    protected function normalizeParameter(string $name, mixed $value): mixed
    {
        if ($value instanceof self) {
            $value = $value->create();
        }

        if ($value instanceof FactoryCollection) {
            $value = $this->normalizeCollection($name, $value);
        }

        return $value;
    }

    /**
     * @internal
     *
     * @param FactoryCollection<mixed> $collection
     *
     * @return self<mixed>[]
     */
    protected function normalizeCollection(string $name, FactoryCollection $collection): array
    {
        return \array_map(fn(Factory $f) => $this->normalizeParameter($name, $f), $collection->all());
    }

    /**
     * @return Attributes
     */
    abstract protected function defaults(): array|callable;
}
