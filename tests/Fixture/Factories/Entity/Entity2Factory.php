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

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity2;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends PersistentObjectFactory<Entity2>
 */
final class Entity2Factory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Entity2::class;
    }

    protected function defaults(): array
    {
        return ['prop1' => 'default'];
    }
}
