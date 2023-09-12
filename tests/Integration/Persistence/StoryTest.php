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
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Story;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Document\GlobalDocument;
use Zenstruck\Foundry\Tests\Fixture\Entity\GlobalEntity;
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\GenericDocumentFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\GenericEntityFactory;
use Zenstruck\Foundry\Tests\Fixture\Model\GenericModel;
use Zenstruck\Foundry\Tests\Fixture\Stories\DocumentStory;
use Zenstruck\Foundry\Tests\Fixture\Stories\EntityStory;
use Zenstruck\Foundry\Tests\Fixture\Stories\GlobalStory;

use function Zenstruck\Foundry\Persistence\repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StoryTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @param class-string<Story>                                 $story
     * @param class-string<PersistentObjectFactory<GenericModel>> $factory
     *
     * @test
     * @dataProvider storiesProvider
     */
    public function stories_only_loaded_once(string $story, string $factory): void
    {
        $factory::repository()->assert()->empty();

        $story::load();
        $story::load();
        $story::load();

        $factory::repository()->assert()->count(1);
    }

    /**
     * @return iterable<array{class-string<Story>, class-string<PersistentObjectFactory<GenericModel>>}>
     */
    public static function storiesProvider(): iterable
    {
        if (\getenv('DATABASE_URL')) {
            yield [EntityStory::class, GenericEntityFactory::class];
        }

        if (\getenv('MONGO_URL')) {
            yield [DocumentStory::class, GenericDocumentFactory::class];
        }
    }

    /**
     * @test
     */
    public function global_stories_are_loaded(): void
    {
        if (!\getenv('DATABASE_URL') && !\getenv('MONGO_URL')) {
            $this->markTestSkipped('No persistence enabled.');
        }

        if (\getenv('DATABASE_URL')) {
            repository(GlobalEntity::class)->assert()->count(1);
        }

        if (\getenv('MONGO_URL')) {
            repository(GlobalDocument::class)->assert()->count(1);
        }
    }

    /**
     * @test
     */
    public function global_stories_cannot_be_loaded_again(): void
    {
        if (!\getenv('DATABASE_URL') && !\getenv('MONGO_URL')) {
            $this->markTestSkipped('No persistence enabled.');
        }

        GlobalStory::load();

        if (\getenv('DATABASE_URL')) {
            repository(GlobalEntity::class)->assert()->count(1);
        }

        if (\getenv('MONGO_URL')) {
            repository(GlobalDocument::class)->assert()->count(1);
        }
    }

    /**
     * @test
     */
    public function story_states(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function story_pools(): void
    {
        $this->markTestIncomplete();
    }
}
