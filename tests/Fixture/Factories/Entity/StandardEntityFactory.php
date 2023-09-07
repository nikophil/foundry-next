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
use Zenstruck\Foundry\Tests\Fixture\Entity\StandardEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandardModelFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardModelFactory<StandardEntity>
 *
 * @method static StandardEntity|Proxy createOne(array|callable $attributes = [])
 * @method static StandardEntity|Proxy first(string $sortBy = 'id')
 * @method static StandardEntity|Proxy last(string $sortBy = 'id')
 *
 * @phpstan-method static (StandardEntity&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (StandardEntity&Proxy) first(string $sortBy = 'id')
 * @phpstan-method static (StandardEntity&Proxy) last(string $sortBy = 'id')
 */
final class StandardEntityFactory extends StandardModelFactory
{
    public static function class(): string
    {
        return StandardEntity::class;
    }
}
