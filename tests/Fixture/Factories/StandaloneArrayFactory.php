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

use Zenstruck\Foundry\Factory\ArrayFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StandaloneArrayFactory extends ArrayFactory
{
    protected function defaults(): array|callable
    {
        return [
            'default1' => 'default value 1',
            'default2' => 'default value 2',
            'fake' => self::faker()->randomElement(['value']),
        ];
    }
}
