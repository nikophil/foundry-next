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
    public function auto_persists(): void
    {
        SimpleEntityFactory::repo()->assert()->empty();

        $entity = SimpleEntityFactory::createOne();

        $this->assertNotNull($entity->id);
        $this->assertSame('default1', $entity->getProp1());
        $this->assertSame('default1', $entity->_refresh()->getProp1());

        SimpleEntityFactory::repo()->assert()->count(1);
    }

    /**
     * @test
     */
    public function can_disable_auto_persist(): void
    {
        SimpleEntityFactory::repo()->assert()->empty();

        $entity = SimpleEntityFactory::new()->withoutPersisting()->create();

        $this->assertNull($entity->id);
        $this->assertSame('default1', $entity->getProp1());

        SimpleEntityFactory::repo()->assert()->empty();
    }

    /**
     * @test
     */
    public function auto_refreshes(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function cannot_auto_refresh_if_changes_detected(): void
    {
        $this->markTestIncomplete();
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
}
