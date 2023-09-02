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

use Zenstruck\Foundry\Tests\Fixture\Entity\StandardEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\StandardEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandardModelFactory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardModelFactoryTestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardModelFactoryTestCase<StandardEntity, StandardEntityFactory>
 */
final class StandardEntityFactoryTest extends StandardModelFactoryTestCase
{
    protected function modelClass(): string
    {
        return StandardEntity::class;
    }

    protected function factory(): StandardModelFactory
    {
        return StandardEntityFactory::new();
    }
}
