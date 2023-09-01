<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Factory\Persistence\ORM;

use Zenstruck\Foundry\Tests\Fixture\Entity\SimpleEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\SimpleEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\SimpleModelFactory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\SimpleModelFactoryTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends SimpleModelFactoryTest<SimpleEntity, SimpleEntityFactory>
 */
final class SimpleEntityFactoryTest extends SimpleModelFactoryTest
{
    protected function modelClass(): string
    {
        return SimpleEntity::class;
    }

    protected function factory(): SimpleModelFactory
    {
        return SimpleEntityFactory::new();
    }
}
