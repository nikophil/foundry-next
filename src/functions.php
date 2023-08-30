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

use Zenstruck\Foundry\Factory\ObjectFactory;

/**
 * Create an anonymous factory for the given class.
 *
 * @template T of object
 *
 * @param class-string<T>                                    $class
 * @param array<string,mixed>|callable():array<string,mixed> $attributes
 *
 * @return ObjectFactory<T>
 */
function factory(string $class, array|callable $attributes = []): ObjectFactory
{
    $anonymousClassName = 'FoundryAnonymousFactory_'.\str_replace('\\', '', $class);
    $anonymousClassName = \preg_replace('/\W/', '', $anonymousClassName); // sanitize for anonymous classes

    /** @var class-string<ObjectFactory<T>> $anonymousClassName */
    if (!\class_exists($anonymousClassName)) {
        $factoryClass = ObjectFactory::class;

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

                protected function defaults(): array|callable
                {
                    return [];
                }
            }
            CODE;

        eval($anonymousClassCode);
    }

    return $anonymousClassName::new($attributes);
}
