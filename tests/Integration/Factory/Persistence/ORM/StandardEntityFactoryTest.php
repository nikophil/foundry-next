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
use Zenstruck\Foundry\Tests\Fixture\Entity\StandardRelationEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\StandardEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandardModelFactory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardModelFactoryTestCase;

use function Zenstruck\Foundry\persistent_factory;
use function Zenstruck\Foundry\repo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardModelFactoryTestCase<StandardEntity, StandardEntityFactory>
 */
final class StandardEntityFactoryTest extends StandardModelFactoryTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\getenv('DATABASE_URL')) {
            self::markTestSkipped('No database available.');
        }
    }

    /**
     * @test
     */
    public function many_to_one_relationship(): void
    {
        repo(StandardRelationEntity::class)->assert()->empty();

        $object = $this->factory()->create([
            'relation' => persistent_factory(StandardRelationEntity::class, ['prop1' => 'value']),
        ]);

        $this->assertInstanceOf(StandardRelationEntity::class, $object->getRelation());
        repo(StandardRelationEntity::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $relation = persistent_factory(StandardRelationEntity::class)::first();

        $this->assertCount(1, $relation->getModels());
        $this->assertSame('value', $relation->getProp1());
    }

    protected function modelClass(): string
    {
        return StandardEntity::class;
    }

    protected function factory(): StandardModelFactory
    {
        return StandardEntityFactory::new();
    }
}
