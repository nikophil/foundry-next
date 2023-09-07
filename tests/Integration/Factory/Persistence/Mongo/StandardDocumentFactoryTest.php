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

use Zenstruck\Foundry\Tests\Fixture\Document\StandardDocument;
use Zenstruck\Foundry\Tests\Fixture\Entity\StandardEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\StandardDocumentFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\StandardEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandardModelFactory;
use Zenstruck\Foundry\Tests\Integration\Factory\Persistence\StandardModelFactoryTestCase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardModelFactoryTestCase<StandardDocument, StandardDocumentFactory>
 */
final class StandardDocumentFactoryTest extends StandardModelFactoryTestCase
{
    public static function setUpBeforeClass(): void
    {
        if (!\getenv('MONGO_URL')) {
            self::markTestSkipped('Mongo is not available.');
        }
    }

    protected function modelClass(): string
    {
        return StandardDocument::class;
    }

    protected function factory(): StandardModelFactory
    {
        return StandardDocumentFactory::new();
    }
}
