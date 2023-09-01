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

use Zenstruck\Foundry\Factory\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Factory\Persistence\Proxy;
use Zenstruck\Foundry\Tests\Fixture\Entity\SimpleEntity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends PersistentObjectFactory<SimpleEntity>
 *
 * @method static SimpleEntity|Proxy createOne(array|callable $attributes = [])
 * @phpstan-method static SimpleEntity&Proxy createOne(array|callable $attributes = [])
 */
final class SimpleEntityFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return SimpleEntity::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'prop1' => 'default1',
        ];
    }
}
