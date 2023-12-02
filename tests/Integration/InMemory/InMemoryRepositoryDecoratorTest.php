<?php

declare(strict_types=1);

namespace Zenstruck\Foundry\Tests\Integration\InMemory;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Address\StandardAddressFactory;
use Zenstruck\Foundry\Tests\Fixture\Factories\Entity\Contact\StandardContactFactory;
use Zenstruck\Foundry\Tests\Fixture\InMemory\InMemoryStandardAddressRepository;
use Zenstruck\Foundry\Tests\Fixture\InMemory\InMemoryStandardContactRepository;
use function Zenstruck\Foundry\InMemory\enable_in_memory;

final class InMemoryRepositoryDecoratorTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private InMemoryStandardAddressRepository $addressRepository;
    private InMemoryStandardContactRepository $contactRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        enable_in_memory();

        $this->addressRepository = self::getContainer()->get(InMemoryStandardAddressRepository::class);
        $this->contactRepository = self::getContainer()->get(InMemoryStandardContactRepository::class);

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @test
     */
    public function can_count(): void
    {
        StandardAddressFactory::createMany(3);

        self::assertCount(3, StandardAddressFactory::repository());
    }

    /**
     * @test
     */
    public function can_find_one_by_one_property(): void
    {
        [$address] = StandardAddressFactory::createSequence(
            [
                ['city' => 'foo'],
                ['city' => 'bar'],
            ]
        );

        self::assertSame($address, StandardAddressFactory::repository()->find(['city' => 'foo']));
    }

    /**
     * @test
     */
    public function can_find_one_by_multiple_property(): void
    {
        [,$address] = StandardAddressFactory::createSequence(
            [
                ['city' => 'foo', 'id' => 1],
                ['city' => 'bar', 'id' => 2],
            ]
        );

        self::assertSame($address, StandardAddressFactory::repository()->find(['city' => 'bar', 'id' => 2]));
    }

    /**
     * @test
     */
    public function can_find_or_create(): void
    {
        $address1 = StandardAddressFactory::findOrCreate(['city' => 'foo', 'id' => 1]);
        $address2 = StandardAddressFactory::findOrCreate(['city' => 'foo', 'id' => 1]);

        StandardAddressFactory::assert()->count(1);
        self::assertSame($address1, $address2);

        StandardAddressFactory::findOrCreate(['city' => 'foo', 'id' => 2]);
        StandardAddressFactory::assert()->count(2);
    }

    /**
     * @test
     */
    public function can_find_or_create_with_nested(): void
    {
        $contact1 = StandardContactFactory::findOrCreate(['name' => 'foo']);
        $contact2 = StandardContactFactory::findOrCreate(['name' => 'foo']);

        StandardContactFactory::assert()->count(1);
        StandardAddressFactory::assert()->count(1);

        self::assertSame($contact1, $contact2);
    }
}
