<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Factory\Persistence;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Entity\SimpleEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\SimpleEntityFactory;

use function Zenstruck\Foundry\persistent_object;
use function Zenstruck\Foundry\repo;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PersistentObjectFactoryTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function can_create_and_update(): void
    {
        SimpleEntityFactory::repository()->assert()->empty();

        $entity = SimpleEntityFactory::createOne();

        $this->assertNotNull($entity->id);
        $this->assertSame('default1', $entity->getProp1());
        $this->assertSame('default1', $entity->_refresh()->getProp1());

        SimpleEntityFactory::repository()->assert()
            ->count(1)
            ->exists(['prop1' => 'default1'])
            ->notExists(['prop1' => 'invalid'])
        ;

        $this->assertSame($entity->id, SimpleEntityFactory::first()->id);
        $this->assertSame($entity->id, SimpleEntityFactory::last()->id);

        $entity->setProp1('new value');
        $entity->_save();

        $this->assertSame('new value', $entity->getProp1());
        SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'new value']);
    }

    /**
     * @test
     */
    public function can_disable_auto_persist(): void
    {
        SimpleEntityFactory::repository()->assert()->empty();

        $entity = SimpleEntityFactory::new()->withoutPersisting()->create();

        $this->assertNull($entity->id);
        $this->assertSame('default1', $entity->getProp1());

        SimpleEntityFactory::repository()->assert()->empty();
    }

    /**
     * @test
     */
    public function auto_refreshes(): void
    {
        $object = SimpleEntityFactory::createOne();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'default1']);

        self::ensureKernelShutdown();

        // modify and save title "externally"
        $ext = SimpleEntityFactory::first();
        $ext->setProp1('external');
        $ext->_save();

        self::ensureKernelShutdown();

        // "calling method" triggers auto-refresh
        $this->assertSame('external', $object->getProp1());
        SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'external']);
    }

    /**
     * @test
     */
    public function cannot_auto_refresh_if_changes_detected(): void
    {
        $object = SimpleEntityFactory::createOne();

        // initial data
        $this->assertSame('default1', $object->getProp1());
        SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'default1']);

        $object->setProp1('new');

        try {
            $object->setProp1('new 1');
        } catch (\RuntimeException) {
            SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'default1']);
            $object->_save();
            $this->assertSame('new', $object->getProp1());
            SimpleEntityFactory::repository()->assert()->exists(['prop1' => 'new']);

            return;
        }

        $this->fail('Exception not thrown');
    }

    /**
     * @test
     */
    public function repository_and_create_function(): void
    {
        repo(SimpleEntity::class)->assert()->empty();

        $entity = persistent_object(SimpleEntity::class, ['prop1' => 'value']);

        $this->assertNotNull($entity->id);
        $this->assertSame('value', $entity->getProp1());
        $this->assertSame('value', $entity->_refresh()->getProp1());

        repo(SimpleEntity::class)->assert()->count(1);
    }

    protected static function modelClass(): string
    {
        return SimpleEntity::class;
    }

    protected static function factoryClass(): string
    {
        return SimpleEntityFactory::class;
    }
}
