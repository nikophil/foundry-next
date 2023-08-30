<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Factory;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Tests\Fixture\Factories\ServiceObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandaloneObjectFactory;
use Zenstruck\Foundry\Tests\Fixture\SimpleObject;

use function Zenstruck\Foundry\factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ObjectFactoryTest extends KernelTestCase
{
    use Factories;

    /**
     * @test
     */
    public function can_create_service_factory(): void
    {
        $object = ServiceObjectFactory::createOne();

        $this->assertSame('router-constructor', $object->getProp1());
        $this->assertSame('default-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function can_create_standalone_factory(): void
    {
        $object = StandaloneObjectFactory::createOne();

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('default-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }

    /**
     * @test
     */
    public function create_anonymous_factory(): void
    {
        $object = factory(SimpleObject::class, ['prop1' => 'value1'])->create(['prop2' => 'value2']);

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('value2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());

        $object = factory(SimpleObject::class, ['prop1' => 'value1'])->create(['prop2' => 'value2']);

        $this->assertSame('value1-constructor', $object->getProp1());
        $this->assertSame('value2-constructor', $object->getProp2());
        $this->assertNull($object->getProp3());
    }
}
