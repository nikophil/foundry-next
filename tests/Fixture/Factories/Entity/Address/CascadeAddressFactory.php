<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Address;

use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\Entity\Address\CascadeAddress;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends PersistentObjectFactory<CascadeAddress>
 */
final class CascadeAddressFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return CascadeAddress::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'city' => self::faker()->city(),
        ];
    }
}
