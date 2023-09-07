<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Factories\Entity;

use Zenstruck\Foundry\Factory\Persistence\Proxy;
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity1;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends Model1Factory<Entity1>
 *
 * @method static Entity1|Proxy createOne(array|callable $attributes = [])
 * @method static Entity1|Proxy first(string $sortBy = 'id')
 * @method static Entity1|Proxy last(string $sortBy = 'id')
 *
 * @phpstan-method static (Entity1&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (Entity1&Proxy) first(string $sortBy = 'id')
 * @phpstan-method static (Entity1&Proxy) last(string $sortBy = 'id')
 */
final class Entity1Factory extends Model1Factory
{
    public static function class(): string
    {
        return Entity1::class;
    }
}
