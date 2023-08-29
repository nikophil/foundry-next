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
 * @internal
 */
final class Configuration
{
    private static ?self $instance = null;

    public function __construct(public readonly FactoryRegistry $factories)
    {
    }

    public static function instance(): self
    {
        return self::$instance ?? throw new \LogicException('Foundry is not yet booted. Ensure ZenstruckFoundryBundle is enabled. If in a test, ensure your TestCase has the Factories trait.');
    }

    public static function boot(?self $configuration = null): void
    {
        self::$instance = $configuration ?? new self(new FactoryRegistry([]));
    }

    public static function shutdown(): void
    {
        self::$instance = null;
    }
}
