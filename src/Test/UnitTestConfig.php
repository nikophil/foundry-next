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
use Zenstruck\Foundry\FactoryRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UnitTestConfig
{
    private static ?Faker\Generator $faker = null;

    public static function configure(?Faker\Generator $faker = null): void
    {
        self::$faker = $faker;
    }

    /**
     * @internal
     */
    public static function build(): Configuration
    {
        $faker = self::$faker ?? Faker\Factory::create();
        $faker->unique(true);

        return new Configuration(new FactoryRegistry([]), $faker);
    }
}
