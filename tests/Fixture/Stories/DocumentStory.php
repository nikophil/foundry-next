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
use Zenstruck\Foundry\Tests\Fixture\Factories\Document\GenericDocumentFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DocumentStory extends Story
{
    public function build(): void
    {
        GenericDocumentFactory::createOne();
    }
}
