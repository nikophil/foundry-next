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

use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\ProxyHelper;
use Zenstruck\Foundry\Factory\Persistence\IsProxy;
use Zenstruck\Foundry\Factory\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Factory\Persistence\Proxy;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ProxyGenerator
{
    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T&Proxy
     */
    public static function wrap(object $object): object
    {
        return self::generateClassFor($object)::createLazyProxy(static fn() => $object); // @phpstan-ignore-line
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ($persistent is true ? class-string<PersistentObjectFactory<T>> : class-string<ObjectFactory<T>>)
     */
    public static function anonymousFactoryFor(string $class, bool $persistent): string
    {
        $anonymousClassName = 'FoundryAnonymousFactory_'.\str_replace('\\', '', $class);
        $anonymousClassName = \preg_replace('/\W/', '', $anonymousClassName); // sanitize for anonymous classes

        /** @var class-string<ObjectFactory<T>> $anonymousClassName */
        if (!\class_exists($anonymousClassName)) {
            $factoryClass = $persistent ? PersistentObjectFactory::class : ObjectFactory::class;

            $anonymousClassCode = <<<CODE
                /**
                 * @internal
                 */
                final class {$anonymousClassName} extends {$factoryClass}
                {
                    public static function class(): string
                    {
                        return "{$class}";
                    }

                    protected function defaults(): array
                    {
                        return [];
                    }
                }
                CODE;

            eval($anonymousClassCode);
        }

        return $anonymousClassName;
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return class-string<LazyObjectInterface&Proxy&T>
     */
    private static function generateClassFor(object $object): string
    {
        $proxyClass = \str_replace('\\', '', $object::class).'Proxy';

        /** @var class-string<LazyObjectInterface&Proxy&T> $proxyClass */
        if (\class_exists($proxyClass)) {
            return $proxyClass;
        }

        $proxyCode = 'class '.$proxyClass.ProxyHelper::generateLazyProxy(new \ReflectionClass($object::class));
        $proxyCode = \str_replace(
            [
                'implements \Symfony\Component\VarExporter\LazyObjectInterface',
                'use \Symfony\Component\VarExporter\LazyProxyTrait;',
                'if (isset($this->lazyObjectState)) {',
            ],
            [
                \sprintf('implements \%s, \Symfony\Component\VarExporter\LazyObjectInterface', Proxy::class),
                \sprintf('use \%s, \Symfony\Component\VarExporter\LazyProxyTrait;', IsProxy::class),
                "\$this->_autoRefresh();\n\n        if (isset(\$this->lazyObjectReal)) {",
            ],
            $proxyCode
        );

        eval($proxyCode);

        return $proxyClass;
    }
}
