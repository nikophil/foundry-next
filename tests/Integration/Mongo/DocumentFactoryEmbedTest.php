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
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function embed_many(): void
    {
        $this->markTestIncomplete();
    }
}
