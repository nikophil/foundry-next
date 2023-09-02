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
use Zenstruck\Foundry\Tests\Fixture\Entity\SimpleEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\SimpleModelFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends SimpleModelFactory<SimpleEntity>
 *
 * @method static SimpleEntity|Proxy createOne(array|callable $attributes = [])
 * @method static SimpleEntity|Proxy first(string $sortBy = 'id')
 * @method static SimpleEntity|Proxy last(string $sortBy = 'id')
 *
 * @phpstan-method static (SimpleEntity&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (SimpleEntity&Proxy) first(string $sortBy = 'id')
 * @phpstan-method static (SimpleEntity&Proxy) last(string $sortBy = 'id')
 */
final class SimpleEntityFactory extends SimpleModelFactory
{
    public static function class(): string
    {
        return SimpleEntity::class;
    }

    protected function defaults(): array
    {
        return [
            'prop1' => 'default1',
        ];
    }
}
