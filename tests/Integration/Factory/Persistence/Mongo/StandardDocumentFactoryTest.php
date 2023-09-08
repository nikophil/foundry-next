<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Factory\Persistence\Mongo;

use Zenstruck\Foundry\Tests\Fixture\Document\Document2;
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\Document1Factory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\RequiresMongo;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardFactoryTestCase;

use function Zenstruck\Foundry\factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StandardDocumentFactoryTest extends StandardFactoryTestCase
{
    use RequiresMongo;

    /**
     * @test
     */
    public function embed_one(): void
    {
        $object = $this->factory()->create([
            'relation' => factory(Document2::class, ['prop1' => 'value']),
        ]);

        $this->assertInstanceOf(Document2::class, $object->getRelation());

        self::ensureKernelShutdown();

        $object = $this->factory()::first();

        $this->assertInstanceOf(Document2::class, $object->getRelation());
        $this->assertSame('value', $object->getRelation()->getProp1());
    }

    protected function factory(): Model1Factory
    {
        return Document1Factory::new();
    }
}
