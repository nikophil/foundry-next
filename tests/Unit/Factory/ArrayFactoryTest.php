<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Unit\Factory;

use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Tests\Fixture\Factories\ArrayFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayFactoryTest extends TestCase
{
    use Factories;

    /**
     * @test
     */
    public function can_create_with_defaults(): void
    {
        $this->assertSame(
            [
                'router' => false,
                'default1' => 'default value 1',
                'default2' => 'default value 2',
                'fake' => 'value',
            ],
            ArrayFactory::createOne()
        );
    }

    /**
     * @test
     */
    public function can_create_with_overrides(): void
    {
        $this->assertSame(
            [
                'router' => false,
                'default1' => 'default value 1',
                'default2' => 'override value 2',
                'fake' => 'value',
                'foo' => 'baz',
            ],
            ArrayFactory::new(['foo' => 'bar'])
                ->with(fn() => ['foo' => LazyValue::new(fn() => 'baz')])
                ->create(['default2' => 'override value 2'])
        );
    }
}
