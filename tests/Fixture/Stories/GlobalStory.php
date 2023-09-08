<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Stories;

use Zenstruck\Foundry\Story;
use Zenstruck\Foundry\Tests\Fixture\Document\Document3;
use Zenstruck\Foundry\Tests\Fixture\Entity\Entity3;

use function Zenstruck\Foundry\persistent_object;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GlobalStory extends Story
{
    public function build(): void
    {
        if (\getenv('DATABASE_URL')) {
            persistent_object(Entity3::class);
        }

        if (\getenv('MONGO_URL')) {
            persistent_object(Document3::class);
        }
    }
}
