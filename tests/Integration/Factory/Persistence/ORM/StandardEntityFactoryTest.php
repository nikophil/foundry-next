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

use Zenstruck\Foundry\Tests\Fixture\Entity\Entity1;
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity2;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Entity1Factory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardFactoryTestCase;

use function Zenstruck\Foundry\persistent_factory;
use function Zenstruck\Foundry\repo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardFactoryTestCase<Entity1, Entity1Factory>
 */
final class StandardEntityFactoryTest extends StandardFactoryTestCase
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
        repo(Entity2::class)->assert()->empty();

        $object = $this->factory()->create([
            'relation' => persistent_factory(Entity2::class, ['prop1' => 'value']),
        ]);

        $this->assertInstanceOf(Entity2::class, $object->getRelation());
        repo(Entity2::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $relation = persistent_factory(Entity2::class)::first();

        $this->assertCount(1, $relation->getModels());
        $this->assertSame('value', $relation->getProp1());
    }

    protected function modelClass(): string
    {
        return Entity1::class;
    }

    protected function factory(): Model1Factory
    {
        return Entity1Factory::new();
    }
}
