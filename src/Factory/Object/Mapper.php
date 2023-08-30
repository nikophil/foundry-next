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

use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Zenstruck\Foundry\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @immutable
 *
 * @phpstan-import-type Parameters from Factory
 */
final class Mapper
{
    public const ALLOW_EXTRA_ATTRIBUTES = 1;
    public const ALWAYS_FORCE_PROPERTIES = 2;

    private static PropertyAccessor $defaultAccessor;

    private PropertyAccessorInterface $accessor;

    private bool $allowExtraAttributes = false;

    /** @var string[] */
    private array $extraAttributes = [];

    private bool $alwaysForceProperties = false;

    /** @var string[] */
    private array $forceProperties = [];

    /**
     * @internal
     */
    public function __construct(?PropertyAccessorInterface $accessor = null)
    {
        $this->accessor = $accessor ?? self::$defaultAccessor ??= new PropertyAccessor();
    }

    /**
     * @template T of object
     *
     * @param T          $object
     * @param Parameters $parameters
     *
     * @return T
     */
    public function __invoke(object $object, array $parameters): object
    {
        foreach ($parameters as $parameter => $value) {
            if (\in_array($parameter, $this->extraAttributes, true)) {
                continue;
            }

            if ($this->alwaysForceProperties || \in_array($parameter, $this->forceProperties, true)) {
                try {
                    self::set($object, $parameter, $value);
                } catch (\InvalidArgumentException $e) {
                    if (!$this->allowExtraAttributes) {
                        throw $e;
                    }
                }

                continue;
            }

            try {
                $this->accessor->setValue($object, $parameter, $value);
            } catch (NoSuchPropertyException $e) {
                if (!$this->allowExtraAttributes) {
                    throw new \InvalidArgumentException(\sprintf('Cannot set attribute "%s" for object "%s" (not public and no setter).', $parameter, $object::class), previous: $e);
                }
            }
        }

        return $object;
    }

    /**
     * Ignore attributes that can't be set to object.
     *
     * @param string ...$parameters The parameters you'd like the mapper to ignore (if empty, ignore any extra)
     */
    public function allowExtra(string ...$parameters): self
    {
        $clone = clone $this;

        if (!$parameters) {
            $clone->allowExtraAttributes = true;
        }

        $clone->extraAttributes = $parameters;

        return $clone;
    }

    /**
     * Always force properties, never use setters (still uses constructor unless disabled).
     *
     * @param string ...$properties The properties you'd like the mapper to "force set" (if empty, force set all)
     */
    public function alwaysForce(string ...$properties): self
    {
        $clone = clone $this;

        if (!$properties) {
            $clone->alwaysForceProperties = true;
        }

        $clone->forceProperties = $properties;

        return $clone;
    }

    /**
     * @param int-mask-of<self::*> $mode
     */
    public function withMode(int $mode): self
    {
        $clone = clone $this;

        if ($mode & self::ALLOW_EXTRA_ATTRIBUTES) {
            $clone = $clone->allowExtra();
        }

        if ($mode & self::ALWAYS_FORCE_PROPERTIES) {
            $clone = $clone->alwaysForce();
        }

        return $clone;
    }

    private static function set(object $object, string $property, mixed $value): void
    {
        self::accessibleProperty($object, $property)->setValue($object, $value);
    }

    private static function accessibleProperty(object $object, string $name): \ReflectionProperty
    {
        $class = new \ReflectionClass($object);

        if (!$property = self::reflectionProperty($class, $name)) {
            throw new \InvalidArgumentException(\sprintf('Class "%s" does not have property "%s".', $class->getName(), $name));
        }

        return $property;
    }

    /**
     * @param \ReflectionClass<object> $class
     */
    private static function reflectionProperty(\ReflectionClass $class, string $name): ?\ReflectionProperty
    {
        try {
            return $class->getProperty($name);
        } catch (\ReflectionException) {
            if ($class = $class->getParentClass()) {
                return self::reflectionProperty($class, $name);
            }
        }

        return null;
    }
}
