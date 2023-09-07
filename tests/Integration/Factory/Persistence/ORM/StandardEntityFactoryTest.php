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
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity3;
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity4;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Entity1Factory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardFactoryTestCase;

use function Zenstruck\Foundry\factory;
use function Zenstruck\Foundry\persistent_factory;
use function Zenstruck\Foundry\persistent_object;
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

    /**
     * @test
     */
    public function many_to_one_cascade_persist(): void
    {
        repo(Entity4::class)->assert()->empty();

        $object = persistent_object(Entity3::class, [
            'prop1' => 'value',
            'relation' => factory(Entity4::class, ['prop1' => 'value']),
        ]);

        $this->assertInstanceOf(Entity4::class, $object->getRelation());
        repo(Entity4::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $relation = persistent_factory(Entity4::class)::first();

        $this->assertCount(1, $relation->getModels());
        $this->assertSame('value', $relation->getProp1());
    }

    /**
     * @test
     */
    public function many_to_one_cascade_persist_with_persistent_factory(): void
    {
        repo(Entity4::class)->assert()->empty();

        $object = persistent_object(Entity3::class, [
            'prop1' => 'value',
            'relation' => persistent_factory(Entity4::class, ['prop1' => 'value']),
        ]);

        $this->assertInstanceOf(Entity4::class, $object->getRelation());
        repo(Entity4::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $relation = persistent_factory(Entity4::class)::first();

        $this->assertCount(1, $relation->getModels());
        $this->assertSame('value', $relation->getProp1());
    }

    /**
     * @test
     */
    public function create_many_with_new_relationship_entity(): void
    {
        $models = $this->factory()
            ->createMany(3, fn(int $i) => ['prop1' => "value{$i}", 'relation' => persistent_factory(Entity2::class, ['prop1' => 'value'])])
        ;

        $this->factory()::repository()->assert()->count(3);
        repo(Entity2::class)->assert()->count(3);

        self::ensureKernelShutdown();

        $this->assertSame('value1', $models[0]->getProp1());
        $this->assertSame(1, $models[0]->getRelation()?->id);
        $this->assertSame('value2', $models[1]->getProp1());
        $this->assertSame(2, $models[1]->getRelation()?->id);
        $this->assertSame('value3', $models[2]->getProp1());
        $this->assertSame(3, $models[2]->getRelation()?->id);
    }

    /**
     * @test
     */
    public function create_many_with_existing_relationship_entity(): void
    {
        $relation = persistent_object(Entity2::class, ['prop1' => 'value']);
        $models = $this->factory()
            ->createMany(3, fn(int $i) => ['prop1' => "value{$i}", 'relation' => $relation])
        ;

        $this->factory()::repository()->assert()->count(3);
        repo(Entity2::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $this->assertSame('value1', $models[0]->getProp1());
        $this->assertSame(1, $models[0]->getRelation()?->id);
        $this->assertSame('value2', $models[1]->getProp1());
        $this->assertSame(1, $models[1]->getRelation()?->id);
        $this->assertSame('value3', $models[2]->getProp1());
        $this->assertSame(1, $models[2]->getRelation()?->id);
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
