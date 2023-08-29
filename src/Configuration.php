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
 * @internal
 */
final class Configuration
{
    /** @var \Closure():self|self|null */
    private static \Closure|self|null $instance = null;

    public function __construct(
        public readonly FactoryRegistry $factories,
        public readonly Faker\Generator $faker,
    ) {
    }

    public static function instance(): self
    {
        if (!self::$instance) {
            throw new \LogicException('Foundry is not yet booted. Ensure ZenstruckFoundryBundle is enabled. If in a test, ensure your TestCase has the Factories trait.');
        }

        return \is_callable(self::$instance) ? (self::$instance)() : self::$instance;
    }

    public static function isBooted(): bool
    {
        return null !== self::$instance;
    }

    public static function boot(\Closure|self $configuration): void
    {
        self::$instance = $configuration;
    }

    public static function shutdown(): void
    {
        self::$instance = null;
    }
}
