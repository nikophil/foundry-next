<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Persistence;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\ObjectFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Model\Embeddable;
use Zenstruck\Foundry\Tests\Fixture\Model\WithEmbeddable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class EmbeddableFactoryTestCase extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function embed_one(): void
    {
        $factory = $this->withEmbeddableFactory();
        $object = $factory->create(['embeddable' => $this->embeddableFactory()->with(['prop1' => 'value1'])]);

        $this->assertSame('value1', $object->getEmbeddable()->getProp1());
        $factory::repository()->assert()->count(1);

        self::ensureKernelShutdown();

        $object = $factory::first();

        $this->assertSame('value1', $object->getEmbeddable()->getProp1());
    }

    /**
     * @return PersistentObjectFactory<WithEmbeddable>
     */
    abstract protected function withEmbeddableFactory(): PersistentObjectFactory;

    /**
     * @return ObjectFactory<Embeddable>
     */
    abstract protected function embeddableFactory(): ObjectFactory;
}
