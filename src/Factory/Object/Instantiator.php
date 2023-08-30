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
    private const WITH_CONSTRUCTOR = '_with_constructor';
    private const WITHOUT_CONSTRUCTOR = '_without_constructor';

    private function __construct(private string $mode)
    {
    }

    /**
     * @template T of object
     *
     * @param Parameters      $parameters
     * @param class-string<T> $class
     *
     * @return T
     */
    public function __invoke(array &$parameters, string $class): object
    {
        $refClass = new \ReflectionClass($class);

        if (self::WITHOUT_CONSTRUCTOR === $this->mode) {
            return $refClass->newInstanceWithoutConstructor();
        }

        if (!$method = $this->factoryMethodFor($refClass)) {
            return $refClass->newInstance();
        }

        $arguments = [];

        foreach ($method->getParameters() as $parameter) {
            /** @var \ReflectionParameter $parameter */
            if (\array_key_exists($parameter->name, $parameters)) {
                $arguments[] = $parameters[$parameter->name];
                unset($parameters[$parameter->name]);

                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            throw new \LogicException(\sprintf('Missing required argument "%s" for "%s::%s()".', $parameter->name, $class, $method->name));
        }

        if ($method->isConstructor()) {
            return $refClass->newInstance(...$arguments);
        }

        $object = $method->invoke(null, ...$arguments);

        if (!$object instanceof $class) {
            throw new \LogicException(\sprintf('Named constructor "%s" for "%s" must return an instance of "%s".', $method->name, $class, $class));
        }

        return $object;
    }

    public static function withConstructor(): self
    {
        return new self(self::WITH_CONSTRUCTOR);
    }

    public static function withoutConstructor(): self
    {
        return new self(self::WITHOUT_CONSTRUCTOR);
    }

    public static function namedConstructor(string $method): self
    {
        return new self($method);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private function factoryMethodFor(\ReflectionClass $class): ?\ReflectionMethod
    {
        if (self::WITH_CONSTRUCTOR === $this->mode) {
            return self::constructorFor($class);
        }

        return self::namedConstructorFor($class, $this->mode);
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function namedConstructorFor(\ReflectionClass $class, string $name): \ReflectionMethod
    {
        if (!$method = $class->getMethod($name)) {
            throw new \LogicException(\sprintf('Named constructor "%s" for "%s" does not exist.', $name, $class->getName()));
        }

        if (!$method->isPublic()) {
            throw new \LogicException(\sprintf('Named constructor "%s" for "%s" is not public.', $name, $class->getName()));
        }

        if (!$method->isStatic()) {
            throw new \LogicException(\sprintf('Named constructor "%s" for "%s" is not static.', $name, $class->getName()));
        }

        return $method;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function constructorFor(\ReflectionClass $class): ?\ReflectionMethod
    {
        if (!$constructor = $class->getConstructor()) {
            return null;
        }

        if (!$constructor->isPublic()) {
            throw new \LogicException(\sprintf('Constructor for "%s" is not public.', $class->getName()));
        }

        return $constructor;
    }
}
