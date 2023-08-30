<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Test;

use Faker;
use Zenstruck\Foundry\Configuration;
use Zenstruck\Foundry\Factory\FactoryRegistry;
use Zenstruck\Foundry\Factory\Object\Instantiator;
use Zenstruck\Foundry\Factory\ObjectFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @phpstan-import-type InstantiatorCallable from ObjectFactory
 */
final class UnitTestConfig
{
    /** @var InstantiatorCallable|null */
    private static $instantiator;
    private static ?Faker\Generator $faker = null;

    /**
     * @param InstantiatorCallable|null $instantiator
     */
    public static function configure(
        ?callable $instantiator = null,
        ?Faker\Generator $faker = null,
    ): void {
        self::$instantiator = $instantiator;
        self::$faker = $faker;
    }

    /**
     * @internal
     */
    public static function build(): Configuration
    {
        $faker = self::$faker ?? Faker\Factory::create();
        $faker->unique(true);

        return new Configuration(
            new FactoryRegistry([]),
            $faker,
            self::$instantiator ?? Instantiator::withConstructor(),
        );
    }
}
