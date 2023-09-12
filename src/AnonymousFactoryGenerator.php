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

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class AnonymousFactoryGenerator
{
    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return ($persistent is true ? class-string<PersistentObjectFactory<T>> : class-string<ObjectFactory<T>>)
     */
    public static function create(string $class, bool $persistent): string
    {
        $anonymousClassName = $persistent ? 'FoundryAnonymousPersistentFactory_' : 'FoundryAnonymousFactory_';
        $anonymousClassName .= \str_replace('\\', '', $class);
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
}
