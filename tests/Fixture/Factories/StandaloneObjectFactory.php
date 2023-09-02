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

use Zenstruck\Foundry\Factory\ObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\SimpleObject;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends ObjectFactory<SimpleObject>
 */
final class StandaloneObjectFactory extends ObjectFactory
{
    public static function class(): string
    {
        return SimpleObject::class;
    }

    protected function defaults(): array
    {
        return [
            'prop1' => 'value1',
        ];
    }
}
