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
 * @implements \IteratorAggregate<Factory<T>>
 *
 * @phpstan-import-type Attributes from Factory
 */
final class FactoryCollection implements \IteratorAggregate
{
    /**
     * @param Factory<T> $factory
     */
    private function __construct(public readonly Factory $factory, private int $min, private int $max)
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
    public static function many(Factory $factory, int $count): self
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
     * @param Attributes $attributes
     *
     * @return T[]
     */
    public function create(array|callable $attributes = []): array
    {
        return \array_map(static fn(Factory $f) => $f->create($attributes), $this->all());
    }

    /**
     * @return list<Factory<T>>
     */
    public function all(): array
    {
        $factories = [];

        foreach (\array_keys(\array_fill(0, \random_int($this->min, $this->max), null)) as $i) {
            $factories[] = $this->factory->with(['__index' => $i + 1]);
        }

        return $factories;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->all());
    }

    /**
     * @return iterable<array{Factory<T>}>
     */
    public function asDataProvider(): iterable
    {
        foreach ($this as $factory) {
            yield [$factory];
        }
    }
}
