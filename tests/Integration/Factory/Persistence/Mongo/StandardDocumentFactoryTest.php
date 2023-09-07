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

use Zenstruck\Foundry\Tests\Fixture\Document\Document1;
use Zenstruck\Foundry\Tests\Fixture\Document\Document2;
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\Document1Factory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardFactoryTestCase;

use function Zenstruck\Foundry\factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardFactoryTestCase<Document1, Document1Factory>
 */
final class StandardDocumentFactoryTest extends StandardFactoryTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\getenv('MONGO_URL')) {
            self::markTestSkipped('Mongo is not available.');
        }
    }

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

    protected function modelClass(): string
    {
        return Document1::class;
    }

    protected function factory(): Model1Factory
    {
        return Document1Factory::new();
    }
}
