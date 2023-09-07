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
use Zenstruck\Foundry\Tests\Fixture\Document\Document1;
use Zenstruck\Foundry\Tests\Fixture\Factories\Model1Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @extends Model1Factory<Document1>
 *
 * @method static Document1|Proxy createOne(array|callable $attributes = [])
 * @method static Document1|Proxy first(string $sortBy = 'id')
 * @method static Document1|Proxy last(string $sortBy = 'id')
 *
 * @phpstan-method static (Document1&Proxy) createOne(array|callable $attributes = [])
 * @phpstan-method static (Document1&Proxy) first(string $sortBy = 'id')
 * @phpstan-method static (Document1&Proxy) last(string $sortBy = 'id')
 */
final class Document1Factory extends Model1Factory
{
    public static function class(): string
    {
        return Document1::class;
    }
}
