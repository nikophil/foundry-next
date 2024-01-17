<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Tests\Unit;

use Faker;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\UnitTestConfig;

use function Zenstruck\Foundry\faker;

final class FactoryTest extends TestCase
{
    use Factories;

    /**
     * @test
     */
    public function can_register_custom_faker(): void
    {
        $defaultFaker = faker();
        UnitTestConfig::configure(faker: Faker\Factory::create());

        $this->assertNotSame(faker(), $defaultFaker);
    }
}
