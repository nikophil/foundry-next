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

use Zenstruck\Foundry\Object\Instantiator;
use Zenstruck\Foundry\Object\Mapper;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of object
 * @extends Factory<T>
 *
 * @phpstan-type InstantiatorCallable = Instantiator|callable(Parameters,class-string<T>):T
 * @phpstan-import-type Parameters from Factory
 * @phpstan-import-type Attributes from Factory
 */
abstract class ObjectFactory extends Factory
{
    /** @var list<callable(Parameters,class-string<T>):Parameters> */
    private array $beforeInstantiate = [];

    /** @var list<callable(T,Parameters):void> */
    private array $afterInstantiate = [];

    /** @var InstantiatorCallable|null */
    private $instantiator;

    private Mapper|false $mapper;

    /**
     * @return class-string<T>
     */
    abstract public static function class(): string;

    /**
     * @final
     */
    public function create(callable|array $attributes = []): object
    {
        $parameters = $this->normalizeAttributes($attributes);

        foreach ($this->beforeInstantiate as $hook) {
            $parameters = $hook($parameters, static::class());

            if (!\is_array($parameters)) {
                throw new \LogicException('Before Instantiate hook callback must return a parameter array.');
            }
        }

        $parameters = $this->normalizeParameters($parameters);
        $instantiator = $this->instantiator ?? Configuration::instance()->instantiator;
        $object = $instantiator($parameters, static::class());

        // don't use mapper if instantiator is a custom callback
        if ($instantiator instanceof Instantiator && $mapper = $this->mapper ?? Configuration::instance()->mapper) {
            $object = $mapper($object, $parameters);
        }

        foreach ($this->afterInstantiate as $hook) {
            $hook($object, $parameters);
        }

        return $object;
    }

    /**
     * @param InstantiatorCallable $instantiator
     */
    final public function instantiateWith(callable $instantiator): static
    {
        $clone = clone $this;
        $clone->instantiator = $instantiator;

        return $clone;
    }

    /**
     * @param int-mask-of<Mapper::*>|callable(Mapper):Mapper $with
     */
    final public function configureMapping(callable|int $with): static
    {
        $mapper = $this->mapper ?? null;

        if (!$mapper) {
            $mapper = Configuration::instance()->mapper;
        }

        $clone = clone $this;

        if (\is_int($with)) {
            $clone->mapper = $mapper->withMode($with);

            return $clone;
        }

        $mapper = $with($mapper);

        if (!$mapper instanceof Mapper) {
            throw new \LogicException('Mapper callback must return a Mapper instance.');
        }

        $clone->mapper = $mapper;

        return $clone;
    }

    final public function disableMapping(): static
    {
        $clone = clone $this;
        $clone->mapper = false;

        return $clone;
    }

    /**
     * @param callable(Parameters,class-string<T>):Parameters $callback
     */
    final public function beforeInstantiate(callable $callback): static
    {
        $clone = clone $this;
        $clone->beforeInstantiate[] = $callback;

        return $clone;
    }

    /**
     * @final
     *
     * @param callable(T,Parameters):void $callback
     */
    public function afterInstantiate(callable $callback): static
    {
        $clone = clone $this;
        $clone->afterInstantiate[] = $callback;

        return $clone;
    }
}
