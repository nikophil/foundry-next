<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\Mongo;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Document\Document;
use Zenstruck\Foundry\Tests\Fixture\Document\Embeddable;
use function Zenstruck\Foundry\factory;
use function Zenstruck\Foundry\Persistence\persist;
use function Zenstruck\Foundry\Persistence\persistent_factory;
use function Zenstruck\Foundry\Persistence\repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentFactoryEmbedTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function embed_one(): void
    {
        $document = persist(Document::class, ['embeddable' => factory(Embeddable::class, ['prop1' => 'value1'])]);

        $this->assertSame('value1', $document->getEmbeddable()?->getProp1());
        repository(Document::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $document = persistent_factory(Document::class)::first();

        $this->assertSame('value1', $document->getEmbeddable()?->getProp1());
    }

    /**
     * @test
     */
    public function embed_many(): void
    {
        $document = persist(Document::class, ['embeddables' => factory(Embeddable::class, ['prop1' => 'value1'])->many(2)]);

        $this->assertCount(2, $document->getEmbeddables());
        repository(Document::class)->assert()->count(1);

        self::ensureKernelShutdown();

        $document = persistent_factory(Document::class)::first();

        $this->assertCount(2, $document->getEmbeddables());
    }
}
