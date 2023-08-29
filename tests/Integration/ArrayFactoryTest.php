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
use Zenstruck\Foundry\LazyValue;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Tests\Fixture\Factories\ServiceArrayFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\StandaloneArrayFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayFactoryTest extends KernelTestCase
{
    use Factories;

    /**
     * @test
     */
    public function can_use_service(): void
    {
        $this->assertSame(
            [
                'router' => true,
                'fake' => 'value',
            ],
            ServiceArrayFactory::createOne()
        );
    }

    /**
     * @test
     */
    public function can_create_with_defaults(): void
    {
        $this->assertSame(
            [
                'default1' => 'default value 1',
                'default2' => 'default value 2',
                'fake' => 'value',
            ],
            StandaloneArrayFactory::createOne()
        );
    }

    /**
     * @test
     */
    public function can_create_with_overrides(): void
    {
        $this->assertSame(
            [
                'default1' => 'default value 1',
                'default2' => 'override value 2',
                'fake' => 'value',
                'foo' => 'baz',
            ],
            StandaloneArrayFactory::new(['foo' => 'bar'])
                ->with(fn() => ['foo' => LazyValue::new(fn() => 'baz')])
                ->create(['default2' => 'override value 2'])
        );
    }
}
