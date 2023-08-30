<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Factory\Persistence;

use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\ProxyHelper;

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
                "\$this->autoRefresh();\n\n        if (isset(\$this->lazyObjectReal)) {",
            ],
            $proxyCode
        );

        eval($proxyCode);

        return $proxyClass;
    }
}
