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
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\SimpleEntityFactory;

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
        $entity = SimpleEntityFactory::createOne();

        $this->assertNotNull($entity->id);
        $this->assertSame('default1', $entity->getProp1());
        $this->assertSame('default1', $entity->_refresh()->getProp1());
    }

    /**
     * @test
     */
    public function ensure_schema_is_reset(): void
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
}
