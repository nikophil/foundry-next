<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Factories;

use Zenstruck\Foundry\Factory\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\Model\SimpleModel;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template T of SimpleModel
 * @extends PersistentObjectFactory<T>
 */
abstract class SimpleModelFactory extends PersistentObjectFactory
{
    protected function defaults(): array|callable
    {
        return [
            'prop1' => 'default1',
        ];
    }
}
