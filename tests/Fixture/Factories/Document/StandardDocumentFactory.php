<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Fixture\Factories\Document;

use Zenstruck\Foundry\Factory\Persistence\Proxy;
use Zenstruck\Foundry\Tests\Fixture\Document\StandardDocument;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandardModelFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends StandardModelFactory<StandardDocument>
 *
 * @method static StandardDocument|Proxy createOne(array|callable $attributes = [])
 * @method static StandardDocument|Proxy first(string $sortBy = 'id')
 * @method static StandardDocument|Proxy last(string $sortBy = 'id')
 *
 * @phpstan-method static (StandardDocument&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (StandardDocument&Proxy) first(string $sortBy = 'id')
 * @phpstan-method static (StandardDocument&Proxy) last(string $sortBy = 'id')
 */
final class StandardDocumentFactory extends StandardModelFactory
{
    public static function class(): string
    {
        return StandardDocument::class;
    }
}
