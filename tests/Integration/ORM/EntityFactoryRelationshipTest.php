<?php

/*
 * This file is part of the zenstruck/foundry package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Foundry\Tests\Integration\ORM;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class EntityFactoryRelationshipTest extends KernelTestCase
{
    use Factories, ResetDatabase;

    /**
     * @test
     */
    public function many_to_one(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function one_to_many(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function many_to_many_owning(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function many_to_many_inverse(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function one_to_one_owning(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function one_to_one_inverse(): void
    {
        $this->markTestIncomplete();
    }
}
