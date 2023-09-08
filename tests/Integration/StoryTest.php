<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Factory\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Story;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\Document1Factory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Entity1Factory;
use Zenstruck\Foundry\Tests\Fixture\Model\Model1;
use Zenstruck\Foundry\Tests\Fixture\Stories\DocumentStory;
use Zenstruck\Foundry\Tests\Fixture\Stories\EntityStory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class StoryTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @param class-string<Story>                           $story
     * @param class-string<PersistentObjectFactory<Model1>> $factory
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
     * @return iterable<array{class-string<Story>, class-string<PersistentObjectFactory<Model1>>}>
     */
    public static function storiesProvider(): iterable
    {
        if (\getenv('DATABASE_URL')) {
            yield [EntityStory::class, Entity1Factory::class];
        }

        if (\getenv('MONGO_URL')) {
            yield [DocumentStory::class, Document1Factory::class];
        }
    }
}
